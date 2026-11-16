<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Tuition fee transactions become the single source of truth for what a
 * student has paid; tuition_fees.amount_paid is dropped.
 *
 * Before dropping it, any amount the column recorded that no transaction
 * accounts for (payments recorded before every payment created a
 * transaction) is preserved as one balancing transaction per record, with a
 * transaction_id starting "BAL-", so no student's paid amount changes.
 * Where transactions add up to more than the column, the transactions win;
 * those records are logged.
 *
 * transaction amounts are widened to decimal(15,2), the size of the tuition
 * total, so a single payment (or balancing entry) can cover a full tuition.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tuition_fee_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        if (!Schema::hasColumn('tuition_fees', 'amount_paid')) {
            return;
        }

        $paidByTransactions = DB::table('tuition_fee_transactions')
            ->select('tuition_id', DB::raw('SUM(amount) as paid'))
            ->groupBy('tuition_id')
            ->pluck('paid', 'tuition_id');

        DB::table('tuition_fees')->orderBy('id')->each(function ($fee) use ($paidByTransactions) {
            $recorded = round((float) $fee->amount_paid, 2);
            $transacted = round((float) ($paidByTransactions[$fee->id] ?? 0), 2);
            $missing = round($recorded - $transacted, 2);

            if ($missing > 0) {
                DB::table('tuition_fee_transactions')->insert([
                    'id' => (string) Str::uuid(),
                    'transaction_id' => 'BAL-' . strtoupper(Str::random(10)),
                    'amount' => $missing,
                    'payment_method' => 'cash',
                    'tuition_id' => $fee->id,
                    'school_branch_id' => $fee->school_branch_id,
                    'created_at' => $fee->updated_at ?? now(),
                    'updated_at' => now(),
                ]);
            } elseif ($missing < 0) {
                Log::warning('Tuition fee transactions exceed the recorded amount paid; transactions are kept as the source of truth.', [
                    'tuition_fee_id' => $fee->id,
                    'amount_paid_column' => $recorded,
                    'transactions_total' => $transacted,
                ]);
            }
        });

        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->default(0.00);
        });

        DB::table('tuition_fees')->update([
            'amount_paid' => DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM tuition_fee_transactions WHERE tuition_fee_transactions.tuition_id = tuition_fees.id)'),
        ]);
    }
};
