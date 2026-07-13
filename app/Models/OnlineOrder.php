<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineOrder extends Model
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const PAYMENT_UNPAID = 'UNPAID';
    public const PAYMENT_WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    public const PAYMENT_PAID = 'PAID';
    public const PAYMENT_REJECTED = 'REJECTED';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID,
        self::PAYMENT_WAITING_CONFIRMATION,
        self::PAYMENT_PAID,
        self::PAYMENT_REJECTED,
    ];

    public const PAYMENT_METHODS = [
        Sale::PAYMENT_CASH,
        Sale::PAYMENT_QRIS,
        Sale::PAYMENT_TRANSFER,
        Sale::PAYMENT_EDC,
    ];

    protected $fillable = [
        'customer_id',
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
        'cod_confirmed_at',
        'cod_confirmed_by',
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
        'cod_confirmed_at' => 'datetime',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function codConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_confirmed_by');
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatusLabel($this->payment_status);
    }

    public function getPaymentStatusClassAttribute(): string
    {
        return self::paymentStatusClass($this->payment_status);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return self::statusClass($this->status);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethodLabel($this->payment_method);
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
        if ($this->status === self::STATUS_COMPLETED
            && $this->payment_status === self::PAYMENT_PAID
            && is_null($this->sale_id)) {
            return 'Anomali Belum Masuk Penjualan';
        }

        return $this->sale_id ? 'Sudah Masuk Penjualan' : 'Belum Masuk Penjualan';
    }

    public function getSaleConversionStatusClassAttribute(): string
    {
        if ($this->status === self::STATUS_COMPLETED
            && $this->payment_status === self::PAYMENT_PAID
            && is_null($this->sale_id)) {
            return 'bg-danger bg-opacity-10 text-danger';
        }

        return $this->sale_id
            ? 'bg-success bg-opacity-10 text-success'
            : 'bg-secondary bg-opacity-10 text-secondary';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'Order Baru',
            self::STATUS_CONFIRMED => 'Dikonfirmasi',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public static function paymentStatusLabels(): array
    {
        return [
            self::PAYMENT_UNPAID => 'Belum Dibayar',
            self::PAYMENT_WAITING_CONFIRMATION => 'Menunggu Konfirmasi',
            self::PAYMENT_PAID => 'Dibayar',
            self::PAYMENT_REJECTED => 'Ditolak',
        ];
    }

    public static function paymentMethodLabels(): array
    {
        return [
            Sale::PAYMENT_CASH => 'Tunai / COD',
            Sale::PAYMENT_QRIS => 'QRIS',
            Sale::PAYMENT_TRANSFER => 'Transfer',
            Sale::PAYMENT_EDC => 'EDC / Kartu',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return self::statusLabels()[$status] ?? ($status ?: '-');
    }

    public static function paymentStatusLabel(?string $status): string
    {
        return self::paymentStatusLabels()[$status] ?? ($status ?: '-');
    }

    public static function paymentMethodLabel(?string $method): string
    {
        return self::paymentMethodLabels()[$method] ?? ($method ?: 'Belum dipilih');
    }

    public static function statusClass(?string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'bg-primary bg-opacity-10 text-primary',
            self::STATUS_CONFIRMED => 'bg-info bg-opacity-10 text-info',
            self::STATUS_PROCESSING => 'bg-warning bg-opacity-10 text-warning',
            self::STATUS_COMPLETED => 'bg-success bg-opacity-10 text-success',
            self::STATUS_CANCELLED => 'bg-danger bg-opacity-10 text-danger',
            default => 'bg-light text-body border',
        };
    }

    public static function paymentStatusClass(?string $status): string
    {
        return match ($status) {
            self::PAYMENT_UNPAID => 'bg-secondary bg-opacity-10 text-secondary',
            self::PAYMENT_WAITING_CONFIRMATION => 'bg-warning bg-opacity-10 text-warning',
            self::PAYMENT_PAID => 'bg-success bg-opacity-10 text-success',
            self::PAYMENT_REJECTED => 'bg-danger bg-opacity-10 text-danger',
            default => 'bg-light text-body border',
        };
    }

    public function consistencyIndicators(): array
    {
        $indicators = [];

        if ($this->status === self::STATUS_COMPLETED && $this->payment_status !== self::PAYMENT_PAID) {
            $indicators[] = 'Selesai tetapi belum PAID';
        }

        if ($this->status === self::STATUS_COMPLETED
            && $this->payment_status === self::PAYMENT_PAID
            && is_null($this->sale_id)) {
            $indicators[] = 'Anomali Belum Masuk Penjualan';
        }

        if (! is_null($this->sale_id) && $this->status !== self::STATUS_COMPLETED) {
            $indicators[] = 'Sudah ada Sale tetapi belum selesai';
        }

        if (in_array($this->status, [self::STATUS_PROCESSING, self::STATUS_COMPLETED], true)
            && is_null($this->stock_deducted_at)) {
            $indicators[] = 'Diproses/Selesai tanpa pengurangan stok';
        }

        if ($this->status === self::STATUS_CANCELLED && ! is_null($this->stock_deducted_at)) {
            $indicators[] = 'Dibatalkan tetapi stok sudah dikurangi';
        }

        return $indicators;
    }

    public function canConfirmPayment(): bool
    {
        return $this->canManageManualPayment();
    }

    public function canRejectPayment(): bool
    {
        return $this->canManageManualPayment();
    }

    public function hasStockDeducted(): bool
    {
        return ! is_null($this->stock_deducted_at);
    }

    public function canConfirmCod(): bool
    {
        return $this->payment_method === Sale::PAYMENT_CASH
            && $this->status === self::STATUS_NEW
            && $this->payment_status !== self::PAYMENT_PAID
            && ! $this->hasStockDeducted()
            && is_null($this->cod_confirmed_at);
    }

    public function canUpdatePublicPayment(): bool
    {
        return $this->status === self::STATUS_NEW
            && $this->payment_status !== self::PAYMENT_PAID
            && ! $this->hasStockDeducted()
            && is_null($this->sale_id)
            && is_null($this->completed_at)
            && is_null($this->cancelled_at);
    }

    private function canManageManualPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_WAITING_CONFIRMATION
            && $this->payment_method !== Sale::PAYMENT_CASH
            && $this->status === self::STATUS_NEW
            && ! $this->hasStockDeducted()
            && is_null($this->sale_id)
            && is_null($this->processed_at)
            && is_null($this->completed_at)
            && is_null($this->cancelled_at);
    }

    public function canProcess(): bool
    {
        if ($this->payment_method === Sale::PAYMENT_CASH) {
            return $this->status === self::STATUS_CONFIRMED;
        }

        if ($this->status !== self::STATUS_NEW) {
            return false;
        }

        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_CONFIRMED], true)
            && ! $this->hasStockDeducted();
    }

    public function canConvertToSale(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && $this->payment_status === self::PAYMENT_PAID
            && is_null($this->sale_id);
    }
}
