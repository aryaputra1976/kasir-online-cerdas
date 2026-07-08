<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->boolean('payment_cash_enabled')->default(true)->after('receipt_show_powered_by');
            $table->boolean('payment_qris_enabled')->default(false)->after('payment_cash_enabled');
            $table->boolean('payment_transfer_enabled')->default(false)->after('payment_qris_enabled');
            $table->boolean('payment_edc_enabled')->default(false)->after('payment_transfer_enabled');

            $table->string('qris_merchant_name')->nullable()->after('payment_edc_enabled');
            $table->string('qris_image_path')->nullable()->after('qris_merchant_name');
            $table->text('qris_note')->nullable()->after('qris_image_path');

            $table->string('bank_name')->nullable()->after('qris_note');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->text('transfer_note')->nullable()->after('bank_account_name');

            $table->text('edc_note')->nullable()->after('transfer_note');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_cash_enabled',
                'payment_qris_enabled',
                'payment_transfer_enabled',
                'payment_edc_enabled',
                'qris_merchant_name',
                'qris_image_path',
                'qris_note',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'transfer_note',
                'edc_note',
            ]);
        });
    }
};