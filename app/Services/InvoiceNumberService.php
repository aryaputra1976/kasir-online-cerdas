<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function next(string $prefix, ?string $date = null): string
    {
        $date ??= now()->format('Ymd');
        $sequenceKey = "{$prefix}-{$date}";

        return DB::transaction(function () use ($sequenceKey) {
            $sequence = DB::table('invoice_sequences')
                ->where('sequence_key', $sequenceKey)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                DB::table('invoice_sequences')->insert([
                    'sequence_key' => $sequenceKey,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = DB::table('invoice_sequences')
                    ->where('sequence_key', $sequenceKey)
                    ->lockForUpdate()
                    ->first();
            }

            $nextNumber = ((int) $sequence->last_number) + 1;

            DB::table('invoice_sequences')
                ->where('sequence_key', $sequenceKey)
                ->update([
                    'last_number' => $nextNumber,
                    'updated_at' => now(),
                ]);

            return $sequenceKey . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
