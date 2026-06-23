<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordRecoverySession
{
    private const SESSION_KEY = 'password_recovery';

    private const MAX_DURATION_SECONDS = 900;

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        $state = $this->state($request);

        // Paso 1 (Email): Inicia la sesión
        if ($this->isEmailStep($routeName) && ! isset($state['started_at'])) {
            $state['started_at'] = now()->timestamp;
            $this->putState($request, $state);
        }

        // Verificar expiración
        if (isset($state['started_at']) && $this->isExpired((int) $state['started_at'])) {
            $this->clearState($request);

            return redirect()
                ->route('password.recovery.email.show')
                ->with('error', 'La sesión de recuperación expiró. Inicia nuevamente.');
        }

        // Paso 2 (Phone): Requiere email confirmado (user_id presente)
        if ($this->isPhoneStep($routeName) && ! isset($state['user_id'])) {
            return redirect()
                ->route('password.recovery.email.show')
                ->with('error', 'Primero confirma tu correo electrónico.');
        }

        // Paso 3 (Code): Requiere teléfono confirmado (user_id presente)
        if ($this->isCodeStep($routeName) && ! isset($state['user_id'])) {
            return redirect()
                ->route('password.recovery.email.show')
                ->with('error', 'Primero confirma tu correo electrónico.');
        }

        // Paso 4 (Reset): Requiere código verificado
        if ($this->isResetStep($routeName) && ! isset($state['code_verified_at'])) {
            return redirect()
                ->route('password.recovery.code.show')
                ->with('error', 'Primero valida el código de verificación.');
        }

        return $next($request);
    }

    private function state(Request $request): array
    {
        $state = $request->session()->get(self::SESSION_KEY, []);

        return is_array($state) ? $state : [];
    }

    private function putState(Request $request, array $state): void
    {
        $request->session()->put(self::SESSION_KEY, $state);
    }

    private function clearState(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    private function isEmailStep(string $routeName): bool
    {
        return str_starts_with($routeName, 'password.recovery.email.');
    }

    private function isPhoneStep(string $routeName): bool
    {
        return str_starts_with($routeName, 'password.recovery.phone.');
    }

    private function isCodeStep(string $routeName): bool
    {
        return str_starts_with($routeName, 'password.recovery.code.');
    }

    private function isResetStep(string $routeName): bool
    {
        return str_starts_with($routeName, 'password.recovery.reset.');
    }

    private function isExpired(int $startedAt): bool
    {
        return now()->timestamp - $startedAt > self::MAX_DURATION_SECONDS;
    }
}
