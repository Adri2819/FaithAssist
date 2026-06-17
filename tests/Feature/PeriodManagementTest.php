<?php

namespace Tests\Feature;

use App\Globals\Status;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Operation\Period;;
use App\Models\Regions\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PeriodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prevents_overlapping_periods_within_the_same_diocese(): void
    {
        $user = User::factory()->create();
        $diocese = $this->createDiocese();

        Period::query()->create([
            'diocese_id' => $diocese->id,
            'name' => 'PERIODO 2025-2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-06-30',
            'years' => '2025-2026',
            'status' => Status::COMPLETED,
        ]);

        $this->permission('periodos.create');
        $user->givePermissionTo('periodos.create');

        $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $diocese->id,
                'name' => 'PERIODO SOLAPADO',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'status' => Status::UPCOMING,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_it_allows_updating_a_period_without_conflicting_with_itself(): void
    {
        $user = User::factory()->create();
        $diocese = $this->createDiocese();
        $period = Period::query()->create([
            'diocese_id' => $diocese->id,
            'name' => 'PERIODO 2026-2027',
            'start_date' => '2026-08-01',
            'end_date' => '2027-06-30',
            'years' => '2026-2027',
            'status' => Status::UPCOMING,
        ]);

        $this->permission('periodos.update');
        $user->givePermissionTo('periodos.update');

        $this->actingAs($user)
            ->putJson("/periodos/{$period->id}", [
                'diocese_id' => $diocese->id,
                'name' => 'PERIODO 2026-2027',
                'start_date' => '2026-08-01',
                'end_date' => '2027-06-30',
                'status' => Status::UPCOMING,
            ])
            ->assertOk()
            ->assertJsonPath('data.years', '2026-2027');
    }

    public function test_it_allows_the_same_date_range_in_different_dioceses(): void
    {
        $user = User::factory()->create();
        $firstDiocese = $this->createDiocese('DIOCESIS NORTE');
        $secondDiocese = $this->createDiocese('DIOCESIS SUR', 'Estado Sur');

        Period::query()->create([
            'diocese_id' => $firstDiocese->id,
            'name' => 'PERIODO NORTE',
            'start_date' => '2026-08-01',
            'end_date' => '2027-06-30',
            'years' => '2026-2027',
            'status' => Status::UPCOMING,
        ]);

        $this->permission('periodos.create');
        $user->givePermissionTo('periodos.create');

        $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $secondDiocese->id,
                'name' => 'PERIODO SUR',
                'start_date' => '2026-08-01',
                'end_date' => '2027-06-30',
                'status' => Status::UPCOMING,
            ])
            ->assertCreated();
    }

    public function test_it_only_allows_one_period_in_progress_per_diocese(): void
    {
        $user = User::factory()->create();
        $diocese = $this->createDiocese();

        Period::query()->create([
            'diocese_id' => $diocese->id,
            'name' => 'PERIODO ACTUAL',
            'start_date' => '2026-08-01',
            'end_date' => '2027-06-30',
            'years' => '2026-2027',
            'status' => Status::IN_PROGRESS,
        ]);

        $this->permission('periodos.create');
        $user->givePermissionTo('periodos.create');

        $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $diocese->id,
                'name' => 'PERIODO SIGUIENTE',
                'start_date' => '2027-08-01',
                'end_date' => '2028-06-30',
                'status' => Status::IN_PROGRESS,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    private function createDiocese(string $name = 'DIOCESIS CENTRAL', string $stateName = 'Estado Central'): Diocese
    {
        $state = State::query()->create([
            'name' => $stateName,
            'short_name' => substr($stateName, 0, 3),
            'status' => Status::ACTIVE,
        ]);

        return Diocese::query()->create([
            'state_id' => $state->id,
            'name' => $name,
            'bishop' => 'OBISPO CENTRAL',
            'status' => Status::ACTIVE,
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
