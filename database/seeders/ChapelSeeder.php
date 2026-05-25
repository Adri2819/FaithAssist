<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\User;
use App\Globals\Status;

class ChapelSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (!$superadmin) {
            $this->command?->warn('No se encontro el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');
            return;
        }

        // Buscar la parroquia principal
        $church = Church::where('name', 'PARROQUIA NUESTRA SENORA DE LA ASUNCION')->first();

        if (!$church) {
            $this->command->warn('No se encontró la parroquia principal. Ejecuta ChurchSeeder primero.');
            return;
        }

        // Buscar algunas comunidades para crear capillas
        $santaAna = Community::where('name', 'SANTA ANA')->first();
        $sanMiguel = Community::where('name', 'SAN MIGUEL')->first();

        if ($santaAna) {
            Chapel::updateOrCreate(
                [
                    'church_id' => $church->id,
                    'name' => 'CAPILLA SANTA ANA',
                ],
                [
                    'address' => 'Santa Ana, Coatepec Harinas, Estado de México',
                    'community_id' => $santaAna->id,
                    'status' => Status::ACTIVE,
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );
        }

        if ($sanMiguel) {
            Chapel::updateOrCreate(
                [
                    'church_id' => $church->id,
                    'name' => 'CAPILLA SAN MIGUEL',
                ],
                [
                    'address' => 'San Miguel, Coatepec Harinas, Estado de México',
                    'community_id' => $sanMiguel->id,
                    'status' => Status::ACTIVE,
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );
        }

        $this->command->info('Capillas creadas exitosamente.');
    }
}
