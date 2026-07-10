<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'is_active',
        'note',
        'last_transaction_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-success bg-opacity-10 text-success'
            : 'bg-danger bg-opacity-10 text-danger';
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function onlineOrders(): HasMany
    {
        return $this->hasMany(OnlineOrder::class);
    }    
}