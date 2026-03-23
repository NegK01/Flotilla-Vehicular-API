<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

Route::apiResource('user', UserController::class)->missing(function (Request $request) {
    return response()->json([
        'message' => 'Usuario no encontrado',
    ], 404);
});
Route::patch('user/{id}/restore', [UserController::class, 'restore']);
