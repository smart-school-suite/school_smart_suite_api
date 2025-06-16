<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Specialty;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimetableStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $specialtyId;
    protected $studentBatchId;
    protected $semesterId;
    public function __construct(
        string $schoolBranchId,
        string $specialtyId,
        string $studentBatchId,
        string $semesterId
    )
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
        $this->studentBatchId = $studentBatchId;
        $this->semesterId = $semesterId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $specialtyId = $this->specialtyId;
        $schoolBranchId = $this->schoolBranchId;
        $semesterId = $this->semesterId;
        $studentBatchId = $this->studentBatchId;
        $year = now()->year;
        $month = now()->month;
         $kpiNames = [
            "total_number_of_courses_per_semester",
            "total_number_of_courses_per_specialty",
            "total_number_of_courses_per_teacher",
            "total_average_course_per_day"
        ];
        $timetable = DB::table('timetables')->where("school_branch_id", $schoolBranchId)
                                ->where("semester_id", $semesterId)
                                ->where("specialty_id", $specialtyId)
                                ->where("student_batch_id", $studentBatchId)
                                ->get();
        $specialty = Specialty::where("school_branch_id", $schoolBranchId)->find($schoolBranchId);
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $this->totalCoursesCountBySemester(
           $year,
           $month,
           $semesterId,
           $kpis->get('total_number_of_courses_per_semester'),
           $schoolBranchId,
           $timetable
        );

        $this->totalCoursePerSpecialty(
            $year,
           $month,
           $semesterId,
           $specialtyId,
           $kpis->get('total_number_of_courses_per_specialty'),
           $schoolBranchId,
           $timetable
        );

        $this->averageCoursePerDay(
            $year,
            $month,
            $semesterId,
            $kpis->get("total_average_course_per_day"),
            $schoolBranchId,
            $timetable
        );

        $this->averageCoursePerTeacher(
            $year,
            $month,
            $semesterId,
            $kpis->get('total_number_of_courses_per_teacher'),
            $schoolBranchId,
            $timetable
        );
    }

    private function totalCoursesCountBySemester($year, $month, $semesterId,  $kpi, $schoolBranchId, $timetable){

        DB::table('time_table_stats')->insert([
             'id' => Str::uuid(),
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => $timetable->count(),
             'decimal_value' => null,
             'json_value' => null,
             'semester_id' => $semesterId,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function totalCoursePerSpecialty($year, $month, $semesterId, $specialtyId, $kpi, $schoolBranchId, $timetable){

        DB::table('time_table_stats')->insert([
             'id' => Str::uuid(),
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => null,
             'specialty_id' => $specialtyId,
             'decimal_value' => $timetable->count() / 7,
             'json_value' => null,
             'semester_id' => $semesterId,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }
    private function averageCoursePerDay($year, $month, $semesterId, $kpi, $schoolBranchId, $timetable){
        DB::table('time_table_stats')->insert([
             'id' => Str::uuid(),
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => null,
             'decimal_value' => $timetable->count() / 7,
             'json_value' => null,
             'semester_id' => $semesterId,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function averageCoursePerTeacher($year, $month, $semesterId, $kpi, $schoolBranchId, $timetable){
        $teacherCount = $timetable->pluck('teacher_id')->unique()->count();
        $courseCount = $timetable->pluck('course_id')->unique()->count();
        DB::table('time_table_stats')->insert([
            'id' => Str::uuid(),
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => null,
             'decimal_value' => $courseCount/$teacherCount,
             'json_value' => null,
             'semester_id' => $semesterId,
             'year' => $year,
             'month' => $month,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }
}
