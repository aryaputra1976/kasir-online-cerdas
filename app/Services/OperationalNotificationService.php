<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\OperationalNotificationRead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class OperationalNotificationService
{
    public const NEW_ONLINE_ORDERS = 'new_online_orders';
    public const WAITING_PAYMENT_CONFIRMATIONS = 'waiting_payment_confirmations';
    public const COMPLETED_NOT_CONVERTED = 'completed_not_converted';
    public const LOW_STOCK = 'low_stock';
    public const EMPTY_STOCK = 'empty_stock';

    public const KEYS = [
        self::NEW_ONLINE_ORDERS,
        self::WAITING_PAYMENT_CONFIRMATIONS,
        self::COMPLETED_NOT_CONVERTED,
        self::LOW_STOCK,
        self::EMPTY_STOCK,
    ];

    public function dataFor(?User $user): array
    {
        if (! $user) {
            return [
                'action_required_count' => 0,
                'unread_count' => 0,
                'badge_text' => '0',
                'notifications' => collect(),
            ];
        }

        $reads = OperationalNotificationRead::query()
            ->where('user_id', $user->id)
            ->pluck('last_read_at', 'notification_key');

        $notifications = $this->definitions($user)
            ->filter(fn (array $notification) => $notification['count'] > 0)
            ->map(function (array $notification) use ($reads) {
                $lastReadAt = $reads[$notification['key']] ?? null;
                $latestAt = $notification['latest_at'];

                $notification['unread'] = $latestAt
                    && (! $lastReadAt || Carbon::parse($latestAt)->gt(Carbon::parse($lastReadAt)));

                return $notification;
            })
            ->values();

        $unreadCount = $notifications->where('unread', true)->count();
        $actionRequiredCount = $notifications->sum('count');

        return [
            'action_required_count' => $actionRequiredCount,
            'unread_count' => $unreadCount,
            'badge_text' => $unreadCount > 99 ? '99+' : (string) $unreadCount,
            'notifications' => $notifications,
        ];
    }

    public function markRead(User $user, string $key): ?string
    {
        $notification = $this->definitions($user)->firstWhere('key', $key);

        if (! $notification) {
            return null;
        }

        OperationalNotificationRead::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_key' => $key,
            ],
            ['last_read_at' => now()]
        );

        return $notification['route'];
    }

    public function markAllRead(User $user): void
    {
        foreach ($this->definitions($user) as $notification) {
            OperationalNotificationRead::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_key' => $notification['key'],
                ],
                ['last_read_at' => now()]
            );
        }
    }

    private function definitions(User $user): Collection
    {
        $definitions = collect();

        if ($user->hasAnyRole([User::ROLE_OWNER, User::ROLE_ADMIN, User::ROLE_KASIR])) {
            $definitions->push($this->newOnlineOrders());
        }

        if ($user->hasAnyRole([User::ROLE_OWNER, User::ROLE_ADMIN])) {
            $definitions->push($this->waitingPaymentConfirmations());
            $definitions->push($this->completedNotConverted());
            $definitions->push($this->lowStock());
            $definitions->push($this->emptyStock());
        }

        return $definitions;
    }

    private function newOnlineOrders(): array
    {
        $query = OnlineOrder::query()->where('status', OnlineOrder::STATUS_NEW);
        $count = $query->count();

        return [
            'key' => self::NEW_ONLINE_ORDERS,
            'count' => $count,
            'latest_at' => $count > 0 ? (clone $query)->max('updated_at') : null,
            'title' => 'Order online baru',
            'description' => 'Ada ' . number_format($count, 0, ',', '.') . ' order online baru yang perlu diproses.',
            'route' => 'online-orders.index',
            'icon' => 'shopping_cart',
            'iconClass' => 'text-warning',
            'status' => 'Perlu diproses',
        ];
    }

    private function waitingPaymentConfirmations(): array
    {
        $query = OnlineOrder::query()
            ->where('payment_status', OnlineOrder::PAYMENT_WAITING_CONFIRMATION);
        $count = $query->count();

        return [
            'key' => self::WAITING_PAYMENT_CONFIRMATIONS,
            'count' => $count,
            'latest_at' => $count > 0 ? (clone $query)->max('updated_at') : null,
            'title' => 'Pembayaran menunggu konfirmasi',
            'description' => 'Ada ' . number_format($count, 0, ',', '.') . ' pembayaran online yang perlu divalidasi.',
            'route' => 'payments.index',
            'icon' => 'fact_check',
            'iconClass' => 'text-primary',
            'status' => 'Perlu validasi',
        ];
    }

    private function completedNotConverted(): array
    {
        $query = OnlineOrder::query()
            ->where('status', OnlineOrder::STATUS_COMPLETED)
            ->where('payment_status', OnlineOrder::PAYMENT_PAID)
            ->whereNull('sale_id');
        $count = $query->count();

        return [
            'key' => self::COMPLETED_NOT_CONVERTED,
            'count' => $count,
            'latest_at' => $count > 0 ? (clone $query)->max('updated_at') : null,
            'title' => 'Anomali belum masuk penjualan',
            'description' => 'Ada ' . number_format($count, 0, ',', '.') . ' order selesai dan dibayar yang belum masuk penjualan.',
            'route' => 'reports.online-orders.index',
            'icon' => 'sync_problem',
            'iconClass' => 'text-warning',
            'status' => 'Perlu dicek',
        ];
    }

    private function lowStock(): array
    {
        $query = Product::query()->activeLowStock();
        $count = $query->count();

        return [
            'key' => self::LOW_STOCK,
            'count' => $count,
            'latest_at' => $count > 0 ? (clone $query)->max('updated_at') : null,
            'title' => 'Stok menipis',
            'description' => 'Ada ' . number_format($count, 0, ',', '.') . ' produk dengan stok kurang atau sama dengan minimum.',
            'route' => 'stocks.low',
            'icon' => 'inventory_2',
            'iconClass' => 'text-danger',
            'status' => 'Perlu restock',
        ];
    }

    private function emptyStock(): array
    {
        $query = Product::query()->activeEmptyStock();
        $count = $query->count();

        return [
            'key' => self::EMPTY_STOCK,
            'count' => $count,
            'latest_at' => $count > 0 ? (clone $query)->max('updated_at') : null,
            'title' => 'Stok kosong',
            'description' => 'Ada ' . number_format($count, 0, ',', '.') . ' produk dengan stok kosong.',
            'route' => 'stocks.low',
            'icon' => 'production_quantity_limits',
            'iconClass' => 'text-danger',
            'status' => 'Stok kosong',
        ];
    }
}
