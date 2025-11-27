<?php

namespace App\Http\Controllers\ExamTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamTimetable\AutoGenExamTimetableRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\ExamTimetable\CreateExamTimetableRequest;
use App\Http\Requests\ExamTimetable\UpdateExamTimetableRequest;
use App\Services\ExamTimetable\AutoGenExamTimetableService;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use illuminate\Http\JsonResponse;
use App\Services\ExamTimetable\ExamTimetableService;
class ExamTimetableController extends Controller
{
        protected ExamTimetableService $examTimeTableService;
    protected AutoGenExamTimetableService $autoGenExamTimetableService;

    public function __construct(ExamTimetableService $examTimeTableService, AutoGenExamTimetableService $autoGenExamTimetableService)
    {
        $this->examTimeTableService = $examTimeTableService;
        $this->autoGenExamTimetableService  = $autoGenExamTimetableService;
    }

    public function autoGenExamTimetable(AutoGenExamTimetableRequest $request){
         try{
            $currentSchool = $request->attributes->get('currentSchool');
         $examTimetable = $this->autoGenExamTimetableService->autoGenExamTimetable($currentSchool, $request->validated());
         return ApiResponseService::success("Exam Timetable Generated Successfully", $examTimetable, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }
    /**
     * Creates a new exam timetable.
     *
     * @param CreateExamTimetableRequest $request The request containing the exam timetable data.
     * @param string $examId The ID of the exam.
     * @return JsonResponse
     */
    public function createTimetable(CreateExamTimetableRequest $request, string $examId): JsonResponse
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createdExamTimeTable = $this->examTimeTableService->createExamTimeTable($request->entries, $currentSchool, $examId);
            return ApiResponseService::success("Timetable Created Successfully", $createdExamTimeTable, null, Response::HTTP_CREATED);
        } catch (InvalidArgumentException $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a single exam timetable entry.
     *
     * @param Request $request The request.
     * @param string $entryId The ID of the exam timetable entry to delete.
     * @return JsonResponse
     */
    public function deleteTimetableEntry(Request $request, string $entryId): JsonResponse
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deletedExamTimeTableEntry = $this->examTimeTableService->deleteTimetableEntry($entryId, $currentSchool);

        if ($deletedExamTimeTableEntry) {
            return ApiResponseService::success("Exam Timetable Entry Deleted Successfully", $deletedExamTimeTableEntry, null, Response::HTTP_OK);
        } else {
            return ApiResponseService::error("Exam Timetable Entry Not Found", null, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Deletes the entire exam timetable for a given exam.
     *
     * @param Request $request The request.
     * @param string $examId The ID of the exam.
     * @return JsonResponse
     */
    public function deleteTimetable(Request $request, string $examId): JsonResponse
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $deletedTimetable = $this->examTimeTableService->deleteExamTimetable($examId, $currentSchool);
            return ApiResponseService::success("Exam Timetable Deleted Successfully", $deletedTimetable, null, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::error("Exam Not Found", null, Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Updates an existing exam timetable.
     *
     * @param UpdateExamTimetableRequest $request The request containing the updated exam timetable data.
     * @return JsonResponse
     */
    public function updateTimetable(UpdateExamTimetableRequest $request): JsonResponse
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $updatedExamTimetable = $this->examTimeTableService->updateExamTimetable($request->entries, $currentSchool);
            return ApiResponseService::success("Exam Timetable Updated Successfully", $updatedExamTimetable, null, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return ApiResponseService::error("Exam Timetable Entry Not Found", null, Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Retrieves the exam timetable for a specific specialty and level.
     *
     * @param Request $request The request.
     * @param string $specialtyId The ID of the specialty.
     * @param string $levelId The ID of the level.
     * @return JsonResponse
     */
    public function generateExamTimetable(Request $request): JsonResponse
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $generatedExamTimeTable = $this->examTimeTableService->generateExamTimeTable($examId, $currentSchool);
        return ApiResponseService::success("Exam Timetable Generated Successfully", $generatedExamTimeTable, null, Response::HTTP_OK);
    }

    /**
     * Prepares the data needed to create an exam timetable.
     *
     * @param Request $request The request.
     * @param string $examId The ID of the exam.
     * @return JsonResponse
     */
    public function prepareExamTimeTableData(Request $request, string $examId): JsonResponse
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $preparedExamTimeTableData = $this->examTimeTableService->prepareExamTimeTableData($examId, $currentSchool);
            return ApiResponseService::success("Exam Timetable Data Fetched Successfully", $preparedExamTimeTableData, null, Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return ApiResponseService::error("Exam Not Found", null, Response::HTTP_NOT_FOUND);
        }
    }

    public function getExamTimetableStudentIdExamId(Request $request){
         $currentSchool = $request->attributes->get('currentSchool');
            $studentId = $request->route('studentId');
            $examId = $request->route('examId');
            $examTimetable = $this->examTimeTableService->getExamTimetableStudentIdExamId($currentSchool, $studentId, $examId);
            return ApiResponseService::success("Exam Timetable Fetched Successfully", $examTimetable, null, 200);
    }
}
