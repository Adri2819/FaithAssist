<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Chapel;
use App\Models\User;

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
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Chapel $chapel): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Chapel $chapel): bool
    {
        return $this->can($user, 'delete');
    }
}
