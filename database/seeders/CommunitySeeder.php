<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\User;
use App\Globals\Status;

class CommunitySeeder extends Seeder
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

        $communities = [
            "ACUITLAPILCO",
            "CAPULIN REDONDO",
            "COCHISQUILA",
            "COLONIA GUADALUPE",
            "CEDRITO",
            "EL POTRERITO",
            "EL REYNOSO",
            "HUAYANALCO",
            "IXTLAHUACA",
            "LA JAVIELA",
            "LA LOMA",
            "LA PRESA",
            "LAS JARAS",
            "PIEDRAS ANCHAS",
            "SAN ANTONIO",
            "SAN FELIPE",
            "SAN FRANCISCO",
            "SAN JOSÉ",
            "SAN LUIS",
            "SAN MARTÍN",
            "SAN MIGUEL",
            "SAN PABLO",
            "SAN PEDRO",
            "SANTA ANA",
            "SANTIAGUITO",
            "SANTO NIÑO",
            "TECOLOTEPEC",
            "TEOCOTITLA",
            "CENTRO",
            "ANALCO",
            "ZACANGUILLO",
            "ZACATONES",
        ];

        foreach ($communities as $name) {
            Community::updateOrCreate(
                [
                    'municipality_id' => $municipality->id,
                    'name' => $name,
                ],
                [
                    'status' => Status::ACTIVE,
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );
        }

        $this->command->info('Comunidades creadas exitosamente.');
    }
}
