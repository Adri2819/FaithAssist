<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Municipality;
use App\Models\Ecclesiastes\Deanery;
use App\Models\User;
use App\Globals\Status;

class ChurchSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (!$superadmin) {
            $this->command?->warn('No se encontro el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');
            return;
        }

        // Buscar el municipio de Coatepec Harinas
        $municipality = Municipality::where('name', 'Coatepec Harinas')->first();

        if (!$municipality) {
            $this->command->warn('No se encontró el municipio de Coatepec Harinas. Ejecuta MunicipalitySeeder primero.');
            return;
        }

        // Buscar el decanato
        $deanery = Deanery::where('name', 'NUESTRA SENORA DE LA ASUNCION')->first();

        if (!$deanery) {
            $this->command->warn('No se encontró el decanato. Ejecuta DeanerySeeder primero.');
            return;
        }

        // Crear la parroquia principal
        Church::updateOrCreate(
            ['name' => 'PARROQUIA NUESTRA SENORA DE LA ASUNCION'],
            [
                'alias' => 'Parroquia Principal',
                'email' => 'parroquia@asuncion.com',
                'phone' => '7141234567',
                'address' => 'Centro, Coatepec Harinas, Estado de México',
                'municipality_id' => $municipality->id,
                'deanery_id' => $deanery->id,
                'status' => Status::ACTIVE,
                'created_by' => $superadmin->id,
                'updated_by' => $superadmin->id,
            ]
        );

        $this->command->info('Parroquia creada exitosamente.');
    }
}
