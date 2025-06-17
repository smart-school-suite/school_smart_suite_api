<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\RegistrationFeeTransactions;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationFeeStatJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $transactionId;
    public $schoolBranchId;
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
        $registrationFeeDetails = RegistrationFeeTransactions::where("school_branch_id", $schoolBranchId)
                                             ->with(['registrationFee.student'])
                                             ->find($transactionId);

        $this->totalAmountPaid(
            $year,
            $schoolBranchId,
            $kpis->get('total_amount_paid'),
            $registrationFeeDetails
        );

        $this->totalAmountPaidBySpecialty(
            $year,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_specialty'),
            $registrationFeeDetails
        );

        $this->totalAmountPaidByDepartment(
            $year,
            $schoolBranchId,
            $kpis->get('total_amount_paid_by_department'),
            $registrationFeeDetails
        );

    }

    private function totalAmountPaid($year, $schoolBranchId, $kpi, $registrationFeeDetails){
        $registrationFeeStat = DB::table('registration_fee_stats')->where("year", $year)
                                        ->where("school_branch_id", $schoolBranchId)
                                        ->where("stat_type_id", $kpi->id)
                                        ->first();
        if($registrationFeeStat){
            $registrationFeeStat->decimal_value += $registrationFeeDetails->amount;
            $registrationFeeStat->save();
        }

        DB::table('registration_fee_stats')->insert([
             'id' => Str::uuid(),
             'decimal_value' => $registrationFeeDetails->amount,
             'stat_type_id' => $kpi->id,
             'specialty_id' => null,
             'department_id' => null,
             'school_branch_id' => $schoolBranchId,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now()
        ]);
    }

    private function totalAmountPaidBySpecialty($year, $schoolBranchId, $kpi, $registrationFeeDetails){
        $registrationFeeStat = DB::table('registration_fee_stats')->where("year", $year)
                                        ->where("school_branch_id", $schoolBranchId)
                                        ->where("stat_type_id", $kpi->id)
                                        ->where('specialty_id', $registrationFeeDetails->registrationFee->student->specialty_id)
                                        ->first();
        if($registrationFeeStat){
            $registrationFeeStat->decimal_value += $registrationFeeDetails->amount;
            $registrationFeeStat->save();
        }

        DB::table('registration_fee_stats')->insert([
            'id' => Str::uuid(),
             'decimal_value' => $registrationFeeDetails->amount,
             'stat_type_id' => $kpi->id,
             'specialty_id' => $registrationFeeDetails->registrationFee->student->specialty_id,
             'department_id' => null,
             'school_branch_id' => $schoolBranchId,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now()
        ]);
    }

    private function totalAmountPaidByDepartment($year, $schoolBranchId, $kpi, $registrationFeeDetails){
        $registrationFeeStat = DB::table('registration_fee_stats')->where("year", $year)
                                        ->where("school_branch_id", $schoolBranchId)
                                        ->where("stat_type_id", $kpi->id)
                                        ->where('specialty_id', $registrationFeeDetails->registrationFee->student->department_id)
                                        ->first();
        if($registrationFeeStat){
            $registrationFeeStat->decimal_value += $registrationFeeDetails->amount;
            $registrationFeeStat->save();
        }

        DB::table('registration_fee_stats')->insert([
            'id' => Str::uuid(),
             'decimal_value' => $registrationFeeDetails->amount,
             'stat_type_id' => $kpi->id,
             'specialty_id' => null,
             'department_id' => $registrationFeeDetails->registrationFee->student->department_id,
             'school_branch_id' => $schoolBranchId,
             'year' => $year,
             'created_at' => now(),
             'updated_at' => now()
        ]);
    }

}
