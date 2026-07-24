<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->can('assign-roles');
    }

    public function view(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->can('assign-roles');
    }
}
