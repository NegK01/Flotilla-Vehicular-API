<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $request->validate([
            'role' => ['nullable', 'integer', 'in:1,2,3'],
            'trashed' => ['nullable', 'in:only,with'],
        ]);

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
    public function store(StoreUserRequest $request)
    {
        //
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
        //
        return response()->json([
            'message' => 'Usuario seleccionado:',
            'data' => $user->load('role:id,name'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
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
        //
        $user->delete();

        return response()->json([
            'message' => 'Usuario desactivado correctamente.',
        ], 200);
    }

    public function restore(User $user)
    {
        //
        if (!$user->trashed()) {
            return response()->json([
                'message' => 'No se pudo reactivar el ususario.',
            ], 404);
        }

        $user->restore();

        return response()->json([
            'message' => 'Usuario reactivado correctamente.',
            'data' => $user->fresh()->load('role:id,name'),
        ], 200);
    }
}
