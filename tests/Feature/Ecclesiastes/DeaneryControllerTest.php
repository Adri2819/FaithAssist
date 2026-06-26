<?php

namespace Tests\Feature\Ecclesiastes;

use App\Models\Ecclesiastes\Deanery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class DeaneryControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/decanatos')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/decanatos')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('decanato.read');

        $this->actingAs($user)
            ->postJson('/decanatos', ['diocese_id' => $chain['diocese']->id, 'name' => 'X', 'status' => 'active'])
            ->assertForbidden();
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function test_global_user_sees_all_deaneries(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['deanery']->update(['name' => 'Decanato B']);

        $user = $this->makeGlobalUser('decanato.read');

        $this->actingAs($user)->get('/decanatos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('deaneries.total', 2));
    }

    public function test_diocese_scoped_user_sees_only_own_deaneries(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['deanery']->update(['name' => 'Decanato Foraneo']);

        $user = $this->makeDioceseUser($chain1['diocese'], 'decanato.read');

        $this->actingAs($user)->get('/decanatos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('deaneries.total', 1));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('decanato.read');

        $this->actingAs($user)->get('/decanatos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Ecclesiastes/Deaneries/Index'));
    }

    public function test_store_creates_deanery_and_returns_201(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('decanato.create');

        $response = $this->actingAs($user)
            ->postJson('/decanatos', [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'DECANATO NUEVO',
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'DECANATO NUEVO');

        $this->assertDatabaseHas('deaneries', ['name' => 'DECANATO NUEVO']);
    }

    public function test_update_modifies_deanery_and_returns_200(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('decanato.update');

        $response = $this->actingAs($user)
            ->putJson("/decanatos/{$chain['deanery']->id}", [
                'diocese_id' => $chain['diocese']->id,
                'name'       => 'DECANATO ACTUALIZADO',
                'status' => 'active',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'DECANATO ACTUALIZADO');
    }

    public function test_destroy_soft_deletes_deanery_and_returns_200(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('decanato.delete');

        $this->actingAs($user)
            ->deleteJson("/decanatos/{$chain['deanery']->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('deaneries', ['id' => $chain['deanery']->id]);
    }
}
