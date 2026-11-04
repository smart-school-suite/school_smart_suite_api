<?php

namespace App\Services\ExamTimetable;

use App\Jobs\ExamTimetable\GenerateFixedExamTimetable;
use App\Models\Exams;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Job\SystemJob;

class ExamTimetableGenerationDispatcher
{
    public function dispatch(object $currentSchool, array $payload, ?object $user): ExamTimetableVersion
    {
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
            'initiated_by_id' => $user?->id,
            'initiated_by_type' => $user ? $user::class : null,
            'queue' => "database",
            'started_at' => now(),
            'payload' => $payload,
        ]);

        GenerateFixedExamTimetable::dispatch($currentSchool, $payload, $systemJob->id);

        return $version;
    }
}
