<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\TravelRouteController;
use App\Http\Controllers\TripController;
use App\Models\TravelRoute;

Route::apiResource('roles', RoleController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Rol no encontrado.',
    ], 404);
});

Route::apiResource('users', UserController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Usuario no encontrado.',
    ], 404);
});
Route::patch('users/{id}/restore', [UserController::class, 'restore']);

Route::apiResource('vehicles', VehicleController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Vehiculo no encontrado.',
    ], 404);
});
Route::patch('vehicles/{id}/restore', [VehicleController::class, 'restore']);

Route::apiResource('maintenances', MaintenanceController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Mantenimiento no encontrado.',
    ], 404);
});
Route::patch('maintenances/{id}/restore', [MaintenanceController::class, 'restore']);

Route::apiResource('travelRoutes', TravelRouteController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Viaje no encontrado.',
    ], 404);
});
Route::patch('travelRoute/{id}/restore', [TravelRouteController::class, 'restore']);





Route::apiResource('trips', TripController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Viaje no encontrado.',
    ], 404);
});
Route::patch('trips/{id}/restore', [TripController::class, 'restore']);