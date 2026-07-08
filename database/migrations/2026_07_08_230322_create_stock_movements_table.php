<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('movement_type', 30);
            $table->integer('quantity_change')->default(0);

            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);

            $table->date('movement_date');
            $table->string('reference_no', 100)->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'movement_type']);
            $table->index('movement_date');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};