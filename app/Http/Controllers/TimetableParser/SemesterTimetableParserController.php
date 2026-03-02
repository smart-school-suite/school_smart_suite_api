<?php

namespace App\Http\Controllers\TimetableParser;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\TimetableParser\SemesterTimetableParserService;
use Illuminate\Http\Request;

class SemesterTimetableParserController extends Controller
{
    protected SemesterTimetableParserService $semesterTimetableParserService;
    public function __construct(SemesterTimetableParserService $semesterTimetableParserService)
    {
        $this->semesterTimetableParserService = $semesterTimetableParserService;
    }

    public function interpret(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $response = $this->semesterTimetableParserService->interpret($currentSchool);
        return ApiResponseService::success("Timetable Interpreted Successfully", $response, null, 200);
    }
}
