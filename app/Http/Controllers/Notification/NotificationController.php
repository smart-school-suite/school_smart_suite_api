<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schooladmin;
use App\Notifications\testNotification;
use App\Services\ApiResponseService;
use App\Services\Notification\NotificationService;
use App\Http\Requests\Auth\RegisterDeviceRequest;
class NotificationController extends Controller
{
        protected NotificationService $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function registerDevice(RegisterDeviceRequest $request)
    {
            $currentSchool = $request->attributes->get('currentSchool');
            $authStudent = $this->resolveUser();
            $registerDevice = $this->notificationService->registerDevice($request->all(), $currentSchool, $authStudent);
            return ApiResponseService::success("Device Registered Successfully", $registerDevice, null, 201);
    }

    public function getNotifications(Request $request)
    {
        $user = $this->resolveUser();
        $getNotifications = $this->notificationService->getNotifications($user);
        return ApiResponseService::success("Notifications Fetched Successfully", $getNotifications, null, 200);
    }

    public function readAllNofications(Request $request)
    {
        $user = $this->resolveUser();
        $this->notificationService->markAllAsRead($user);
        return ApiResponseService::success("All Notifications Marked As Read Successfully", null, null, 200);
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

    public function testNotification($actorId)
    {
        $schoolAdmin = Schooladmin::findOrFail($actorId);
        $title = "test notification";
        $body = "This is the notification body";
        $type = "test";
        $data = "Test notification Data";
        $schoolAdmin->notify(new testNotification(
            $title,
            $body,
            $type,
            $data
        ));
    }
}
