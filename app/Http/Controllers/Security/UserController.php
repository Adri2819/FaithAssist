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
        $editor = $request->user();

        $users = User::query()
            ->with(['profile', 'roles', 'municipality:id,name', 'church:id,name'])
            ->when(
                $editor->municipality_id !== null || $editor->church_id !== null,
                function ($q) use ($editor) {
                    $q->where(function ($scope) use ($editor) {
                        if ($editor->municipality_id !== null) {
                            $scope->where('municipality_id', $editor->municipality_id);
                        }
                        if ($editor->church_id !== null) {
                            $method = $editor->municipality_id !== null ? 'orWhere' : 'where';
                            $scope->{$method}('church_id', $editor->church_id);
                        }
                    });
                }
            )
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
                'municipality' => $user->municipality?->name,
                'church' => $user->church?->name,
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
            'selectedMunicipality' => null,
            'selectedChurch' => null,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $editor = $request->user();

        $user = User::create([
            'name' => trim("{$request->name} {$request->paterno} ".($request->materno ?? '')),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'municipality_id' => $request->input('municipality_id'),
            'church_id' => $request->input('church_id'),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'paterno' => $request->paterno,
            'materno' => $request->materno,
        ]);

        $allowedRoleIds = $this->getAllowedRoles($editor)->pluck('id');
        $roleId = $request->role_id && $allowedRoleIds->contains($request->role_id)
            ? $request->role_id
            : null;

        if ($roleId) {
            $user->syncRoles([$roleId]);
        }

        $editorPermissionIds = $editor->getAllPermissions()->pluck('id');
        $submittedIds = collect(array_filter((array) $request->input('permissions', [])));
        $safeIds = $submittedIds->intersect($editorPermissionIds)->all();

        $user->syncPermissions(empty($safeIds) ? [] : Permission::whereIn('id', $safeIds)->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): Response
    {
        $usuario->loadMissing([
            'profile',
            'roles',
            'municipality:id,name',
            'church:id,name',
        ]);

        $editor = auth()->user();
        $editorPermissionIds = $editor->getAllPermissions()->pluck('id');

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
            'selectedPermissions' => $usuario->getAllPermissions()
                ->pluck('id')
                ->intersect($editorPermissionIds)
                ->values()
                ->toArray(),
            'selectedMunicipality' => $usuario->municipality_id,
            'selectedChurch' => $usuario->church_id,
        ]);
    }

    public function update(UserRequest $request, User $usuario): RedirectResponse
    {
        $editor = $request->user();

        $usuario->update([
            'email' => $request->email,
            'name' => trim("{$request->name} {$request->paterno} ".($request->materno ?? '')),
            'municipality_id' => $request->input('municipality_id'),
            'church_id' => $request->input('church_id'),
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

        $allowedRoleIds = $this->getAllowedRoles($editor)->pluck('id');
        $roleId = $request->role_id && $allowedRoleIds->contains($request->role_id)
            ? $request->role_id
            : null;

        $usuario->syncRoles($roleId ? [$roleId] : []);

        $editorPermissionIds = $editor->getAllPermissions()->pluck('id');

        // Preserve permissions the target has that the editor cannot manage
        $preservedPerms = $usuario->getAllPermissions()
            ->filter(fn (Permission $p) => ! $editorPermissionIds->contains($p->id));

        // Grant only submitted permissions within the editor's scope
        $submittedIds = collect(array_filter((array) $request->input('permissions', [])));
        $grantedPerms = Permission::whereIn('id',
            $submittedIds->intersect($editorPermissionIds)->all()
        )->get();

        $finalPerms = $preservedPerms->merge($grantedPerms)->unique('id');
        $usuario->syncPermissions($finalPerms);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    private function getGroupedPermissions(User $editor): array
    {
        $editorPermissionIds = $editor->getAllPermissions()->pluck('id');

        return Permission::query()
            ->whereIn('id', $editorPermissionIds)
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

    private function getAllowedRoles(User $editor): \Illuminate\Support\Collection
    {
        $editorPermissionIds = $editor->getAllPermissions()->pluck('id');

        return Role::with('permissions:id')
            ->orderBy('name')
            ->get(['id', 'name', 'description'])
            ->filter(function (Role $role) use ($editorPermissionIds): bool {
                return $role->permissions->every(
                    fn ($p) => $editorPermissionIds->contains($p->id)
                );
            })
            ->values();
    }

    private function formOptions(): array
    {
        $editor = auth()->user();

        return [
            'roles' => $this->getAllowedRoles($editor),
            'permissionGroups' => $this->getGroupedPermissions($editor),
            'municipalities' => Municipality::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']),
            'churches' => Church::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'municipality_id']),
        ];
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
}
