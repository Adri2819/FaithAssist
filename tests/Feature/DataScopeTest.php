<?php

namespace Tests\Feature;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_scope_ids_are_loaded_from_unified_scopes_table(): void
    {
        $this->assertFalse(Schema::hasTable('municipality_user'));
        $this->assertFalse(Schema::hasTable('church_user'));
        $this->assertFalse(Schema::hasTable('community_user'));

        $user = User::factory()->create();
        $municipality = Municipality::query()->create(['name' => 'Centro']);
        $church = Church::query()->create(['name' => 'Parroquia Centro']);

        $user->assignedMunicipalities()->sync([$municipality->id]);
        $user->assignedChurches()->sync([$church->id]);

        $this->assertDatabaseHas('scopes', [
            'user_id' => $user->id,
            'scope_type' => Scope::TYPE_MUNICIPALITY,
            'scope_id' => $municipality->id,
        ]);
        $this->assertDatabaseHas('scopes', [
            'user_id' => $user->id,
            'scope_type' => Scope::TYPE_CHURCH,
            'scope_id' => $church->id,
        ]);

        $this->assertSame([$municipality->id], $user->fresh()->allowedMunicipalityIds()->all());
        $this->assertSame([$church->id], $user->fresh()->allowedChurchIds()->all());

        $loadedUser = User::query()
            ->with(['assignedMunicipalities:id,name', 'assignedChurches:id,name'])
            ->findOrFail($user->id);

        $this->assertSame(['Centro'], $loadedUser->assignedMunicipalities->pluck('name')->all());
        $this->assertSame(['Parroquia Centro'], $loadedUser->assignedChurches->pluck('name')->all());
    }

    public function test_user_community_scope_ids_include_direct_and_municipality_derived_scopes(): void
    {
        $user = User::factory()->create();
        $municipality = Municipality::query()->create(['name' => 'Norte']);
        $derivedCommunity = Community::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Comunidad Norte',
        ]);
        $directCommunity = Community::query()->create(['name' => 'Comunidad Directa']);

        $user->assignedMunicipalities()->sync([$municipality->id]);
        $user->assignedCommunities()->sync([$directCommunity->id]);

        $this->assertSame(
            collect([$directCommunity->id, $derivedCommunity->id])->sort()->values()->all(),
            $user->fresh()->allowedCommunityIds()->sort()->values()->all()
        );
    }

    public function test_user_can_access_chapel_by_direct_community_or_church_scope(): void
    {
        $user = User::factory()->create();
        $community = Community::query()->create(['name' => 'Comunidad Capilla']);
        $church = Church::query()->create(['name' => 'Parroquia Capilla']);
        $communityChapel = Chapel::query()->create([
            'name' => 'Capilla Comunidad',
            'community_id' => $community->id,
        ]);
        $churchChapel = Chapel::query()->create([
            'name' => 'Capilla Parroquia',
            'church_id' => $church->id,
        ]);

        $user->assignedCommunities()->sync([$community->id]);
        $user->assignedChurches()->sync([$church->id]);

        $freshUser = $user->fresh();

        $this->assertTrue($freshUser->canAccessChapel($communityChapel));
        $this->assertTrue($freshUser->canAccessChapel($churchChapel));
    }
}