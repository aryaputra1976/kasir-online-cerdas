<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ORDER_STATUSES = [
        'NEW',
        'CONFIRMED',
        'PROCESSING',
        'COMPLETED',
        'CANCELLED',
    ];

    private const PAYMENT_STATUSES = [
        'UNPAID',
        'WAITING_CONFIRMATION',
        'PAID',
        'REJECTED',
    ];

    private const PAYMENT_METHODS = [
        'CASH',
        'QRIS',
        'TRANSFER',
        'EDC',
    ];

    public function up(): void
    {
        match (DB::getDriverName()) {
            'mysql', 'mariadb' => $this->addMysqlConstraints(),
            'pgsql' => $this->addPostgresConstraints(),
            'sqlsrv' => $this->addSqlServerConstraints(),
            default => null,
        };
    }

    public function down(): void
    {
        match (DB::getDriverName()) {
            'mysql' => $this->dropMysqlConstraints(),
            'mariadb' => $this->dropMariaDbConstraints(),
            'pgsql' => $this->dropPostgresConstraints(),
            'sqlsrv' => $this->dropSqlServerConstraints(),
            default => null,
        };
    }

    private function addMysqlConstraints(): void
    {
        DB::statement($this->addConstraintSql('online_orders_status_check', 'status', self::ORDER_STATUSES));
        DB::statement($this->addConstraintSql('online_orders_payment_status_check', 'payment_status', self::PAYMENT_STATUSES));
        DB::statement($this->addNullableConstraintSql('online_orders_payment_method_check', 'payment_method', self::PAYMENT_METHODS));
    }

    private function addPostgresConstraints(): void
    {
        $this->addMysqlConstraints();
    }

    private function addSqlServerConstraints(): void
    {
        DB::statement($this->addSqlServerConstraintSql('online_orders_status_check', 'status', self::ORDER_STATUSES));
        DB::statement($this->addSqlServerConstraintSql('online_orders_payment_status_check', 'payment_status', self::PAYMENT_STATUSES));
        DB::statement($this->addSqlServerNullableConstraintSql('online_orders_payment_method_check', 'payment_method', self::PAYMENT_METHODS));
    }

    private function dropMysqlConstraints(): void
    {
        DB::statement('ALTER TABLE online_orders DROP CHECK online_orders_payment_method_check');
        DB::statement('ALTER TABLE online_orders DROP CHECK online_orders_payment_status_check');
        DB::statement('ALTER TABLE online_orders DROP CHECK online_orders_status_check');
    }

    private function dropMariaDbConstraints(): void
    {
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_payment_method_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_payment_status_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_status_check');
    }

    private function dropPostgresConstraints(): void
    {
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT IF EXISTS online_orders_payment_method_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT IF EXISTS online_orders_payment_status_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT IF EXISTS online_orders_status_check');
    }

    private function dropSqlServerConstraints(): void
    {
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_payment_method_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_payment_status_check');
        DB::statement('ALTER TABLE online_orders DROP CONSTRAINT online_orders_status_check');
    }

    private function addConstraintSql(string $name, string $column, array $allowedValues): string
    {
        return sprintf(
            'ALTER TABLE online_orders ADD CONSTRAINT %s CHECK (%s IN (%s))',
            $name,
            $column,
            $this->quotedValues($allowedValues)
        );
    }

    private function addNullableConstraintSql(string $name, string $column, array $allowedValues): string
    {
        return sprintf(
            'ALTER TABLE online_orders ADD CONSTRAINT %s CHECK (%s IS NULL OR %s IN (%s))',
            $name,
            $column,
            $column,
            $this->quotedValues($allowedValues)
        );
    }

    private function addSqlServerConstraintSql(string $name, string $column, array $allowedValues): string
    {
        return sprintf(
            'ALTER TABLE online_orders WITH CHECK ADD CONSTRAINT %s CHECK (%s IN (%s))',
            $name,
            $column,
            $this->quotedValues($allowedValues)
        );
    }

    private function addSqlServerNullableConstraintSql(string $name, string $column, array $allowedValues): string
    {
        return sprintf(
            'ALTER TABLE online_orders WITH CHECK ADD CONSTRAINT %s CHECK (%s IS NULL OR %s IN (%s))',
            $name,
            $column,
            $column,
            $this->quotedValues($allowedValues)
        );
    }

    private function quotedValues(array $values): string
    {
        return collect($values)
            ->map(fn (string $value) => DB::getPdo()->quote($value))
            ->implode(', ');
    }
};
