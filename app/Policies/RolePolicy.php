<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

class RolePolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user)
    {
        return true;
    }
    public function view(User $user)
    {
        return true;
    }
    public function create(User $user)
    {
        return $user->hasRole('super_admin');
    }
    public function update(User $user)
    {
        return $user->hasRole('super_admin');
    }
    public function delete(User $user)
    {
        return $user->hasRole('super_admin');
    }
}
