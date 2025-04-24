<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\ResitTimeTableService;
use App\Http\Requests\CreateResitTimetableRequest;
use App\Http\Requests\UpdateResitTimetableRequest;
use InvalidArgumentException;
use Exception;
use Illuminate\Http\Request;

class ResitTimeTableController extends Controller
{


    protected $resitTimeTableService;
    public function __construct(ResitTimeTableService $resitTimeTableService)
    {
        $this->resitTimeTableService = $resitTimeTableService;
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
            ApiResponseService::error($e->getMessage(), null, 422);
        } catch (Exception $e) {
            ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function deleteResitTimetable(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteResitTimetable = $this->resitTimeTableService->deleteResitTimetable($resitExamId, $currentSchool);
        return ApiResponseService::success("Resit Time table deleted Successfully", $deleteResitTimetable, null, 200);
    }
    public function updateResitTimetable(UpdateResitTimetableRequest $request, $resitExamId){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateResitTimetable = $this->resitTimeTableService->updateResitTimetable($request->entries, $currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Timetable Updated Successfully", $updateResitTimetable, null, 200);
    }

}
