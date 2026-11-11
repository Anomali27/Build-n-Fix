<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],
            'recipient_name' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:255'],
            'phone' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:30'],
            'address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:500'],
            'city' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:100'],
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
            'recipient_name.required_if' => 'Nama penerima wajib diisi untuk metode pengiriman.',
            'phone.required_if' => 'Nomor telepon penerima wajib diisi untuk metode pengiriman.',
            'address.required_if' => 'Alamat pengiriman wajib diisi untuk metode pengiriman.',
            'city.required_if' => 'Kota tujuan wajib diisi untuk metode pengiriman.',
        ];
    }
}
