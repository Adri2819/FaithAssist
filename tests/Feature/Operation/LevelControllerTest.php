<?php

namespace Tests\Feature\Operation;

use App\Models\Operation\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class LevelControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/niveles')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/niveles')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('niveles.read');

        $this->actingAs($user)
            ->postJson('/niveles', ['diocese_id' => $chain['diocese']->id, 'name' => 'X', 'status' => 'active'])
            ->assertForbidden();
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function test_global_user_sees_all_levels(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis B']);

        Level::query()->create(['diocese_id' => $chain1['diocese']->id, 'name' => 'Nivel A', 'status' => 'active']);
        Level::query()->create(['diocese_id' => $chain2['diocese']->id, 'name' => 'Nivel B', 'status' => 'active']);

        $user = $this->makeGlobalUser('niveles.read');

        $this->actingAs($user)->get('/niveles')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('levels.total', 2));
    }

    public function test_diocese_scoped_user_sees_only_own_levels(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis B']);

        Level::query()->create(['diocese_id' => $chain1['diocese']->id, 'name' => 'Nivel A', 'status' => 'active']);
        Level::query()->create(['diocese_id' => $chain2['diocese']->id, 'name' => 'Nivel B', 'status' => 'active']);

        $user = $this->makeDioceseUser($chain1['diocese'], 'niveles.read');

        $this->actingAs($user)->get('/niveles')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('levels.total', 1));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('niveles.read');

        $this->actingAs($user)->get('/niveles')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Operation/Levels/Index'));
    }

    public function test_store_creates_level_and_returns_201(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('niveles.create');

        $response = $this->actingAs($user)
            ->postJson('/niveles', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'NIVEL NUEVO',
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'NIVEL NUEVO');

        $this->assertDatabaseHas('levels', ['name' => 'NIVEL NUEVO']);
    }

    public function test_update_modifies_level_and_returns_200(): void
    {
        $chain = $this->createChain();
        $level = Level::query()->create(['diocese_id' => $chain['diocese']->id, 'name' => 'Original', 'status' => 'active']);
        $user = $this->makeGlobalUser('niveles.update');

        $response = $this->actingAs($user)
            ->putJson("/niveles/{$level->id}", [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'NIVEL ACTUALIZADO',
                'status' => 'active',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'NIVEL ACTUALIZADO');
    }

    public function test_destroy_soft_deletes_level_and_returns_200(): void
    {
        $chain = $this->createChain();
        $level = Level::query()->create(['diocese_id' => $chain['diocese']->id, 'name' => 'A Eliminar', 'status' => 'active']);
        $user = $this->makeGlobalUser('niveles.delete');

        $this->actingAs($user)
            ->deleteJson("/niveles/{$level->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('levels', ['id' => $level->id]);
    }

    public function test_name_must_be_unique_per_diocese(): void
    {
        $chain = $this->createChain();
        Level::query()->create(['diocese_id' => $chain['diocese']->id, 'name' => 'NIVEL DUPLICADO', 'status' => 'active']);
        $user = $this->makeGlobalUser('niveles.create');

        $this->actingAs($user)
            ->postJson('/niveles', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'NIVEL DUPLICADO',
                'status' => 'active',
            ])
            ->assertUnprocessable();
    }
}
