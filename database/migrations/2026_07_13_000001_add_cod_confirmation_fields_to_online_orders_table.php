<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->timestamp('cod_confirmed_at')
                ->nullable()
                ->after('status');

            $table->foreignId('cod_confirmed_by')
                ->nullable()
                ->after('cod_confirmed_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cod_confirmed_by');
            $table->dropColumn('cod_confirmed_at');
        });
    }
};
