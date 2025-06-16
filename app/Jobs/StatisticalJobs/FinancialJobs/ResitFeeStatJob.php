<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;
use App\Models\StatTypes;
use App\Models\ResitFeeTransactions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResitFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $resitTransactionId;
    protected $schoolBranchId;
    public function __construct(string $resitTransactionId, string $schoolBranchId)
    {
        $this->resitTransactionId = $resitTransactionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resitTransactionId = $this->resitTransactionId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            'total_amount_paid',
            'total_amount_paid_by_specialty',
            'total_amount_paid_by_department'
        ];
        $resitDetails = ResitFeeTransactions::where("school_branch_id", $schoolBranchId)
            ->with(['studentResit.student'])
            ->find($resitTransactionId);

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $this->resitTotalAmountPaid(
             $year,
             $month,
             $schoolBranchId,
             $kpis->get('total_amount_paid'),
             $resitDetails
        );

        $this->totalAmountPaidByDepartment(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_department'),
            $resitDetails
        );

        $this->totalAmountPaidBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_department'),
            $resitDetails
        );
    }

    public function resitTotalAmountPaid($year, $month, $schoolBranchId, $kpi,  $resitDetails)
    {
        $stat = DB::table('resit_fee_transactions')
            ->where("year", $year)
            ->where("school_branch_id", $schoolBranchId)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $resitDetails->amount;
            $stat->save();
        }

        DB::table('resit_fee_transaction_stats')->insert([
            'id' => Str::uuid(),
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => $resitDetails->amount,
            'integer_value' => null,
            'json_value' => null,
            'year' => $year,
            'month' => $month,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function totalAmountPaidByDepartment($year, $month, $schoolBranchId, $kpi, $resitDetails)
    {
        $stat = DB::table('resit_fee_transactions')
            ->where("year", $year)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $resitDetails->studentResit->student->department_id)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $resitDetails->amount;
            $stat->save();
        }

        DB::table('resit_fee_transaction_stats')->insert([
            'id' => Str::uuid(),
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => $resitDetails->amount,
            'integer_value' => null,
            'json_value' => null,
            'department_id' => $resitDetails->studentResit->student->department_id,
            'year' => $year,
            'month' => $month,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function totalAmountPaidBySpecialty($year, $month, $schoolBranchId, $kpi, $resitDetails){
        $stat = DB::table('resit_fee_transactions')
            ->where("year", $year)
            ->where("school_branch_id", $schoolBranchId)
            ->where("specialty_id", $resitDetails->studentResit->student->specialty_id)
            ->where("stat_type_id", $kpi->id)
            ->first();
        if ($stat) {
            $stat->decimal_value += $resitDetails->amount;
            $stat->save();
        }

        DB::table('resit_fee_transaction_stats')->insert([
            'id' => Str::uuid(),
            'stat_type_id' => $kpi->id,
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => $resitDetails->amount,
            'integer_value' => null,
            'json_value' => null,
            'specialty_id' => $resitDetails->studentResit->student->specialty_id,
            'year' => $year,
            'month' => $month,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
