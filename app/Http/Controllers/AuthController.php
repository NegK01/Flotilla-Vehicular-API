<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::withTrashed()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        if ($user->trashed()) {
            return response()->json([
                'message' => 'Usuario inactivo.'
            ], 403);
        }

        $token = $user->createToken('postman-token')->plainTextToken;
        return response()->json([
            'message' => 'Login exitoso.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user->load('role'),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso.'
        ], 200);
    }
}
