<?php

namespace App\Policies;

use App\Models\Regions\Community;
use App\Models\User;

class CommunityPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'comunidades';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Community $community): bool
    {
        return $this->can($user, 'show')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($community->municipality_id));
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create') && $this->hasFullScope($user);
    }

    public function update(User $user, Community $community): bool
    {
        return $this->can($user, 'update')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($community->municipality_id));
    }

    public function delete(User $user, Community $community): bool
    {
        return $this->can($user, 'delete')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($community->municipality_id));
    }
}
