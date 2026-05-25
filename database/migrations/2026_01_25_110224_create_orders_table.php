<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('KHR');
            $table->string('md5')->unique();
            $table->string('status')->default('PENDING'); // PENDING, PAID, FAILED
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};

