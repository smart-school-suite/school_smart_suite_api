<?php

namespace App\Http\Controllers\ResitJointCourse;

use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamJointCourse\ConfirmExamJointCourseSlotRequest;
use App\Http\Requests\ExamJointCourse\GenerateExamJointCourseSlotsRequest;
use App\Http\Requests\ExamJointCourse\SaveExamJointCourseSlotRequest;
use App\Services\ApiResponseService;
use App\Services\ResitJointCourse\ResitJointCourseTimetableService;
use Illuminate\Http\Request;

/** The resit joint course timetable: same endpoints and shapes as the exam one. */
class ResitJointCourseTimetableController extends Controller
{
    public function __construct(private ResitJointCourseTimetableService $timetable) {}

    public function getTerms(Request $request)
    {
        return ApiResponseService::success('Joint Resit Terms Fetched Successfully', $this->timetable->terms($this->school($request)), null, 200);
    }

    public function getTerm(Request $request, string $schoolYearId, string $examTypeId)
    {
        return ApiResponseService::success('Joint Resits Fetched Successfully', $this->timetable->term($this->school($request), $schoolYearId, $examTypeId), null, 200);
    }

    public function getJointCourse(Request $request, string $jointCourseId)
    {
        return ApiResponseService::success('Joint Resit Fetched Successfully', $this->timetable->show($this->school($request), $jointCourseId), null, 200);
    }

    public function syncResitExam(Request $request, string $resitExamId)
    {
        return ApiResponseService::success('Joint Resits Linked Successfully', $this->timetable->syncResitExam($this->school($request), $resitExamId), null, 200);
    }

    public function generateSlots(GenerateExamJointCourseSlotsRequest $request, string $jointCourseId)
    {
        return ApiResponseService::success('Joint Resit Times Generated Successfully', $this->timetable->generate($this->school($request), $jointCourseId, $request->validated()), null, 200);
    }

    public function confirmSlot(ConfirmExamJointCourseSlotRequest $request, string $jointCourseId, string $proposalId)
    {
        return $this->saving(fn() => $this->timetable->confirmProposal($this->school($request), $jointCourseId, $proposalId, $request->validated()['option_id']));
    }

    public function saveSlot(SaveExamJointCourseSlotRequest $request, string $jointCourseId)
    {
        return $this->saving(fn() => $this->timetable->saveSlot($this->school($request), $jointCourseId, $request->validated()));
    }

    public function removeSlot(Request $request, string $jointCourseId)
    {
        return ApiResponseService::success('Joint Resit Time Removed Successfully', $this->timetable->removeSlot($this->school($request), $jointCourseId), null, 200);
    }

    private function saving(callable $save)
    {
        try {
            $result = $save();
        } catch (ExamJointCourseSlotConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => 'Joint Resit Time Not Possible',
                'description' => "This time can't be used for the joint resit because of the problem(s) listed below. Pick another time.",
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success('Joint Resit Time Saved Successfully', $result, null, 200);
    }

    private function school(Request $request): object
    {
        return $request->attributes->get('currentSchool');
    }
}
