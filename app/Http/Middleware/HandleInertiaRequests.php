<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn (): array => [
                'user' => $this->buildAuthUserPayload($request->user()),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name') ?? [],
                'roles' => $request->user()?->getRoleNames() ?? [],
                'scope' => $this->buildScopePayload($request->user()),
            ],
        ];
    }

    private function buildAuthUserPayload(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $user->loadMissing([
            'profile',
            'municipality:id,name',
            'church:id,name',
        ]);

        $profileName = trim(collect([
            $user->profile?->name,
            $user->profile?->paterno,
            $user->profile?->materno,
        ])->filter()->implode(' '));

        $displayName = $profileName !== '' ? $profileName : $user->name;

        return [
            'id' => $user->id,
            'email' => $user->email,
            'display_name' => $displayName,
            'initials' => $this->resolveInitials($user),
            'photo_url' => $user->profile_photo_path,
            'profile' => $user->profile ? [
                'name' => $user->profile->name,
                'paterno' => $user->profile->paterno,
                'materno' => $user->profile->materno,
            ] : null,
            'municipality' => $user->municipality ? [
                'id' => $user->municipality->id,
                'name' => $user->municipality->name,
            ] : null,
            'church' => $user->church ? [
                'id' => $user->church->id,
                'name' => $user->church->name,
            ] : null,
        ];
    }

    private function buildScopePayload(?User $user): array
    {
        if (! $user) {
            return [
                'municipality_id' => null,
                'church_id' => null,
            ];
        }

        return [
            'municipality_id' => $user->municipality_id,
            'church_id' => $user->church_id,
        ];
    }

    private function resolveInitials(User $user): string
    {
        $firstName = trim((string) $user->profile?->name);
        $firstLastname = trim((string) $user->profile?->paterno);

        if ($firstName !== '' && $firstLastname !== '') {
            return mb_strtoupper(mb_substr($firstName, 0, 1).mb_substr($firstLastname, 0, 1));
        }

        $chunks = preg_split('/\s+/', trim($user->name)) ?: [];

        if (count($chunks) >= 2) {
            return mb_strtoupper(mb_substr($chunks[0], 0, 1).mb_substr($chunks[1], 0, 1));
        }

        return mb_strtoupper(mb_substr((string) ($chunks[0] ?? 'U'), 0, 1));
    }
}
