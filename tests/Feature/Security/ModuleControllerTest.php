<?php

namespace Tests\Feature\Security;

use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\ControllerTestHelpers;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use ControllerTestHelpers, RefreshDatabase;

    // ── Authorization ─────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_index(): void
    {
        $this->get('/modulos')->assertRedirect('/login');
    }

    public function test_user_without_read_permission_gets_403_on_index(): void
    {
        $user = $this->makeGlobalUser();
        $this->actingAs($user)->get('/modulos')->assertForbidden();
    }

    public function test_user_without_create_permission_gets_403_on_store(): void
    {
        $user = $this->makeGlobalUser('modulos.read');

        $this->actingAs($user)
            ->postJson('/modulos', ['name' => 'X', 'description' => 'Desc', 'key' => 'x'])
            ->assertForbidden();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function test_index_returns_inertia_response(): void
    {
        $user = $this->makeGlobalUser('modulos.read');

        $this->actingAs($user)->get('/modulos')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Security/Modules/Index'));
    }

    public function test_store_creates_module_and_returns_201(): void
    {
        $user = $this->makeGlobalUser('modulos.create');

        $response = $this->actingAs($user)
            ->postJson('/modulos', [
                'name'        => 'MODULO NUEVO',
                'description' => 'Descripción del módulo',
                'key'         => 'modulo_nuevo',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'MODULO NUEVO');

        $this->assertDatabaseHas('modules', ['name' => 'MODULO NUEVO', 'key' => 'modulo_nuevo']);
    }

    public function test_update_modifies_module_and_returns_200(): void
    {
        $module = Module::query()->create(['name' => 'Original', 'description' => 'Desc', 'key' => 'original']);
        $user = $this->makeGlobalUser('modulos.update');

        $response = $this->actingAs($user)
            ->putJson("/modulos/{$module->id}", [
                'name'        => 'MODULO ACTUALIZADO',
                'description' => 'Nueva descripción',
                'key'         => 'modulo_actualizado',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'MODULO ACTUALIZADO');
    }

    public function test_destroy_soft_deletes_module_and_returns_200(): void
    {
        $module = Module::query()->create(['name' => 'A Eliminar', 'description' => 'Desc', 'key' => 'a_eliminar']);
        $user = $this->makeGlobalUser('modulos.delete');

        $this->actingAs($user)
            ->deleteJson("/modulos/{$module->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('modules', ['id' => $module->id]);
    }
}
