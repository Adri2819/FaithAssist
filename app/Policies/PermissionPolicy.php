<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'permisos';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $this->can($user, 'delete');
    }
}
