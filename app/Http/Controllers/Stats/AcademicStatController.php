<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\Analytics\Academic\Widget\Card\CardStats;
use App\Services\Analytics\Academic\Widget\FailRate\ExamTypeFailRate;
use App\Services\Analytics\Academic\Widget\FailRate\LevelFailRate;
use App\Services\Analytics\Academic\Widget\FailRate\SchoolFailRate;
use App\Services\Analytics\Academic\Widget\Gpa\ExamTypeAverageGpa;
use App\Services\Analytics\Academic\Widget\Gpa\LevelAverageGpa;
use App\Services\Analytics\Academic\Widget\Gpa\SchoolAverageGpa;
use App\Services\Analytics\Academic\Widget\Grade\ExamTypeGradeDistribution;
use App\Services\Analytics\Academic\Widget\Grade\LevelGradeDistribution;
use App\Services\Analytics\Academic\Widget\Grade\SchoolGradeDistribution;
use App\Services\Analytics\Academic\Widget\PassRate\ExamTypePassRate;
use App\Services\Analytics\Academic\Widget\PassRate\LevelPassRate;
use App\Services\Analytics\Academic\Widget\PassRate\SchoolPassRate;
use App\Services\Analytics\Academic\Widget\Resit\ExamTypeResit;
use App\Services\Analytics\Academic\Widget\Resit\LevelResit;
use App\Services\Analytics\Academic\Widget\Resit\ResitSuccessRate;
use App\Services\Analytics\Academic\Widget\Resit\SchoolResitTotal;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicStatController extends Controller
{
    public function getCardStats(
        Request $request,
        CardStats $cardStats,
        $year
    ): JsonResponse {
        $currrentSchool = $request->attributes->get("currentSchool");
        $stats = $cardStats->getCardStats($currrentSchool, $year);
        return ApiResponseService::success("Academic Card Stats Fetched Successfully", $stats, null, 200);
    }

    public function getExamTypeFailRate(
        Request $request,
        ExamTypeFailRate $examTypeFailRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $examTypeFailRate->getExamTypeFailRate($currentSchool, $year);
        return ApiResponseService::success("Exam Type Fail Rate Fetched Successfully", $stats, null, 200);
    }

    public function getLevelFailRate(
        Request $request,
        LevelFailRate $levelFailRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $levelFailRate->getLevelFailRate($currentSchool, $year);
        return ApiResponseService::success("Level Fail Rate Fetched Successfully", $stats, null, 200);
    }

    public function getSchoolFailRate(
        Request $request,
        SchoolFailRate $schoolFailRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolFailRate->getSchoolFailRate($currentSchool, $year);
        return ApiResponseService::success("School Fail Rate Fetched Successfully", $stats, null, 200);
    }

    public function getExamTypeAverageGpa(
        Request $request,
        ExamTypeAverageGpa $examTypeAverageGpa,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $examTypeAverageGpa->getExamTypeAverageGpa($currentSchool, $year);
        return ApiResponseService::success("Exam Type Average Gpa Fetched Successfully", $stats, null, 200);
    }

    public function getLevelAverageGpa(
        Request $request,
        LevelAverageGpa $levelAverageGpa,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $levelAverageGpa->getLevelAverageGpa($currentSchool, $year);
        return ApiResponseService::success("Level Average Gpa Fetched Successfully", $stats, null, 200);
    }

    public function getSchoolAverageGpa(
        Request $request,
        SchoolAverageGpa $schoolAverageGpa,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolAverageGpa->getSchoolAverageGpa($currentSchool, $year);
        return ApiResponseService::success("School Average Gpa Fetched Successfully", $stats, null, 200);
    }

    public function getExamTypeGradeDistribution(
        Request $request,
        ExamTypeGradeDistribution $examTypeGradeDistribution,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $examTypeGradeDistribution->getExamTypeGradeDistribution($currentSchool, $year);
        return ApiResponseService::success("Exam Type Grade Distribution Fetched Successfully", $stats, null, 200);
    }

    public function getLevelGradeDistribution(
        Request $request,
        LevelGradeDistribution $levelGradeDistribution,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $levelGradeDistribution->getLevelGradeDistribution($currentSchool, $year);
        return ApiResponseService::success("Level Grade Distribution Fetched Successfully", $stats, null, 200);
    }

    public function getSchoolGradeDistribution(
        Request $request,
        SchoolGradeDistribution $schoolGradeDistribution,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolGradeDistribution->getSchoolGradeDistribution($currentSchool, $year);
        return ApiResponseService::success("School Grade Distribution Fetched Successfully", $stats, null, 200);
    }

    public function getExamTypePassRate(
        Request $request,
        ExamTypePassRate $examTypePassRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $examTypePassRate->getExamTypePassRate($currentSchool, $year);
        return ApiResponseService::success("Exam type pass rate Fetched Successfully", $stats, null, 200);
    }

    public function getLevelPassRate(
        Request $request,
        LevelPassRate $levelPassRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $levelPassRate->getLevelPassRate($currentSchool, $year);
        return ApiResponseService::success("Level Pass Rate Fetched Successfully", $stats, null, 200);
    }

    public function getSchoolPassRate(
        Request $request,
        SchoolPassRate $schoolPassRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolPassRate->getSchoolPassRate($currentSchool, $year);
        return ApiResponseService::success("School Pass Rate Fetched Successfully", $stats, null, 200);
    }

    public function getExamTypeResit(
        Request $request,
        ExamTypeResit  $examTypeResit,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $examTypeResit->getExamTypeResit($currentSchool, $year);
        return ApiResponseService::success("Exam Type Resit Total Fetched Succesfully", $stats, null, 200);
    }

    public function getResitLevelTotal(
        Request $request,
        LevelResit $levelResit,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $levelResit->getResitLevel($currentSchool, $year);
        return ApiResponseService::success("Level Resit Total Fetched Successfully", $stats, null, 200);
    }

    public function getResitSuccessRate(
        Request $request,
        ResitSuccessRate $resitSuccessRate,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $resitSuccessRate->getResitSuccessRate($currentSchool, $year);
        return ApiResponseService::success("Resit Success Rate Fetched Successfully", $stats, null, 200);
    }

    public function getSchoolResitTotal(
        Request $request,
        SchoolResitTotal $schoolResitTotal,
        $year
    ): JsonResponse {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolResitTotal->getTotalResit($currentSchool, $year);
        return ApiResponseService::success("School Resit Total Fetched Successfully", $stats, null, 200);
    }
}
