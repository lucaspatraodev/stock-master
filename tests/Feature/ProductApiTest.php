<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_flow_create_list_update_inactivate(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $createPayload = [
            'title' => 'Produto Teste',
            'description' => '<p>Descricao</p>',
            'cost' => 10.00,
            'sale_price' => 11.00,
            'images' => [
                UploadedFile::fake()->image('foto1.jpg'),
            ],
        ];

        $createResponse = $this->actingAs($user, 'sanctum')
            ->post('/api/products', $createPayload, ['Accept' => 'application/json']);

        $createResponse->assertCreated();
        $createdId = $createResponse->json('data.id');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonFragment(['id' => $createdId]);

        $updatePayload = [
            'title' => 'Produto Atualizado',
            'description' => '<p>Descricao</p>',
            'cost' => 20.00,
            'sale_price' => 22.00,
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/products/{$createdId}", $updatePayload)
            ->assertOk()
            ->assertJsonFragment(['title' => 'Produto Atualizado']);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/products/{$createdId}/inactivate")
            ->assertOk()
            ->assertJsonFragment(['is_active' => false]);
    }

    public function test_upload_validation_rejects_non_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $payload = [
            'title' => 'Produto Imagem Invalida',
            'description' => '<p>Descricao</p>',
            'cost' => 10.00,
            'sale_price' => 11.00,
            'images' => [
                UploadedFile::fake()->create('arquivo.txt', 10, 'text/plain'),
            ],
        ];

        $this->actingAs($user, 'sanctum')
            ->post('/api/products', $payload, ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }
}
