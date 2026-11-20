<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('order_status_histories')) {
            Schema::create('order_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status_from', 50)->nullable();
                $table->string('status_to', 50);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'discount')) {
                $table->unsignedBigInteger('discount')->default(0)->after('delivery_fee');
            }
            if (! Schema::hasColumn('orders', 'voucher_code')) {
                $table->string('voucher_code', 50)->nullable()->after('discount');
            }
        });

        // Migrate existing order statuses to standard naming and seed initial history
        $orders = DB::table('orders')->get();
        foreach ($orders as $order) {
            $mappedStatus = match (strtolower((string) $order->order_status)) {
                'paid' => 'menunggu_diproses',
                'proses' => 'sedang_disiapkan',
                'ready_to_pick_up' => 'siap_diambil',
                'on_delivery' => 'sedang_diantar',
                'order_completed', 'completed' => 'pesanan_selesai',
                default => $order->order_status,
            };

            if ($mappedStatus !== $order->order_status) {
                DB::table('orders')->where('id', $order->id)->update([
                    'order_status' => $mappedStatus,
                ]);
            }

            $historyExists = DB::table('order_status_histories')->where('order_id', $order->id)->exists();
            if (! $historyExists) {
                DB::table('order_status_histories')->insert([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'status_from' => null,
                    'status_to' => $mappedStatus,
                    'notes' => 'Pesanan berhasil dibuat dan pembayaran dikonfirmasi.',
                    'created_at' => $order->created_at ?? now(),
                    'updated_at' => $order->created_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
            if (Schema::hasColumn('orders', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
