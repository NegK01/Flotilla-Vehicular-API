<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

Route::apiResource('users', UserController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Usuario no encontrado',
    ], 404);
});
Route::patch('users/{id}/restore', [UserController::class, 'restore']);

Route::get('roles',        [RoleController::class, 'index']);
Route::get('roles/{role}', [RoleController::class, 'show']);