<?php

namespace Tests\Feature\Operation;

use App\Models\Operation\PeriodMovementType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class PeriodMovementTypeControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/tipos-movimientos-periodo')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/tipos-movimientos-periodo')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $user = $this->makeGlobalUser('tipos_movimientos_periodo.read');

        $this->actingAs($user)
            ->postJson('/tipos-movimientos-periodo', ['name' => 'X', 'status' => 'active'])
            ->assertForbidden();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('tipos_movimientos_periodo.read');

        $this->actingAs($user)->get('/tipos-movimientos-periodo')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Operation/PeriodMovementTypes/Index'));
    }

    public function test_store_creates_movement_type_and_returns_201(): void
    {
        $user = $this->makeGlobalUser('tipos_movimientos_periodo.create');

        $response = $this->actingAs($user)
            ->postJson('/tipos-movimientos-periodo', [
                'name'   => 'TIPO NUEVO',
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'TIPO NUEVO');

        $this->assertDatabaseHas('period_movement_types', ['name' => 'TIPO NUEVO']);
    }

    public function test_update_modifies_movement_type_and_returns_200(): void
    {
        $type = PeriodMovementType::query()->create(['name' => 'Original', 'status' => 'active']);
        $user = $this->makeGlobalUser('tipos_movimientos_periodo.update');

        $response = $this->actingAs($user)
            ->putJson("/tipos-movimientos-periodo/{$type->id}", [
                'name'   => 'TIPO ACTUALIZADO',
                'status' => 'inactive',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'TIPO ACTUALIZADO');
    }

    public function test_destroy_soft_deletes_movement_type_and_returns_200(): void
    {
        $type = PeriodMovementType::query()->create(['name' => 'A Eliminar', 'status' => 'active']);
        $user = $this->makeGlobalUser('tipos_movimientos_periodo.delete');

        $this->actingAs($user)
            ->deleteJson("/tipos-movimientos-periodo/{$type->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('period_movement_types', ['id' => $type->id]);
    }
}
