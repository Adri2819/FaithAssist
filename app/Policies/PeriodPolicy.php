<?php

namespace App\Policies;

use App\Models\Operation\Period;
use App\Models\User;

class PeriodPolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'periodos';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'read');
    }

    public function view(User $user, Period $period): bool
    {
        return $this->can($user, 'show');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, Period $period): bool
    {
        return $this->can($user, 'update');
    }

    public function delete(User $user, Period $period): bool
    {
        return $this->can($user, 'delete');
    }
}
