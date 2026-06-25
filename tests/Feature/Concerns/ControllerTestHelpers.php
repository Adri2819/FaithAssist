<?php

namespace Tests\Feature\Concerns;

use App\Models\Ecclesiastes\Church;
use App\Models\Ecclesiastes\Deanery;
use App\Models\Ecclesiastes\Diocese;
use App\Models\Regions\Community;
use App\Models\Regions\Municipality;
use App\Models\Regions\State;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

trait ControllerTestHelpers
{
    /**
     * Create and return the full geographic/ecclesiastical hierarchy.
     *
     * @return array{state: State, diocese: Diocese, deanery: Deanery, municipality: Municipality, church: Church, community: Community}
     */
    protected function createChain(): array
    {
        $state = State::query()->create(['name' => 'Estado Test', 'short_name' => 'ET', 'status' => 'active']);
        $diocese = Diocese::query()->create(['name' => 'Diócesis Test', 'state_id' => $state->id, 'status' => 'active']);
        $deanery = Deanery::query()->create(['name' => 'Decanato Test', 'diocese_id' => $diocese->id, 'status' => 'active']);
        $municipality = Municipality::query()->create(['name' => 'Municipio Test', 'state_id' => $state->id, 'diocese_id' => $diocese->id, 'status' => 'active']);
        $church = Church::query()->create(['name' => 'Parroquia Test', 'deanery_id' => $deanery->id, 'municipality_id' => $municipality->id, 'status' => 'active']);
        $community = Community::query()->create(['name' => 'Comunidad Test', 'municipality_id' => $municipality->id, 'status' => 'active']);

        return compact('state', 'diocese', 'deanery', 'municipality', 'church', 'community');
    }

    /**
     * Create a global admin user (diocese_id = null) with the given permissions.
     */
    protected function makeGlobalUser(string ...$permissions): User
    {
        $user = User::factory()->create(['diocese_id' => null, 'deanery_id' => null, 'church_id' => null]);
        $this->givePermissions($user, ...$permissions);

        return $user;
    }

    /**
     * Create a user scoped to the given diocese with the given permissions.
     */
    protected function makeDioceseUser(Diocese $diocese, string ...$permissions): User
    {
        $user = User::factory()->create(['diocese_id' => $diocese->id, 'deanery_id' => null, 'church_id' => null]);
        $this->givePermissions($user, ...$permissions);

        return $user;
    }

    /**
     * Create a user scoped to a specific church with the given permissions.
     */
    protected function makeChurchUser(Diocese $diocese, Deanery $deanery, Church $church, string ...$permissions): User
    {
        $user = User::factory()->create([
            'diocese_id' => $diocese->id,
            'deanery_id' => $deanery->id,
            'church_id'  => $church->id,
        ]);
        $this->givePermissions($user, ...$permissions);

        return $user;
    }

    /**
     * Create (or retrieve) Spatie permissions and assign them to the user.
     */
    protected function givePermissions(User $user, string ...$permissions): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['description' => $name, 'module_key' => 'test']
            );
            $user->givePermissionTo($permission);
        }
    }
}
