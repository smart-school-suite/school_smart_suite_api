<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyTimetable\AiGenerateTimetableRequest;
use App\Services\ApiResponseService;
use App\Services\Timetable\AiGenerateTimetableService;
class AIGenTimetableController extends Controller
{
    protected AiGenerateTimetableService $aiGenerateTimetableService;
    public function __construct(AiGenerateTimetableService $aiGenerateTimetableService)
    {
        $this->aiGenerateTimetableService = $aiGenerateTimetableService;
    }

    public function generateTimetable(AiGenerateTimetableRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $timetable = $this->aiGenerateTimetableService->generateTimetable($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable Generated Successfully", $timetable, null, 200);
    }
}
