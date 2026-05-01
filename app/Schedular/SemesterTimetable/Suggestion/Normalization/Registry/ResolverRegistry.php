<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Registry;

use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Assignment\RequestedAssignmentResolver;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Course\CourseRequestedSlotResolver;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Hall\HallRequestedSlotResolver;
// use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule\BreakPeriodResolver;
// use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule\PeriodDurationResolver;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule\RequestedFreePeriodResolver;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Teacher\TeacherRequestedSlotResolver;

class ResolverRegistry
{
    protected array $resolvers = [
        RequestedAssignmentResolver::class,
        CourseRequestedSlotResolver::class,
        HallRequestedSlotResolver::class,
        //BreakPeriodResolver::class,
        //PeriodDurationResolver::class,
        RequestedFreePeriodResolver::class,
        TeacherRequestedSlotResolver::class
    ];

    public function resolve(ScenarioDTO $scenario)
    {
        foreach ($this->resolvers as $resolver) {
            $resolver = new $resolver();
            if ($resolver->supports($scenario->decision->target_type)) {
                return $resolver;
            }
        }
    }
}
