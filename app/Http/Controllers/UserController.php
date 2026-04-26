<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexRequest $request)
    {
        $query = User::with('role:id,name')
            ->latest()
            ->when($request->role, fn($q) => $q->where('role_id', $request->role))
            ->when($request->trashed === 'only', fn($q) => $q->onlyTrashed())
            ->when($request->trashed === 'with', fn($q) => $q->withTrashed());

        $users = $query->paginate(10);

        return response()->json([
            'message' => 'Lista de usuarios seleccionados:',
            'data' => $users,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'data' => $user->load('role:id,name'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'message' => 'Usuario seleccionado:',
            'data' => $user->load('role:id,name'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $user->fresh()->load('role:id,name'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario desactivado correctamente.',
        ], 200);
    }

    public function restore(User $user)
    {
        if (!$user->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el usuario.',
            ], 404);
        }

        if (User::where('email', $user->email)->exists()) {
            return response()->json([
                'message' => 'No se puede reactivar el usuario porque el correo electrónico ya está en uso por otro usuario activo.',
            ], 409);
        }

        $user->restore();

        return response()->json([
            'message' => 'Usuario reactivado correctamente.',
            'data' => $user->fresh()->load('role:id,name'),
        ], 200);
    }
}
