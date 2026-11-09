<?php

namespace App\Services\ExamTimetable;

use App\Exceptions\ExamTimetable\JointCoursesWithoutTime;
use App\Jobs\ExamTimetable\GenerateExamTimetable;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Job\SystemJob;

class ExamTimetableGenerationDispatcher
{
    public function dispatch(object $currentSchool, array $payload, object $user): ExamTimetableVersion
    {
        // Checked before a version is created, so a refused request leaves nothing behind.
        $unscheduled = $this->jointCoursesWithoutTime($currentSchool->id, $payload['exam_id']);
        if ($unscheduled) {
            throw new JointCoursesWithoutTime($unscheduled);
        }

        $version = !empty($payload['version_id'])
            ? ExamTimetableVersion::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $payload['exam_id'])
                ->find($payload['version_id'])
            : null;
        $version ??= ExamTimetableVersion::createNext($currentSchool->id, $payload['exam_id']);
        $version->update(['scheduler_status' => ExamTimetableVersion::STATUS_GENERATING]);
        $payload['version_id'] = $version->id;

        $systemJob = SystemJob::create([
            'type' => "Exam Timetable Generation",
            'context_type' => Exams::class,
            'stage' => "Processing",
            'context_id' => $payload['exam_id'],
            'initiated_by_id' => $user->id,
            'initiated_by_type' => $user::class,
            'queue' => "database",
            'started_at' => now(),
            'payload' => $payload,
        ]);

        GenerateExamTimetable::dispatch($currentSchool, $payload, $systemJob->id);

        return $version;
    }

    /** The exam's joint courses that have no joint exam time yet. */
    public function jointCoursesWithoutTime(string $branchId, string $examId): array
    {
        return ExamJointCourseRef::where('school_branch_id', $branchId)
            ->where('exam_id', $examId)
            ->whereHas('examJointCourse', fn($query) => $query->whereDoesntHave('examJointCourseSlot'))
            ->with('examJointCourse.course')
            ->get()
            ->map(fn(ExamJointCourseRef $ref) => [
                'exam_joint_course_id' => $ref->exam_js_id,
                'course_id' => $ref->examJointCourse->course_id,
                'course_title' => $ref->examJointCourse->course?->course_title,
            ])
            ->unique('exam_joint_course_id')
            ->values()
            ->all();
    }
}
