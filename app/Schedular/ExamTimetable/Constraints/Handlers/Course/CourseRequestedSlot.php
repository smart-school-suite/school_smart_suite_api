<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest as CourseRequestedSlotConstraint;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallCapacityValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\RequiredJointCourseSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Invigilator\InvigilatorRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\SessionDurationValidator;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Helpers\HallBuilder;
use Override;

class CourseRequestedSlot implements ConstraintHandler
{
    #[Override]
    public static function supports(): string
    {
        return CourseRequestedSlotConstraint::KEY;
    }

    #[Override]
    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();
        $rCs = $context->courseRequestedSlots();
        if ($rCs->isEmpty()) {
            return;
        }

        foreach ($rCs as $rC) {
            $params = [
                'course_id'    => $rC['course_id'],
                'start_time' => $rC['start_time'],
                'end_time'   => $rC['end_time'],
                'date'        => $rC['date'],
                'slot_type'  => CourseRequestedSlotConstraint::KEY,
            ];

            $blockers = array_filter([
                ...app(OperationalPeriodValidator::class)->check($params),
                ...app(SessionDurationValidator::class)->check($params),
                app(RequestedAssignmentValidator::class)->check($params),
                app(RequiredJointCourseSlotValidator::class)->check($params),
                app(HallCapacityValidator::class)->check($params),
                app(HallRequestedSlotValidator::class)->check($params),
                app(InvigilatorRequestedSlotValidator::class)->check($params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => array_filter([
                        'key'        => CourseRequestedSlotConstraint::KEY,
                        'course_id'    => $rC['course_id'],
                        'start_time' => $rC['start_time'],
                        'end_time'   => $rC['end_time'],
                        'date'        => $rC['date']
                    ]),
                    'blockers' => array_values($blockers),
                ];
                continue;
            }

            $this->enforce($state, $params, $context);
        }
    }

    protected function enforce(State $state, array $rC, RequestContext $context)
    {
        $params = [
            "start_time" => $rC["start_time"],
            "end_time" => $rC["end_time"],
            "date" => $rC["date"],
            "course_id" => $rC["course_id"]
        ];

        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();
        $hall = app(HallAvailability::class)->availableHalls($rC["date"], $rC['start_time'], $rC['end_time'])->first();
        $invig = collect(app(InvigilatorAvailability::class)->getAvailableInvigilators($params))->first();

        $state->dateGrid[$rC["date"]]->allocations[] = [
            'start_time' => $rC['start_time'],
            'end_time'   => $rC['end_time'],
            'hall'       => isset($hall['group'])
                ? app(HallBuilder::class)->buildGroupedHall(
                    $rC['course_id'],
                    $hall,
                    $invig,
                    $candidateCount,
                    $specialtyId
                )
                : app(HallBuilder::class)->buildSingleHall(
                    $rC['course_id'],
                    $hall,
                    $invig,
                    $candidateCount,
                    $specialtyId
                ),
        ];
    }
}
