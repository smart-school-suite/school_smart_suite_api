<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\CourseRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\RequiredJointCourseSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallCapacityValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Invigilator\InvigilatorRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\SessionDurationValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use Override;

class RequestedAssignment implements ConstraintHandler
{
    #[Override]
    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    #[Override]
    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();
        $rAs = $context->requestedAssignments();
        if ($rAs->isEmpty()) {
            return;
        }
        foreach ($rAs as $rA) {
            $params = [
                'resource' => $rA['resource'], // [ [ "hall_id" => "", "invigilators" => [""] ], [ "hall_id" => "", "invigilators" => [""] ]b ]
                'course_id' => $rA['course_id'],
                'start_time' => $rA['start_time'],
                'end_time'   => $rA['end_time'],
                'date'        => $rA['date'],
                'slot_type'  => RequestedAssignmentConstraint::KEY,
            ];

            $blockers = array_filter([
                ...app(OperationalPeriodValidator::class)->check($params),
                ...app(SessionDurationValidator::class)->check($params),
                app(CourseRequestedSlotValidator::class)->check($params),
                app(RequiredJointCourseSlotValidator::class)->check($params),
                app(HallCapacityValidator::class)->check($params),
                app(HallRequestedSlotValidator::class)->check($params),
                app(InvigilatorRequestedSlotValidator::class)->check($params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => array_filter([
                        'key'        => RequestedAssignmentConstraint::KEY,
                        'hall_group' => $rA["hall_group"],
                        "invigilator_group" => $rA["invigilator_group"],
                        "invigilator_id" => $rA["invigilator_id"],
                        'hall_id'    => $rA['hall_id'],
                        'start_time' => $rA['start_time'],
                        'end_time'   => $rA['end_time'],
                        'date'        => $rA['date'],,
                    ]),
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
        }
    }

    protected function enforce(State $state, array $rA){

    }
}
