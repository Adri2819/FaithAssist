<?php

namespace App\Policies;

use App\Models\Regions\Municipality;
use App\Models\User;

class MunicipalityPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'municipios';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Municipality $municipality): bool
    {
        return $this->can($user, 'show')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($municipality->id));
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create') && $this->hasFullScope($user);
    }

    public function update(User $user, Municipality $municipality): bool
    {
        return $this->can($user, 'update')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($municipality->id));
    }

    public function delete(User $user, Municipality $municipality): bool
    {
        return $this->can($user, 'delete')
            && ($this->hasFullScope($user) || $user->canAccessMunicipalityId($municipality->id));
    }
}
