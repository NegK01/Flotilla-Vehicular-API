<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\TravelRouteController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VehicleRequestController;

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::apiResource('roles', RoleController::class)
    ->only(['index', 'show'])
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\Role')
    ->middlewareFor('show', 'can:view,role')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Rol no encontrado.',
        ], 404);
    });


Route::apiResource('users', UserController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\User')
    ->middlewareFor('show', 'can:view,user')
    ->middlewareFor('store', 'can:create,App\Models\User')
    ->middlewareFor('update', 'can:update,user')
    ->middlewareFor('destroy', 'can:delete,user')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Usuario no encontrado.',
        ], 404);
    });
Route::patch('users/{user}/restore', [UserController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,user']);


Route::apiResource('vehicles', VehicleController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\Vehicle')
    ->middlewareFor('show', 'can:view,vehicle')
    ->middlewareFor('store', 'can:create,App\Models\Vehicle')
    ->middlewareFor('update', 'can:update,vehicle')
    ->middlewareFor('destroy', 'can:delete,vehicle')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Vehiculo no encontrado.',
        ], 404);
    });
Route::patch('vehicles/{vehicle}/restore', [VehicleController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,vehicle']);


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


Route::apiResource('vehicleRequest', VehicleRequestController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Solicitud no encontrado.',
    ], 404);
});
Route::patch('vehicleRequest/{id}/restore', [VehicleRequestController::class, 'restore']);
