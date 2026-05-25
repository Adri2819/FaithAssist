<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            [
                'name' => 'Superadmin',
                'description' => 'Acceso total al sistema',
            ],
            [
                'name' => 'Coordinador',
                'description' => 'Gestiona modulos asignados',
            ],
            [
                'name' => 'Catequista',
                'description' => 'Gestion operativa de capillas',
            ],
            [
                'name' => 'Capturista',
                'description' => 'Consulta de informacion en capillas',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::query()->updateOrCreate(
                [
                    'name' => $roleData['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $roleData['description'],
                ]
            );
        }
    }
}
