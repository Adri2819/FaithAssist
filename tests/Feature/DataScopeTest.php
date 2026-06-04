<?php

namespace Tests\Feature;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_persists_assigned_municipalities_and_churches(): void
    {
        $admin = User::factory()->create();
        $municipality = Municipality::query()->create(['name' => 'Municipio Norte', 'status' => 'active']);
        $church = Church::query()->create(['name' => 'Parroquia Centro', 'status' => 'active']);

        $response = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Juan',
            'paterno' => 'Perez',
            'materno' => 'Lopez',
            'email' => 'juan@example.com',
            'municipality_ids' => [$municipality->id],
            'church_ids' => [$church->id],
            'permissions' => [],
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::query()->where('email', 'juan@example.com')->firstOrFail();

        $response->assertRedirect('/usuarios');
        $this->assertDatabaseHas('municipality_user', [
            'user_id' => $user->id,
            'municipality_id' => $municipality->id,
        ]);
        $this->assertDatabaseHas('church_user', [
            'user_id' => $user->id,
            'church_id' => $church->id,
        ]);
    }

    public function test_community_index_only_returns_assigned_records_without_full_scope(): void
    {
        $allowedMunicipality = Municipality::query()->create(['name' => 'Municipio Permitido', 'status' => 'active']);
        $otherMunicipality = Municipality::query()->create(['name' => 'Municipio Externo', 'status' => 'active']);
        $allowed = Community::query()->create(['municipality_id' => $allowedMunicipality->id, 'name' => 'Comunidad Permitida', 'status' => 'active']);
        $hidden = Community::query()->create(['municipality_id' => $otherMunicipality->id, 'name' => 'Comunidad Oculta', 'status' => 'active']);
        $user = User::factory()->create();

        $this->permission('comunidades.read');
        $user->givePermissionTo('comunidades.read');
        $user->assignedMunicipalities()->sync([$allowedMunicipality->id]);

        $this->actingAs($user)
            ->get('/comunidades')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Regions/Communities/Index')
                ->where('communities.data', [[
                    'id' => $allowed->id,
                    'municipality_id' => $allowedMunicipality->id,
                    'name' => 'Comunidad Permitida',
                    'status' => 'active',
                ]])
            );

        $this->assertNotEquals($allowed->id, $hidden->id);
    }

    public function test_chapel_index_uses_community_or_church_scope(): void
    {
        $allowedMunicipality = Municipality::query()->create(['name' => 'Municipio Permitido', 'status' => 'active']);
        $otherMunicipality = Municipality::query()->create(['name' => 'Municipio Externo', 'status' => 'active']);
        $allowedCommunity = Community::query()->create(['municipality_id' => $allowedMunicipality->id, 'name' => 'Comunidad Permitida', 'status' => 'active']);
        $otherCommunity = Community::query()->create(['municipality_id' => $otherMunicipality->id, 'name' => 'Comunidad Externa', 'status' => 'active']);
        $allowedChurch = Church::query()->create(['name' => 'Parroquia Permitida', 'status' => 'active']);
        $otherChurch = Church::query()->create(['name' => 'Parroquia Externa', 'status' => 'active']);

        $byCommunity = Chapel::query()->create([
            'community_id' => $allowedCommunity->id,
            'church_id' => $otherChurch->id,
            'name' => 'Capilla Comunidad',
            'status' => 'active',
        ]);

        $byChurch = Chapel::query()->create([
            'community_id' => $otherCommunity->id,
            'church_id' => $allowedChurch->id,
            'name' => 'Capilla Parroquia',
            'status' => 'active',
        ]);

        Chapel::query()->create([
            'community_id' => $otherCommunity->id,
            'church_id' => $otherChurch->id,
            'name' => 'Capilla Oculta',
            'status' => 'active',
        ]);

        $user = User::factory()->create();
        $this->permission('capillas.read');
        $user->givePermissionTo('capillas.read');
        $user->assignedMunicipalities()->sync([$allowedMunicipality->id]);
        $user->assignedChurches()->sync([$allowedChurch->id]);

        $this->actingAs($user)
            ->get('/capillas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ecclesiastes/Chapels/Index')
                ->has('chapels.data', 2)
                ->where('chapels.data.0.id', $byCommunity->id)
                ->where('chapels.data.1.id', $byChurch->id)
            );
    }

    public function test_updates_are_forbidden_outside_of_scope(): void
    {
        $allowedMunicipality = Municipality::query()->create(['name' => 'Municipio Permitido', 'status' => 'active']);
        $otherMunicipality = Municipality::query()->create(['name' => 'Municipio Externo', 'status' => 'active']);
        $allowed = Community::query()->create(['municipality_id' => $allowedMunicipality->id, 'name' => 'Comunidad Permitida', 'status' => 'active']);
        $hidden = Community::query()->create(['municipality_id' => $otherMunicipality->id, 'name' => 'Comunidad Oculta', 'status' => 'active']);
        $user = User::factory()->create();

        $this->permission('comunidades.update');
        $user->givePermissionTo('comunidades.update');
        $user->assignedMunicipalities()->sync([$allowedMunicipality->id]);

        $this->actingAs($user)
            ->putJson("/comunidades/{$hidden->id}", [
                'municipality_id' => $otherMunicipality->id,
                'name' => 'COMUNIDAD OCULTA',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_municipality_index_only_returns_assigned_records_without_full_scope(): void
    {
        $allowed = Municipality::query()->create(['name' => 'Municipio Permitido', 'status' => 'active']);
        Municipality::query()->create(['name' => 'Municipio Oculto', 'status' => 'active']);
        $user = User::factory()->create();

        $this->permission('municipios.read');
        $user->givePermissionTo('municipios.read');
        $user->assignedMunicipalities()->sync([$allowed->id]);

        $this->actingAs($user)
            ->get('/municipios')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Regions/Municipalities/Index')
                ->where('municipalities.data', [[
                    'id' => $allowed->id,
                    'state_id' => null,
                    'diocese_id' => null,
                    'name' => 'Municipio Permitido',
                    'status' => 'active',
                ]])
            );
    }

    public function test_creating_communities_requires_full_module_scope(): void
    {
        $user = User::factory()->create();

        $this->permission('comunidades.create');
        $user->givePermissionTo('comunidades.create');

        $this->actingAs($user)
            ->postJson('/comunidades', [
                'municipality_id' => null,
                'name' => 'NUEVA COMUNIDAD',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    private function permission(string $name): Permission
    {
        return Permission::query()->firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            [
                'description' => $name,
                'module_key' => str_contains($name, 'comunidades') || str_contains($name, 'municipios')
                    ? 'regions'
                    : 'ecclesiastes',
            ]
        );
    }
}
