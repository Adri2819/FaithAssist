<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Chapel;
use App\Models\User;
use App\Services\UserScopeService;

class ChapelPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'capillas';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Chapel $chapel): bool
    {
        if (! $this->can($user, 'show')) {
            return false;
        }

        $scope = new UserScopeService($user);

        if ($scope->isGlobal()) {
            return true;
        }

        return ($chapel->church_id && $scope->churchIds()->contains($chapel->church_id))
            || ($chapel->community_id && $scope->communityIds()->contains($chapel->community_id));
    }

    public function create(User $user): bool
    {
        if (! $this->can($user, 'create')) {
            return false;
        }

        if ($this->hasFullScope($user)) {
            return true;
        }

        $scope = new UserScopeService($user);

        return $scope->communityIds()->isNotEmpty() || $scope->churchIds()->isNotEmpty();
    }

    public function update(User $user, Chapel $chapel): bool
    {
        if (! $this->can($user, 'update')) {
            return false;
        }

        $scope = new UserScopeService($user);

        if ($scope->isGlobal()) {
            return true;
        }

        return ($chapel->church_id && $scope->churchIds()->contains($chapel->church_id))
            || ($chapel->community_id && $scope->communityIds()->contains($chapel->community_id));
    }

    public function delete(User $user, Chapel $chapel): bool
    {
        if (! $this->can($user, 'delete')) {
            return false;
        }

        $scope = new UserScopeService($user);

        if ($scope->isGlobal()) {
            return true;
        }

        return ($chapel->church_id && $scope->churchIds()->contains($chapel->church_id))
            || ($chapel->community_id && $scope->communityIds()->contains($chapel->community_id));
    }
}
