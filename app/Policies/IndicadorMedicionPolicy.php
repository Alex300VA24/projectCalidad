<?php

namespace App\Policies;

use App\Models\IndicadorMedicion;
use App\Models\User;

class IndicadorMedicionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, IndicadorMedicion $indicadorMedicion): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('indicator.consolidate');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IndicadorMedicion $indicadorMedicion): bool
    {
        return $user->can('indicator.analyze');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IndicadorMedicion $indicadorMedicion): bool
    {
        return $user->can('indicator.manage');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IndicadorMedicion $indicadorMedicion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IndicadorMedicion $indicadorMedicion): bool
    {
        return false;
    }

    public function consolidate(User $user): bool
    {
        return $user->can('indicator.consolidate');
    }
}
