<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        // operacion ternaria condición ? valor_si_true : valor_si_false
        return (int) $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre roles.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre roles.');
    }
}
