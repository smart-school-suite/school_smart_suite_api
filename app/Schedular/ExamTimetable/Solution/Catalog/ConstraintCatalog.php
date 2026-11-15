<?php

namespace App\Schedular\ExamTimetable\Solution\Catalog;

use App\Schedular\ExamTimetable\Solution\Contracts\ConstraintDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Assignment\RequestedAssignmentDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\CourseAlreadyScheduledDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\CoursePlacementDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\CourseRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Course\RequiredJointCourseDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\GenericDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallBusyDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallCapacityDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Hall\HallUnavailableDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorBusyDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorRequestedSlotDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorTeachesCourseDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Invigilator\InvigilatorUnavailableDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\NoExamSessionDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\OperationalPeriodDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Schedule\SessionDurationDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Student\CandidateBusyDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Student\StudentDailyLoadDefinition;

/** Every constraint and blocker key the solution engine knows how to explain and fix. */
class ConstraintCatalog
{
    protected array $definitions = [
        OperationalPeriodDefinition::class,
        SessionDurationDefinition::class,
        NoExamSessionDefinition::class,
        RequiredJointCourseDefinition::class,
        CoursePlacementDefinition::class,
        CourseAlreadyScheduledDefinition::class,
        CourseRequestedSlotDefinition::class,
        RequestedAssignmentDefinition::class,
        HallRequestedSlotDefinition::class,
        HallBusyDefinition::class,
        HallCapacityDefinition::class,
        HallUnavailableDefinition::class,
        InvigilatorRequestedSlotDefinition::class,
        InvigilatorBusyDefinition::class,
        InvigilatorTeachesCourseDefinition::class,
        InvigilatorUnavailableDefinition::class,
        StudentDailyLoadDefinition::class,
        CandidateBusyDefinition::class,
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

    private function index(): array
    {
        if ($this->byKey === null) {
            $this->byKey = [];
            foreach ($this->definitions as $class) {
                $definition = app($class);
                foreach ($definition->keys() as $key) {
                    $this->byKey[$key] = $definition;
                }
            }
        }
        return $this->byKey;
    }
}
