<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UsersPerRoleSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Usuario Superadmin',
                'email' => 'superadmin@faithassistqr.test',
                'password' => 'password',
                'role' => 'Superadmin',
            ],
            [
                'name' => 'Usuario Coordinador',
                'email' => 'coordinador@faithassistqr.test',
                'password' => 'password',
                'role' => 'Coordinador',
            ],
            [
                'name' => 'Usuario Catequista',
                'email' => 'catequista@faithassistqr.test',
                'password' => 'password',
                'role' => 'Catequista',
            ],
            [
                'name' => 'Usuario Capturista',
                'email' => 'capturista@faithassistqr.test',
                'password' => 'password',
                'role' => 'Capturista',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'email_verified_at' => Carbon::now(),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
