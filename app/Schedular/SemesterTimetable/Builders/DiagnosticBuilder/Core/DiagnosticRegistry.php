<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment\RequestedAssignmentDiagnostic;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\BreakPeriodDiagnostic;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\DailyPeriodDiagnostic;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\PeriodDurationDiagnostic;
use Illuminate\Support\Collection;

class DiagnosticRegistry
{
     protected array $map = [
        RequiredJointCourse::KEY => RequiredJointCourseDiagnostic::class,
        BreakPeriod::KEY => BreakPeriodDiagnostic::class,
        PeriodDuration::KEY => PeriodDurationDiagnostic::class,
        ScheduleDailyPeriod::KEY => DailyPeriodDiagnostic::class,
        RequestedAssignment::KEY => RequestedAssignmentDiagnostic::class
     ];

     public function build($diagnostics): Collection {
        $diagnosticDTOs = [];
        foreach($diagnostics as $diagnostic) {
            $type = $diagnostic["constraint_failed"]["key"];
            if (isset($this->map[$type])) {
                $builderClass = $this->map[$type];
                $builder = new $builderClass();
                $diagnosticDTOs[] = $builder->build($diagnostic);
            }
        }
        return collect($diagnosticDTOs);
     }
}
