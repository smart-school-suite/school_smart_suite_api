<?php

namespace App\Jobs\StatisticalJobs\OperationalJobs;

use App\Models\Courses;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $schoolBranchId;
    public $courseId;
    public function __construct(string $schoolBranchId, string $courseId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->courseId = $courseId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $courseId = $this->courseId;

        $kpiNames = [
            'total_courses_count',
            'total_courses_count_by_specialty',
            'total_courses_count_by_department'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $courseDetails = Courses::where("school_branch_id", $schoolBranchId)->find($courseId);

        $this->totalCourseCount(
            $schoolBranchId,
            $kpis->get('total_courses_count')
        );
        $this->totalCourseCountBySpecialty(
            $schoolBranchId,
            $kpis->get('total_courses_count_by_specialty'),
            $courseDetails
        );
        $this->totalCourseCountByDepartment(
            $schoolBranchId,
            $kpis->get('total_courses_count_by_department'),
            $courseDetails
        );
    }

    private function totalCourseCount($schoolBranchId, $kpi){
        $courseCountStat = DB::table('course_stats')->where("stat_type_id", $kpi->id)
                                             ->where("school_branch_id", $schoolBranchId)
                                             ->first();
        if($courseCountStat){
            $courseCountStat->interger_value++;
            $courseCountStat->save();
        }

        DB::table('course_stats')->insert([
             'id' => Str::uuid(),
             'specialty_id' => null,
             'department_id' => null,
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => 1,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function totalCourseCountBySpecialty($schoolBranchId, $kpi, $courseDetails){
        $courseCountStat = DB::table('course_stats')->where("stat_type_id", $kpi->id)
                                 ->where("school_branch_id", $schoolBranchId)
                                 ->where("specialty_id", $courseDetails->specialty_id)
                                  ->first();
        if($courseCountStat){
            $courseCountStat->integer_value++;
            $courseCountStat->save();
        }

        DB::table('course_stats')->insert([
             'id' => Str::uuid(),
             'specialty_id' => $courseDetails->specialty_id,
             'department_id' => null,
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => 1,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }

    private function totalCourseCountByDepartment($schoolBranchId, $kpi, $courseDetails){
        $courseCountStat = DB::table('course_stats')->where("stat_type_id", $kpi->id)
                                 ->where("school_branch_id", $schoolBranchId)
                                 ->where("department_id", $courseDetails->department_id)
                                  ->first();
        if($courseCountStat){
           $courseCountStat->integer_value++;
            $courseCountStat->save();
        }

        DB::table('course_stats')->insert([
            'id' => Str::uuid(),
             'department_id' => $courseDetails->department_id,
             'specialty_id' => null,
             'school_branch_id' => $schoolBranchId,
             'stat_type_id' => $kpi->id,
             'integer_value' => 1,
             'created_at' => now(),
             'updated_at' => now(),
        ]);
    }
}
