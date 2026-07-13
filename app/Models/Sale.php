<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    public const PAYMENT_CASH = 'CASH';
    public const PAYMENT_QRIS = 'QRIS';
    public const PAYMENT_TRANSFER = 'TRANSFER';
    public const PAYMENT_EDC = 'EDC';

    public const PAYMENT_METHODS = [
        self::PAYMENT_CASH,
        self::PAYMENT_QRIS,
        self::PAYMENT_TRANSFER,
        self::PAYMENT_EDC,
    ];

    public const STATUS_COMPLETED = 'COMPLETED';

    protected $fillable = [
        'customer_id',
        'created_by',
        'invoice_no',
        'sale_date',
        'customer_name',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'created_by' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethodLabel($this->payment_method);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Selesai',
            default => $this->status,
        };
    }

    public static function paymentMethodLabels(): array
    {
        return [
            self::PAYMENT_CASH => 'Tunai / Cash',
            self::PAYMENT_QRIS => 'QRIS',
            self::PAYMENT_TRANSFER => 'Transfer Bank',
            self::PAYMENT_EDC => 'EDC / Kartu',
        ];
    }

    public static function paymentMethodLabel(?string $method): string
    {
        return self::paymentMethodLabels()[$method] ?? ($method ?: 'Lainnya');
    }

    public static function paymentProgressClass(?string $method): string
    {
        return match ($method) {
            self::PAYMENT_CASH => 'bg-success',
            self::PAYMENT_QRIS => 'bg-primary',
            self::PAYMENT_TRANSFER => 'bg-info',
            self::PAYMENT_EDC => 'bg-warning',
            default => 'bg-secondary',
        };
    }
}
