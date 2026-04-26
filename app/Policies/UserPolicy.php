<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver información de usuarios.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        return $user->id === $model->id || $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver información de otro usuario.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para crear un usuario.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para actualizar un usuario.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para desactivar un usuario.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para reactivar un usuario.');
    }

    public function viewDriverHistory(User $user, User $driver): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver el reporte de historial del chofer.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para eliminar un usuario.');
    }
}
