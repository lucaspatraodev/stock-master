<?php

namespace App\Http\Requests;

use App\Services\HtmlSanitizer;
use App\Services\ProductPricing;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => [
                'nullable',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (!$this->isAllowedHtml($value)) {
                        $fail('A descricao permite apenas as tags: <p>, <br>, <b>, <strong>.');
                    }
                },
            ],
            'sale_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $cost = (float) $this->input('cost');
                    if ($cost > 0 && !ProductPricing::isSalePriceValid($cost, (float) $value)) {
                        $fail('O preco de venda deve ser no minimo 10% maior que o custo.');
                    }
                },
            ],
            'cost' => ['required', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Parametros de body para o Scribe.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'title' => [
                'description' => 'Titulo do produto.',
                'example' => 'Notebook Gamer',
            ],
            'description' => [
                'description' => 'Descricao em HTML permitido (<p>, <br>, <b>, <strong>).',
                'example' => '<p>Descricao atualizada</p>',
            ],
            'sale_price' => [
                'description' => 'Preco de venda (deve ser >= custo + 10%).',
                'example' => 219.90,
            ],
            'cost' => [
                'description' => 'Custo do produto.',
                'example' => 170.00,
            ],
            'images' => [
                'description' => 'Novas imagens (multipart).',
            ],
        ];
    }

    private function isAllowedHtml(?string $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return HtmlSanitizer::sanitize($value) === $value;
    }
}
