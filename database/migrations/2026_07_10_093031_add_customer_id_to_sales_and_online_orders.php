<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('customers')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('online_orders') && ! Schema::hasColumn('online_orders', 'customer_id')) {
            Schema::table('online_orders', function (Blueprint $table) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('customers')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('online_orders') && Schema::hasColumn('online_orders', 'customer_id')) {
            Schema::table('online_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('customer_id');
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropConstrainedForeignId('customer_id');
            });
        }
    }
};