<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('contact_person', 100);
            $table->string('phone', 30);
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->default('Pontianak');
            $table->string('status', 20)->default('active')->index();
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
