<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('You are not authorized to access the vehicles module.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $model): Response
    {
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('You are not authorized to access the vehicles module.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (int) $user->role_id === 1;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $model): bool
    {
        return (int) $user->role_id === 1;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $model): bool
    {
        return (int) $user->role_id === 1;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vehicle $model): bool
    {
        return (int) $user->role_id === 1;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vehicle $model): bool
    {
        return (int) $user->role_id === 1;
    }
}
