<?php

namespace Database\Seeders;

use App\Globals\Status;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Operation\Period;
use App\Models\Operation\PeriodMovement;
use App\Models\Operation\PeriodMovementType;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::query()->where('email', 'superadmin@faithassistqr.test')->first();

        if (! $superadmin) {
            $this->command?->warn('No se encontró el usuario Superadmin. Ejecuta UsersPerRoleSeeder primero.');

            return;
        }

        $preinscripciones = PeriodMovementType::where('name', 'PREINSCRIPCIONES')->first();
        $inscripciones    = PeriodMovementType::where('name', 'INSCRIPCIONES')->first();
        $reinscripciones  = PeriodMovementType::where('name', 'REINSCRIPCIONES')->first();

        if (! $preinscripciones || ! $inscripciones || ! $reinscripciones) {
            $this->command?->warn('No se encontraron los tipos de movimiento. Ejecuta PeriodMovementTypeSeeder primero.');

            return;
        }

        $dioceses = Diocese::all();

        foreach ($dioceses as $diocese) {
            // ── Periodo anterior (2023-2024) ──────────────────────────────
            $periodoAnterior = Period::updateOrCreate(
                ['diocese_id' => $diocese->id, 'name' => 'PERIODO 2023-2024'],
                [
                    'start_date' => '2023-01-16',
                    'end_date'   => '2023-11-30',
                    'years'      => '2023-2024',
                    'status'     => Status::COMPLETED,
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );

            $this->createMovements($periodoAnterior, $superadmin->id, [
                [
                    'type'       => $preinscripciones,
                    'status'     => Status::COMPLETED,
                    'start_date' => '2023-01-16',
                    'end_date'   => '2023-02-28',
                    'notes'      => 'Preinscripciones periodo 2023-2024.',
                ],
                [
                    'type'       => $inscripciones,
                    'status'     => Status::COMPLETED,
                    'start_date' => '2023-03-01',
                    'end_date'   => '2023-05-31',
                    'notes'      => 'Inscripciones periodo 2023-2024.',
                ],
                [
                    'type'       => $reinscripciones,
                    'status'     => Status::COMPLETED,
                    'start_date' => '2023-06-01',
                    'end_date'   => '2023-08-31',
                    'notes'      => 'Reinscripciones periodo 2023-2024.',
                ],
            ]);

            // ── Periodo actual (2024-2025) ────────────────────────────────
            $periodoActual = Period::updateOrCreate(
                ['diocese_id' => $diocese->id, 'name' => 'PERIODO 2024-2025'],
                [
                    'start_date' => '2024-01-15',
                    'end_date'   => '2024-11-30',
                    'years'      => '2024-2025',
                    'status'     => Status::IN_PROGRESS,
                    'created_by' => $superadmin->id,
                    'updated_by' => $superadmin->id,
                ]
            );

            $this->createMovements($periodoActual, $superadmin->id, [
                [
                    'type'       => $preinscripciones,
                    'status'     => Status::COMPLETED,
                    'start_date' => '2024-01-15',
                    'end_date'   => '2024-02-29',
                    'notes'      => 'Preinscripciones periodo 2024-2025.',
                ],
                [
                    'type'       => $inscripciones,
                    'status'     => Status::IN_PROGRESS,
                    'start_date' => '2024-03-01',
                    'end_date'   => '2024-05-31',
                    'notes'      => 'Inscripciones periodo 2024-2025.',
                ],
                [
                    'type'       => $reinscripciones,
                    'status'     => Status::PENDING,
                    'start_date' => '2024-06-01',
                    'end_date'   => '2024-08-31',
                    'notes'      => 'Reinscripciones periodo 2024-2025.',
                ],
            ]);
        }

        $this->command?->info('Periodos y movimientos creados exitosamente.');
    }

    private function createMovements(Period $period, int $userId, array $movements): void
    {
        foreach ($movements as $data) {
            PeriodMovement::updateOrCreate(
                [
                    'period_id'              => $period->id,
                    'period_movement_type_id' => $data['type']->id,
                ],
                [
                    'status'     => $data['status'],
                    'start_date' => $data['start_date'],
                    'end_date'   => $data['end_date'],
                    'notes'      => $data['notes'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );
        }
    }
}
