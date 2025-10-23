<?php

namespace Database\Seeders;

use App\Jobs\DataCleanupJobs\UpdateElectionResultStatus;
use Illuminate\Database\Seeder;
use App\Models\CurrentElectionWinners;
use App\Models\EventTag;
use Illuminate\Support\Facades\DB;
use App\Jobs\DataCreationJob\CreateExamJob;
use App\Models\SchoolSemester;
class test extends Seeder
{
    public function run(): void {
         $schoolSemesters = SchoolSemester::with(['specialty'])->get();
         foreach($schoolSemesters as $schoolSemester){
            $data = [
            'specialty_id' => $schoolSemester->specialty_id,
            'school_branch_id' => $schoolSemester->school_branch_id,
            'semester_id' =>  $schoolSemester->semester_id,
            'school_semester_id' => $schoolSemester->id,
            'level_id' => $schoolSemester->specialty->level_id,
            'student_batch_id' => $schoolSemester->student_batch_id,
            'school_year' => $schoolSemester->school_year
            ];
             CreateExamJob::dispatch($data, $schoolSemester->school_branch_id);
         }
    }
}
