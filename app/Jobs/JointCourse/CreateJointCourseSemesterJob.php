<?php

namespace App\Jobs\JointCourse;

use App\Models\Course\CourseSpecialty;
use App\Models\Course\SemesterJointCourse;
use App\Models\SchoolSemester;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Course\SemesterJoinCourseReference;
use Illuminate\Support\Str;

class CreateJointCourseSemesterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    protected string $schoolSemesterId;
    protected object $currentSchool;
    public function __construct(string $schoolSemesterId, object $currentSchool)
    {
        $this->schoolSemesterId = $schoolSemesterId;
        $this->currentSchool = $currentSchool;
    }

    public function handle(): void
    {
        $semester = SchoolSemester::where('school_branch_id', $this->currentSchool->id)
            ->where('id', $this->schoolSemesterId)
            ->select(['id', 'semester_id', 'school_year_id'])
            ->firstOrFail();

        $jointCourseIds = CourseSpecialty::query()
            ->where('school_branch_id', $this->currentSchool->id)
            ->whereHas('course', fn($q) => $q->where('semester_id', $semester->semester_id))
            ->groupBy('course_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('course_id')
            ->all();

        if (empty($jointCourseIds)) {
            return;
        }

        $existingJointCourseIds = SemesterJointCourse::query()
            ->where('school_branch_id', $this->currentSchool->id)
            ->where('school_year_id', $semester->school_year_id)
            ->where('semester_id', $semester->semester_id)
            ->pluck('course_id', 'id')
            ->all();

        $coursesToCreate = array_diff($jointCourseIds, array_values($existingJointCourseIds));

        $createdJointIdsByCourse = $existingJointCourseIds;

        if (!empty($coursesToCreate)) {
            $now = now();
            $newRecords = [];

            foreach ($coursesToCreate as $courseId) {
                $uuid = Str::uuid()->toString();
                $newRecords[] = [
                    'id'               => $uuid,
                    'school_branch_id' => $this->currentSchool->id,
                    'school_year_id'   => $semester->school_year_id,
                    'semester_id'      => $semester->semester_id,
                    'course_id'        => $courseId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $createdJointIdsByCourse[$courseId] = $uuid;
            }

            SemesterJointCourse::insert($newRecords);
        }

        $referenceData = SemesterJoinCourseReference::query()
            ->where('school_branch_id', $this->currentSchool->id)
            ->where('school_semester_id', $semester->id)
            ->pluck('semester_joint_course_id')
            ->all();

        $existingRefJointIds = array_flip($referenceData);

        $refRecords = [];
        $now = now();

        foreach ($jointCourseIds as $courseId) {
            $jointId = $createdJointIdsByCourse[$courseId] ?? null;

            if (!$jointId) {
                continue;
            }

            if (!isset($existingRefJointIds[$jointId])) {
                $refRecords[] = [
                    'id'                     => Str::uuid()->toString(),
                    'semester_joint_course_id' => $jointId,
                    'school_semester_id'       => $semester->id,
                    'school_branch_id'         => $this->currentSchool->id,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        if (!empty($refRecords)) {
            SemesterJoinCourseReference::insert($refRecords);
        }
    }
}
