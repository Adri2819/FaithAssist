<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Diocese;
use App\Models\User;

class DiocesePolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'diocesis';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Diocese $diocese): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Diocese $diocese): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Diocese $diocese): bool
    {
        return $this->can($user, 'delete');
    }
}
