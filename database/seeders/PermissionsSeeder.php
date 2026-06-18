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
            'modulos' => 'security',
            'permisos' => 'security',
            'estados' => 'regions',
            'municipios' => 'regions',
            'comunidades' => 'regions',
            'diocesis' => 'ecclesiastes',
            'decanato' => 'ecclesiastes',
            'parroquias' => 'ecclesiastes',
            'capillas' => 'ecclesiastes',
            'periodos' => 'operation',
            'periodo_movimientos' => 'operation',
            'niveles' => 'operation',
            'roles' => 'security',
            'usuarios' => 'security',
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

        foreach ([
            ['name' => 'municipios.scope.all', 'module_key' => 'regions', 'description' => 'Permite ver todos los municipios'],
            ['name' => 'comunidades.scope.all', 'module_key' => 'regions', 'description' => 'Permite ver todas las comunidades'],
            ['name' => 'parroquias.scope.all', 'module_key' => 'ecclesiastes', 'description' => 'Permite ver todas las parroquias'],
            ['name' => 'capillas.scope.all', 'module_key' => 'ecclesiastes', 'description' => 'Permite ver todas las capillas'],
        ] as $permission) {
            Permission::query()->updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $permission['description'],
                    'module_key' => $permission['module_key'],
                    'arg' => null,
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
