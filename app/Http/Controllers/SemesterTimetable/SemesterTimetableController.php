<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SemesterTimetable\GeneratePreferenceSemesterTimetableService;
use App\Services\SemesterTimetable\GenerateFixedSemesterTimetableService;
use App\Services\SemesterTimetable\SemesterTimetableConversationService;

class SemesterTimetableController extends Controller
{
    protected GeneratePreferenceSemesterTimetableService $preferenceTimetableService;
    protected GenerateFixedSemesterTimetableService $fixedTimetableService;

    public function __construct(
        GeneratePreferenceSemesterTimetableService $preferenceTimetableService,
        GenerateFixedSemesterTimetableService $fixedTimetableService
    ) {
        $this->preferenceTimetableService = $preferenceTimetableService;
        $this->fixedTimetableService = $fixedTimetableService;
    }

    public function generatePreferenceTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $generate = $this->preferenceTimetableService->generateTimetable($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable generated successfully", $generate, null, 200);
    }
    public function generateFixedTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $generate = $this->fixedTimetableService->generateTimetable($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable generated successfully", $generate, null, 200);
    }

    public function getTimetableConversations(
        SemesterTimetableConversationService $conversationService,
        Request $request,
        string $schoolSemesterId
    ) {
        $currentSchool = $request->attributes->get('currentSchool');
        $conversations = $conversationService->getConversationHistory($schoolSemesterId, $currentSchool);
        return ApiResponseService::success(
            "Conversation history retrieved successfully.",
            $conversations,
            null,
            200
        );
    }
}
