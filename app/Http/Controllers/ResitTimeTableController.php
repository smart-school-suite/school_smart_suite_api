<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\ResitTimeTableService;
use App\Http\Resources\ResitCourseByExamResource;
use App\Http\Requests\CreateResitTimetableRequest;
use InvalidArgumentException;
use Exception;
use Illuminate\Http\Request;

class ResitTimeTableController extends Controller
{
    //ResitTimeTableController
    //ResitcontrollerTimetable

    protected $resitTimeTableService;
    public function __construct(ResitTimeTableService $resitTimeTableService)
    {
        $this->resitTimeTableService = $resitTimeTableService;
    }

    public function getResitCoursesByExam(Request $request, $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $resitableCourses = $this->resitTimeTableService->getResitableCoursesByExam($currentSchool, $examId);
        return ApiResponseService::success("Resit Courses Fetched Successfully", ResitCourseByExamResource::collection($resitableCourses), null, 200);
     }

    public function createResitTimetable(CreateResitTimetableRequest $request, $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createExamTimeTable = $this->resitTimeTableService->createResitTimetable($request->entries, $currentSchool, $examId);
            return ApiResponseService::success("Time Table Created Sucessfully", $createExamTimeTable, null, 201);
        } catch (InvalidArgumentException $e) {
            ApiResponseService::error($e->getMessage(), null, 422);

        } catch (Exception $e) {
            ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteResitTimetable(Request $request, $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteResitTimetable = $this->resitTimeTableService->deleteResitTimetable($examId, $currentSchool);
        return ApiResponseService::success("Resit Time table deleted Successfully", $deleteResitTimetable, null, 200);
    }

}
