<?php

namespace Tests\Unit;

use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserDataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_scope_ids_from_municipality_and_church_assignments(): void
    {
        $user = User::factory()->create();
        $municipalities = collect([
            Municipality::query()->create(['name' => 'Municipio 1', 'status' => 'active']),
            Municipality::query()->create(['name' => 'Municipio 2', 'status' => 'active']),
        ]);
        $communities = collect([
            Community::query()->create(['municipality_id' => $municipalities[0]->id, 'name' => 'Comunidad 1', 'status' => 'active']),
            Community::query()->create(['municipality_id' => $municipalities[1]->id, 'name' => 'Comunidad 2', 'status' => 'active']),
        ]);
        $churches = collect([
            Church::query()->create(['name' => 'Parroquia 1', 'status' => 'active']),
            Church::query()->create(['name' => 'Parroquia 2', 'status' => 'active']),
        ]);

        $user->assignedMunicipalities()->sync(collect($municipalities)->pluck('id'));
        $user->assignedChurches()->sync(collect($churches)->pluck('id'));

        $this->assertSame(
            collect($municipalities)->pluck('id')->all(),
            $user->fresh()->allowedMunicipalityIds()->all(),
        );

        $this->assertSame(
            collect($communities)->pluck('id')->all(),
            $user->fresh()->allowedCommunityIds()->all(),
        );

        $this->assertSame(
            collect($churches)->pluck('id')->all(),
            $user->fresh()->allowedChurchIds()->all(),
        );
    }

    public function test_it_detects_full_scope_permissions_per_module(): void
    {
        $user = User::factory()->create();

        Permission::query()->create([
            'name' => 'capillas.scope.all',
            'guard_name' => 'web',
            'description' => 'Acceso global a capillas',
            'module_key' => 'ecclesiastes',
        ]);

        $user->givePermissionTo('capillas.scope.all');

        $this->assertTrue($user->hasModuleFullScope('capillas'));
        $this->assertFalse($user->hasModuleFullScope('parroquias'));
    }
}
