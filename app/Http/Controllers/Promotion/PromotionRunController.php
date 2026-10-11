<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Promotion\PromotionRunService;
use Illuminate\Http\Request;

class PromotionRunController extends Controller
{
    protected PromotionRunService $promotionRunService;
    public function __construct(PromotionRunService $promotionRunService)
    {
        $this->promotionRunService = $promotionRunService;
    }
    public function getPromotionRuns(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionRuns = $this->promotionRunService->getPromotionRuns($currentSchool);
        return ApiResponseService::success("Promotion Runs Fetched Successfully", $promotionRuns, null, 200);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
