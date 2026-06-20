<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsappMessage;

class WhatsappMessagePolicy extends BasePermissionPolicy
{
    protected function permissionModule(): string
    {
        return 'whatsapp';
    }

    public function viewAny(User $user): bool
    {
        return $user->can('whatsapp.send');
    }

    public function view(User $user, WhatsappMessage $whatsappMessage): bool
    {
        return $user->can('whatsapp.send');
    }

    public function create(User $user): bool
    {
        return $user->can('whatsapp.send');
    }

    public function update(User $user, WhatsappMessage $whatsappMessage): bool
    {
        return $user->can('whatsapp.send');
    }

    public function delete(User $user, WhatsappMessage $whatsappMessage): bool
    {
        return $user->can('whatsapp.send');
    }
}
