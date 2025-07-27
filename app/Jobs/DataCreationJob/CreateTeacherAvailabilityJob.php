<?php

namespace App\Jobs\DataCreationJob;

use App\Models\InstructorAvailability;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateTeacherAvailabilityJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected array $createData;
    public function __construct($createData)
    {
        $this->createData = $createData;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $specialtyId = $this->createData['specialty_id'];
        $schoolBranchId = $this->createData['school_branch_id'];
        $schoolSemesterId = $this->createData['school_semester_id'];
        $levelId = $this->createData['level_id'];

        $this->createTeacherAvailability($specialtyId, $schoolBranchId, $schoolSemesterId, $levelId);
    }

    private function createTeacherAvailability($specialtyId, $schoolBranchId, $schoolSemesterId, $levelId)
{
    $teacherPreference = TeacherSpecailtyPreference::where("specialty_id", $specialtyId)
        ->where("school_branch_id", $schoolBranchId)
        ->get();


    if ($teacherPreference->isEmpty()) {
        Log::info("No teacher preferences found for specialty ID: {$specialtyId} in school branch ID: {$schoolBranchId}");
        return;
    }
    $teacherIds = $teacherPreference->pluck('teacher_id')->toArray();
    $teacherIds = array_unique($teacherIds);
    foreach ($teacherIds as $teacherId){
        InstructorAvailability::create([
            'school_branch_id' => $schoolBranchId,
            'teacher_id' => $teacherId,
            'level_id' => $levelId,
            'school_semester_id' => $schoolSemesterId,
            'specialty_id' => $specialtyId
        ]);
    }
}
}
