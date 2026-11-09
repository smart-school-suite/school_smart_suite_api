<?php

namespace App\Jobs\DataCreationJob;

use App\Models\InstructorAvailability;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\Teacher\TeacherSpecialty;
use App\Notifications\TeacherNewSemesterAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Opens an availability record for every teacher who teaches the
 * semester's specialty, then tells those teachers the semester exists.
 */
class CreateInstructorAvailabilityJob implements ShouldQueue
{
    use Queueable;

    protected string $schoolBranchId;
    protected string $schoolSemesterId;

    public function __construct(string $schoolBranchId, string $schoolSemesterId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->schoolSemesterId = $schoolSemesterId;
    }

    public function handle(): void
    {
        $schoolSemester = SchoolSemester::where('school_branch_id', $this->schoolBranchId)
            ->with(['schoolYear.specialty.level', 'schoolYear.systemAcademicYear', 'semester'])
            ->find($this->schoolSemesterId);

        if (!$schoolSemester) {
            Log::warning("School semester {$this->schoolSemesterId} not found; no instructor availability created.");
            return;
        }

        // A semester belongs to a school year, and the school year carries the specialty.
        $specialty = $schoolSemester->schoolYear?->specialty;
        if (!$specialty) {
            Log::warning("School semester {$this->schoolSemesterId} has no specialty; no instructor availability created.");
            return;
        }

        $teachers = $this->getTeachers($specialty->id);
        if ($teachers->isEmpty()) {
            Log::info("No teachers found for specialty {$specialty->id} in school branch {$this->schoolBranchId}.");
            return;
        }

        foreach ($teachers as $teacher) {
            InstructorAvailability::firstOrCreate([
                'school_branch_id' => $this->schoolBranchId,
                'teacher_id' => $teacher->id,
                'school_semester_id' => $schoolSemester->id,
            ]);
        }

        Notification::send($teachers, new TeacherNewSemesterAvailable([
            'schoolYear' => $schoolSemester->schoolYear?->systemAcademicYear?->name,
            'semester' => $schoolSemester->semester?->name,
            'specialty' => $specialty->specialty_name,
            'level' => $specialty->level?->name,
        ]));
    }

    private function getTeachers(string $specialtyId)
    {
        $teacherIds = TeacherSpecialty::where('school_branch_id', $this->schoolBranchId)
            ->where('specialty_id', $specialtyId)
            ->pluck('teacher_id')
            ->unique();

        return Teacher::whereIn('id', $teacherIds)->get();
    }
}
