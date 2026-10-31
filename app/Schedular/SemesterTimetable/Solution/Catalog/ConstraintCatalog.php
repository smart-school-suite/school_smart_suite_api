<?php

namespace App\Schedular\SemesterTimetable\Solution\Catalog;

use App\Schedular\SemesterTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Assignment\RequestedAssignmentDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Course\CourseDailyFrequencyDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Course\CourseRequestedSlotDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Course\RequiredJointCourseDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\GenericDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Hall\HallBusyDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Hall\HallRequestedSlotDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\BreakPeriodDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\OperationalPeriodDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\PeriodDurationDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\RequestedFreePeriodDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\ScheduleDailyFreePeriodDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Schedule\ScheduleDailyPeriodDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Teacher\TeacherBusyDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Teacher\TeacherDailyHoursDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Teacher\TeacherRequestedSlotDefinition;
use App\Schedular\SemesterTimetable\Solution\Definitions\Teacher\TeacherUnavailableDefinition;

class ConstraintCatalog
{
    protected array $definitions = [
        OperationalPeriodDefinition::class,
        PeriodDurationDefinition::class,
        BreakPeriodDefinition::class,
        RequestedFreePeriodDefinition::class,
        ScheduleDailyPeriodDefinition::class,
        ScheduleDailyFreePeriodDefinition::class,
        RequiredJointCourseDefinition::class,
        CourseRequestedSlotDefinition::class,
        CourseDailyFrequencyDefinition::class,
        TeacherRequestedSlotDefinition::class,
        TeacherBusyDefinition::class,
        TeacherUnavailableDefinition::class,
        TeacherDailyHoursDefinition::class,
        HallRequestedSlotDefinition::class,
        HallBusyDefinition::class,
        RequestedAssignmentDefinition::class,
    ];

    private ?array $byKey = null;

    public function for(string $type): ConstraintDefinition
    {
        return $this->index()[$type] ?? (new GenericDefinition())->for($type);
    }

    public function has(string $type): bool
    {
        return isset($this->index()[$type]);
    }

    public function diagnosticBuilders(): array
    {
        return $this->builders(fn(ConstraintDefinition $definition) => [$definition->constraintKey(), $definition->diagnosticBuilder()]);
    }

    public function blockerBuilders(): array
    {
        return $this->builders(fn(ConstraintDefinition $definition) => [$definition->violationKey(), $definition->blockerBuilder()]);
    }

    private function builders(callable $pick): array
    {
        $map = [];

        foreach ($this->all() as $definition) {
            [$key, $builder] = $pick($definition);
            if ($key && $builder) {
                $map[$key] = $builder;
            }
        }

        return $map;
    }

    private function all(): array
    {
        $unique = [];

        foreach ($this->index() as $definition) {
            $unique[spl_object_id($definition)] = $definition;
        }

        return array_values($unique);
    }

    private function index(): array
    {
        if ($this->byKey !== null) {
            return $this->byKey;
        }

        $this->byKey = [];

        foreach ($this->definitions as $class) {
            $definition = app($class);

            foreach (array_filter([$definition->constraintKey(), $definition->violationKey()]) as $key) {
                $this->byKey[$key] = $definition;
            }
        }

        return $this->byKey;
    }
}
