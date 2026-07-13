<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const STOCK_STATUS_SAFE = 'safe';
    public const STOCK_STATUS_LOW = 'low';
    public const STOCK_STATUS_EMPTY = 'empty';
    public const STOCK_STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_status === self::STOCK_STATUS_LOW;
    }

    public function getStockStatusAttribute(): string
    {
        if (! $this->is_active) {
            return self::STOCK_STATUS_INACTIVE;
        }

        if ($this->stock <= 0) {
            return self::STOCK_STATUS_EMPTY;
        }

        if ($this->stock <= $this->minimum_stock) {
            return self::STOCK_STATUS_LOW;
        }

        return self::STOCK_STATUS_SAFE;
    }

    public function scopeWithStockStatus($query, ?string $status)
    {
        return match ($status) {
            self::STOCK_STATUS_SAFE => $query
                ->where('is_active', true)
                ->whereColumn('stock', '>', 'minimum_stock'),
            self::STOCK_STATUS_LOW => $query
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->whereColumn('stock', '<=', 'minimum_stock'),
            self::STOCK_STATUS_EMPTY => $query
                ->where('is_active', true)
                ->where('stock', '<=', 0),
            self::STOCK_STATUS_INACTIVE => $query->where('is_active', false),
            default => $query,
        };
    }

    public function scopeActiveSafeStock($query)
    {
        return $query->withStockStatus(self::STOCK_STATUS_SAFE);
    }

    public function scopeActiveLowStock($query)
    {
        return $query->withStockStatus(self::STOCK_STATUS_LOW);
    }

    public function scopeActiveEmptyStock($query)
    {
        return $query->withStockStatus(self::STOCK_STATUS_EMPTY);
    }
}
