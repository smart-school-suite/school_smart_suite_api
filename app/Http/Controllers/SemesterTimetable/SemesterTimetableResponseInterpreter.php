<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\TimetableResponseInterpreter\SemesterTimetableResponseInterpreterService;
use Illuminate\Http\Request;

class SemesterTimetableResponseInterpreter extends Controller
{
    protected SemesterTimetableResponseInterpreterService $semesterTimetableResponseInterpreterService;
    public function __construct(
        SemesterTimetableResponseInterpreterService
       $semesterTimetableResponseInterpreterService
      )
    {
        $this->semesterTimetableResponseInterpreterService = $semesterTimetableResponseInterpreterService;
    }

    public function interpret(Request $request){
        $result = $this->semesterTimetableResponseInterpreterService->interpret();
        return ApiResponseService::success("Timetable response interpreted successfully", $result, );
    }
}
