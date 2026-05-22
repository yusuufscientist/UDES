<?php

namespace App\Policies;

use App\Models\SolarSystem;
use App\Models\User;

class SolarSystemPolicy
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
    public function view(User $user, SolarSystem $solarSystem): bool
    {
        return $user->id === $solarSystem->user_id || $user->isAdmin();
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
    public function update(User $user, SolarSystem $solarSystem): bool
    {
        return $user->id === $solarSystem->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolarSystem $solarSystem): bool
    {
        return $user->id === $solarSystem->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SolarSystem $solarSystem): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SolarSystem $solarSystem): bool
    {
        return $user->isAdmin();
    }
}
