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
     * @group Auth
     * @groupDescription Endpoints de autenticacao (login, registro inicial e logout).
     *
     * Verifica se o registro inicial ainda esta liberado.
     *
     * @unauthenticated
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
     * @group Auth
     *
     * Cria o primeiro usuario do sistema.
     *
     * @unauthenticated
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
     * @group Auth
     *
     * Login e retorno do token Bearer.
     *
     * @unauthenticated
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
     * @group Auth
     *
     * Retorna o usuario autenticado.
     *
     * @authenticated
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @group Auth
     *
     * Invalida o token atual.
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Desconectado com sucesso']);
    }
}
