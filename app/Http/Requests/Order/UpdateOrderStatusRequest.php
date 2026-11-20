<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role;

        return in_array($role, ['admin', 'owner'], true);
    }

    protected function prepareForValidation(): void
    {
        $status = (string) $this->input('order_status', '');
        $normalized = Order::normalizeStatus($status);

        $this->merge([
            'order_status' => $normalized,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'order_status' => [
                'required',
                'string',
                'in:'.implode(',', [
                    Order::STATUS_MENUNGGU_DIPROSES,
                    Order::STATUS_SEDANG_DISIAPKAN,
                    Order::STATUS_SEDANG_DIKEMAS,
                    Order::STATUS_SIAP_DIKIRIM,
                    Order::STATUS_SEDANG_DIANTAR,
                    Order::STATUS_SIAP_DIAMBIL,
                    Order::STATUS_PESANAN_SELESAI,
                    Order::STATUS_CANCELLED,
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_status' => ['nullable', 'in:unpaid,pending,paid,failed,refunded'],
        ];
    }

    /**
     * Custom validation to enforce fulfillment method constraints.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $orderParam = $this->route('order');
            if (! $orderParam) {
                return;
            }

            /** @var Order|null $targetOrder */
            $targetOrder = $orderParam instanceof Order
                ? $orderParam
                : Order::where('order_number', (string) $orderParam)
                    ->when(is_numeric($orderParam), fn ($q) => $q->orWhere('id', (int) $orderParam))
                    ->first();

            if (! $targetOrder) {
                return;
            }

            $method = strtolower((string) $targetOrder->fulfillment_method);
            $newStatus = (string) $this->input('order_status');

            if ($method === 'delivery' && $newStatus === Order::STATUS_SIAP_DIAMBIL) {
                $v->errors()->add('order_status', 'Pesanan metode Delivery tidak boleh memiliki status "Siap Diambil". Gunakan status Siap Dikirim atau Sedang Diantar.');
            }

            if ($method === 'pickup' && in_array($newStatus, [Order::STATUS_SIAP_DIKIRIM, Order::STATUS_SEDANG_DIANTAR], true)) {
                $v->errors()->add('order_status', 'Pesanan metode Pickup tidak boleh memiliki status pengiriman ekspedisi ("Siap Dikirim" atau "Sedang Diantar"). Gunakan status Siap Diambil.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_status.required' => 'Status pesanan wajib dipilih.',
            'order_status.in' => 'Status pesanan yang dipilih tidak valid dalam sistem.',
            'notes.max' => 'Catatan status maksimal 500 karakter.',
        ];
    }
}
