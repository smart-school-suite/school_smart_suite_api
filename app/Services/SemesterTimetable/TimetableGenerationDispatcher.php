<?php

namespace App\Services\SemesterTimetable;

use App\Jobs\SemesterTimetable\GenerateFixedSemesterTimetable;
use App\Jobs\SemesterTimetable\GeneratePreferenceSemesterTimetable;
use App\Models\Job\SystemJob;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableVersion;

class TimetableGenerationDispatcher
{
    public function dispatch(object $currentSchool, array $payload, object $user): SemesterTimetableVersion
    {
        $version = !empty($payload['version_id'])
            ? SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)->find($payload['version_id'])
            : null;
        $version ??= SemesterTimetableVersion::createNext($currentSchool->id, $payload['school_semester_id']);
        $version->update(['scheduler_status' => SemesterTimetableVersion::STATUS_GENERATING]);
        $payload['version_id'] = $version->id;

        $systemJob = SystemJob::create([
            'type' => "Semester Timetable Generation",
            'context_type' => SchoolSemester::class,
            'stage' => "Processing",
            'context_id' => $payload['school_semester_id'],
            'initiated_by_id' => $user->id,
            'initiated_by_type' => $user::class,
            'queue' => "database",
            'started_at' => now(),
            'payload' => $payload,
        ]);

        $this->jobFor($currentSchool)::dispatch($currentSchool, $payload, $systemJob->id);

        return $version;
    }

    public function ignoresTeacherPreference(object $currentSchool): bool
    {
        $settings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', ['timetable.ignore_teacher_preference', 'timetable.respect_teacher_preference']))
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->settingDefination->key => $setting->value]);

        return !empty($settings['timetable.ignore_teacher_preference'] ?? null);
    }

    private function jobFor(object $currentSchool): string
    {
        return $this->ignoresTeacherPreference($currentSchool)
            ? GenerateFixedSemesterTimetable::class
            : GeneratePreferenceSemesterTimetable::class;
    }
}
