<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialtyTimetable\AutomaticGenerateTimetableRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\AutomaticTimetableService;
class AutomaticTimetableController extends Controller
{
    protected AutomaticTimetableService $automaticTimetableService;
    public function __construct(AutomaticTimetableService $automaticTimetableService){
         $this->automaticTimetableService = $automaticTimetableService;
    }

    public function generateTimetable(AutomaticGenerateTimetableRequest $request, $schoolSemesterId){
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableSlots = $this->automaticTimetableService->generateRandomTimetable($currentSchool, $schoolSemesterId, $request->validated());
        return ApiResponseService::success("Timetable Generated Successfully", $timetableSlots, null, 200);
    }
}
