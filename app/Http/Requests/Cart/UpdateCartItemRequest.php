<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['nullable', 'integer', 'min:0'],
            'fulfillment_method' => ['nullable', 'string', 'in:pickup,delivery'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];
    }
}
