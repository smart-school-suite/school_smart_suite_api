<?php

namespace App\Schedular\ExamTimetable\Solution\Catalog;

use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Assignment\RequestedAssignmentDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\CourseExamSessionDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\CourseRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\RequiredJointCourseDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\GenericDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallBusyDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallCapacityDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallUnavailableDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorBusyDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorUnavailableDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\NoExamInWindowDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\OperationalPeriodDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\SessionDurationDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Student\StudentDailyLoadDefinition;

/**
 * Every exam constraint and violation the scheduler knows. The diagnostic and blocker registries read their
 * builders from here, so registering a definition registers everything for that key.
 */
class ConstraintCatalog
{
    protected array $definitions = [
        OperationalPeriodDefinition::class,
        SessionDurationDefinition::class,
        RequiredJointCourseDefinition::class,
        CourseExamSessionDefinition::class,
        RequestedAssignmentDefinition::class,
        CourseRequestedSlotDefinition::class,
        InvigilatorRequestedSlotDefinition::class,
        HallRequestedSlotDefinition::class,
        StudentDailyLoadDefinition::class,
        HallBusyDefinition::class,
        HallCapacityDefinition::class,
        HallUnavailableDefinition::class,
        InvigilatorBusyDefinition::class,
        InvigilatorUnavailableDefinition::class,
        NoExamInWindowDefinition::class,
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
