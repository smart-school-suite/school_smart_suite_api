<?php

namespace App\Jobs\DataCreationJob;

use App\Models\InstructorAvailability;
use App\Models\SchoolSemester;
use App\Models\TeacherSpecailtyPreference;
use App\Notifications\TeacherNewSemesterAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class CreateInstructorAvailabilityJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected string $schoolBranchId;
    protected string $schoolSemesterId;
    public function __construct(string $schoolBranchId, string $schoolSemesterId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->schoolSemesterId = $schoolSemesterId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $schoolSemester = SchoolSemester::where("school_branch_id", $this->schoolBranchId)
                                         ->with(['specialty.level', 'semester'])
                                         ->find($this->schoolSemesterId);
       $teachers = $this->getTeachers($schoolSemester->specialty->id, $this->schoolBranchId);
       foreach($teachers as $teacher){
          InstructorAvailability::create([
             'school_branch_id' => $this->schoolBranchId,
             'teacher_id' => $teacher->id,
             'level_id' => $schoolSemester->specialty->level->id,
             'school_semester_id' => $schoolSemester->id,
             'specialty_id' => $schoolSemester->specialty->id
          ]);
       }
       $semesterData = [
          'schoolYear' => $schoolSemester->school_year,
          'semester' => $schoolSemester->semester->name,
          'specialty' => $schoolSemester->specialty->specialty_name,
          'level' => $schoolSemester->specialty->level->name
       ];
       Notification::send($teachers, new TeacherNewSemesterAvailable($semesterData));
    }

    public function getTeachers($specialtyId, $schoolBranchId){
        $teacherPrefrences = TeacherSpecailtyPreference::where("school_branch_id", $schoolBranchId)
                                               ->where("specialty_id", $specialtyId)
                                               ->with(["teacher"])
                                               ->get();
        return $teacherPrefrences->teacher;
    }
}
