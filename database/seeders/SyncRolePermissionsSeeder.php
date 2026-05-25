<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncRolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allActions = ['create', 'read', 'update', 'delete', 'show'];

        $superadmin = Role::query()->firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => 'web',
        ]);

        $coordinador = Role::query()->firstOrCreate([
            'name' => 'Coordinador',
            'guard_name' => 'web',
        ]);

        $catequista = Role::query()->firstOrCreate([
            'name' => 'Catequista',
            'guard_name' => 'web',
        ]);

        $capturista = Role::query()->firstOrCreate([
            'name' => 'Capturista',
            'guard_name' => 'web',
        ]);

        $superadmin->syncPermissions(Permission::all());

        $coordinador->syncPermissions(
            $this->permissionsForModules(['municipios', 'comunidades', 'parroquias', 'capillas'], $allActions)
        );

        $catequista->syncPermissions(
            $this->permissionsForModules(['capillas'], $allActions)
        );

        $capturista->syncPermissions(['capillas.show']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, string>
     */
    private function permissionsForModules(array $modules, array $actions): array
    {
        $permissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }
}
