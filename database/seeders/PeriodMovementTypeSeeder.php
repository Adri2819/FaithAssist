<?php

namespace Database\Seeders;

use App\Globals\Status;
use App\Models\Operation\PeriodMovementType;
use Illuminate\Database\Seeder;

class PeriodMovementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'PREINSCRIPCIONES',
                'description' => 'Movimientos previos al registro definitivo del periodo.',
                'status' => Status::ACTIVE,
            ],
            [
                'name' => 'INSCRIPCIONES',
                'description' => 'Movimientos de registro principal del periodo.',
                'status' => Status::ACTIVE,
            ],
            [
                'name' => 'REINSCRIPCIONES',
                'description' => 'Movimientos de reinscripción dentro del periodo.',
                'status' => Status::ACTIVE,
            ],
        ];

        foreach ($types as $type) {
            PeriodMovementType::query()->updateOrCreate(
                ['name' => $type['name']],
                $type,
            );
        }
    }
}
