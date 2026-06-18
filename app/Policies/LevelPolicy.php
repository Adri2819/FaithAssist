<?php

namespace App\Policies;

use App\Models\Operation\Level;
use App\Models\User;

class LevelPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'niveles';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Level $level): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Level $level): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Level $level): bool
    {
        return $this->can($user, 'delete');
    }
}