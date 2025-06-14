<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\StatTypes;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentRegistrationStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $studentId;
    protected $schoolBranchId;
    public function __construct(string $studentId, string $schoolBranchId)
    {
        $this->studentId = $studentId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $studentId = $this->studentId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $student = Student::where("school_branch_id", $schoolBranchId)->find($studentId);
        $kpiNames = [
            "registered_students_count_over_time",
            "female_registered_student_count_over_time",
            "male_registered_student_count_over_time",
            "specialty_registration_count_over_time",
            "department_registration_count_over_time",
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $this->registeredStudentCountOverTime($year, $month, $kpis->get('registered_students_count_over_time'), $schoolBranchId);
        if($student->gender === 'female'){
            $this->femaleRegisteredStudentCountOverTime(
            $year,
            $month,
            $kpis->get('female_registered_student_count_over_time'),
            $schoolBranchId,
        );
        }
        if($student->gender === 'male'){
             $this->maleRegisteredStudentCountOverTime(
            $year,
            $month,
            $kpis->get('male_registered_student_count_over_time'),
            $schoolBranchId,
        );
        }
        $this->specialtyBasedStudentRegistrationOverTime(
            $year,
            $month,
            $kpis->get('specialty_registration_count_over_time'),
            $schoolBranchId,
            $student
        );
        $this->departmentBasedStudentRegistrationOverTime(
            $year,
            $month,
            $kpis->get('department_registration_count_over_time'),
            $schoolBranchId,
            $student
        );
    }

    private function registeredStudentCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
    {
        $kpiData =  DB::table('school_operational_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }

        DB::table('school_operational_stats')->insert([
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

    private function femaleRegisteredStudentCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
    {
        $kpiData =  DB::table('school_operational_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }
        DB::table('school_operational_stats')->insert([
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

    private function maleRegisteredStudentCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
    {
        $kpiData =  DB::table('school_operational_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }
        DB::table('school_operational_stats')->insert([
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

    private function specialtyBasedStudentRegistrationOverTime(int $year, int $month, $kpi, $schoolBranchId, $student)
    {
        $specialtyId = $student->specialty_id;
        $kpiData =  DB::table('school_operational_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("specialty_id", $specialtyId)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }
        DB::table('school_operational_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'specialty_id' => $specialtyId,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function departmentBasedStudentRegistrationOverTime(int $year, int $month, $kpi, $schoolBranchId, $student)
    {
        $departmentId = $student->department_id;
        $kpiData =  DB::table('school_operational_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("specialty_id", $departmentId)
            ->where("school_branch_id", $schoolBranchId)
            ->first();
        if ($kpiData) {
            $kpi->integer_value++;
            $kpi->save();
        }
        DB::table('school_operational_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => null,
            'integer_value' => 1,
            'json_value' => null,
            'department_id' => $departmentId,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
