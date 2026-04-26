<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleRequest;
use Illuminate\Auth\Access\Response;

class VehicleRequestPolicy
{
    public function viewAny(User $user): Response
    {
        return in_array($user->role_id, [1, 2, 3], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para ver informacion sobre solicitudes.');
    }

    public function view(User $user, VehicleRequest $model): Response
    {
        if (in_array($user->role_id, [1, 2], true)) {
            return Response::allow();
        }

        if ($user->role_id === 3 && $model->driver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('El usuario no esta autorizado para ver informacion de solicitudes ajenas.');
    }

    public function create(User $user): Response
    {
        return $user->role_id === 3
            ? Response::allow()
            : Response::deny('Solo los choferes pueden crear solicitudes regulares. Los operadores/administradores deben usar la asignación directa.');
    }

    public function update(User $user, VehicleRequest $model): Response
    {
        if (in_array($user->role_id, [1, 2], true)) {
            return Response::allow();
        }

        if ($user->role_id === 3 && $model->driver_id === $user->id && $model->status === VehicleRequest::STATUS_PENDING) {
            return Response::allow();
        }

        return Response::deny('El usuario no esta autorizado para actualizar esta solicitud.');
    }

    public function delete(User $user, VehicleRequest $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para desactivar una solicitud.');
    }

    public function restore(User $user, VehicleRequest $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para reactivar una solicitud.');
    }

    public function forceDelete(User $user, VehicleRequest $model): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para eliminar una solicitud.');
    }

    public function approve(User $user, VehicleRequest $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para aprobar solicitudes.');
    }

    public function reject(User $user, VehicleRequest $model): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para rechazar solicitudes.');
    }

    public function cancel(User $user, VehicleRequest $model): Response
    {
        if (in_array($user->role_id, [1, 2], true)) {
            return Response::allow();
        }

        if ($user->role_id === 3 && $model->driver_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('El usuario no esta autorizado para cancelar esta solicitud.');
    }

    public function directAssignment(User $user): Response
    {
        return in_array($user->role_id, [1, 2], true)
            ? Response::allow()
            : Response::deny('El usuario no esta autorizado para realizar asignaciones directas.');
    }
}
