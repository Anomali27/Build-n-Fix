<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('order_status') === 'completed') {
            $this->merge(['order_status' => 'order_completed']);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'order_status' => ['required', 'in:paid,proses,ready_to_pick_up,on_delivery,order_completed,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,pending,paid,failed,refunded'],
        ];
    }
}
