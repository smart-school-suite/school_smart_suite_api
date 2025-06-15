<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\AdditionalFees;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\StatTypes;
class AdditionalFeeStatJob implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $additionalFeeId;
    protected $schoolBranchId;
    public function __construct(string $additionalFeeId, string $schoolBranchId)
    {
        $this->additionalFeeId = $additionalFeeId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $additionalFeeId = $this->additionalFeeId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            "total_additional_fee",
            "total_additional_fee_by_department",
            "total_additional_fee_by_specialty"
        ];

        $additionalFeeDetails = AdditionalFees::where("school_branch_id", $schoolBranchId)
                                               ->with(['student'])
                                               ->find($additionalFeeId);
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->additionalFeeTotal(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_additional_fee'),
            $additionalFeeDetails
        );

        $this->additionalFeeByDepartment(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_additional_fee_by_department'),
            $additionalFeeDetails
        );

        $this->additionalFeeBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_additional_fee_by_specialty'),
            $additionalFeeDetails
        );

    }

    private function additionalFeeTotal($year, $month, $schoolBranchId, $kpi, $additionalFee){
        $stat = DB::table('additional_fee_stat')
                        ->where("year", $year)
                        ->where("month", $month)
                        ->where("school_branch_id", $schoolBranchId)
                        ->where("stat_type_id", $kpi->id)
                        ->first();
        if($stat){
            $stat->decimal_value += $additionalFee->amount;
            $stat->save();
        }

        DB::table('additional_fee_stat')
             ->insert([
                 'id' => Str::uuid(),
                 'stat_type_id' => $kpi->id,
                 'decimal_value' => $additionalFee->amount,
                 'integer_value' => null,
                 'json_value' => null,
                 'school_branch_id' => $schoolBranchId,
                 'month' => $month,
                 'year' => $year,
                 'created_at' => now(),
                 'updated_at' => now(),
       ]);
    }
    private function additionalFeeByDepartment($year, $month, $schoolBranchId, $kpi, $additionalFee){
        $stat = DB::table('additional_fee_stat')
                    ->where("stat_type_id", $kpi->id)
                    ->where("department_id", $additionalFee->student->department_id)
                    ->where("month", $month)
                    ->where("year", $year)
                    ->where("school_branch_id", $schoolBranchId)
                    ->first();
        if($stat){
            $stat->decimal_value += $additionalFee->amount;
            $stat->save();
        }

        DB::table('additional_fee_stat')->insert([
                 'id' => Str::uuid(),
                 'stat_type_id' => $kpi->id,
                 'decimal_value' => $additionalFee->amount,
                 'department_id' => $additionalFee->student->department_id,
                 'integer_value' => null,
                 'json_value' => null,
                 'school_branch_id' => $schoolBranchId,
                 'month' => $month,
                 'year' => $year,
                 'created_at' => now(),
                 'updated_at' => now(),
        ]);
    }
    private function additionalFeeBySpecialty($year, $month, $schoolBranchId, $kpi, $additionalFee){
        $stat = DB::table('additional_fee_stat')
                    ->where("stat_type_id", $kpi->id)
                    ->where("specialty_id", $additionalFee->student->specialty_id)
                    ->where("month", $month)
                    ->where("year", $year)
                    ->where("school_branch_id", $schoolBranchId)
                    ->first();
        if($stat){
            $stat->decimal_value += $additionalFee->amount;
            $stat->save();
        }

          DB::table('additional_fee_stat')->insert([
                 'id' => Str::uuid(),
                 'stat_type_id' => $kpi->id,
                 'decimal_value' => $additionalFee->amount,
                 'specialty_id' => $additionalFee->student->specialty_id,
                 'integer_value' => null,
                 'json_value' => null,
                 'school_branch_id' => $schoolBranchId,
                 'month' => $month,
                 'year' => $year,
                 'created_at' => now(),
                 'updated_at' => now(),
        ]);
    }
}
