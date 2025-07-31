<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\TargetAudienceSerivice;
class TargetAudienceController extends Controller
{
    protected TargetAudienceSerivice $targetAudienceSerivice;
    public function __construct(TargetAudienceSerivice $targetAudienceSerivice){
        $this->targetAudienceSerivice = $targetAudienceSerivice;
    }

    public function getTargetAudience(Request $request){
       $currentSchool = $request->attributes->get("currentSchool");
       $audience = $this->targetAudienceSerivice->getAnnouncementTargetAudience($currentSchool);
       return ApiResponseService::success("Target Audience Fetched Successfully", $audience, null, 200);
    }
}
