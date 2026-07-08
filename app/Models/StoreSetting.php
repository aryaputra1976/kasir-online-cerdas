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
        'receipt_policy_text',
        'receipt_show_logo',
        'receipt_show_sku',
        'receipt_show_powered_by',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'receipt_show_logo' => 'boolean',
        'receipt_show_sku' => 'boolean',
        'receipt_show_powered_by' => 'boolean',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Kasir Online Cerdas',
                'tax_percentage' => 0,
                'receipt_footer' => 'Terima kasih sudah berbelanja.',
                'receipt_policy_text' => 'Barang yang sudah dibeli tidak dapat dikembalikan.',
                'receipt_show_logo' => true,
                'receipt_show_sku' => true,
                'receipt_show_powered_by' => true,
            ]
        );
    }
}