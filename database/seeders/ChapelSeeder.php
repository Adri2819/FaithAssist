<?php

namespace Database\Seeders;

use App\Globals\Status;
use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChapelSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (! $superadmin) {
            $this->command?->warn('No se encontró el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');

            return;
        }

/**
 * Format: ['church' => string, 'name' => string, 'municipality' => string, 'community' => string, 'address' => string]
 */
        $chapels = [
            // ── Coatepec Harinas ──────────────────────────────────────────
            [
                'church'       => 'PARROQUIA NUESTRA SENORA DE LA ASUNCION',
                'name'         => 'CAPILLA SANTA ANA',
                'municipality' => 'Coatepec Harinas',
                'community'    => 'SANTA ANA',
                'address'      => 'Santa Ana, Coatepec Harinas, Estado de México',
            ],
            [
                'church'       => 'PARROQUIA NUESTRA SENORA DE LA ASUNCION',
                'name'         => 'CAPILLA SAN MIGUEL',
                'municipality' => 'Coatepec Harinas',
                'community'    => 'SAN MIGUEL',
                'address'      => 'San Miguel, Coatepec Harinas, Estado de México',
            ],

            // ── Tenancingo ────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN FRANCISCO DE ASIS',
                'name'         => 'CAPILLA SANTIAGUITO',
                'municipality' => 'Tenancingo',
                'community'    => 'SANTIAGUITO',
                'address'      => 'Santiaguito, Tenancingo, Estado de México',
            ],
            [
                'church'       => 'PARROQUIA SAN FRANCISCO DE ASIS',
                'name'         => 'CAPILLA LA JOYA',
                'municipality' => 'Tenancingo',
                'community'    => 'LA JOYA',
                'address'      => 'La Joya, Tenancingo, Estado de México',
            ],

            // ── Villa Guerrero ────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA CRISTO REY',
                'name'         => 'CAPILLA SAN FRANCISCO',
                'municipality' => 'Villa Guerrero',
                'community'    => 'SAN FRANCISCO',
                'address'      => 'San Francisco, Villa Guerrero, Estado de México',
            ],
            [
                'church'       => 'PARROQUIA CRISTO REY',
                'name'         => 'CAPILLA SAN PABLO',
                'municipality' => 'Villa Guerrero',
                'community'    => 'SAN PABLO',
                'address'      => 'San Pablo, Villa Guerrero, Estado de México',
            ],

            // ── Zumpahuacan ───────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN MIGUEL ARCANGEL DE ZUMPAHUACAN',
                'name'         => 'CAPILLA SAN LUCAS',
                'municipality' => 'Zumpahuacan',
                'community'    => 'SAN LUCAS',
                'address'      => 'San Lucas, Zumpahuacan, Estado de México',
            ],
            [
                'church'       => 'PARROQUIA SAN MIGUEL ARCANGEL DE ZUMPAHUACAN',
                'name'         => 'CAPILLA LAS PALMAS',
                'municipality' => 'Zumpahuacan',
                'community'    => 'LAS PALMAS',
                'address'      => 'Las Palmas, Zumpahuacan, Estado de México',
            ],

            // ── Cuernavaca ────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA NUESTRA SENORA DE LA ASUNCION CUERNAVACA',
                'name'         => 'CAPILLA PALMIRA',
                'municipality' => 'Cuernavaca',
                'community'    => 'PALMIRA',
                'address'      => 'Palmira, Cuernavaca, Morelos',
            ],
            [
                'church'       => 'PARROQUIA NUESTRA SENORA DE LA ASUNCION CUERNAVACA',
                'name'         => 'CAPILLA LOMAS DE CORTÉS',
                'municipality' => 'Cuernavaca',
                'community'    => 'LOMAS DE CORTÉS',
                'address'      => 'Lomas de Cortés, Cuernavaca, Morelos',
            ],

            // ── Jiutepec ──────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN JUAN BAUTISTA JIUTEPEC',
                'name'         => 'CAPILLA TEJALPA',
                'municipality' => 'Jiutepec',
                'community'    => 'TEJALPA',
                'address'      => 'Tejalpa, Jiutepec, Morelos',
            ],
            [
                'church'       => 'PARROQUIA SAN JUAN BAUTISTA JIUTEPEC',
                'name'         => 'CAPILLA PROGRESO',
                'municipality' => 'Jiutepec',
                'community'    => 'PROGRESO',
                'address'      => 'Progreso, Jiutepec, Morelos',
            ],

            // ── Temixco ───────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN MARCOS EVANGELISTA TEMIXCO',
                'name'         => 'CAPILLA ACATLIPA',
                'municipality' => 'Temixco',
                'community'    => 'ACATLIPA',
                'address'      => 'Acatlipa, Temixco, Morelos',
            ],
            [
                'church'       => 'PARROQUIA SAN MARCOS EVANGELISTA TEMIXCO',
                'name'         => 'CAPILLA CUENTEPEC',
                'municipality' => 'Temixco',
                'community'    => 'CUENTEPEC',
                'address'      => 'Cuentepec, Temixco, Morelos',
            ],

            // ── Yautepec ──────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SANTA CATARINA MARTIR YAUTEPEC',
                'name'         => 'CAPILLA COCOYOC',
                'municipality' => 'Yautepec',
                'community'    => 'COCOYOC',
                'address'      => 'Cocoyoc, Yautepec, Morelos',
            ],
            [
                'church'       => 'PARROQUIA SANTA CATARINA MARTIR YAUTEPEC',
                'name'         => 'CAPILLA OAXTEPEC',
                'municipality' => 'Yautepec',
                'community'    => 'OAXTEPEC',
                'address'      => 'Oaxtepec, Yautepec, Morelos',
            ],

            // ── Chilpancingo ──────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN MATEO APOSTOL CHILPANCINGO',
                'name'         => 'CAPILLA XOCHIPALA',
                'municipality' => 'Chilpancingo de los Bravo',
                'community'    => 'XOCHIPALA',
                'address'      => 'Xochipala, Chilpancingo, Guerrero',
            ],
            [
                'church'       => 'PARROQUIA SAN MATEO APOSTOL CHILPANCINGO',
                'name'         => 'CAPILLA LAS PETAQUILLAS',
                'municipality' => 'Chilpancingo de los Bravo',
                'community'    => 'LAS PETAQUILLAS',
                'address'      => 'Las Petaquillas, Chilpancingo, Guerrero',
            ],

            // ── Tixtla ────────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN MARTIN OBISPO TIXTLA',
                'name'         => 'CAPILLA CUEXCONTITLAN',
                'municipality' => 'Tixtla de Guerrero',
                'community'    => 'CUEXCONTITLÁN',
                'address'      => 'Cuexcontitlán, Tixtla, Guerrero',
            ],
            [
                'church'       => 'PARROQUIA SAN MARTIN OBISPO TIXTLA',
                'name'         => 'CAPILLA ACTEOPAN',
                'municipality' => 'Tixtla de Guerrero',
                'community'    => 'ACTEOPAN',
                'address'      => 'Acteopan, Tixtla, Guerrero',
            ],

            // ── Chilapa ───────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SAN MIGUEL ARCANGEL CHILAPA',
                'name'         => 'CAPILLA AHUIXTLA',
                'municipality' => 'Chilapa de Álvarez',
                'community'    => 'AHUIXTLA',
                'address'      => 'Ahuixtla, Chilapa, Guerrero',
            ],
            [
                'church'       => 'PARROQUIA SAN MIGUEL ARCANGEL CHILAPA',
                'name'         => 'CAPILLA IXCATEOPAN',
                'municipality' => 'Chilapa de Álvarez',
                'community'    => 'IXCATEOPAN',
                'address'      => 'Ixcateopan, Chilapa, Guerrero',
            ],

            // ── Mochitlán ─────────────────────────────────────────────────
            [
                'church'       => 'PARROQUIA SANTIAGO APOSTOL MOCHITLAN',
                'name'         => 'CAPILLA SAN MIGUEL MOCHITLAN',
                'municipality' => 'Mochitlán',
                'community'    => 'SAN MIGUEL',
                'address'      => 'San Miguel, Mochitlán, Guerrero',
            ],
            [
                'church'       => 'PARROQUIA SANTIAGO APOSTOL MOCHITLAN',
                'name'         => 'CAPILLA LA CAÑADA',
                'municipality' => 'Mochitlán',
                'community'    => 'LA CAÑADA',
                'address'      => 'La Cañada, Mochitlán, Guerrero',
            ],
        ];

        foreach ($chapels as $data) {
            $church    = Church::where('name', $data['church'])->first();
            $community = Community::whereHas('municipality', fn ($q) => $q->where('name', $data['municipality']))
                ->where('name', $data['community'])
                ->first();

            if (! $church) {
                $this->command?->warn("Parroquia no encontrada: {$data['church']}.");
                continue;
            }

            Chapel::updateOrCreate(
                ['church_id' => $church->id, 'name' => $data['name']],
                [
                    'address'      => $data['address'],
                    'community_id' => $community?->id,
                    'status'       => Status::ACTIVE,
                    'created_by'   => $superadmin->id,
                    'updated_by'   => $superadmin->id,
                ]
            );
        }

        $this->command?->info('Capillas creadas exitosamente.');
    }
}
