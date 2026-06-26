<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Church;
use App\Models\User;
use App\Services\UserScopeService;

class ChurchPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'parroquias';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Church $church): bool
    {
        if (! $this->can($user, 'show')) {
            return false;
        }

        $scope = new UserScopeService($user);

        return $scope->isGlobal() || $scope->churchIds()->contains($church->id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create') && $this->hasFullScope($user);
    }

    public function update(User $user, Church $church): bool
    {
        if (! $this->can($user, 'update')) {
            return false;
        }

        $scope = new UserScopeService($user);

        return $scope->isGlobal() || $scope->churchIds()->contains($church->id);
    }

    public function delete(User $user, Church $church): bool
    {
        if (! $this->can($user, 'delete')) {
            return false;
        }

        $scope = new UserScopeService($user);

        return $scope->isGlobal() || $scope->churchIds()->contains($church->id);
    }
}
