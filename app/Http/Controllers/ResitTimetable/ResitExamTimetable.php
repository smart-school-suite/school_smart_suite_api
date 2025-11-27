<?php

namespace App\Http\Controllers\ResitTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResitExamTimetable\AutoGenResitExamTimetableRequest;
use App\Services\ApiResponseService;
use App\Services\ResitTimetable\ResitTimetableService;
use App\Services\ResitTimetable\AutoGenResitExamTimetableService;
use App\Http\Requests\ResitExamTimetable\UpdateResitExamTimetableRequest;
use App\Http\Requests\ResitExamTimetable\CreateResitTimetableRequest;
use InvalidArgumentException;
use Exception;
use Illuminate\Http\Request;

class ResitExamTimetable extends Controller
{
        protected ResitTimeTableService $resitTimeTableService;
    protected AutoGenResitExamTimetableService $autoGenResitExamTimetableService;
    public function __construct(
        ResitTimeTableService $resitTimeTableService,
         AutoGenResitExamTimetableService $autoGenResitExamTimetableService
        )
    {
        $this->resitTimeTableService = $resitTimeTableService;
        $this->autoGenResitExamTimetableService = $autoGenResitExamTimetableService;
    }
    public function getResitCoursesByExam(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $resitableCourses = $this->resitTimeTableService->getResitableCoursesByExam($currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Courses Fetched Successfully", $resitableCourses, null, 200);
    }
    public function createResitTimetable(CreateResitTimetableRequest $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createExamTimeTable = $this->resitTimeTableService->createResitTimetable($request->entries, $currentSchool, $resitExamId);
            return ApiResponseService::success("Time Table Created Sucessfully", $createExamTimeTable, null, 201);
        } catch (InvalidArgumentException $e) {
           return  ApiResponseService::error($e->getMessage(), null, 422);
        } catch (Exception $e) {
           return   ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function deleteResitTimetable(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteResitTimetable = $this->resitTimeTableService->deleteResitTimetable($resitExamId, $currentSchool);
        return ApiResponseService::success("Resit Time table deleted Successfully", $deleteResitTimetable, null, 200);
    }
    public function updateResitTimetable(UpdateResitExamTimetableRequest $request, $resitExamId){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateResitTimetable = $this->resitTimeTableService->updateResitTimetable($request->entries, $currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Timetable Updated Successfully", $updateResitTimetable, null, 200);
    }

    public function autoGenerateResitExamTimetable(AutoGenResitExamTimetableRequest $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $genResitExamTimetable = $this->autoGenResitExamTimetableService->autoGenExamTimetable($currentSchool,$request->validated());
         return ApiResponseService::success("Resit Timetable Generated Successfully", $genResitExamTimetable, null, 200);
    }
}
