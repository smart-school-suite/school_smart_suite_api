<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\Specialty;
use App\Models\StatTypes;
use App\Models\TuitionFees;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TuitionFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $tuitionFeeId;
    protected $schoolBranchId;
    public function __construct($tuitionFeeId, $schoolBranchId)
    {
        $this->tuitionFeeId = $tuitionFeeId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tuitonFeeId = $this->tuitionFeeId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $kpiNames = [
            'total_fee_debt',
            'total_tuition_fee_debt_by_department',
            'total_tuition_fee_debt_by_specialty',
            'total_indepted_students',
            'total_indepted_student_by_department',
            'total_indepted_student_by_specialty',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $tuitionFee = TuitionFees::where("school_branch_id", $schoolBranchId)->find($tuitonFeeId);

        $this->totalFeeDebt(
            $year,
            $month,
            $schoolBranchId,
            $tuitionFee,
            $kpis->get('tuitionFee->specialty_id')
        );
        $this->totalFeeDebtByDepartment(
            $year,
            $month,
            $schoolBranchId,
            $tuitionFee,
            $kpis->get('total_tuition_fee_debt_by_department')
        );
        $this->totalFeeDebtBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $tuitionFee,
            $kpis->get('total_tuition_fee_debt_by_specialty')
        );
        $this->totalIndeptedStudents(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_indepted_students')
        );
        $this->totalIndeptedStudentsByDepartment(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_indepted_student_by_departments'),
            $tuitionFee
        );
        $this->totalIndeptedStudentBySpecialty(
            $year,
            $month,
            $schoolBranchId,
            $kpis->get('total_indepted_student_by_departments'),
            $tuitionFee
        );
    }

    private function totalIndeptedStudentBySpecialty($year, $month, $schoolBranchId, $tuitionFee, $kpi)
    {
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("specialty_id", $tuitionFee->specialty_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'specialty_id' => $tuitionFee->specialty_id,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function totalIndeptedStudentsByDepartment($year, $month, $schoolBranchId, $kpi, $tuitionFee)
    {
        $departmentId = Specialty::where("school_branch_id", $schoolBranchId)
            ->findOrFail($tuitionFee->specialty_id);
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $departmentId->department_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'department_id' => $departmentId->department_id,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function totalIndeptedStudents($year, $month, $schoolBranchId, $kpi)
    {
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function totalFeeDebt($year, $month, $schoolBranchId, $tuitionFee, $kpi)
    {
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->decimal_value += $tuitionFee->tution_fee_total;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => $tuitionFee->tution_fee_total,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function totalFeeDebtByDepartment($year, $month, $schoolBranchId, $tuitionFee, $kpi)
    {
        $departmentId = Specialty::where("school_branch_id", $schoolBranchId)->findOrFail($schoolBranchId);
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $departmentId->department_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value += $tuitionFee->tution_fee_total;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'department_id' => $departmentId->department_id,
            'decimal_value' => $tuitionFee->tution_fee_total,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    private function totalFeeDebtBySpecialty($year, $month, $schoolBranchId, $tuitionFee, $kpi)
    {
        $kpiData =  DB::table('school_financial_stats')
            ->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("department_id", $tuitionFee->specialty_id)
            ->first();
        if ($kpiData) {
            $kpi->integer_value += $tuitionFee->tution_fee_total;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'department_id' => $tuitionFee->specialty_id,
            'decimal_value' => $tuitionFee->tution_fee_total,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
