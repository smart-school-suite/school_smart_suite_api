<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UploadStudentAvatarRequest;
use App\Models\Student;
use App\Services\ApiResponseService;
use App\Services\Asset\StudentAvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAvatarController extends Controller
{
    protected StudentAvatarService $studentAvatarService;

    public function __construct(StudentAvatarService $studentAvatarService)
    {
        $this->studentAvatarService = $studentAvatarService;
    }

    public function uploadAvatar(
        UploadStudentAvatarRequest $request,
    ): JsonResponse {
        $currentSchool = $request->attributes->get('currentSchool');
        $student = $this->resolveUser();
        $picture = $request->safe()->only(['picture'])['picture'];
        $avatar = $this->studentAvatarService->uploadAvatar(
            $student,
            $picture,
            $currentSchool->id
        );
        return  ApiResponseService::success("Avatar Uploaded Successfully", $avatar, null, 200);
    }

    public function removeAvatar(
        Request $request,
    ): JsonResponse {
        $student = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');

        $this->studentAvatarService->removeAvatar(
            $student,
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
