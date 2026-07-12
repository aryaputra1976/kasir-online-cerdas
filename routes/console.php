<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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
