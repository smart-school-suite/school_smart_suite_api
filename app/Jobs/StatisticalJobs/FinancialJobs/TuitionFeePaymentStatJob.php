<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\Specialty;
use App\Models\TuitionFeeTransactions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class TuitionFeePaymentStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $tuitionFeePaymentId;
    protected $schoolBranchId;
    public function __construct(string $tuitionFeePaymentId, string $schoolBranchId)
    {
        $this->tuitionFeePaymentId = $tuitionFeePaymentId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tuitionFeePaymentId = $this->tuitionFeePaymentId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $kpiNames = [
            'total_fee_debt',
            'total_tuition_fee_debt_by_department',
            'total_tuition_fee_debt_by_specialty',
            'total_amount_paid',
            'total_tuition_fee_paid_by_department',
            'total_tuition_fee_paid_by_specialty',
            'total_indepted_students',
            'total_indepted_student_by_department',
            'total_indepted_student_by_specialty',
        ];
        $tuitionFeePayment = TuitionFeeTransactions::where("school_branch_id", $schoolBranchId)
                                                  ->with(['tuition'])
                                                  ->find($tuitionFeePaymentId);
         $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

         $this->totalFeeDeptDeduction(
            $year,
            $schoolBranchId,
             $kpis->get("total_fee_debt"),
                $tuitionFeePayment
         );
         $this->totalTuitionFeeDebtByDepartment(
            $year,
            $schoolBranchId,
            $kpis->get("total_tuition_fee_debt_by_department"),
            $tuitionFeePayment
         );
         $this->totalTuitionFeeDebtBySpecialty(
            $year,
            $schoolBranchId,
            $kpis->get("total_tuition_fee_debt_by_specialty"),
            $tuitionFeePayment
         );
         $this->totalTuitionFeeAmountPaid(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get("total_amount_paid"),
            $tuitionFeePayment
         );
         $this->totalTuitionFeeAmountPaidBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get("total_tuition_fee_paid_by_specialty"),
            $tuitionFeePayment
         );

         $this->totalTuitionFeeDebtByDepartment(
            $year,
            $schoolBranchId,
            $kpis->get("total_indepted_student_by_department"),
            $tuitionFeePayment
         );

         $this->indeptedStudentCount(
             $year,
            $schoolBranchId,
            $kpis->get("total_indepted_students"),
            $tuitionFeePayment
         );

         $this->indeptedStudentCountByDepartment(
            $year,
            $schoolBranchId,
            $kpis->get("total_indepted_student_by_department"),
            $tuitionFeePayment
         );

         $this->indeptedStudentCountBySpecialty(
            $year,
            $schoolBranchId,
            $kpis->get("total_indepted_student_by_specialty"),
            $tuitionFeePayment
         );


    }

    private function totalFeeDeptDeduction($year, $schoolBranchId, $kpi, $tuitionFeePayment){
         $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->decimal_value -= $tuitionFeePayment->amount;
            $kpi->save();
        }
    }

    private function totalTuitionFeeDebtByDepartment($year, $schoolBranchId, $kpi, $tuitionFeePayment){
        $department = Specialty::where("school_branch_id", $schoolBranchId)
                                 ->find($tuitionFeePayment->tuition->specialty_id);
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("department_id", $department->department_id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if($kpiData->decimal_value > 0){
            $kpi->decimal_value -= $tuitionFeePayment->amount;
        }
    }

    private function totalTuitionFeeDebtBySpecialty($year, $schoolBranchId, $kpi, $tuitionFeePayment){
         $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("specialty_id", $tuitionFeePayment->tuition->specialty_id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if($kpiData->decimal_value > 0){
            $kpi->decimal_value -= $tuitionFeePayment->amount;
        }
    }

    private function totalTuitionFeeAmountPaid($year, $month, $schoolBranchId, $kpi, $tuitionFeePayment){
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->decimal_value += $tuitionFeePayment->amount;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'department_id' => null,
            'decimal_value' => $tuitionFeePayment->amount,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function totalTuitionFeeAmountPaidBySpecialty($year, $month, $schoolBranchId, $kpi, $tuitionFeePayment){
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("specialty_id", $tuitionFeePayment->tuition->specialty_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'specialty_id' => $tuitionFeePayment->tuition->specialty_id,
            'decimal_value' => $tuitionFeePayment->amount,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function totalTuitionFeeAmountPaidByDepartment($year, $month, $schoolBranchId, $kpi, $tuitionFeePayment){
         $department = Specialty::where("school_branch_id", $schoolBranchId)
                                 ->find($tuitionFeePayment->tuition->specialty_id);
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $department->department_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'department_id' => $department->department_id,
            'decimal_value' => $tuitionFeePayment->amount,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function indeptedStudentCount($year, $schoolBranchId, $kpi, $tuitionFeePayment){
        if($tuitionFeePayment->tuition->amount_left === 0){
              $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData->integer_value > 0) {
            $kpi->integer_value--;
            $kpi->save();
        }
        }
    }

    private function indeptedStudentCountBySpecialty($year, $schoolBranchId, $kpi, $tuitionFeePayment){
        if($tuitionFeePayment->tuition->amount_left === 0){
              $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("specialty_id", $tuitionFeePayment->tuition->specialty_id)
            ->first();
        if ($kpiData->integer_value > 0) {
            $kpi->integer_value--;
            $kpi->save();
        }
        }
    }

    private function indeptedStudentCountByDepartment($year, $schoolBranchId, $kpi, $tuitionFeePayment){
        $department = Specialty::where("school_branch_id", $schoolBranchId)
                                 ->find($tuitionFeePayment->tuition->specialty_id);
         if($tuitionFeePayment->tuition->amount_left === 0){
              $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $department->department_id)
            ->first();
        if ($kpiData->integer_value > 0) {
            $kpi->integer_value--;
            $kpi->save();
        }
        }
    }
}
