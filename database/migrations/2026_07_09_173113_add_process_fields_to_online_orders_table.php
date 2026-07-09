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
        Schema::table('online_orders', function (Blueprint $table) {
            $table->timestamp('stock_deducted_at')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->after('stock_deducted_at');
            $table->timestamp('completed_at')->nullable()->after('processed_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropColumn([
                'stock_deducted_at',
                'processed_at',
                'completed_at',
                'cancelled_at',
            ]);
        });
    }
};