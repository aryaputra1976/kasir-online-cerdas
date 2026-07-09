<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('online_order_id')
                ->constrained('online_orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('unit')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal_amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index('online_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_items');
    }
};