<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_access_products_when_unauthenticated(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_authenticated_user_can_access_products(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/products')
            ->assertOk();
    }

    public function test_register_is_blocked_when_user_already_exists(): void
    {
        User::factory()->create();

        $payload = [
            'name' => 'Segundo Usuario',
            'email' => 'segundo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'O registro foi desativado. Um usuário já está registrado no sistema.',
            ]);
    }
}
