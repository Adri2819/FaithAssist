<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Regions\State;
use App\Globals\Status;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (!$superadmin) {
            $this->command?->warn('No se encontro el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');
            return;
        }

        $states = [
            [
                'name' => 'Estado de México',
                'short_name' => 'Edomex',
                'status' => Status::ACTIVE,
            ],
            [
                'name' => 'Ciudad de México',
                'short_name' => 'CDMX',
                'status' => Status::ACTIVE,
            ],
        ];

        foreach ($states as $state) {
            State::updateOrCreate(
                ['name' => $state['name']],
                [
                    'short_name' => $state['short_name'],
                    'status' => $state['status'],
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );
        }
    }
}
