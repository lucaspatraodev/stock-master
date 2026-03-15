<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Verificação - usuário registrado ou não (para controle de registro único)
     */
    public function canRegister()
    {
        $userExists = User::exists();

        return response()->json([
            'can_register' => !$userExists,
            'user_count' => User::count(),
        ]);
    }

    /**
     * Registro de novo usuário (apenas se nenhum usuário existir)
     */
    public function register(Request $request)
    {
        if (User::exists()) {
            return response()->json([
                'errors' => [
                    'registration' => ['O registro foi desativado. Um usuário já está registrado no sistema.']
                ]
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
            'message' => 'Usuário registrado com sucesso!',
        ], 201);
    }

    /**
     * Login do usuário e retorna token Sanctum
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Os dados informados estão incorretos.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ]);
    }

    /**
     * Retorna usuário autenticado
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout do usuário (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Desconectado com sucesso']);
    }
}
