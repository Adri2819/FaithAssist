<?php

namespace Tests\Feature\Operation;

use App\Models\Operation\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class PeriodControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/periodos')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/periodos')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('periodos.read');

        $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'P1',
                'start_date' => '2025-01-01',
                'end_date'   => '2025-12-31',
                'status' => 'upcoming',
            ])
            ->assertForbidden();
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function test_global_user_sees_all_periods(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis B']);

        Period::query()->create(['diocese_id' => $chain1['diocese']->id, 'name' => 'Periodo A', 'start_date' => '2025-01-01', 'end_date' => '2025-06-30', 'years' => '2025', 'status' => 'completed']);
        Period::query()->create(['diocese_id' => $chain2['diocese']->id, 'name' => 'Periodo B', 'start_date' => '2025-01-01', 'end_date' => '2025-06-30', 'years' => '2025', 'status' => 'completed']);

        $user = $this->makeGlobalUser('periodos.read');

        $this->actingAs($user)->get('/periodos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('periods.total', 2));
    }

    public function test_diocese_scoped_user_sees_only_own_periods(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis B']);

        Period::query()->create(['diocese_id' => $chain1['diocese']->id, 'name' => 'Periodo A', 'start_date' => '2025-01-01', 'end_date' => '2025-06-30', 'years' => '2025', 'status' => 'completed']);
        Period::query()->create(['diocese_id' => $chain2['diocese']->id, 'name' => 'Periodo B', 'start_date' => '2025-01-01', 'end_date' => '2025-06-30', 'years' => '2025', 'status' => 'completed']);

        $user = $this->makeDioceseUser($chain1['diocese'], 'periodos.read');

        $this->actingAs($user)->get('/periodos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('periods.total', 1));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('periodos.read');

        $this->actingAs($user)->get('/periodos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Operation/Periods/Index'));
    }

    public function test_store_creates_period_and_returns_201_with_single_year(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('periodos.create');

        $response = $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'PERIODO 2025',
                'start_date' => '2025-01-01',
                'end_date'   => '2025-12-31',
                'status' => 'upcoming',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'PERIODO 2025')
            ->assertJsonPath('data.years', '2025');

        $this->assertDatabaseHas('periods', ['name' => 'PERIODO 2025', 'years' => '2025']);
    }

    public function test_store_sets_multi_year_span_when_dates_cross_years(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('periodos.create');

        $response = $this->actingAs($user)
            ->postJson('/periodos', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'PERIODO BIANUAL',
                'start_date' => '2024-09-01',
                'end_date'   => '2025-06-30',
                'status' => 'completed',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.years', '2024-2025');
    }

    public function test_update_modifies_period_and_recalculates_years(): void
    {
        $chain = $this->createChain();
        $period = Period::query()->create(['diocese_id' => $chain['diocese']->id, 'name' => 'Original', 'start_date' => '2024-01-01', 'end_date' => '2024-12-31', 'years' => '2024', 'status' => 'completed']);
        $user = $this->makeGlobalUser('periodos.update');

        $response = $this->actingAs($user)
            ->putJson("/periodos/{$period->id}", [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'ACTUALIZADO',
                'start_date' => '2025-06-01',
                'end_date'   => '2026-05-31',
                'status' => 'upcoming',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.years', '2025-2026');
    }

    public function test_destroy_soft_deletes_period_and_returns_200(): void
    {
        $chain = $this->createChain();
        $period = Period::query()->create(['diocese_id' => $chain['diocese']->id, 'name' => 'A Eliminar', 'start_date' => '2023-01-01', 'end_date' => '2023-12-31', 'years' => '2023', 'status' => 'completed']);
        $user = $this->makeGlobalUser('periodos.delete');

        $this->actingAs($user)
            ->deleteJson("/periodos/{$period->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('periods', ['id' => $period->id]);
    }
}
