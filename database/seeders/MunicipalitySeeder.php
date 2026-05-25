<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Regions\Municipality;
use App\Models\Regions\State;
use App\Models\Ecclesiastes\Diocese;
use App\Models\User;
use App\Globals\Status;

class MunicipalitySeeder extends Seeder
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

        // Buscar la Diócesis de Tenancingo
        $diocese = Diocese::where('name', 'DIOCESIS DE TENANCINGO')->first();

        if (!$diocese) {
            $this->command->warn('No se encontró la Diócesis de Tenancingo. Ejecuta DioceseSeeder primero.');
            return;
        }

        // Crear municipio de Coatepec Harinas
        Municipality::updateOrCreate(
            ['name' => 'Coatepec Harinas'],
            [
                'state_id' => $state->id,
                'diocese_id' => $diocese->id,
                'status' => Status::ACTIVE,
                'created_by' => $superadmin->id,
                'updated_by' => $superadmin->id,
            ]
        );

        $this->command->info('Municipio creado exitosamente.');
    }
}
