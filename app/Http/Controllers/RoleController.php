<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::latest()->paginate(10);

        return response()->json([
            'message' => 'Lista de roles seleccionados:',
            'data' => $roles,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json([
            'message' => 'Rol seleccionado:',
            'data' => $role,
        ], 200);
    }
}
