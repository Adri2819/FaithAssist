<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Regions\State;
use App\Models\User;
use App\Globals\Status;

class DioceseSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (!$superadmin) {
            $this->command?->warn('No se encontro el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');
            return;
        }

        // Buscar el Estado de México
        $state = State::where('name', 'Estado de México')->first();

        if (!$state) {
            $this->command->warn('No se encontró el Estado de México. Ejecuta StateSeeder primero.');
            return;
        }

        Diocese::updateOrCreate(
            ['name' => 'DIOCESIS DE TENANCINGO'],
            [
                'state_id' => $state->id,
                'bishop' => 'MONS. VICTOR CARABES CHAVEZ',
                'status' => Status::ACTIVE,
                'created_by' => $superadmin->id,
                'updated_by' => $superadmin->id,
            ]
        );

        $this->command->info('Diócesis creada exitosamente.');
    }
}
