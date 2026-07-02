<?php

namespace Tests\Feature;

use App\Models\Ecclesiastes\Chapel;
use App\Models\Ecclesiastes\Church;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\Regions\State;
use App\Models\User;
use App\Services\UserScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_columns_exist_on_users_table_and_scopes_table_is_gone(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'diocese_id'));
        $this->assertTrue(Schema::hasColumn('users', 'deanery_id'));
        $this->assertTrue(Schema::hasColumn('users', 'church_id'));
        $this->assertFalse(Schema::hasTable('scopes'));
    }

    public function test_global_user_is_global(): void
    {
        $user = User::factory()->create(['diocese_id' => null, 'deanery_id' => null, 'church_id' => null]);
        $scope = new UserScopeService($user);

        $this->assertTrue($scope->isGlobal());
        $this->assertSame('global', $scope->level());
        $this->assertEmpty($scope->dioceseIds());
        $this->assertEmpty($scope->deaneryIds());
        $this->assertEmpty($scope->churchIds());
        $this->assertEmpty($scope->municipalityIds());
    }

    public function test_diocese_scope_returns_correct_level_and_ids(): void
    {
        $state = State::query()->create(['name' => 'Estado Test', 'short_name' => 'ET']);
        $diocese = Diocese::query()->create(['name' => 'Diocesis Test', 'state_id' => $state->id]);
        $other = Diocese::query()->create(['name' => 'Otra Diocesis', 'state_id' => $state->id]);

        $user = User::factory()->create(['diocese_id' => $diocese->id, 'deanery_id' => null, 'church_id' => null]);
        $scope = new UserScopeService($user);

        $this->assertFalse($scope->isGlobal());
        $this->assertSame('diocese', $scope->level());
        $this->assertTrue($scope->dioceseIds()->contains($diocese->id));
        $this->assertFalse($scope->dioceseIds()->contains($other->id));
        $this->assertTrue($scope->stateIds()->contains($state->id));
    }

    public function test_deanery_scope_returns_correct_level_and_ids(): void
    {
        $state = State::query()->create(['name' => 'Estado D', 'short_name' => 'ED']);
        $diocese = Diocese::query()->create(['name' => 'Diocesis D', 'state_id' => $state->id]);
        $deanery = Deanery::query()->create(['name' => 'Decanato Test', 'diocese_id' => $diocese->id]);
        $otherDeanery = Deanery::query()->create(['name' => 'Otro Decanato', 'diocese_id' => $diocese->id]);

        $user = User::factory()->create(['diocese_id' => $diocese->id, 'deanery_id' => $deanery->id, 'church_id' => null]);
        $scope = new UserScopeService($user);

        $this->assertSame('deanery', $scope->level());
        $this->assertTrue($scope->deaneryIds()->contains($deanery->id));
        $this->assertFalse($scope->deaneryIds()->contains($otherDeanery->id));
    }

    public function test_church_scope_filters_municipalities_and_church(): void
    {
        $state = State::query()->create(['name' => 'Estado C', 'short_name' => 'EC']);
        $diocese = Diocese::query()->create(['name' => 'Diocesis C', 'state_id' => $state->id]);
        $deanery = Deanery::query()->create(['name' => 'Decanato C', 'diocese_id' => $diocese->id]);
        $municipality = Municipality::query()->create(['name' => 'Municipio C', 'diocese_id' => $diocese->id, 'state_id' => $state->id]);
        $church = Church::query()->create(['name' => 'Parroquia C', 'deanery_id' => $deanery->id, 'municipality_id' => $municipality->id]);

        $user = User::factory()->create(['diocese_id' => $diocese->id, 'deanery_id' => $deanery->id, 'church_id' => $church->id]);
        $scope = new UserScopeService($user);

        $this->assertSame('church', $scope->level());
        $this->assertTrue($scope->churchIds()->contains($church->id));
        $this->assertTrue($scope->municipalityIds()->contains($municipality->id));
    }

    public function test_chapel_scope_applied_to_query(): void
    {
        $state = State::query()->create(['name' => 'Estado P', 'short_name' => 'EP']);
        $diocese = Diocese::query()->create(['name' => 'Diocesis P', 'state_id' => $state->id]);
        $deanery = Deanery::query()->create(['name' => 'Decanato P', 'diocese_id' => $diocese->id]);
        $municipality = Municipality::query()->create(['name' => 'Municipio P', 'diocese_id' => $diocese->id, 'state_id' => $state->id]);
        $church = Church::query()->create(['name' => 'Parroquia P', 'deanery_id' => $deanery->id, 'municipality_id' => $municipality->id]);

        $allowedChapel = Chapel::query()->create(['name' => 'Capilla Propia', 'church_id' => $church->id]);
        $otherChapel = Chapel::query()->create(['name' => 'Capilla Ajena']);

        $user = User::factory()->create(['diocese_id' => $diocese->id, 'deanery_id' => $deanery->id, 'church_id' => $church->id]);
        $scope = new UserScopeService($user);

        $chapelIds = $scope->applyChapelScope(Chapel::query())->pluck('id');

        $this->assertTrue($chapelIds->contains($allowedChapel->id));
        $this->assertFalse($chapelIds->contains($otherChapel->id));
    }

    public function test_global_scope_does_not_filter_chapel_query(): void
    {
        $chapel = Chapel::query()->create(['name' => 'Capilla Global']);
        $user = User::factory()->create(['diocese_id' => null, 'deanery_id' => null, 'church_id' => null]);
        $scope = new UserScopeService($user);

        $this->assertTrue($scope->applyChapelScope(Chapel::query())->pluck('id')->contains($chapel->id));
    }
}
