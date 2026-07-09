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
            $table->foreignId('sale_id')
                ->nullable()
                ->after('cancelled_at')
                ->constrained('sales')
                ->nullOnDelete();

            $table->timestamp('converted_to_sale_at')
                ->nullable()
                ->after('sale_id');
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sale_id');
            $table->dropColumn('converted_to_sale_at');
        });
    }
};