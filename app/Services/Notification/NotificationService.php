<?php

namespace App\Services\Notification;

use App\Models\UserDevices;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function registerDevice($data, $currentSchool, $student)
    {
        try {
            DB::beginTransaction();
            $deviceExist = UserDevices::where("devicesable_id", $student->id)
                ->exists();
            if ($deviceExist) {
                $deviceExist->delete();
            }
            $userDevice = UserDevices::create([
                'devicesable_id' => $student->id,
                'devicesable_type' => get_class($student),
                'device_token' => $data['device_token'],
                'platform' => $data['platform'],
                'school_branch_id' => $currentSchool->id
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
        Log::info('Getting notifications for user: ' . get_class($user) . ' with ID: ' . $user->id);
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

    public function deleteNotification($user, $id)
    {
        $user->notifications()->delete($id);
        return ['message' => 'Notification Deleted Successfully'];
    }
}
