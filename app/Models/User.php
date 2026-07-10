<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'OWNER';
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_KASIR = 'KASIR';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_KASIR => 'Kasir',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roleOptions()[$this->role] ?? $this->role;
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'bg-primary bg-opacity-10 text-primary',
            self::ROLE_ADMIN => 'bg-info bg-opacity-10 text-info',
            self::ROLE_KASIR => 'bg-warning bg-opacity-10 text-warning',
            default => 'bg-light text-body border',
        };
    }

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
}