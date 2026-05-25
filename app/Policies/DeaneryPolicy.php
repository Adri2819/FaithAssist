<?php

namespace App\Policies;

use App\Models\Ecclesiastes\Deanery;
use App\Models\User;

class DeaneryPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'decanato';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Deanery $deanery): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Deanery $deanery): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Deanery $deanery): bool
    {
        return $this->can($user, 'delete');
    }
}
