<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DRIVER_CHECK_SUPPORT = ['mysql', 'mariadb', 'pgsql', 'sqlsrv'];

    public function up(): void
    {
        $this->normalizeStockMovementProductForeignKey();
        $this->addIndexes();

        if (! in_array(DB::getDriverName(), self::DRIVER_CHECK_SUPPORT, true)) {
            return;
        }

        $this->addCheck('sales', 'sales_status_check', "status IN ('COMPLETED')");
        $this->addCheck('sales', 'sales_payment_method_check', "payment_method IN ('CASH', 'QRIS', 'TRANSFER', 'EDC')");
        $this->addCheck('sales', 'sales_amounts_non_negative_check', 'subtotal_amount >= 0 AND discount_amount >= 0 AND tax_amount >= 0 AND total_amount >= 0 AND paid_amount >= 0 AND change_amount >= 0');

        $this->addCheck('sale_items', 'sale_items_quantity_positive_check', 'quantity > 0');
        $this->addCheck('sale_items', 'sale_items_amounts_non_negative_check', 'unit_price >= 0 AND (purchase_price IS NULL OR purchase_price >= 0) AND subtotal_amount >= 0');

        $this->addCheck('online_order_items', 'online_order_items_quantity_positive_check', 'quantity > 0');
        $this->addCheck('online_order_items', 'online_order_items_amounts_non_negative_check', 'unit_price >= 0 AND subtotal_amount >= 0');

        $this->addCheck('stock_movements', 'stock_movements_type_check', "movement_type IN ('IN', 'OUT', 'ADJUSTMENT')");
        $this->addCheck('stock_movements', 'stock_movements_chain_check', 'stock_before >= 0 AND stock_after >= 0 AND stock_before + quantity_change = stock_after');

        $this->addCheck('products', 'products_amounts_non_negative_check', 'purchase_price >= 0 AND selling_price >= 0 AND minimum_stock >= 0');
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), self::DRIVER_CHECK_SUPPORT, true)) {
            foreach ([
                ['products', 'products_amounts_non_negative_check'],
                ['stock_movements', 'stock_movements_chain_check'],
                ['stock_movements', 'stock_movements_type_check'],
                ['online_order_items', 'online_order_items_amounts_non_negative_check'],
                ['online_order_items', 'online_order_items_quantity_positive_check'],
                ['sale_items', 'sale_items_amounts_non_negative_check'],
                ['sale_items', 'sale_items_quantity_positive_check'],
                ['sales', 'sales_amounts_non_negative_check'],
                ['sales', 'sales_payment_method_check'],
                ['sales', 'sales_status_check'],
            ] as [$table, $constraint]) {
                $this->dropCheck($table, $constraint);
            }
        }

        $this->dropIndexes();
    }

    private function normalizeStockMovementProductForeignKey(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });
    }

    private function addIndexes(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'sale_date', 'payment_method'], 'sales_status_sale_date_payment_idx');
        });

        Schema::table('online_orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'online_orders_status_created_idx');
            $table->index(['payment_status', 'created_at'], 'online_orders_payment_status_created_idx');
            $table->index(['status', 'payment_status', 'completed_at'], 'online_orders_status_payment_completed_idx');
            $table->index('sale_id', 'online_orders_sale_id_idx');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index(['product_id', 'sale_id'], 'sale_items_product_sale_idx');
        });
    }

    private function dropIndexes(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('sale_items_product_sale_idx');
        });

        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropIndex('online_orders_sale_id_idx');
            $table->dropIndex('online_orders_status_payment_completed_idx');
            $table->dropIndex('online_orders_payment_status_created_idx');
            $table->dropIndex('online_orders_status_created_idx');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_sale_date_payment_idx');
        });
    }

    private function addCheck(string $table, string $name, string $expression): void
    {
        $sql = DB::getDriverName() === 'sqlsrv'
            ? "ALTER TABLE {$table} WITH CHECK ADD CONSTRAINT {$name} CHECK ({$expression})"
            : "ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})";

        DB::statement($sql);
    }

    private function dropCheck(string $table, string $name): void
    {
        $sql = match (DB::getDriverName()) {
            'mysql' => "ALTER TABLE {$table} DROP CHECK {$name}",
            'mariadb', 'pgsql' => "ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}",
            'sqlsrv' => "ALTER TABLE {$table} DROP CONSTRAINT {$name}",
            default => null,
        };

        if ($sql) {
            try {
                DB::statement($sql);
            } catch (Throwable) {
                // Constraint may already be absent on partially rolled-back databases.
            }
        }
    }
};
