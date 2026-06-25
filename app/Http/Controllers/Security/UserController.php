<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\UserRequest;
use App\Models\Ecclesiastes\Church;
use App\Models\Lada;
use App\Models\Profile;
use App\Models\Regions\Municipality;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $users = User::query()
            ->with(['profile', 'roles', 'assignedMunicipalities:id,name', 'assignedChurches:id,name'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('profile', fn ($p) => $p->where('name', 'like', "%{$search}%")
                    ->orWhere('paterno', 'like', "%{$search}%")
                    ->orWhere('materno', 'like', "%{$search}%"))
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($user) => [
                'id' => $user->id,
                'email' => $user->email,
                'photo_url' => $user->profile_photo_path,
                'initials' => $this->resolveInitials($user),
                'full_name' => $this->resolveFullName($user),
                'role' => $user->roles->first()?->name,
                'municipalities' => $user->assignedMunicipalities->pluck('name')->values()->all(),
                'churches' => $user->assignedChurches->pluck('name')->values()->all(),
            ]);

        return Inertia::render('Security/Users/Index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Security/Users/Form', [
            'user' => null,
            ...$this->formOptions(),
            'selectedRole' => null,
            'selectedPermissions' => [],
            'selectedMunicipalities' => [],
            'selectedChurches' => [],
            'selectedCountryCode' => Lada::defaultCode(),
            'countryCodes' => $this->getCountryCodes(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => trim("{$request->name} {$request->paterno} ".($request->materno ?? '')),
            'email' => $request->email,
            'whatsapp_phone' => $this->normalizeWhatsAppPhone(
                $request->input('whatsapp_phone'),
                $request->input('whatsapp_country_code')
            ),
            'password' => Hash::make($request->password),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'paterno' => $request->paterno,
            'materno' => $request->materno,
        ]);

        if ($request->role_id) {
            $user->syncRoles([$request->role_id]);
        }

        $permIds = array_filter((array) $request->input('permissions', []));
        $user->syncPermissions(
            empty($permIds) ? [] : Permission::whereIn('id', $permIds)->get()
        );
        $user->assignedMunicipalities()->sync($request->input('municipality_ids', []));
        $user->assignedChurches()->sync($request->input('church_ids', []));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        $usuario->loadMissing([
            'profile',
            'roles',
            'assignedMunicipalities:id,name',
            'assignedChurches:id,name',
        ]);

        return Inertia::render('Security/Users/Form', [
            'user' => [
                'id' => $usuario->id,
                'email' => $usuario->email,
                'whatsapp_phone' => $this->localWhatsAppPhone($usuario->whatsapp_phone),
                'whatsapp_country_code' => $this->whatsappCountryCode($usuario->whatsapp_phone),
                'name' => $usuario->profile?->name ?? '',
                'paterno' => $usuario->profile?->paterno ?? '',
                'materno' => $usuario->profile?->materno ?? '',
                'photo_url' => $usuario->profile_photo_path,
                'initials' => $this->resolveInitials($usuario),
                'full_name' => $this->resolveFullName($usuario),
                'created_at' => $usuario->created_at?->format('d/m/Y'),
            ],
            ...$this->formOptions(),
            'selectedRole' => $usuario->roles->first()?->id,
            'selectedPermissions' => $usuario->getAllPermissions()->pluck('id')->toArray(),
            'selectedMunicipalities' => $usuario->assignedMunicipalities->pluck('id')->toArray(),
            'selectedChurches' => $usuario->assignedChurches->pluck('id')->toArray(),
            'selectedCountryCode' => $this->whatsappCountryCode($usuario->whatsapp_phone),
            'countryCodes' => $this->getCountryCodes(),
        ]);
    }

    public function update(UserRequest $request, User $usuario): RedirectResponse
    {
        $usuario->update([
            'email' => $request->email,
            'whatsapp_phone' => $this->normalizeWhatsAppPhone(
                $request->input('whatsapp_phone'),
                $request->input('whatsapp_country_code')
            ),
            'name' => trim("{$request->name} {$request->paterno} ".($request->materno ?? '')),
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        $profile = $usuario->profile ?? new Profile(['user_id' => $usuario->id]);
        $profile->fill([
            'name' => $request->name,
            'paterno' => $request->paterno,
            'materno' => $request->materno,
        ])->save();

        $usuario->syncRoles($request->role_id ? [$request->role_id] : []);

        $permIds = array_filter((array) $request->input('permissions', []));
        $usuario->syncPermissions(
            empty($permIds) ? [] : Permission::whereIn('id', $permIds)->get()
        );
        $usuario->assignedMunicipalities()->sync($request->input('municipality_ids', []));
        $usuario->assignedChurches()->sync($request->input('church_ids', []));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    private function getGroupedPermissions(): array
    {
        return Permission::query()
            ->orderBy('module_key')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'module_key'])
            ->groupBy('module_key')
            ->map(fn ($perms, $key) => [
                'key' => $key,
                'label' => $this->getModuleLabel($key),
                'permissions' => $perms->values(),
            ])
            ->values()
            ->toArray();
    }

    private function formOptions(): array
    {
        return [
            'roles' => Role::orderBy('name')->get(['id', 'name', 'description']),
            'permissionGroups' => $this->getGroupedPermissions(),
            'municipalities' => Municipality::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']),
            'churches' => Church::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    private function getCountryCodes(): array
    {
        return Lada::options();
    }

    private function getModuleLabel(string $key): string
    {
        return match ($key) {
            'regions' => 'Regiones',
            'ecclesiastes' => 'Eclesiasticos',
            'security' => 'Seguridad',
            'whatsapp' => 'WhatsApp',
            'operation' => 'Operación',
            default => ucfirst($key),
        };
    }

    private function resolveFullName(User $user): string
    {
        $p = $user->profile;
        if ($p) {
            return trim(collect([$p->name, $p->paterno, $p->materno])->filter()->implode(' '));
        }

        return $user->name;
    }

    private function resolveInitials(User $user): string
    {
        $p = $user->profile;
        if ($p && $p->name && $p->paterno) {
            return mb_strtoupper(mb_substr($p->name, 0, 1).mb_substr($p->paterno, 0, 1));
        }
        $chunks = preg_split('/\s+/', trim($user->name)) ?: [];
        if (count($chunks) >= 2) {
            return mb_strtoupper(mb_substr($chunks[0], 0, 1).mb_substr($chunks[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($chunks[0] ?? 'U', 0, 1));
    }

    private function normalizeWhatsAppPhone(?string $phone, ?string $countryCode = null): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $clean = preg_replace('/\D/', '', $phone) ?? '';

        if ($clean === '') {
            return null;
        }

        $countryCode = preg_replace('/\D/', '', (string) ($countryCode ?: Lada::defaultCode())) ?: Lada::defaultCode();

        if (strlen($clean) === 10) {
            return '+'.$countryCode.$clean;
        }

        if (str_starts_with($clean, $countryCode) && strlen($clean) > 10) {
            return '+'.$clean;
        }

        return '+'.$clean;
    }

    private function localWhatsAppPhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $countryCode = $this->whatsappCountryCode($phone);
        $digits = preg_replace('/\D/', '', $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, $countryCode) && strlen($digits) > strlen($countryCode)) {
            return substr($digits, strlen($countryCode));
        }

        if (str_starts_with($digits, '52') && strlen($digits) > 10) {
            return substr($digits, -10);
        }

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    private function whatsappCountryCode(?string $phone): string
    {
        return Lada::detectCountryCode($phone);
    }
}
