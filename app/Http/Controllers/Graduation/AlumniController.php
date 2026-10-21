<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Graduation\AlumniResource;
use App\Services\ApiResponseService;
use App\Services\Graduation\AlumniService;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    protected AlumniService $alumniService;

    public function __construct(AlumniService $alumniService)
    {
        $this->alumniService = $alumniService;
    }
    public function getAlumni(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $alumni = $this->alumniService->getAlumni($currentSchool);
        return ApiResponseService::success("Alumni Fetched Successfully", AlumniResource::collection($alumni), null, 200);
    }
    public function getAlumusDetails(Request $request, string $alumusId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $alumus = $this->alumniService->getAlumnusDetails($currentSchool, $alumusId);
        return ApiResponseService::success("Alumni Details Fetched Successfully", $alumus, null, 200);
    }
    public function reinstateAlumus(Request $request, string $alumusId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reinstate = $this->alumniService->reinstateAlumnus($currentSchool, $alumusId);
        return ApiResponseService::success("Alumus Reinstated Successfully", $reinstate, null, 200);
    }
}
