<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetWhatsappCode;
use App\Models\User;
use App\Services\MetaWhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ForgotPasswordController extends Controller
{
    private const WHATSAPP_COUNTRY_CODES = [
        '521' => 'MX (+521)',
        '52' => 'MX (+52)',
        '1' => 'US/CA (+1)',
        '57' => 'CO (+57)',
        '51' => 'PE (+51)',
        '54' => 'AR (+54)',
        '56' => 'CL (+56)',
        '593' => 'EC (+593)',
        '503' => 'SV (+503)',
        '502' => 'GT (+502)',
        '504' => 'HN (+504)',
        '505' => 'NI (+505)',
        '506' => 'CR (+506)',
        '507' => 'PA (+507)',
        '58' => 'VE (+58)',
    ];

    public function show(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
            'maskedPhone' => $request->session()->get('masked_phone'),
            'recoveryPhone' => old('whatsapp_phone', (string) $request->session()->get('recovery_phone', '')),
            'recoveryCountryCode' => old('whatsapp_country_code', (string) $request->session()->get('recovery_country_code', config('services.whatsapp.default_country_code', '521'))),
            'countryCodes' => $this->getCountryCodes(),
        ]);
    }

    public function sendCode(Request $request, MetaWhatsAppService $metaWhatsApp): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_country_code' => ['required', 'string', 'max:5', 'regex:/^[0-9]{1,4}$/'],
            'whatsapp_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9\s\-\(\)]{10,15}$/'],
        ]);

        $normalizedPhone = $this->normalizePhone($validated['whatsapp_phone'], $validated['whatsapp_country_code']);
        $user = User::query()->where('whatsapp_phone', $normalizedPhone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'whatsapp_phone' => 'No encontramos una cuenta con ese numero de WhatsApp.',
            ]);
        }

        if (! $user->whatsapp_phone) {
            throw ValidationException::withMessages([
                'whatsapp_phone' => 'Tu cuenta no tiene un numero de WhatsApp registrado. Contacta a mesa de ayuda.',
            ]);
        }

        $rateLimitKey = sprintf('password-reset-whatsapp:%d|%s', $user->id, $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'whatsapp_phone' => "Espera {$minutes} minuto(s) antes de solicitar otro codigo.",
            ]);
        }

        $code = (string) random_int(100000, 999999);

        DB::transaction(function () use ($user, $code): void {
            PasswordResetWhatsappCode::query()->where('user_id', $user->id)->delete();

            PasswordResetWhatsappCode::query()->create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        $appName = (string) config('app.name', 'FaithAssist QR');

        try {
            $metaWhatsApp->sendPasswordResetCode($user->whatsapp_phone, $appName, $code);
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();

            Log::error('No se pudo enviar codigo de recuperacion por WhatsApp', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $errorMessage,
            ]);

            if (str_contains($errorMessage, 'Recipient phone number not in allowed list') || str_contains($errorMessage, '131030')) {
                throw ValidationException::withMessages([
                    'whatsapp_phone' => 'Este numero aun no esta autorizado en Meta WhatsApp para pruebas. Agregalo a la lista de destinatarios permitidos y vuelve a intentar.',
                ]);
            }

            throw ValidationException::withMessages([
                'whatsapp_phone' => 'No se pudo enviar el codigo por WhatsApp. Intenta de nuevo en unos minutos.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 600);

        return redirect()->route('password.forgot.form')->with([
            'status' => 'Te enviamos un codigo de 6 digitos por WhatsApp para restablecer tu contrasena.',
            'masked_phone' => $this->maskPhone($user->whatsapp_phone),
            'recovery_phone' => $user->whatsapp_phone,
            'recovery_country_code' => $validated['whatsapp_country_code'],
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_country_code' => ['required', 'string', 'max:5', 'regex:/^[0-9]{1,4}$/'],
            'whatsapp_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9\s\-\(\)]{10,15}$/'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $normalizedPhone = $this->normalizePhone($validated['whatsapp_phone'], $validated['whatsapp_country_code']);
        $user = User::query()->where('whatsapp_phone', $normalizedPhone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => 'El codigo es invalido o ha expirado.',
            ]);
        }

        $resetCode = PasswordResetWhatsappCode::query()->where('user_id', $user->id)->first();

        if (! $resetCode || $resetCode->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'El codigo es invalido o ha expirado.',
            ]);
        }

        if ($resetCode->attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => 'Superaste el numero de intentos permitidos. Solicita un nuevo codigo.',
            ]);
        }

        if (! Hash::check($validated['code'], $resetCode->code_hash)) {
            $resetCode->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'El codigo es invalido o ha expirado.',
            ]);
        }

        User::query()
            ->whereKey($user->id)
            ->update([
                'password' => Hash::make($validated['password']),
            ]);

        $resetCode->delete();

        return redirect()->route('login')->with('status', 'Contrasena actualizada correctamente. Ya puedes iniciar sesion.');
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return $phone;
        }

        $visible = substr($digits, -4);

        return '*** *** '.$visible;
    }

    private function getCountryCodes(): array
    {
        return collect(self::WHATSAPP_COUNTRY_CODES)
            ->map(fn (string $label, string $code): array => [
                'value' => $code,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    private function normalizePhone(string $phone, string $countryCode): string
    {
        $clean = preg_replace('/\D/', '', trim($phone)) ?? '';

        if ($clean === '') {
            return '';
        }

        $countryCode = preg_replace('/\D/', '', trim($countryCode)) ?: (string) config('services.whatsapp.default_country_code', '521');

        if (strlen($clean) === 10) {
            return '+'.$countryCode.$clean;
        }

        return '+'.$clean;
    }
}
