<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)
                ->nullable()
                ->after('unit_price');
        });

        DB::table('sale_items')
            ->whereNotNull('product_id')
            ->update([
                'purchase_price' => DB::raw('(select products.purchase_price from products where products.id = sale_items.product_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });
    }
};
