<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Http\Requests\OperationalStat\StudentDropoutRateRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Analytics\Operational\Widget\Student\TotalStudent;
use App\Services\Analytics\Operational\Widget\Student\StudentSource;
use App\Services\Analytics\Operational\Widget\Card\CardStat;
use App\Services\Analytics\Operational\Widget\StudentDropout\StudentDropoutRateLevel;
use App\Services\Analytics\Operational\Widget\StudentDropout\StudentDropoutRate;
use App\Services\Analytics\Operational\Widget\StudentRetention\StudentLevelRetentionRate;
use App\Services\Analytics\Operational\Widget\StudentRetention\StudentRetentionRate;
use App\Services\Analytics\Operational\Widget\Teacher\TeacherStudentRatio;
use App\Services\Analytics\Operational\Widget\Teacher\TeacherStudentRatioLevel;
use App\Services\Analytics\Operational\Widget\Teacher\TeacherRetentionRate;
use App\Services\Analytics\Operational\Widget\Student\StudentRegistration;
use App\Services\Analytics\Operational\Widget\Student\StudentLevelRegistration;

class OperationalStatController extends Controller
{
    public function getTeacherRetentionRate(
         Request $request,
         TeacherRetentionRate $teacherRetentionRate
    ) : JsonResponse {
          $currentSchool = $request->attributes->get("currentSchool");
          $stats = $teacherRetentionRate->getTeacherRentionRate($currentSchool);
          return ApiResponseService::success(
            "Teacher Retention Rate Fetched Successfully",
            $stats,
            null,
            200
          );
    }
    public function getStudentLevelRegistration(
        Request $request,
        StudentLevelRegistration $studentLevelRegistration,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $studentLevelRegistration->getStudentLevelRegistration($currentSchool);
        return ApiResponseService::success("Student Level Registration Stats Fetched Successfully", $stats, null, 200);
    }
    public function getStudentRegistration(
        Request $request,
        StudentRegistration $studentRegistration,
        $year
    ): JsonResponse {
        $currentSchool =  $request->attributes->get("currentSchool");
        $stats = $studentRegistration->getStudentRegistration($currentSchool, $year);
        return ApiResponseService::success("Student Registration Stats Fetched Successfully", $stats, null, 200);
    }
    public function getTeacherStudentRatiolLevel(
        Request $request,
        TeacherStudentRatioLevel $teacherStudentRatioLevel
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $teacherStudentRatioLevel->getTeacherStudentLevelRatio($currentSchool);
        return ApiResponseService::success("Teacher Student Ratio Level Fetched Successfully", $stats, null, 200);
    }
    public function getTeacherStudentRatio(
        Request $request,
        TeacherStudentRatio $teacherStudentRatio
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $teacherStudentRatio->getTeacherStudentRatio($currentSchool);
        return ApiResponseService::success("Teacher Student Ratio Fetched Successfully", $stats, null, 200);
    }
    public function getStudentRetentionRate(
        Request $request,
        StudentRetentionRate $studentRetentionRate
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $studentRetentionRate->getStudentRententionRate($currentSchool);
        return ApiResponseService::success("Student Retention Rate Fetched Successfully", $stats, null, 200);
    }
    public function getStudentLevelRetentionRate(
        Request $request,
        StudentLevelRetentionRate $studentLevelRetentionRate
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stat = $studentLevelRetentionRate->getStudentLevelRetentionRate($currentSchool);
        return ApiResponseService::success("Student Level Retention Rate Fetched Successfully", $stat, null, 200);
    }
    public function getStudentLevelDropoutRate(
        Request $request,
        StudentDropoutRateLevel $studentDropoutRateLevel,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stat = $studentDropoutRateLevel->getStudentDropoutRateLevel($currentSchool, $year);
        return ApiResponseService::success("Student Level Dropout Rate Fetched Successfully", $stat, null, 200);
    }
    public function getStudentDropoutRate(
        Request $request,
        StudentDropoutRate $studentDropoutRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $studentDropoutRate->getStudentDropoutRate($currentSchool, $year);
        return ApiResponseService::success("Student Dropout Rate Fetched Successfully", $stats, null, 200);
    }
    public function getCardStats(
        Request $request,
        CardStat $cardStat,
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $cardStat->getCardStats($currentSchool);
        return ApiResponseService::success("Operational Stats Fetched Successfully", $stats, null, 200);
    }
    public function getStudentTotal(
        Request $request,
        TotalStudent $totalStudent
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $totalStudent->getTotalStudents($currentSchool);
        return ApiResponseService::success("Student Total Fetched Successfully", $stats, null, 200);
    }
    public function getStudentRegistrationSource(
        Request $request,
        StudentSource $studentSource,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $studentSource->getStudentSource($currentSchool, $year);
        return ApiResponseService::success("Student Registration Source Fetched Successfully", $stats, null, 200);
    }
}
