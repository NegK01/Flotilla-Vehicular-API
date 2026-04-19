<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleRequest;
use Illuminate\Auth\Access\Response;

class VehicleRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre solicitudes.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VehicleRequest $model): Response
    {
        if (in_array((int) $user->role_id, [1, 2], true)) {
            return Response::allow();
        }

        if ((int) $user->role_id === 3 && $model->driver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('El usuario no esta autorizado para ver informacion sobre solicitudes.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para crear una solicitud.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VehicleRequest $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para actualizar una solicitud.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VehicleRequest $model): Response
    {
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para desactivar una solicitud.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VehicleRequest $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para reactivar una solicitud.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VehicleRequest $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para eliminar una solicitud.');
    }
}
