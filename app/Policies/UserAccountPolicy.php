<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserAccount  $userAccount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserAccount $userAccount)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return  $user->hasPermissionTo('create_user_account');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserAccount  $userAccount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserAccount $userAccount)
    {
        return  $user->hasPermissionTo('update_user_account');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserAccount  $userAccount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserAccount $userAccount)
    {
        return  $user->hasPermissionTo('delete_user_account');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserAccount  $userAccount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, UserAccount $userAccount)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserAccount  $userAccount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserAccount $userAccount)
    {
        return true;
    }
}
