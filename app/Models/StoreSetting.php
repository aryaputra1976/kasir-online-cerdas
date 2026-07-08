<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'owner_name',
        'phone',
        'email',
        'address',
        'logo_path',
        'tax_percentage',
        'receipt_footer',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Kasir Online Cerdas',
                'tax_percentage' => 0,
                'receipt_footer' => 'Terima kasih sudah berbelanja.',
            ]
        );
    }
}