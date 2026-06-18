<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\UserRequest;
use App\Models\Ecclesiastes\Church;
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
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => trim("{$request->name} {$request->paterno} ".($request->materno ?? '')),
            'email' => $request->email,
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
            'selectedPermissions' => $usuario->permissions()->pluck('permissions.id')->toArray(),
            'selectedMunicipalities' => $usuario->assignedMunicipalities->pluck('id')->toArray(),
            'selectedChurches' => $usuario->assignedChurches->pluck('id')->toArray(),
        ]);
    }

    public function update(UserRequest $request, User $usuario): RedirectResponse
    {
        $usuario->update([
            'email' => $request->email,
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

    private function getModuleLabel(string $key): string
    {
        return match ($key) {
            'core' => 'Core',
            'regions' => 'Regiones',
            'ecclesiastes' => 'Eclesiasticos',
            'security' => 'Seguridad',
            'whatsapp' => 'WhatsApp',
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
}
