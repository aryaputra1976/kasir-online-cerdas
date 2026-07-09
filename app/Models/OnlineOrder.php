<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineOrder extends Model
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const PAYMENT_UNPAID = 'UNPAID';
    public const PAYMENT_WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    public const PAYMENT_PAID = 'PAID';
    public const PAYMENT_REJECTED = 'REJECTED';

    protected $fillable = [
        'order_no',
        'tracking_token',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_proof_path',
        'payment_note',
        'admin_payment_note',
        'paid_at',
        'payment_confirmed_at',
        'payment_rejected_at',
        'status',
        'stock_deducted_at',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'sale_id',
        'converted_to_sale_at',
        'note',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'converted_to_sale_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OnlineOrderItem::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'Belum Dibayar',
            self::PAYMENT_WAITING_CONFIRMATION => 'Menunggu Konfirmasi',
            self::PAYMENT_PAID => 'Dibayar',
            self::PAYMENT_REJECTED => 'Ditolak',
            default => $this->payment_status,
        };
    }

    public function getPaymentStatusClassAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'bg-secondary bg-opacity-10 text-secondary',
            self::PAYMENT_WAITING_CONFIRMATION => 'bg-warning bg-opacity-10 text-warning',
            self::PAYMENT_PAID => 'bg-success bg-opacity-10 text-success',
            self::PAYMENT_REJECTED => 'bg-danger bg-opacity-10 text-danger',
            default => 'bg-light text-body border',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Order Baru',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'bg-primary bg-opacity-10 text-primary',
            self::STATUS_PROCESSING => 'bg-warning bg-opacity-10 text-warning',
            self::STATUS_COMPLETED => 'bg-success bg-opacity-10 text-success',
            self::STATUS_CANCELLED => 'bg-danger bg-opacity-10 text-danger',
            default => 'bg-light text-body border',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            Sale::PAYMENT_CASH => 'Tunai / COD',
            Sale::PAYMENT_QRIS => 'QRIS',
            Sale::PAYMENT_TRANSFER => 'Transfer',
            Sale::PAYMENT_EDC => 'EDC / Kartu',
            null => 'Belum dipilih',
            default => $this->payment_method,
        };
    }

    public function getStockStatusLabelAttribute(): string
    {
        return $this->stock_deducted_at
            ? 'Stok Sudah Dikurangi'
            : 'Stok Belum Dikurangi';
    }

    public function getStockStatusClassAttribute(): string
    {
        return $this->stock_deducted_at
            ? 'bg-success bg-opacity-10 text-success'
            : 'bg-secondary bg-opacity-10 text-secondary';
    }

    public function getSaleConversionStatusLabelAttribute(): string
    {
        return $this->sale_id
            ? 'Sudah Masuk Penjualan'
            : 'Belum Masuk Penjualan';
    }

    public function getSaleConversionStatusClassAttribute(): string
    {
        return $this->sale_id
            ? 'bg-success bg-opacity-10 text-success'
            : 'bg-secondary bg-opacity-10 text-secondary';
    }

    public function canConfirmPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_WAITING_CONFIRMATION;
    }

    public function canRejectPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_WAITING_CONFIRMATION;
    }

    public function hasStockDeducted(): bool
    {
        return ! is_null($this->stock_deducted_at);
    }

    public function canProcess(): bool
    {
        if ($this->status !== self::STATUS_NEW) {
            return false;
        }

        if ($this->payment_method === Sale::PAYMENT_CASH) {
            return true;
        }

        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function canCancel(): bool
    {
        return $this->status === self::STATUS_NEW
            && ! $this->hasStockDeducted();
    }

    public function canConvertToSale(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && $this->payment_status === self::PAYMENT_PAID
            && is_null($this->sale_id);
    }
}