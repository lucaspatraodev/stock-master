<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * @group Produtos
     * @groupDescription Listar produtos e gerenciar cadastros.
     *
     * Lista todos os produtos.
     *
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('images')
            ->orderBy('title')
            ->get();

        return ProductResource::collection($products)->response();
    }

    /**
     * @group Produtos
     *
     * Cria um produto com imagens opcionais.
     *
     * @authenticated
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->safe()->except('images');
        $images = $request->file('images', []);
        if ($images instanceof \Illuminate\Http\UploadedFile) {
            $images = [$images];
        }

        $product = DB::transaction(function () use ($payload, $user) {
            $product = Product::create([
                ...$payload,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);

            return $product;
        });

        if ($images !== []) {
            $this->storeImages($product, $images);
        }

        $product->refresh();
        $product->load('images');

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @group Produtos
     *
     * Atualiza os dados do produto.
     *
     * @authenticated
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $payload = $request->safe()->except('images');
        $images = $request->file('images', []);
        if ($images instanceof \Illuminate\Http\UploadedFile) {
            $images = [$images];
        }
        $fields = array_keys($payload);

        $product = DB::transaction(function () use ($product, $payload, $fields, $user) {
            $before = $product->only($fields);
            $product->fill($payload);
            $product->updated_by = $user?->id;

            $dirty = array_intersect_key($product->getDirty(), array_flip($fields));
            $product->save();

            return $product;
        });

        if ($images !== []) {
            $this->storeImages($product, $images);
        }

        $product->load('images');

        return (new ProductResource($product))->response();
    }

    /**
     * @group Produtos
     *
     * Inativa o produto (sem excluir).
     *
     * @authenticated
     */
    public function inactivate(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$product->is_active) {
            return response()->json([
                'message' => 'Produto já está inativo.',
            ], 409);
        }

        $product = DB::transaction(function () use ($product, $user) {
            $before = ['is_active' => $product->is_active];
            $product->is_active = false;
            $product->updated_by = $user?->id;
            $product->save();

            return $product;
        });

        return (new ProductResource($product))->response();
    }

    /**
     * @group Produtos
     *
     * Remove uma imagem do produto.
     *
     * @authenticated
     */
    public function destroyImage(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Imagem nao encontrada.'], 404);
        }

        DB::transaction(function () use ($image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        });

        $product->load('images');

        return (new ProductResource($product))->response();
    }

    /**
     * @group Produtos
     *
     * Reordena as imagens do produto.
     *
     * @authenticated
     */
    public function reorderImages(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ]);

        $order = $data['order'];
        $existingIds = $product->images()->pluck('id')->all();

        if (array_diff($order, $existingIds) !== [] || count($order) !== count($existingIds)) {
            return response()->json(['message' => 'Ordem de imagens invalida.'], 422);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $imageId) {
                ProductImage::whereKey($imageId)->update(['position' => $index]);
            }
        });

        $product->load('images');

        return (new ProductResource($product))->response();
    }

    /**
     * Salva imagens e mantem a ordem.
     *
     * @param \Illuminate\Http\UploadedFile[] $images
     */
    private function storeImages(Product $product, array $images): void
    {
        $startPosition = (int) $product->images()->max('position');
        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'position' => ++$startPosition,
            ]);
        }
    }

}
