<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Stats\AnnouncementStatService;
use Illuminate\Http\Request;

class AnnouncementStatController extends Controller
{
    protected AnnouncementStatService $announcementStatService;
    public function __construct(AnnouncementStatService $announcementStatService){
        $this->announcementStatService = $announcementStatService;
    }

    public function getAnnouncementStatService(Request $request, $year){
        $currentSchool = $request->attributes->get('currentSchool');
        $announcementStats = $this->announcementStatService->getAnnouncementStats($currentSchool, $year);
        return ApiResponseService::success("Announcement Stats Fetched Successfully", $announcementStats, null, 200);
    }
}
