<?php

namespace App\Services;

use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\UserDevices;
use Exception;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    // Implement your logic here
    public function registerDevice($actorId, $deviceData)
    {
        $devicesable_type = null;
        try {
            DB::beginTransaction();
            if (Student::find($actorId)) {
                $devicesable_type = 'App\\Models\\Student';
            } elseif (Teacher::find($actorId)) {
                $devicesable_type = 'App\\Models\\Teacher';
            } elseif (Schooladmin::find($actorId)) {
                $devicesable_type = 'App\\Models\\SchoolAdmin';
            } else {
                throw new Exception("Actor not found.");
            }

            $userDevice = UserDevices::create([
                'devicesable_id' => $actorId,
                'devicesable_type' => $devicesable_type,
                'device_token' => $deviceData['device_token'],
                'platform' => $deviceData['platform'],
                'app_version' => $deviceData['app_version'],
            ]);

            DB::commit();
            return $userDevice;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getNotifications($user)
    {
        return [
            'unread' => $user->unreadNotifications,
            'read' => $user->readNotifications
        ];
    }

    public function markAsRead($user, $id)
    {
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return ['message' => 'Notification marked as read'];
    }

    public function markAllAsRead($user)
    {
        $user->unreadNotifications->markAsRead();
    }

    public function deleteNotification($user, $id){
        $user->notifications()->delete($id);
        return ['message' => 'Notification Deleted Successfully'];
    }

}
