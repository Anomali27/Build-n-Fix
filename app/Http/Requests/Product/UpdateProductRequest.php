<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $productParam = $this->route('product');
        $productId = $productParam instanceof Product
            ? $productParam->id
            : Product::findByIdentifier((string) $productParam)?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:0'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,inactive'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'stocks' => ['nullable', 'array'],
            'stocks.*' => ['nullable', 'integer', 'min:0'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
