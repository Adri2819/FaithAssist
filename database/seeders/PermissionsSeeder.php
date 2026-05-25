<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = [
            'estados' => 'regions',
            'municipios' => 'regions',
            'comunidades' => 'regions',
            'diocesis' => 'ecclesiastes',
            'decanato' => 'ecclesiastes',
            'parroquias' => 'ecclesiastes',
            'capillas' => 'ecclesiastes',
        ];

        $actions = ['create', 'read', 'update', 'delete', 'show'];

        foreach ($modules as $module => $moduleKey) {
            foreach ($actions as $action) {
                Permission::query()->updateOrCreate(
                    [
                        'name' => "{$module}.{$action}",
                        'guard_name' => 'web',
                    ],
                    [
                        'description' => "Permite {$action} en {$module}",
                        'module_key' => $moduleKey,
                        'arg' => null,
                    ]
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
