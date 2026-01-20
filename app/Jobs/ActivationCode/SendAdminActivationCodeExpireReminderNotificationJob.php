<?php

namespace App\Jobs\ActivationCode;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Schooladmin;
use App\Notifications\ActivationCode\Admin\AdminActivationCodeExpireReminderNotification;

class SendAdminActivationCodeExpireReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function handle(): void
    {
        $today = Carbon::today();

        $reminderDays = [30, 14, 7, 1];

        foreach ($reminderDays as $daysLeft) {
            $expireDate = $today->copy()->addDays($daysLeft);

            $activationCodesByBranch = DB::table('activation_codes')
                ->select([
                    'school_branch_id',
                    DB::raw("SUM(CASE WHEN type = 'student' THEN 1 ELSE 0 END) as student_count"),
                    DB::raw("SUM(CASE WHEN type = 'teacher' THEN 1 ELSE 0 END) as teacher_count"),
                    DB::raw("COUNT(*) as total_count")
                ])
                ->whereDate('expires_at', $expireDate)
                ->where('is_used', false)
                ->groupBy('school_branch_id')
                ->get();

            if ($activationCodesByBranch->isEmpty()) {
                continue;
            }

            $branchIds = $activationCodesByBranch->pluck('school_branch_id')->unique()->all();

            $admins = Schooladmin::query()
                ->whereIn('school_branch_id', $branchIds)
                ->get()
                ->groupBy('school_branch_id');

            foreach ($activationCodesByBranch as $branchStats) {
                $branchId = $branchStats->school_branch_id;

                $quantity = [];

                if ($branchStats->student_count > 0) {
                    $quantity[] = [
                        'type' => 'student',
                        'quantity' => (int) $branchStats->student_count
                    ];
                }

                if ($branchStats->teacher_count > 0) {
                    $quantity[] = [
                        'type' => 'teacher',
                        'quantity' => (int) $branchStats->teacher_count
                    ];
                }

                if (empty($quantity)) {
                    continue;
                }

                $branchAdmins = $admins->get($branchId, collect());

                foreach ($branchAdmins as $admin) {
                    $admin->notify(
                        new AdminActivationCodeExpireReminderNotification(
                            quantity: $quantity,
                            daysRemaining: $daysLeft
                        )
                    );
                }
            }
        }
    }
}
