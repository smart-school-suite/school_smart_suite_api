<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Core;

use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment\RequestedAssignmentBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\CourseAlreadyScheduledBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\CourseRequestedSlotBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallBusyBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallCapacityBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallUnavailableBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorBusyBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorTeachesCourseBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorUnavailableBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\NoExamSessionBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\SessionDurationBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student\CandidateBusyBlocker;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student\StudentDailyLoadBlocker;
use Illuminate\Support\Collection;

class BlockerRegistry
{
    protected array $builders = [
        RequestedAssignmentBlocker::class,
        CourseAlreadyScheduledBlocker::class,
        CourseRequestedSlotBlocker::class,
        RequiredJointCourseBlocker::class,
        HallBusyBlocker::class,
        HallCapacityBlocker::class,
        HallUnavailableBlocker::class,
        InvigilatorBusyBlocker::class,
        InvigilatorTeachesCourseBlocker::class,
        InvigilatorUnavailableBlocker::class,
        NoExamSessionBlocker::class,
        OperationalPeriodBlocker::class,
        SessionDurationBlocker::class,
        StudentDailyLoadBlocker::class,
        CandidateBusyBlocker::class,
    ];

    public function map(): array
    {
        return collect($this->builders)->mapWithKeys(fn(string $builder) => [$builder::type() => $builder])->all();
    }

    public function build(array $blockers): Collection
    {
        $map = $this->map();

        return collect($blockers)
            ->filter(fn($blocker) => isset($map[$blocker['key'] ?? null]))
            ->map(fn(array $blocker) => app($map[$blocker['key']])->build($blocker))
            ->values();
    }
}
