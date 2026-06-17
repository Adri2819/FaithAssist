<?php

namespace Tests\Feature;

use App\Globals\MovStatus;
use App\Globals\Status;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Operation\PeriodMovement;
use App\Models\Operation\Period;
use App\Models\Regions\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MovementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_movements_outside_the_period_dates(): void
    {
        $user = User::factory()->create();
        $period = $this->createPeriod();

        $this->permission('movimientos.create');
        $user->givePermissionTo('movimientos.create');

        $this->actingAs($user)
            ->postJson('/movimientos', [
                'period_id' => $period->id,
                'type' => MovStatus::ENROLLMENTS,
                'status' => Status::PENDING,
                'effective_date' => '2028-01-01',
                'notes' => 'Fuera de rango',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['effective_date']);
    }

    public function test_it_persists_movements_with_valid_period_dates(): void
    {
        $user = User::factory()->create();
        $period = $this->createPeriod();

        $this->permission('movimientos.create');
        $user->givePermissionTo('movimientos.create');

        $this->actingAs($user)
            ->postJson('/movimientos', [
                'period_id' => $period->id,
                'type' => MovStatus::PRE_ENROLLMENTS,
                'status' => Status::PENDING,
                'effective_date' => '2026-09-15',
                'notes' => 'Captura inicial',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('movements', [
            'period_id' => $period->id,
            'type' => MovStatus::PRE_ENROLLMENTS,
            'status' => Status::PENDING,
            'effective_date' => '2026-09-15',
        ]);
    }

    public function test_it_updates_movement_status_and_type(): void
    {
        $user = User::factory()->create();
        $period = $this->createPeriod();
        $movement = Movement::query()->create([
            'period_id' => $period->id,
            'type' => MovStatus::PRE_ENROLLMENTS,
            'status' => Status::PENDING,
            'effective_date' => '2026-09-15',
            'notes' => 'Pendiente',
        ]);

        $this->permission('movimientos.update');
        $user->givePermissionTo('movimientos.update');

        $this->actingAs($user)
            ->putJson("/movimientos/{$movement->id}", [
                'period_id' => $period->id,
                'type' => MovStatus::RE_ENROLLMENTS,
                'status' => Status::COMPLETED,
                'effective_date' => '2026-10-01',
                'notes' => 'Confirmado',
            ])
            ->assertOk()
            ->assertJsonPath('data.type', MovStatus::RE_ENROLLMENTS)
            ->assertJsonPath('data.status', Status::COMPLETED);
    }

    public function test_it_lists_movements_in_the_inertia_page(): void
    {
        $user = User::factory()->create();
        $period = $this->createPeriod();

        Movement::query()->create([
            'period_id' => $period->id,
            'type' => MovStatus::ENROLLMENTS,
            'status' => Status::IN_PROGRESS,
            'effective_date' => '2026-09-20',
            'notes' => 'Seguimiento',
        ]);

        $this->permission('movimientos.read');
        $user->givePermissionTo('movimientos.read');

        $this->actingAs($user)
            ->get('/movimientos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ecclesiastes/Movements/Index')
                ->has('movements.data', 1)
                ->has('periods', 1)
                ->where('movements.data.0.type', MovStatus::ENROLLMENTS)
                ->where('movements.data.0.status', Status::IN_PROGRESS)
            );
    }

    private function createPeriod(): Period
    {
        $state = State::query()->create([
            'name' => 'Estado Movimientos',
            'short_name' => 'MOV',
            'status' => Status::ACTIVE,
        ]);

        $diocese = Diocese::query()->create([
            'state_id' => $state->id,
            'name' => 'DIOCESIS DE MOVIMIENTOS',
            'bishop' => 'OBISPO MOV',
            'status' => Status::ACTIVE,
        ]);

        return Period::query()->create([
            'diocese_id' => $diocese->id,
            'name' => 'PERIODO 2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-06-30',
            'years' => '2026-2027',
            'status' => Status::IN_PROGRESS,
        ]);
    }

    private function permission(string $name): Permission
    {
        return Permission::query()->firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            [
                'description' => $name,
                'module_key' => 'ecclesiastes',
            ]
        );
    }
}
