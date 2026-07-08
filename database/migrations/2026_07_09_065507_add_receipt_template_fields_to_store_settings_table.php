<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->text('receipt_policy_text')->nullable()->after('receipt_footer');
            $table->boolean('receipt_show_logo')->default(true)->after('receipt_policy_text');
            $table->boolean('receipt_show_sku')->default(true)->after('receipt_show_logo');
            $table->boolean('receipt_show_powered_by')->default(true)->after('receipt_show_sku');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_policy_text',
                'receipt_show_logo',
                'receipt_show_sku',
                'receipt_show_powered_by',
            ]);
        });
    }
};