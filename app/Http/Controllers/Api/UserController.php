<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @group Usuarios
     *
     * Lista usuarios do sistema.
     *
     * @authenticated
     */
    public function index(): JsonResponse
    {
        $users = User::query()
            ->orderBy('name')
            ->get();

        return UserResource::collection($users)->response();
    }

    /**
     * @group Usuarios
     *
     * Cria um novo usuario.
     *
     * @authenticated
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @group Usuarios
     *
     * Atualiza dados do usuario.
     *
     * @authenticated
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $payload['name'];
        $user->email = $payload['email'];
        if (!empty($payload['password'])) {
            $user->password = Hash::make($payload['password']);
        }
        $user->save();

        return (new UserResource($user))->response();
    }

    /**
     * @group Usuarios
     *
     * Remove um usuario (exceto o logado).
     *
     * @authenticated
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Voce é o administrador e não pode remover seu proprio usuario.',
            ], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario removido.']);
    }
}
