<?php

namespace App\Jobs\ActivationCode;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Teacher;
use App\Notifications\ActivationCode\Teacher\TeacherSubscriptionRenewalReminderNotification;

class SendTeacherSubscriptionRenewalReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 180;

    public function handle(): void
    {
        $today = Carbon::today();

        $reminderDays = [30, 14, 7, 1];

        foreach ($reminderDays as $daysLeft) {
            $targetExpiry = $today->copy()->addDays($daysLeft);

            $expiringUsages = DB::table('activation_code_usages')
                ->select([
                    'actorable_id',
                    'expires_at',
                    'school_branch_id',
                    'activation_code_id',
                ])
                ->whereDate('expires_at', $targetExpiry)
                ->where('actorable_type', 'App\\Models\\Teacher')
                ->whereNotNull('actorable_id')
                ->whereNotNull('activated_at')
                ->get();

            if ($expiringUsages->isEmpty()) {
                continue;
            }

            $usagesByTeacher = $expiringUsages->groupBy('actorable_id');

            foreach ($usagesByTeacher as $teacherId => $usages) {
                $teacher = Teacher::find($teacherId);

                if (!$teacher || !$teacher->hasVerifiedEmail() || !$teacher->is_active ?? false) {
                    continue;
                }

                $this->notifyTeacher(
                    $teacher,
                    $daysLeft,
                    $usages->first()->expires_at,
                    $usages->count()
                );
            }
        }
    }

    private function notifyTeacher(Teacher $teacher, int $daysLeft, $expiresAt, int $count): void
    {
        $teacher->notify(
            new TeacherSubscriptionRenewalReminderNotification(
                daysRemaining: $daysLeft,
                expiryDate: Carbon::parse($expiresAt)->toDateString()
            )
        );
    }
}
