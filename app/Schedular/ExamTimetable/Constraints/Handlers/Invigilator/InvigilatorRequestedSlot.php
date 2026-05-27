<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as InvigilatorRequestedSlotConstraint;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\CourseRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallCapacityValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\RequiredJointCourseSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Invigilator\InvigilatorRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\SessionDurationValidator;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\HallBuilder;
use Illuminate\Support\Arr;
use Override;

class InvigilatorRequestedSlot implements ConstraintHandler
{
    #[Override]
    public static function supports(): string
    {
        return InvigilatorRequestedSlotConstraint::KEY;
    }

    #[Override]
    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();
        $iRs = $context->invigilatorRequestedSlot();
        if ($iRs->isEmpty()) {
            return;
        }
        foreach ($iRs as $iR) {
            $params = [
                'invigilator_id'    => $iR['invigilator_id'],
                'start_time' => $iR['start_time'],
                'end_time'   => $iR['end_time'],
                'date'        => $iR['date'],
                'slot_type'  => InvigilatorRequestedSlotConstraint::KEY,
            ];

            $blockers = array_filter([
                ...app(OperationalPeriodValidator::class)->check($params),
                ...app(SessionDurationValidator::class)->check($params),
                app(RequestedAssignmentValidator::class)->check($params),
                app(RequiredJointCourseSlotValidator::class)->check($params),
                app(HallCapacityValidator::class)->check($params),
                app(InvigilatorRequestedSlotValidator::class)->check($params),
                app(CourseRequestedSlotValidator::class)->check($params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => array_filter([
                        'key'        => InvigilatorRequestedSlotConstraint::KEY,
                        'invigilator_id'    => $iR['invigilator_id'],
                        'start_time' => $iR['start_time'],
                        'end_time'   => $iR['end_time'],
                        'date'        => $iR['date'],
                    ]),
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
        }
    }

    protected function enforce(State $state, array $iR, RequestContext $context)
    {
        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();
        $courses = $context->courses()->pluck("course_id")->toArray();
        $hall = app(HallAvailability::class)->availableHalls($iR["date"], $iR['start_time'], $iR['end_time'])->first();

        $invig = [
            "invigilator_id" => $iR["invigilator_id"]
        ];

        $randomCourseId = Arr::random($courses);
        $state->dateGrid[$iR["date"]]->allocations[] = [
            'start_time' => $iR['start_time'],
            'end_time'   => $iR['end_time'],
            'hall'       => isset($hall['group'])
                ? app(HallBuilder::class)->buildGroupedHall(
                    $randomCourseId,
                    $hall,
                    $invig,
                    $candidateCount,
                    $specialtyId
                )
                : app(HallBuilder::class)->buildSingleHall(
                    $randomCourseId,
                    $hall,
                    $invig,
                    $candidateCount,
                    $specialtyId
                ),
        ];
    }
}
