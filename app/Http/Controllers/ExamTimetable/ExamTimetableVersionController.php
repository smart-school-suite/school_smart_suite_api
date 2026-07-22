<?php

namespace App\Http\Controllers\ExamTimetable;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\ExamTimetable\ExamTimetableVersionService;
use Illuminate\Http\Request;

class ExamTimetableVersionController extends Controller
{
    protected ExamTimetableVersionService $examTimetableVersionService;
    public function __construct(ExamTimetableVersionService $examTimetableVersionService)
    {
        $this->examTimetableVersionService = $examTimetableVersionService;
    }

    public function getVersionsExamId(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $versions = $this->examTimetableVersionService->getVersionExamId($currentSchool, $examId);
        return ApiResponseService::success("Exam Timetable Versions Fetched Successfully", $versions, null, 200);
    }
    public function createVersion(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'required|uuid|exists:exams,id',
        ]);
        $currentSchool = $request->attributes->get('currentSchool');
        $createVersion = $this->examTimetableVersionService->createVersion($currentSchool, $data);
        return ApiResponseService::success("Exam Timetable Created Successfully", $createVersion, null, 201);
    }

    public function deleteVersion(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteVersion = $this->examTimetableVersionService->deleteVersion($currentSchool, $versionId);
        return ApiResponseService::success("Exam Timetable Version Deleted Successfully", $deleteVersion, null, 200);
    }
}
