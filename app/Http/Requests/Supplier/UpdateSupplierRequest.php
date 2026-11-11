<?php

namespace App\Http\Requests\Supplier;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $supplierParam = $this->route('supplier');
        $supplierId = $supplierParam instanceof Supplier
            ? $supplierParam->id
            : Supplier::findByIdentifier((string) $supplierParam)?->id;

        return [
            'code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('suppliers', 'code')->ignore($supplierId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }
}
