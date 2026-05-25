<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Church;
use App\Models\User;

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
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Church $church): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Church $church): bool
    {
        return $this->can($user, 'delete');
    }
}
