<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core;


use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\PeriodDurationBlocker;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\ScheduleDailyFreePeriodBlocker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BlockerRegistry
{
    protected array $builderMap = [
        BreakPeriod::KEY => BreakPeriodBlocker::class,
        OperationalPeriod::KEY => OperationalPeriodBlocker::class,
        PeriodDuration::KEY => PeriodDurationBlocker::class,
        RequiredJointCourse::KEY => RequiredJointCourseBlocker::class,
        ScheduleDailyPeriod::KEY => ScheduleDailyFreePeriodBlocker::class
    ];

    public function build($blockers): Collection {
        $violations = collect();
        foreach ($blockers as $blocker) {
            Log::info("Building Blocker", ["blocker" => $blocker]);
            $type = $blocker["key"];

            if (isset($this->builderMap[$type])) {
                $builderClass = $this->builderMap[$type];
                $builder = new $builderClass();
                $violation = $builder->build($blocker);
                $violations->push($violation);
            }
        }

        return $violations;
    }
}
