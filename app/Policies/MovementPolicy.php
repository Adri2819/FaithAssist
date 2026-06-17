<?php

namespace App\Policies;

use App\Models\Operation\PeriodMovement;
use App\Models\User;

class MovementPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'movimientos';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Movement $movement): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Movement $movement): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Movement $movement): bool
    {
        return $this->can($user, 'delete');
    }
}
