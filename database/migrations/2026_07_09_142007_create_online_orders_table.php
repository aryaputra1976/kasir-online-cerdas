<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique();
            $table->string('tracking_token')->unique();

            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();

            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('UNPAID');
            $table->string('payment_proof_path')->nullable();
            $table->text('payment_note')->nullable();
            $table->text('admin_payment_note')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->timestamp('payment_rejected_at')->nullable();

            $table->string('status')->default('NEW');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('order_no');
            $table->index('tracking_token');
            $table->index('payment_status');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};