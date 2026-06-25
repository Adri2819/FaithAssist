<?php

namespace Tests\Feature;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_columns_exist_on_users_table_and_scopes_table_is_gone(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'municipality_id'));
        $this->assertTrue(Schema::hasColumn('users', 'church_id'));
        $this->assertFalse(Schema::hasTable('scopes'));
    }

    public function test_user_with_null_fks_has_full_access(): void
    {
        $user = User::factory()->create(['municipality_id' => null, 'church_id' => null]);

        $this->assertTrue($user->hasModuleFullScope('municipios'));
        $this->assertTrue($user->hasModuleFullScope('comunidades'));
        $this->assertTrue($user->hasModuleFullScope('parroquias'));
        $this->assertTrue($user->hasModuleFullScope('capillas'));
        $this->assertTrue($user->canAccessMunicipalityId(9999));
        $this->assertTrue($user->canAccessChurchId(9999));
        $this->assertTrue($user->canAccessCommunityId(9999));
    }

    public function test_user_with_municipality_is_restricted_to_it(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Centro']);
        $other = Municipality::query()->create(['name' => 'Norte']);

        $user = User::factory()->create(['municipality_id' => $municipality->id, 'church_id' => null]);

        $this->assertFalse($user->hasModuleFullScope('municipios'));
        $this->assertTrue($user->canAccessMunicipalityId($municipality->id));
        $this->assertFalse($user->canAccessMunicipalityId($other->id));
        $this->assertSame([$municipality->id], $user->allowedMunicipalityIds()->all());
    }

    public function test_user_with_municipality_can_access_communities_in_it(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Sur']);
        $community = Community::query()->create(['municipality_id' => $municipality->id, 'name' => 'Comunidad Sur']);
        $otherCommunity = Community::query()->create(['name' => 'Comunidad Ajena']);

        $user = User::factory()->create(['municipality_id' => $municipality->id, 'church_id' => null]);

        $this->assertTrue($user->canAccessCommunityId($community->id));
        $this->assertFalse($user->canAccessCommunityId($otherCommunity->id));
        $this->assertTrue($user->allowedCommunityIds()->contains($community->id));
        $this->assertFalse($user->allowedCommunityIds()->contains($otherCommunity->id));
    }

    public function test_user_with_church_is_restricted_to_it(): void
    {
        $church = Church::query()->create(['name' => 'Parroquia Centro']);
        $other = Church::query()->create(['name' => 'Parroquia Norte']);

        $user = User::factory()->create(['municipality_id' => null, 'church_id' => $church->id]);

        $this->assertFalse($user->hasModuleFullScope('parroquias'));
        $this->assertTrue($user->canAccessChurchId($church->id));
        $this->assertFalse($user->canAccessChurchId($other->id));
        $this->assertSame([$church->id], $user->allowedChurchIds()->all());
    }

    public function test_user_can_access_chapel_by_municipality_or_church(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Municipio Capilla']);
        $community = Community::query()->create(['municipality_id' => $municipality->id, 'name' => 'Comunidad Capilla']);
        $church = Church::query()->create(['name' => 'Parroquia Capilla']);

        $communityChapel = Chapel::query()->create(['name' => 'Capilla Comunidad', 'community_id' => $community->id]);
        $churchChapel = Chapel::query()->create(['name' => 'Capilla Parroquia', 'church_id' => $church->id]);
        $otherChapel = Chapel::query()->create(['name' => 'Capilla Ajena']);

        $user = User::factory()->create(['municipality_id' => $municipality->id, 'church_id' => $church->id]);

        $this->assertTrue($user->canAccessChapel($communityChapel));
        $this->assertTrue($user->canAccessChapel($churchChapel));
        $this->assertFalse($user->canAccessChapel($otherChapel));
    }

    public function test_user_with_no_scopes_can_access_any_chapel(): void
    {
        $chapel = Chapel::query()->create(['name' => 'Capilla Cualquiera']);

        $user = User::factory()->create(['municipality_id' => null, 'church_id' => null]);

        $this->assertTrue($user->canAccessChapel($chapel));
    }
}