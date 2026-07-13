<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('app:check-assets', function () {
    $requiredPaths = [
        public_path('assets'),
        public_path('assets/css'),
        public_path('assets/js'),
        public_path('assets/images'),
        public_path('assets/css/style.css'),
        public_path('assets/js/custom/custom.js'),
        public_path('assets/images/logo.svg'),
    ];

    $missingPaths = collect($requiredPaths)
        ->reject(fn (string $path) => File::exists($path))
        ->values();

    if ($missingPaths->isNotEmpty()) {
        $this->error('Asset publik Trezo belum lengkap. Deploy dari clone baru kemungkinan akan rusak.');

        foreach ($missingPaths as $missingPath) {
            $this->line('- Missing: ' . $missingPath);
        }

        $this->newLine();
        $this->warn('Siapkan folder public/assets dari paket deployment privat atau hasil copy lokal sebelum aplikasi dijalankan.');

        return self::FAILURE;
    }

    $this->info('Asset publik Trezo terdeteksi lengkap untuk kebutuhan minimum aplikasi.');

    return self::SUCCESS;
})->purpose('Memeriksa kelengkapan minimum asset publik Trezo untuk deploy');

Artisan::command('app:audit-data-integrity', function () {
    $saleSubtotalMismatchCount = DB::table('sales')
        ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
        ->select('sales.id')
        ->groupBy('sales.id', 'sales.subtotal_amount')
        ->havingRaw(
            'ABS(
                COALESCE(SUM(sale_items.subtotal_amount), 0)
                - sales.subtotal_amount
            ) > 0.01'
        )
        ->get()
        ->count();

    $saleTotalMismatchCount = DB::table('sales')
        ->whereRaw(
            'ABS(
                total_amount
                - (
                    subtotal_amount
                    - discount_amount
                    + tax_amount
                )
            ) > 0.01'
        )
        ->count();

    $checks = [
        'invoice duplikat' => DB::table('sales')
            ->select('invoice_no')
            ->groupBy('invoice_no')
            ->havingRaw('COUNT(*) > 1')
            ->count(),
        'order_no duplikat' => DB::table('online_orders')
            ->select('order_no')
            ->groupBy('order_no')
            ->havingRaw('COUNT(*) > 1')
            ->count(),
        'tracking_token duplikat' => DB::table('online_orders')
            ->select('tracking_token')
            ->groupBy('tracking_token')
            ->havingRaw('COUNT(*) > 1')
            ->count(),
        'sale_items tanpa sale' => DB::table('sale_items')
            ->leftJoin('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.id')
            ->count(),
        'online_order_items tanpa order' => DB::table('online_order_items')
            ->leftJoin('online_orders', 'online_orders.id', '=', 'online_order_items.online_order_id')
            ->whereNull('online_orders.id')
            ->count(),
        'online_orders.sale_id invalid' => DB::table('online_orders')
            ->leftJoin('sales', 'sales.id', '=', 'online_orders.sale_id')
            ->whereNotNull('online_orders.sale_id')
            ->whereNull('sales.id')
            ->count(),
        'stock_movements product_id invalid' => DB::table('stock_movements')
            ->leftJoin('products', 'products.id', '=', 'stock_movements.product_id')
            ->whereNotNull('stock_movements.product_id')
            ->whereNull('products.id')
            ->count(),
        'customer_id invalid pada sales' => DB::table('sales')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->whereNotNull('sales.customer_id')
            ->whereNull('customers.id')
            ->count(),
        'customer_id invalid pada online_orders' => DB::table('online_orders')
            ->leftJoin('customers', 'customers.id', '=', 'online_orders.customer_id')
            ->whereNotNull('online_orders.customer_id')
            ->whereNull('customers.id')
            ->count(),
        'stok produk negatif' => DB::table('products')->where('stock', '<', 0)->count(),
        'mutasi stok chain invalid' => DB::table('stock_movements')
            ->whereRaw('stock_before + quantity_change <> stock_after')
            ->count(),
        'completed paid order tanpa sale' => DB::table('online_orders')
            ->where('status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereNull('sale_id')
            ->count(),
        'sale_id dipakai lebih dari satu order' => DB::table('online_orders')
            ->select('sale_id')
            ->whereNotNull('sale_id')
            ->groupBy('sale_id')
            ->havingRaw('COUNT(*) > 1')
            ->count(),
        'subtotal sale tidak cocok total item' => $saleSubtotalMismatchCount,
        'total akhir sale tidak cocok rumus' => $saleTotalMismatchCount,
        'status sale invalid' => DB::table('sales')
            ->whereNotIn('status', ['COMPLETED'])
            ->count(),
        'status online order invalid' => DB::table('online_orders')
            ->whereNotIn('status', ['NEW', 'CONFIRMED', 'PROCESSING', 'COMPLETED', 'CANCELLED'])
            ->count(),
        'status pembayaran online invalid' => DB::table('online_orders')
            ->whereNotIn('payment_status', ['UNPAID', 'WAITING_CONFIRMATION', 'PAID', 'REJECTED'])
            ->count(),
    ];

    $this->info('Audit integritas data selesai. Command ini tidak mengubah data.');
    $this->table(['Pemeriksaan', 'Jumlah'], collect($checks)->map(
        fn (int $count, string $name) => [$name, $count]
    )->values()->all());

    return collect($checks)->contains(fn (int $count) => $count > 0)
        ? self::FAILURE
        : self::SUCCESS;
})->purpose('Audit read-only integritas data transaksi, stok, invoice, dan order');
