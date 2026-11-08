<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HouseholdPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Household $household): Response
    {
        $allowed = $household->members()->where('user_id', $user->id)->exists();

        return $allowed
            ? Response::allow()
            : Response::deny('You are not a member of this household.');
    }

    public function manage(User $user, Household $household): bool
    {
        return $household->isManagedBy($user->id);
    }
}
