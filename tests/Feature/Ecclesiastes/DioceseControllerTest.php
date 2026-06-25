<?php

namespace Tests\Feature\Ecclesiastes;

use App\Models\Ecclesiastes\Diocese;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class DioceseControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/diocesis')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/diocesis')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('diocesis.read');

        $this->actingAs($user)
            ->postJson('/diocesis', ['state_id' => $chain['state']->id, 'name' => 'X', 'status' => 'active'])
            ->assertForbidden();
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function test_global_user_sees_all_dioceses(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis B']);

        $user = $this->makeGlobalUser('diocesis.read');

        $this->actingAs($user)->get('/diocesis')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('dioceses.total', 2));
    }

    public function test_diocese_scoped_user_sees_only_own_diocese(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['diocese']->update(['name' => 'Diócesis Foranea']);

        $user = $this->makeDioceseUser($chain1['diocese'], 'diocesis.read');

        $this->actingAs($user)->get('/diocesis')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('dioceses.total', 1));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('diocesis.read');

        $this->actingAs($user)->get('/diocesis')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Ecclesiastes/Dioceses/Index'));
    }

    public function test_store_creates_diocese_and_returns_201(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('diocesis.create');

        $response = $this->actingAs($user)
            ->postJson('/diocesis', [
                'state_id' => $chain['state']->id,
                'name'     => 'DIÓCESIS NUEVA',
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'DIÓCESIS NUEVA');

        $this->assertDatabaseHas('dioceses', ['name' => 'DIÓCESIS NUEVA']);
    }

    public function test_update_modifies_diocese_and_returns_200(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('diocesis.update');

        $response = $this->actingAs($user)
            ->putJson("/diocesis/{$chain['diocese']->id}", [
                'state_id' => $chain['state']->id,
                'name'     => 'DIÓCESIS ACTUALIZADA',
                'status' => 'active',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'DIÓCESIS ACTUALIZADA');
    }

    public function test_destroy_soft_deletes_diocese_and_returns_200(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('diocesis.delete');

        $this->actingAs($user)
            ->deleteJson("/diocesis/{$chain['diocese']->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('dioceses', ['id' => $chain['diocese']->id]);
    }
}
