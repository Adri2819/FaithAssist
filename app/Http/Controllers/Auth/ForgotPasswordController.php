<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmEmailRequest;
use App\Http\Requests\Auth\ConfirmPhoneRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Models\Lada;
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
    private const SESSION_KEY = 'password_recovery';

    /**
     * Paso 1: Mostrar formulario para ingresar correo
     */
    public function showEmailStep(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPasswordEmail', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
        ]);
    }

    /**
     * Paso 1: Confirmar correo y detectar usuario + teléfono
     */
    public function confirmEmail(ConfirmEmailRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No se encontró una cuenta con este correo electrónico.',
            ]);
        }

        if (! filled($user->whatsapp_phone)) {
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta no tiene un número de teléfono registrado.',
            ]);
        }

        // Detectar el teléfono registrado en la cuenta
        $registeredPhone = $user->whatsapp_phone;
        $maskedPhone = $this->maskPhone($registeredPhone);

        // Iniciar sesión de recuperación
        $this->putState($request, [
            'started_at' => now()->timestamp,
            'email' => $validated['email'],
            'user_id' => $user->id,
            'phone_normalized' => $registeredPhone,
            'masked_phone' => $maskedPhone,
            'code_verified_at' => null,
        ]);

        return redirect()
            ->route('password.recovery.phone.show');
    }

    /**
     * Paso 2: Mostrar teléfono detectado para confirmación
     */
    public function showPhoneStep(Request $request): Response
    {
        $state = $this->state($request);
        $registeredPhone = (string) $this->state($request, 'phone_normalized', '');
        $maskedPhone = (string) $this->state($request, 'masked_phone', '');

        return Inertia::render('Auth/ForgotPasswordPhone', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
            'email' => (string) $this->state($request, 'email', ''),
            'countryCodes' => $this->countryCodes(),
            'countryCode' => old(
                'whatsapp_country_code',
                Lada::defaultCode()
            ),
            'phone' => old('whatsapp_phone', ''),
            'registeredPhoneLast4' => $this->last4Digits($registeredPhone),
        ]);
    }

    /**
     * Paso 2: Confirmar teléfono y enviar código
     */
    public function confirmPhone(ConfirmPhoneRequest $request, MetaWhatsAppService $metaWhatsApp): RedirectResponse
    {
        $validated = $request->validated();

        $userId = (int) $this->state($request, 'user_id', 0);

        if ($userId === 0) {
            throw ValidationException::withMessages([
                'whatsapp_phone' => 'Sesión inválida. Por favor, intenta de nuevo.',
            ]);
        }

        $user = User::query()->find($userId);

        if (! $user) {
            throw ValidationException::withMessages([
                'whatsapp_phone' => 'Usuario no encontrado.',
            ]);
        }

        $countryCode = preg_replace('/\D/', '', $validated['whatsapp_country_code']) ?: Lada::defaultCode();
        $phoneLocal = preg_replace('/\D/', '', $validated['whatsapp_phone']) ?: '';
        $normalizedPhone = Lada::normalizeLocal($phoneLocal, $countryCode);

        if (! $normalizedPhone) {
            throw ValidationException::withMessages([
                'whatsapp_phone' => ['El número de teléfono no es válido.'],
            ]);
        }

        $rateLimitKey = sprintf('password-reset:%d|%s', $user->id, $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            throw ValidationException::withMessages([
                'whatsapp_phone' => "Espera {$minutes} minuto(s) antes de solicitar otro código.",
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

        try {
            $metaWhatsApp->sendPasswordResetCode(
                $normalizedPhone,
                (string) config('app.name', 'FaithAssist QR'),
                $code
            );
        } catch (Throwable $exception) {
            $errorMessage = $exception->getMessage();

            Log::error('No se pudo enviar código de recuperación', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $errorMessage,
            ]);

            if (str_contains($errorMessage, 'Recipient phone number not in allowed list') || str_contains($errorMessage, '131030')) {
                throw ValidationException::withMessages([
                    'whatsapp_phone' => 'Este número de teléfono no está disponible para recibir el código en este momento. Verifica el número e intenta de nuevo.',
                ]);
            }

            throw ValidationException::withMessages([
                'whatsapp_phone' => 'No se pudo enviar el código. Por favor, intenta de nuevo en unos minutos.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 600);

        // Actualizar el teléfono en la sesión con el que se envió el código
        $this->putState($request, [
            ...$this->state($request),
            'country_code' => $countryCode,
            'phone_local' => $phoneLocal,
            'phone_normalized' => $normalizedPhone,
            'masked_phone' => $this->maskPhone($normalizedPhone),
        ]);

        return redirect()
            ->route('password.recovery.code.show')
            ->with('status', 'Código enviado correctamente.');
    }

    /**
     * Paso 3: Mostrar formulario para ingresar código
     */
    public function showCodeStep(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPasswordCode', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
            'maskedPhone' => (string) $this->state($request, 'masked_phone', ''),
        ]);
    }

    /**
     * Paso 3: Validar código ingresado
     */
    public function verifyCode(VerifyPasswordResetCodeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $userId = (int) $this->state($request, 'user_id', 0);
        $resetCode = PasswordResetWhatsappCode::query()->where('user_id', $userId)->first();

        if (! $resetCode || $resetCode->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        if ($resetCode->attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => 'Superaste el número de intentos permitidos. Solicita un nuevo código.',
            ]);
        }

        if (! Hash::check($validated['code'], $resetCode->code_hash)) {
            $resetCode->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        $this->putState($request, [
            ...$this->state($request),
            'code_verified_at' => now()->timestamp,
        ]);

        return redirect()
            ->route('password.recovery.reset.show')
            ->with('status', 'Código validado correctamente.');
    }

    /**
     * Paso 4: Mostrar formulario para nueva contraseña
     */
    public function showResetStep(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPasswordReset', [
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
            'maskedPhone' => (string) $this->state($request, 'masked_phone', ''),
        ]);
    }

    /**
     * Paso 4: Actualizar contraseña
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $userId = (int) $this->state($request, 'user_id', 0);

        User::query()
            ->whereKey($userId)
            ->update([
                'password' => Hash::make($validated['password']),
            ]);

        PasswordResetWhatsappCode::query()->where('user_id', $userId)->delete();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('login')->with('status', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
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

    private function state(Request $request, ?string $key = null, mixed $default = null): mixed
    {
        $state = $request->session()->get(self::SESSION_KEY, []);

        if (! is_array($state)) {
            $state = [];
        }

        if ($key === null) {
            return $state;
        }

        return $state[$key] ?? $default;
    }

    private function putState(Request $request, array $state): void
    {
        $request->session()->put(self::SESSION_KEY, $state);
    }

    private function countryCodes(): array
    {
        return Lada::options();
    }

    private function last4Digits(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return null;
        }

        return substr($digits, -4);
    }
}

