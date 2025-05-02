<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CreateSpecailtyTimeTableService;
use App\Services\SpecailtyTimeTableService;
use App\Http\Requests\SpecialtyTimetable\CreateTimetableRequest;
use App\Http\Requests\SpecialtyTimetable\GenerateTimetableRequest;
use App\Services\ApiResponseService;


class TimeTableController extends Controller
{
    //
    protected CreateSpecailtyTimeTableService $createSpecailtyTimeTableService;
    protected SpecailtyTimeTableService $specailtyTimeTableService;
    public function __construct(CreateSpecailtyTimeTableService $createSpecailtyTimeTableService, SpecailtyTimeTableService $specailtyTimeTableService)
    {
        $this->createSpecailtyTimeTableService = $createSpecailtyTimeTableService;
        $this->specailtyTimeTableService = $specailtyTimeTableService;
    }
    public function createTimetableByAvailability(CreateTimetableRequest $request,  $semesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $result = $this->createSpecailtyTimeTableService->createTimetableByAvailability($request->scheduleEntries, $currentSchool, $semesterId);
        if ($result['error']) {
            return ApiResponseService::error("Conflicts detected with existing schedules", $result['conflicts'], 409);
        }
        return ApiResponseService::success("Timetable entries successfully created.", $result['data']);
    }
    public function createTimetable(CreateTimetableRequest $request, $semesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $result = $this->createSpecailtyTimeTableService->createTimetable($request->scheduleEntries, $currentSchool, $semesterId);

        if ($result['error']) {
            return ApiResponseService::error("Conflicts detected with existing schedules", $result['conflicts'], 409);
        }

        return ApiResponseService::success("Timetable entries successfully created.", $result['data']);
    }
    public function deleteTimetable(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTimeTableRecord = $this->specailtyTimeTableService->deleteTimeTableEntry($currentSchool, $timetable_id);
        return ApiResponseService::success('Entry deleted sucessfully', $deleteTimeTableRecord, null, 200);
    }
    public function updateTimetable(Request $request, $timetable_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateTimeTable = $this->specailtyTimeTableService->updateTimeTable($request->specialty_timetable, $currentSchool, $timetable_id);
        return ApiResponseService::success('Time Table Updated Succefully', $updateTimeTable, null, 200);
    }
    public function generateTimetable(GenerateTimeTableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $generateTimeTable = $this->specailtyTimeTableService->generateTimeTable($request->validated(), $currentSchool);
        return ApiResponseService::success('Time Table Generated Sucessfully', $generateTimeTable, null, 200);
    }
    public function getTimetableDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $entry_id =  $request->route("entry_id");
        $getTimeTableDetails = $this->specailtyTimeTableService->getTimeTableDetails($entry_id, $currentSchool);
        return ApiResponseService::success("Time Table Details Fetched Sucessfully", $getTimeTableDetails, null, 200);
    }
    public function getInstructorAvailabilityBySemesterSpecialty(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $specialtyId = $request->route("specialty_id");
        $semesterId = $request->route("semester_id");
        $getInstructorAvailability = $this->specailtyTimeTableService->getInstructorAvailability($specialtyId, $semesterId,  $currentSchool,);
        return ApiResponseService::success("Instructor Availability Data Fetched Sucessfully", $getInstructorAvailability, null, 200);
    }
}
