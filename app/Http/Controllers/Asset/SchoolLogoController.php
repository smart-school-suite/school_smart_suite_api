<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\UploadSchoolLogoRequest;
use App\Services\ApiResponseService;
use App\Services\Asset\SchoolLogoService;
use Illuminate\Http\Request;

class SchoolLogoController extends Controller
{
    protected SchoolLogoService $schoolLogoService;
    public function __construct(SchoolLogoService $schoolLogoService)
    {
        $this->schoolLogoService = $schoolLogoService;
    }

    public function uploadLogo(UploadSchoolLogoRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $school = $currentSchool->school;
        $schoolBranchId = $currentSchool->id;
        $logo = $request->safe()->only(['school_logo'])['school_logo'];
        $uploadLogo = $this->schoolLogoService->uploadLogo($school, $logo, $schoolBranchId);
        return ApiResponseService::success("Logo Uploaded Successfully", $uploadLogo, null, 200);
    }
    public function removeLogo(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $school = $currentSchool->school;
        $schoolBranchId = $currentSchool->id;
        $this->schoolLogoService->removeLogo($school, $schoolBranchId);
        return ApiResponseService::success("Logo Removed Successfully", null, null, 200);
    }
}
