<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Teacher;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherRegistrationStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $teacherId;
    protected $schoolBranchId;
    public function __construct(string $teacherId, string $schoolBranchId)
    {
        $this->teacherId = $teacherId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $teacherId = $this->teacherId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $teacher = Teacher::where("school_branch_id", $schoolBranchId)->find($teacherId);
        $kpiNames = [
            "registered_teachers_count_over_time",
            "female_registered_teachers_count_over_time",
            "male_registered_teachers_count_over_time",
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        if ($teacher->gender === 'male') {
            $this->maleRegisteredMaleTeachersCountOverTime(
                $year,
                $month,
                $kpis->get('male_registered_teachers_count_over_time'),
                $schoolBranchId
            );
        }
        if ($teacher->gender === 'female') {
            $this->femaleRegisteredTeachersCountOverTime(
                $year,
                $month,
                $kpis->get('female_registered_teachers_count_over_time'),
                $schoolBranchId
            );
        }

        $this->registeredTeachersCountOverTime(
            $year,
            $month,
            $kpis->get('registered_teachers_count_over_time'),
            $schoolBranchId
        );
    }

    private function registeredTeachersCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
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

    private function femaleRegisteredTeachersCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
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

    private function maleRegisteredMaleTeachersCountOverTime(int $year, int $month, $kpi, $schoolBranchId)
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
}
