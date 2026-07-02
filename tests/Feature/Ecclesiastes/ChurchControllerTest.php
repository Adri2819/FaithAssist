<?php

namespace Tests\Feature\Ecclesiastes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class ChurchControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/parroquias')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/parroquias')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.read');

        $this->actingAs($user)
            ->post('/parroquias', [
                'municipality_id' => $chain['municipality']->id,
                'name' => 'X',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    /**
     * ChurchPolicy::create requires scope.all — even with parroquias.create,
     * a diocese-scoped user without scope.all is denied.
     */
    public function test_scoped_user_without_scope_all_cannot_create(): void
    {
        $chain = $this->createChain();
        $user = $this->makeDioceseUser($chain['diocese'], 'parroquias.create');

        $this->actingAs($user)
            ->post('/parroquias', [
                'municipality_id' => $chain['municipality']->id,
                'name' => 'NUEVA',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    // ── Scope ─────────────────────────────────────────────────────────────────

    public function test_global_user_sees_all_churches(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['church']->update(['name' => 'Parroquia B']);

        $user = $this->makeGlobalUser('parroquias.read');

        $this->actingAs($user)->get('/parroquias')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('churches.total', 2));
    }

    public function test_church_scoped_user_sees_only_own_church(): void
    {
        $chain1 = $this->createChain();
        $chain2 = $this->createChain();
        $chain2['church']->update(['name' => 'Parroquia Foranea']);

        $user = $this->makeChurchUser($chain1['diocese'], $chain1['deanery'], $chain1['church'], 'parroquias.read');

        $this->actingAs($user)->get('/parroquias')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('churches.total', 1));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('parroquias.read');

        $this->actingAs($user)->get('/parroquias')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Ecclesiastes/Churches/Index'));
    }

    public function test_index_exposes_full_scope_access_for_create_button(): void
    {
        $user = $this->makeGlobalUser('parroquias.read', 'parroquias.create', 'parroquias.scope.all');

        $this->actingAs($user)->get('/parroquias')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Ecclesiastes/Churches/Index')
                ->where('auth.scope.full_access.parroquias', true));
    }

    public function test_create_returns_inertia_response(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.create', 'parroquias.scope.all');

        $this->actingAs($user)->get('/parroquias/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Ecclesiastes/Churches/Form')
                ->where('church', null)
                ->where('municipalities.0.id', $chain['municipality']->id)
                ->where('deaneries.0.id', $chain['deanery']->id));
    }

    public function test_edit_returns_inertia_response(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.update');

        $this->actingAs($user)->get("/parroquias/{$chain['church']->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Ecclesiastes/Churches/Form')
                ->where('church.id', $chain['church']->id));
    }

    public function test_store_creates_church_and_redirects(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.create', 'parroquias.scope.all');

        $response = $this->actingAs($user)
            ->post('/parroquias', [
                'municipality_id' => $chain['municipality']->id,
                'deanery_id' => $chain['deanery']->id,
                'name' => 'PARROQUIA NUEVA',
                'status' => 'active',
            ]);

        $response->assertRedirect('/parroquias');

        $this->assertDatabaseHas('churches', ['name' => 'PARROQUIA NUEVA']);
    }

    public function test_update_modifies_church_and_redirects(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.update');

        $response = $this->actingAs($user)
            ->put("/parroquias/{$chain['church']->id}", [
                'municipality_id' => $chain['municipality']->id,
                'name' => 'PARROQUIA ACTUALIZADA',
                'status' => 'active',
            ]);

        $response->assertRedirect('/parroquias');
        $this->assertDatabaseHas('churches', [
            'id' => $chain['church']->id,
            'name' => 'PARROQUIA ACTUALIZADA',
        ]);
    }

    public function test_destroy_soft_deletes_church_and_returns_200(): void
    {
        $chain = $this->createChain();
        $user = $this->makeGlobalUser('parroquias.delete');

        $this->actingAs($user)
            ->deleteJson("/parroquias/{$chain['church']->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('churches', ['id' => $chain['church']->id]);
    }
}
