<?php

namespace App\Jobs\ActivationCode;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Student;
use App\Notifications\ActivationCode\Student\StudentSubscriptionRenewalReminderNotification;

class SendStudentSubscriptionRenewalReminderNotificationJob implements ShouldQueue
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
                ->where('actorable_type', 'App\\Models\\Student')
                ->whereNotNull('actorable_id')
                ->whereNotNull('activated_at')
                ->get();

            if ($expiringUsages->isEmpty()) {
                continue;
            }

            $usagesByStudent = $expiringUsages->groupBy('actorable_id');

            foreach ($usagesByStudent as $studentId => $usages) {
                $student = Student::find($studentId);

                if (!$student || !$student->hasVerifiedEmail() || !$student->is_active) {
                    continue;
                }
                $this->notifyStudent(
                    $student,
                    $daysLeft,
                    $usages->first()->expires_at,
                    $usages->count()
                );
            }
        }
    }

    private function notifyStudent(Student $student, int $daysLeft, $expiresAt, int $count): void
    {
        $student->notify(
            new StudentSubscriptionRenewalReminderNotification(
                daysRemaining: $daysLeft,
                expiryDate: Carbon::parse($expiresAt)->toDateString()
            )
        );
    }
}
