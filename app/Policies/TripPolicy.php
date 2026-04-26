<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Trip;
use Illuminate\Auth\Access\Response;

class TripPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return in_array($user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre viajes.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Trip $model): Response
    {
        if (in_array($user->role_id, [1, 2], true)) {
            return Response::allow();
        }

        if ($user->role_id === 3 && $model->driver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('El usuario no esta autorizado para ver informacion sobre viajes.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para crear un viaje.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Trip $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para actualizar un viaje.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Trip $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para desactivar un viaje.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Trip $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para reactivar un viaje.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Trip $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para eliminar un viaje.');
    }
}
