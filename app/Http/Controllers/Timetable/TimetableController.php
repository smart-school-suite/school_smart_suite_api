<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyTimetable\UpdateTimetableByTeacherAvailability;
use Illuminate\Http\Request;
use App\Http\Requests\SpecialtyTimetable\CreateTimetableRequest;
use App\Http\Requests\SpecialtyTimetable\GenerateTimetableRequest;
use App\Http\Requests\SpecialtyTimetable\CreateTimeTableByTeacherAvailabilityRequest;
use App\Http\Requests\SpecialtyTimetable\UpdateTimetableRequest;
use App\Services\Timetable\CreateTimetableService;
use App\Services\Timetable\UpdateTimetableService;
use App\Services\Timetable\TimetableService;
use App\Services\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;
use Exception;


class TimetableController extends Controller
{
        /**
     * @var CreateTimetableService
     */
    protected CreateTimetableService $createSpecailtyTimeTableService;

    /**
     * @var TimetableService
     */
    protected TimetableService $specailtyTimeTableService;

    /**
     * @var UpdateTimeTableService
     */
    protected UpdateTimeTableService $updateTimeTableService;

    /**
     * Constructor to inject the required services.
     *
     * @param CreateTimetableService $createSpecailtyTimeTableService
     * @param TimetableService $specailtyTimeTableService
     * @param UpdateTimeTableService $updateTimeTableService
     */
    public function __construct(
        CreateTimetableService $createSpecailtyTimeTableService,
        TimetableService $specailtyTimeTableService,
        UpdateTimeTableService $updateTimeTableService
    ) {
        $this->createSpecailtyTimeTableService = $createSpecailtyTimeTableService;
        $this->specailtyTimeTableService = $specailtyTimeTableService;
        $this->updateTimeTableService = $updateTimeTableService;
    }

    /**
     * Creates a timetable based on teacher availability.
     *
     * @param CreateTimeTableByTeacherAvailabilityRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTimetableByAvailability(CreateTimeTableByTeacherAvailabilityRequest $request)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $result = $this->createSpecailtyTimeTableService->createTimetableByAvailability(
                $request->scheduleEntries,
                $currentSchool
            );
            return ApiResponseService::success("Timetable Created Successfully", $result, null, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Creates a new timetable.
     *
     * @param CreateTimetableRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTimetable(CreateTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $result = $this->createSpecailtyTimeTableService->createTimetable(
                $request->scheduleEntries,
                $currentSchool
            );
            return ApiResponseService::success("Timetable Created Successfully", $result, null, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Deletes a timetable based on the provided parameters.
     *
     * @param GenerateTimeTableRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTimetable(GenerateTimeTableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $deleteTimetable = $this->specailtyTimeTableService->deleteTimetable($currentSchool, $request->validated());
            return ApiResponseService::success("Timetable Deleted Successfully", $deleteTimetable, null, Response::HTTP_OK);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Deletes a specific entry from the timetable.
     *
     * @param Request $request
     * @param string $entryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTimeTableEntry(Request $request, string $entryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $deleteTimetableEntry = $this->specailtyTimeTableService->deleteTimeTableEntry($currentSchool, $entryId);
            return ApiResponseService::success("Time Table Entry Deleted Successfully", $deleteTimetableEntry, null, Response::HTTP_OK);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Updates existing timetable entries.
     *
     * @param UpdateTimetableRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimetable(UpdateTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $updateTimetable = $this->updateTimeTableService->updateTimetableEntries($request->scheduleEntries, $currentSchool);
            return ApiResponseService::success("{$updateTimetable} entries updated Successfully", null, null, Response::HTTP_OK);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Updates timetable entries based on teacher availability.
     *
     * @param UpdateTimetableByTeacherAvailability $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTimetableByTeacherAvailability(UpdateTimetableByTeacherAvailability $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $updateTimetable = $this->updateTimeTableService->updateTimetableEntriesByTeacherAvailability(
                $request->scheduleEntries,
                $currentSchool
            );
            return ApiResponseService::success("{$updateTimetable} entries updated Successfully", null, null, Response::HTTP_OK);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Generates a new timetable.
     *
     * @param GenerateTimeTableRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateTimetable(GenerateTimeTableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $generateTimeTable = $this->specailtyTimeTableService->generateTimeTable($request->validated(), $currentSchool);
        return ApiResponseService::success('Time Table Generated Sucessfully', $generateTimeTable, null, Response::HTTP_OK);
    }

    /**
     * Retrieves the details of a specific timetable entry.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimetableDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $entry_id =  $request->route("entryId");
        $getTimeTableDetails = $this->specailtyTimeTableService->getTimeTableDetails($entry_id, $currentSchool);
        return ApiResponseService::success("Time Table Details Fetched Sucessfully", $getTimeTableDetails, null, Response::HTTP_OK);
    }

    /**
     * Retrieves the availability of instructors for a specific semester and specialty.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInstructorAvailabilityBySemesterSpecialty(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $specialtyId = $request->route("specialtyId");
        $semesterId = $request->route("semesterId");
        $getInstructorAvailability = $this->specailtyTimeTableService->getInstructorAvailability($specialtyId, $semesterId,  $currentSchool,);
        return ApiResponseService::success("Instructor Availability Data Fetched Sucessfully", $getInstructorAvailability, null, Response::HTTP_OK);
    }

    public function getTimetableStudent(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $authStudent = $this->resolveUser();
        $timetable = $this->specailtyTimeTableService->getStudentTimetable($currentSchool, $authStudent);
        return ApiResponseService::success("Timetable Fetched Successfully", null, $timetable, 200);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
