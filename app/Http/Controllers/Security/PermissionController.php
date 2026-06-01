<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\PermissionRequest;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permiso');
    }

    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $permissions = Permission::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('module_key', 'like', "%{$search}%"))
            ->orderBy('module_key')
            ->orderBy('name')
            ->paginate(15, ['id', 'name', 'description', 'module_key'])
            ->withQueryString();

        $modules = Module::query()
            ->orderBy('name')
            ->get(['key', 'name']);

        return Inertia::render('Security/Permissions/Index', [
            'permissions' => $permissions,
            'modules'     => $modules,
            'search'      => $search,
        ]);
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            ...$request->validated(),
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'data'    => $permission->only(['id', 'name', 'description', 'module_key']),
            'message' => 'Permiso creado correctamente.',
        ], 201);
    }

    public function update(PermissionRequest $request, Permission $permiso): JsonResponse
    {
        $permiso->update($request->validated());

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'data'    => $permiso->fresh()->only(['id', 'name', 'description', 'module_key']),
            'message' => 'Permiso actualizado correctamente.',
        ]);
    }

    public function destroy(Permission $permiso): JsonResponse
    {
        $permiso->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Permiso eliminado correctamente.',
        ]);
    }
}
