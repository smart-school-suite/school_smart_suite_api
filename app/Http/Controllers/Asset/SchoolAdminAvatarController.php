<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\UploadSchoolAdminAvatarRequest;
use App\Services\ApiResponseService;
use App\Services\Asset\SchoolAdminAvatarService;
use Illuminate\Http\Request;

class SchoolAdminAvatarController extends Controller
{
    protected SchoolAdminAvatarService $schoolAdminAvatarService;
    public function __construct(SchoolAdminAvatarService $schoolAdminAvatarService)
    {
        $this->schoolAdminAvatarService = $schoolAdminAvatarService;
    }

    public function uploadAvatar(UploadSchoolAdminAvatarRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdmin = $this->resolveUser();
        $picture = $request->safe()->only(['picture'])['picture'];
        $avatar = $this->schoolAdminAvatarService->uploadAvatar(
            $schoolAdmin,
            $picture,
            $currentSchool->id
        );
        return  ApiResponseService::success("Avatar Uploaded Successfully", $avatar, null, 200);
    }

    public function removeAvatar(Request $request)
    {
        $schoolAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');

        $this->schoolAdminAvatarService->removeAvatar(
            $schoolAdmin,
            $currentSchool->id
        );

        return ApiResponseService::success("Avatar Removed Successfully", null, null, 200);
    }
    protected function resolveUser(): ?object
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
