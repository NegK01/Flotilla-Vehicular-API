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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleRequestController;

Route::post('login', [AuthController::class, 'login']);
Route::post('registerDriver', [AuthController::class, 'registerDriver']);
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


Route::apiResource('maintenances', MaintenanceController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\Maintenance')
    ->middlewareFor('show', 'can:view,maintenance')
    ->middlewareFor('store', 'can:create,App\Models\Maintenance')
    ->middlewareFor('update', 'can:update,maintenance')
    ->middlewareFor('destroy', 'can:delete,maintenance')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Mantenimiento no encontrado.',
        ], 404);
    });
Route::patch('maintenances/{maintenance}/restore', [MaintenanceController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,maintenance']);


Route::apiResource('travelRoutes', TravelRouteController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\TravelRoute')
    ->middlewareFor('show', 'can:view,travelRoute')
    ->middlewareFor('store', 'can:create,App\Models\TravelRoute')
    ->middlewareFor('update', 'can:update,travelRoute')
    ->middlewareFor('destroy', 'can:delete,travelRoute')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Ruta no encontrada.',
        ], 404);
    });
Route::patch('travelRoutes/{travelRoute}/restore', [TravelRouteController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,travelRoute']);


Route::apiResource('trips', TripController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\Trip')
    ->middlewareFor('show', 'can:view,trip')
    ->middlewareFor('store', 'can:create,App\Models\Trip')
    ->middlewareFor('update', 'can:update,trip')
    ->middlewareFor('destroy', 'can:delete,trip')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Viaje no encontrado.',
        ], 404);
    });
Route::patch('trips/{trip}/restore', [TripController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,trip']);


Route::apiResource('vehicleRequests', VehicleRequestController::class)
    ->middleware('auth:sanctum')
    ->middlewareFor('index', 'can:viewAny,App\Models\VehicleRequest')
    ->middlewareFor('show', 'can:view,vehicleRequest')
    ->middlewareFor('store', 'can:create,App\Models\VehicleRequest')
    ->middlewareFor('update', 'can:update,vehicleRequest')
    ->middlewareFor('destroy', 'can:delete,vehicleRequest')
    ->missing(function (Request $request) {
        return response()->json([
            'message' => 'Solicitud no encontrada.',
        ], 404);
    });
Route::patch('vehicleRequests/{vehicleRequest}/restore', [VehicleRequestController::class, 'restore'])
    ->withTrashed()
    ->middleware(['auth:sanctum', 'can:restore,vehicleRequest']);

    
    
Route::patch('vehicleRequests/{vehicleRequest}/approve', [VehicleRequestController::class, 'approve'])
    ->middleware(['auth:sanctum', 'can:approve,vehicleRequest']);

Route::patch('vehicleRequests/{vehicleRequest}/reject', [VehicleRequestController::class, 'reject'])
    ->middleware(['auth:sanctum', 'can:reject,vehicleRequest']);

Route::patch('vehicleRequests/{vehicleRequest}/cancel', [VehicleRequestController::class, 'cancel'])
    ->middleware(['auth:sanctum', 'can:cancel,vehicleRequest']);

Route::post('vehicleRequests/directAssignment', [VehicleRequestController::class, 'directAssignment'])
    ->middleware(['auth:sanctum', 'can:directAssignment,App\Models\VehicleRequest']);



Route::get('reports/drivers/{driver}/history', [ReportController::class, 'driverHistory'])
    ->middleware(['auth:sanctum', 'can:viewDriverHistory,driver']);
