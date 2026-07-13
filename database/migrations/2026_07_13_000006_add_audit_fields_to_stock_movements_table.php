<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('product_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('source_type', 50)->nullable()->after('created_by');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');

            $table->index(['product_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
            $table->index(['source_type', 'source_id']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });

            DB::statement('ALTER TABLE stock_movements MODIFY product_id BIGINT UNSIGNED NULL');

            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });

            DB::statement('ALTER TABLE stock_movements MODIFY product_id BIGINT UNSIGNED NOT NULL');

            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'movement_date']);
            $table->dropIndex(['movement_type', 'movement_date']);
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
