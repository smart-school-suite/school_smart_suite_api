<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UploadTeacherAvatarRequest;
use App\Services\ApiResponseService;
use App\Services\Asset\TeacherAvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAvatarController extends Controller
{
    protected TeacherAvatarService $teacherAvatarService;
    public function __construct(TeacherAvatarService $teacherAvatarService)
    {
        $this->teacherAvatarService = $teacherAvatarService;
    }

    public function uploadAvatar(UploadTeacherAvatarRequest $request): JsonResponse
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher = $this->resolveUser();
        $picture = $request->safe()->only(['picture'])['picture'];
        $avatar = $this->teacherAvatarService->uploadAvatar(
            $teacher,
            $picture,
            $currentSchool->id
        );
        return  ApiResponseService::success("Avatar Uploaded Successfully", $avatar, null, 200);
    }

    public function removeAvatar(Request $request)
    {
        $teacher = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');

        $this->teacherAvatarService->removeAvatar(
            $teacher,
            $currentSchool->id
        );
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
