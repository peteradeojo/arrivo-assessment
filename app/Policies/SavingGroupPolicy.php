<?php

namespace App\Policies;

use App\Models\SavingGroup;
use App\Models\SavingGroupMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SavingGroupPolicy
{


    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SavingGroup $savingGroup): bool
    {
        return $savingGroup->user_id == $user->id || $savingGroup->members->find(fn($a) => $a->user_id == $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SavingGroup $savingGroup): bool
    {
        return $user->id == $savingGroup->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SavingGroup $savingGroup): bool
    {
        return $user->id == $savingGroup->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SavingGroup $savingGroup): bool
    {
        return $user->id == $savingGroup->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SavingGroup $savingGroup): bool
    {
        return $user->id == $savingGroup->user_id;
    }

    public function inviteUserToGroup(User $user, SavingGroup $savingGroup)
    {
        return $user->id == $savingGroup->user_id;
    }

    public function replyGroupInvite(User $user, SavingGroupMember $invite)
    {
        return $user->id == $invite->user_id;
    }
}
