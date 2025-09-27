<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIGenTimetableService;
class AIGenTimetableController extends Controller
{
    protected  AIGenTimetableService $aiGenTimetableService;
    public function __construct(AIGenTimetableService $aiGenTimetableService){
        $this->aiGenTimetableService = $aiGenTimetableService;
    }

    public function generateTimetable(Request $request, $schoolSemesterId){
        $currentSchool = $request->attributes->get("currentSchool");
        $timetableData = $this->aiGenTimetableService->generateTimetable($currentSchool, $schoolSemesterId);
        return response()->json([
            'status' => 'success',
            'data' => $timetableData
        ]);
    }
}
