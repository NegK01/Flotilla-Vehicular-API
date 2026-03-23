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
        //
        $roles = Role::latest()->paginate(10);

        return response()->json([
            'message' => 'Lista de roles seleccionados:',
            'data' => $roles,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreRoleRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
        return response()->json($role, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UpdateRoleRequest $request, Role $role)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Role $role)
    // {
    //     //
    // }
}
