<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as HallRequestedSlotConstraint;
use App\Models\Hall;
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
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Helpers\HallBuilder;
use Illuminate\Support\Arr;
use Override;

class HallRequestedSlot implements ConstraintHandler
{
    #[Override]
    public static function supports(): string
    {
        return HallRequestedSlotConstraint::KEY;
    }

    #[Override]
    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();
        $hRs = $context->courseRequestedSlots();
        if ($hRs->isEmpty()) {
            return;
        }

        foreach ($hRs as $hR) {
            $params = [
                'hall_id'    => $hR['hall_id'],
                'hall_group' => $hR['hall_group'], // [ "hall" ]
                'start_time' => $hR['start_time'],
                'end_time'   => $hR['end_time'],
                'date'        => $hR['date'],
                'slot_type'  => HallRequestedSlotConstraint::KEY,
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
                        'key'        => HallRequestedSlotConstraint::KEY,
                        'hall_id'    => $hR['hall_id'],
                        'start_time' => $hR['start_time'],
                        'end_time'   => $hR['end_time'],
                        'date'        => $hR['date'],
                    ]),
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
        }
    }

    protected function enforce(State $state, array $hR, RequestContext $context)
    {
        $params = [
            "start_time" => $hR["start_time"],
            "end_time" => $hR["end_time"],
            "date" => $hR["date"],
            "hall_id" => $hR["hall_id"],
            "hall_group" => $hR["hall_group"]
        ];

        $singleHall = [
            "hall_id" => $hR["hall_id"]
        ];

        $groupedHall = collect();
        if (isset($hR["hall_group"])) {
            foreach ($hR['hall_group'] as $hallId) {
                $hall = Hall::find($hallId);
                $groupedHall->push([
                    "hall_id" => $hall->id,
                    "capacity" => $hall->capacity,
                    'hall_name' => $hall->name
                ]);
            }
        }


        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();
        $courses = $context->courses()->pluck("course_id")->toArray();
        $invig = collect(app(InvigilatorAvailability::class)->getAvailableInvigilators($params))->first();

        $randomCourseId = Arr::random($courses);

        $state->dateGrid[$hR["date"]]->allocations[] = [
            'start_time' => $hR['start_time'],
            'end_time'   => $hR['end_time'],
            'hall'       => isset($hR['hall_group'])
                ? app(HallBuilder::class)->buildGroupedHall(
                    $randomCourseId,
                    $groupedHall->toArray(),
                    $invig,
                    $candidateCount,
                    $specialtyId
                )
                : app(HallBuilder::class)->buildSingleHall(
                    $randomCourseId,
                    $singleHall,
                    $invig,
                    $candidateCount,
                    $specialtyId
                ),
        ];
    }
}
