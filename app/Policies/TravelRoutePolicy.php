<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TravelRoute;
use Illuminate\Auth\Access\Response;

class TravelRoutePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre rutas.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelRoute $model): Response
    {
        return in_array((int) $user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre rutas.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para crear una ruta.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelRoute $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para actualizar una ruta.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelRoute $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para desactivar una ruta.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TravelRoute $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para reactivar una ruta.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TravelRoute $model): Response
    {
        return in_array((int) $user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para eliminar una ruta.');
    }
}
