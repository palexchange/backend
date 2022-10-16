<?php

namespace App\Policies;

use App\Models\EntryTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntryTransactionPolicy
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
    * @param  \App\Models\EntryTransaction  $entryTransaction
    * @return \Illuminate\Auth\Access\Response|bool
    */
   public function view(User $user, EntryTransaction $entryTransaction)
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
      return $user->hasPermissionTo('create_entry_transaction');
   }

   /**
    * Determine whether the user can update the model.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\EntryTransaction  $entryTransaction
    * @return \Illuminate\Auth\Access\Response|bool
    */
   public function update(User $user, EntryTransaction $entryTransaction)
   {
      return $user->hasPermissionTo('update_entry_transaction');
   }

   /**
    * Determine whether the user can delete the model.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\EntryTransaction  $entryTransaction
    * @return \Illuminate\Auth\Access\Response|bool
    */
   public function delete(User $user, EntryTransaction $entryTransaction)
   {
      return $user->hasPermissionTo('delete_entry_transaction');
   }

   /**
    * Determine whether the user can restore the model.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\EntryTransaction  $entryTransaction
    * @return \Illuminate\Auth\Access\Response|bool
    */
   public function restore(User $user, EntryTransaction $entryTransaction)
   {
      return true;
   }

   /**
    * Determine whether the user can permanently delete the model.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\EntryTransaction  $entryTransaction
    * @return \Illuminate\Auth\Access\Response|bool
    */
   public function forceDelete(User $user, EntryTransaction $entryTransaction)
   {
      return true;
   }
}
