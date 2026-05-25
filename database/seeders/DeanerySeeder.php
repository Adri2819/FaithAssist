<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Ecclesiastes\Diocese;
use App\Models\User;
use App\Globals\Status;

class DeanerySeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (!$superadmin) {
            $this->command?->warn('No se encontro el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');
            return;
        }

        // Buscar la Diócesis de Tenancingo
        $diocese = Diocese::where('name', 'DIOCESIS DE TENANCINGO')->first();

        if (!$diocese) {
            $this->command->warn('No se encontró la Diócesis de Tenancingo. Ejecuta DioceseSeeder primero.');
            return;
        }

        Deanery::updateOrCreate(
            [
                'diocese_id' => $diocese->id,
                'name' => 'NUESTRA SENORA DE LA ASUNCION',
            ],
            [
                'status' => Status::ACTIVE,
                'created_by' => $superadmin->id,
                'updated_by' => $superadmin->id,
            ]
        );

        $this->command->info('Decanato creado exitosamente.');
    }
}
