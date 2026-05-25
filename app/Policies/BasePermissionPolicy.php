<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePermissionPolicy
{
    abstract protected function permissionModule(): string;

    protected function can(User $user, string $action): bool
    {
        return $user->can($this->permissionModule().'.'.$action);
    }
}
