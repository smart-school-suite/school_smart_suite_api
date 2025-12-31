<?php

namespace App\Http\Controllers\Stats;

use app\Http\Controllers\Controller;
use App\Http\Requests\FiancialStat\LevelExamTypeRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Analytics\Financial\Widget\Card\CardStat;
use App\Services\Analytics\Financial\Widget\AdditionalFee\AdditionalFeePaidVsUnpaidCategory;
use App\Services\Analytics\Financial\Widget\AdditionalFee\AdditionalFeePaidVsUnpaidLevel;
use App\Services\Analytics\Financial\Widget\AdditionalFee\AdditionalFeePaymentRate;
use App\Services\Analytics\Financial\Widget\Revenue\SchoolRevenue;
use App\Services\Analytics\Financial\Widget\Revenue\SchoolRevenueSource;
use App\Services\Analytics\Financial\Widget\RegistrationFee\RegistrationFeeDebtVsPaidLevel;
use App\Services\Analytics\Financial\Widget\RegistrationFee\RegistrationFeePaidProgress;
use App\Services\Analytics\Financial\Widget\TuitionFee\TuitionFeeDebtVsPaidLevel;
use App\Services\Analytics\Financial\Widget\TuitionFee\TuitionFeePaymentRate;
use App\Services\Analytics\Financial\Widget\ResitFee\ResitFeePaidVsDebtLevelExamType;
use App\Services\Analytics\Financial\Widget\ResitFee\ResitFeePaymentRate;
class FinancialStatController extends Controller
{
    public function getResitFeePaidVsDebtLevelExamType(
        LevelExamTypeRequest $request,
        ResitFeePaidVsDebtLevelExamType $resitFeePaidVsDebtLevelExamType,
        $year
    ){
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $resitFeePaidVsDebtLevelExamType->getResitFeePaidVsDebtLevelExamType($currentSchool, $year, $request->validated());
        return ApiResponseService::success(
             "Resit Fee Paid Vs Debt Level Exam Type Fetched Successfully",
             $stats,
             null,
             200
        );
    }
    public function getResitFeePaymentRate(
        Request $request,
        ResitFeePaymentRate $resitFeePaymentRate,
        $year
    ){
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $resitFeePaymentRate->getResitFeePaymentRate($currentSchool, $year);
        return ApiResponseService::success(
             "Resit Fee Payment Rate Fetched Successfully",
             $stats,
             null,
             200
        );
    }
    public function getCardStats(
        Request $request,
        CardStat $cardStat,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $cardStats = $cardStat->getCardData($currentSchool, $year);
        return  ApiResponseService::success(
            "Financial Card Stats Fetched Successfully",
            $cardStats,
            null,
            200
        );
    }
    public function getAdditionalFeePaidVsUnpaidCategory(
        Request $request,
        AdditionalFeePaidVsUnpaidCategory $additionalFeePaidVsUnpaidCategory,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $additionalFeePaidVsUnpaidCategory->getAdditionalFeePaidVsUnpaidCategory($currentSchool, $year);
        return  ApiResponseService::success(
            "Additional Fee Paid Vs Unpaid Category Fetched Successfully",
            $stats,
            null,
            200
        );
    }
    public function getAdditionalFeePaymentRate(
        Request $request,
        AdditionalFeePaymentRate $additionalFeePaymentRate,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $additionalFeePaymentRate->getAdditionalFeePaymentRate($currentSchool, $year);
        return ApiResponseService::success(
            "Additional Fee Payment Rate Fetched Successfully",
            $stats,
            null,
            200
        );
    }
    public function getAdditionalFeePaidVsUnpaidLevel(
        Request  $request,
        AdditionalFeePaidVsUnpaidLevel $additionalFeePaidVsUnpaidLevel,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $additionalFeePaidVsUnpaidLevel->getAdditionalFeeDebtVsPaidLevel($currentSchool, $year);
        return ApiResponseService::success(
            "Additional Fee Paid Vs Unpaid Level Fetched Successfully",
            $stats,
            null,
            200
        );
    }
    public function getSchoolRevenue(
        Request $request,
        SchoolRevenue  $schoolRevenue,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolRevenue->getSchoolRevenue($currentSchool, $year);
        return ApiResponseService::success(
            "school revenue fetched Successfully",
            $stats,
            null,
            200
        );
    }
    public function getSchoolRevenueSource(
        Request $request,
        SchoolRevenueSource $schoolRevenueSource,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $schoolRevenueSource->getSchoolRevenueSource($currentSchool, $year);
        return ApiResponseService::success(
            "School Revenue Resource Fetched Successfully",
            $stats,
            null,
            200
        );
    }

    public function getRegistrationFeePaidVsDebtLevel(
        Request $request,
        RegistrationFeeDebtVsPaidLevel $registrationFeeDebtVsPaidLevel,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $registrationFeeDebtVsPaidLevel->getRegistrationFeePaidUnpaidLevel($currentSchool, $year);
        return ApiResponseService::success(
            "Registration Fee Paid Vs Debt Level Fetched Successfully",
            $stats,
            null,
            200
        );
    }

    public function getRegistrationFeePaidPaymentRate(
        Request $request,
        RegistrationFeePaidProgress $registrationFeePaidProgress,
        $year
    ) {
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $registrationFeePaidProgress->getRegistrationFeePaymentRate($currentSchool, $year);
        return ApiResponseService::success(
            "Registration Fee Payment Rate Fetched Successfully",
            $stats,
            null,
            200
        );
    }

    public function getTuitionFeePaymentRate(
        Request $request,
        TuitionFeePaymentRate $tuitionFeePaymentRate,
        $year
    ){
         $currentSchool = $request->attributes->get('currentSchool');
         $stats = $tuitionFeePaymentRate->getTuitionFeePaymentRate($currentSchool, $year);
         return ApiResponseService::success(
             "Tuition Fee Payment Rate Fetched Successfully",
             $stats,
             null,
             200
         );
    }

    public function getTuitionFeePaidVsUnpaidLevel(
        Request $request,
        TuitionFeeDebtVsPaidLevel $tuitionFeeDebtVsPaidLevel,
        $year
    ){
        $currentSchool = $request->attributes->get("currentSchool");
        $stats = $tuitionFeeDebtVsPaidLevel->getTuitionFeeDebtVsPaidLevel($currentSchool, $year);
        return ApiResponseService::success(
             "Tuition Fee Paid Vs Unpaid Level",
             $stats,
             null,
             200
        );
    }
}
