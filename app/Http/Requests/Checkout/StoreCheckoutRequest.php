<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $itemFulfillments = array_map(
            fn (mixed $method): string => strtolower((string) $method),
            (array) $this->input('item_fulfillments', [])
        );

        $hasDelivery = in_array('delivery', $itemFulfillments, true)
            || strtolower((string) $this->input('fulfillment_method')) === 'delivery';

        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'fulfillment_method' => ['nullable', 'in:pickup,delivery,mixed'],
            'item_fulfillments' => ['nullable', 'array'],
            'item_fulfillments.*' => ['required', 'in:pickup,delivery'],
            'recipient_name' => ['nullable', Rule::requiredIf($hasDelivery), 'string', 'max:255'],
            'phone' => ['nullable', Rule::requiredIf($hasDelivery), 'string', 'max:30'],
            'address' => ['nullable', Rule::requiredIf($hasDelivery), 'string', 'max:500'],
            'city' => ['nullable', Rule::requiredIf($hasDelivery), 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi untuk metode pengiriman.',
            'recipient_name.required_if' => 'Nama penerima wajib diisi untuk metode pengiriman.',
            'phone.required' => 'Nomor telepon penerima wajib diisi untuk metode pengiriman.',
            'phone.required_if' => 'Nomor telepon penerima wajib diisi untuk metode pengiriman.',
            'address.required' => 'Alamat pengiriman wajib diisi untuk metode pengiriman.',
            'address.required_if' => 'Alamat pengiriman wajib diisi untuk metode pengiriman.',
            'city.required' => 'Kota tujuan wajib diisi untuk metode pengiriman.',
            'city.required_if' => 'Kota tujuan wajib diisi untuk metode pengiriman.',
        ];
    }
}
