<?php

namespace App\Services\ResitTimetable;

use App\Jobs\ResitTimetable\GenerateResitTimetable;
use App\Models\Job\SystemJob;
use App\Models\ResitExam;
use App\Models\ResitTimetable\ResitTimetableVersion;

class ResitTimetableGenerationDispatcher
{
    public function dispatch(object $currentSchool, array $payload, object $user): ResitTimetableVersion
    {
        $version = !empty($payload['version_id'])
            ? ResitTimetableVersion::where('school_branch_id', $currentSchool->id)
                ->where('resit_exam_id', $payload['resit_exam_id'])
                ->find($payload['version_id'])
            : null;
        $version ??= ResitTimetableVersion::createNext($currentSchool->id, $payload['resit_exam_id']);
        $version->update(['scheduler_status' => ResitTimetableVersion::STATUS_GENERATING]);
        $payload['version_id'] = $version->id;

        $systemJob = SystemJob::create([
            'type' => 'Resit Timetable Generation',
            'context_type' => ResitExam::class,
            'stage' => 'Processing',
            'context_id' => $payload['resit_exam_id'],
            'initiated_by_id' => $user->id,
            'initiated_by_type' => $user::class,
            'queue' => 'database',
            'started_at' => now(),
            'payload' => $payload,
        ]);

        GenerateResitTimetable::dispatch($currentSchool, $payload, $systemJob->id);

        return $version;
    }
}
