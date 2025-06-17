<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\AdditionalFeeTransactions;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdditionalFeeTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $transactionId;
    protected $schoolBranchId;
    public function __construct(string $transactionId, string $schoolBranchId)
    {
        $this->transactionId = $transactionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transactionId = $this->transactionId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            "total_amount_paid",
            "total_amount_paid_by_department",
            "total_amount_paid_by_specialty"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $additionalFeeTransaction = AdditionalFeeTransactions::where("school_branch_id", $schoolBranchId)
            ->with(['additionFee.student'])
            ->find($transactionId);
        $this->totalAmountPaid(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_amount_paid'),
            $additionalFeeTransaction
        );
        $this->totalAmountPaidByDepartment(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_department'),
            $additionalFeeTransaction
        );
        $this->totalAmountPaidBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_specialty'),
            $additionalFeeTransaction
        );
    }

    private function totalAmountPaid($year, $month, $schoolBranchId, $kpi, $additionalFeeTransaction)
    {
        $stat = DB::table('additional_fee_transactions_stats')
            ->where("school_branch_id", $schoolBranchId)
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $additionalFeeTransaction->amount;
            $stat->save();
        }

        DB::table('additional_fee_transactions_stats')->insert([
            'id' => Str::uuid(),
            'decimal_value' => $additionalFeeTransaction->amount,
            'integer_value' => null,
            'json_value' => null,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function totalAmountPaidByDepartment($year, $month, $schoolBranchId, $kpi, $additionalFeeTransaction)
    {
        $stat = DB::table('additional_fee_transactions_stats')
            ->where("school_branch_id", $schoolBranchId)
            ->where("year", $year)
            ->where("department_id", $additionalFeeTransaction->additionFee->student->department_id)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $additionalFeeTransaction->amount;
            $stat->save();
        }

        DB::table('additional_fee_transactions_stats')->insert([
            'id' => Str::uuid(),
            'decimal_value' => $additionalFeeTransaction->amount,
            'integer_value' => null,
            'json_value' => null,
            'school_branch_id' => $schoolBranchId,
            'department_id' => $additionalFeeTransaction->additionFee->student->department_id,
            'stat_type_id' => $kpi->id,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function totalAmountPaidBySpecialty($year, $month, $schoolBranchId, $kpi, $additionalFeeTransaction)
    {
        $stat = DB::table('additional_fee_transactions_stats')
            ->where("school_branch_id", $schoolBranchId)
            ->where("year", $year)
            ->where("specialty_id", $additionalFeeTransaction->additionFee->student->specialty_id)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $additionalFeeTransaction->amount;
            $stat->save();
        }

        DB::table('additional_fee_transactions_stats')->insert([
            'id' => Str::uuid(),
            'decimal_value' => $additionalFeeTransaction->amount,
            'integer_value' => null,
            'json_value' => null,
            'school_branch_id' => $schoolBranchId,
            'specialty_id' => $additionalFeeTransaction->additionFee->student->specialty_id,
            'stat_type_id' => $kpi->id,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
