<?php

namespace App\Http\Controllers\ExamJointCourse;

use App\Exceptions\ExamJointCourse\ExamJointCourseSlotConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamJointCourse\ConfirmExamJointCourseSlotRequest;
use App\Http\Requests\ExamJointCourse\GenerateExamJointCourseSlotsRequest;
use App\Http\Requests\ExamJointCourse\SaveExamJointCourseSlotRequest;
use App\Services\ApiResponseService;
use App\Services\ExamJointCourse\ExamJointCourseTimetableService;
use Illuminate\Http\Request;

class ExamJointCourseTimetableController extends Controller
{
    public function __construct(private ExamJointCourseTimetableService $timetable) {}

    public function getTerms(Request $request)
    {
        return ApiResponseService::success('Joint Exam Terms Fetched Successfully', $this->timetable->terms($this->school($request)), null, 200);
    }

    public function getTerm(Request $request, string $schoolYearId, string $examTypeId)
    {
        return ApiResponseService::success('Joint Exams Fetched Successfully', $this->timetable->term($this->school($request), $schoolYearId, $examTypeId), null, 200);
    }

    public function getJointCourse(Request $request, string $examJointCourseId)
    {
        return ApiResponseService::success('Joint Exam Fetched Successfully', $this->timetable->show($this->school($request), $examJointCourseId), null, 200);
    }

    public function syncExam(Request $request, string $examId)
    {
        return ApiResponseService::success('Joint Exams Linked Successfully', $this->timetable->syncExam($this->school($request), $examId), null, 200);
    }

    public function generateSlots(GenerateExamJointCourseSlotsRequest $request, string $examJointCourseId)
    {
        return ApiResponseService::success('Joint Exam Times Generated Successfully', $this->timetable->generate($this->school($request), $examJointCourseId, $request->validated()), null, 200);
    }

    public function confirmSlot(ConfirmExamJointCourseSlotRequest $request, string $examJointCourseId, string $proposalId)
    {
        return $this->saving(fn() => $this->timetable->confirmProposal($this->school($request), $examJointCourseId, $proposalId, $request->validated()['option_id']));
    }

    public function saveSlot(SaveExamJointCourseSlotRequest $request, string $examJointCourseId)
    {
        return $this->saving(fn() => $this->timetable->saveSlot($this->school($request), $examJointCourseId, $request->validated()));
    }

    public function removeSlot(Request $request, string $examJointCourseId)
    {
        return ApiResponseService::success('Joint Exam Time Removed Successfully', $this->timetable->removeSlot($this->school($request), $examJointCourseId), null, 200);
    }

    private function saving(callable $save)
    {
        try {
            $result = $save();
        } catch (ExamJointCourseSlotConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success('Joint Exam Time Saved Successfully', $result, null, 200);
    }

    private function school(Request $request): object
    {
        return $request->attributes->get('currentSchool');
    }
}
