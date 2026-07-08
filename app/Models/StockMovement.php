<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'IN';
    public const TYPE_OUT = 'OUT';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'movement_date',
        'reference_no',
        'note',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'quantity_change' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'movement_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            self::TYPE_IN => 'Stok Masuk',
            self::TYPE_OUT => 'Stok Keluar',
            self::TYPE_ADJUSTMENT => 'Penyesuaian Stok',
            default => 'Mutasi Stok',
        };
    }

    public function getQuantityChangeLabelAttribute(): string
    {
        if ($this->quantity_change > 0) {
            return '+' . number_format($this->quantity_change, 0, ',', '.');
        }

        return number_format($this->quantity_change, 0, ',', '.');
    }
}