<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\User\RegisterDriverRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const DRIVER_ROLE_ID = 3;

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::withTrashed()
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
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

    public function registerDriver(RegisterDriverRequest $request)
    {
        $validated = $request->validated();
        $validated['role_id'] = self::DRIVER_ROLE_ID;

        $user = User::create($validated);

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'data' => $user->load('role:id,name'),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso.'
        ], 200);
    }
}
