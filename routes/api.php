<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;

Route::apiResource('roles', RoleController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Rol no encontrado',
    ], 404);
});

Route::apiResource('users', UserController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Usuario no encontrado',
    ], 404);
});
Route::patch('users/{id}/restore', [UserController::class, 'restore']);

Route::apiResource('vehicles', VehicleController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Vehiculo no encontrado',
    ], 404);
});
Route::patch('vehicles/{id}/restore', [VehicleController::class, 'restore']);