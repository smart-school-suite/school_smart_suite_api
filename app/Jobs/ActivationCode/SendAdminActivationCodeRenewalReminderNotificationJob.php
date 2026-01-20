<?php

namespace App\Jobs\ActivationCode;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SchoolAdmin;
use App\Notifications\ActivationCode\Admin\AdminActivationCodeRenewalReminderNotification;

class SendAdminActivationCodeRenewalReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function handle(): void
    {
        $today = Carbon::today();

        $reminderDays = [30, 14, 7, 1];

        foreach ($reminderDays as $daysLeft) {
            $targetExpiry = $today->copy()->addDays($daysLeft);

            $expiringByBranch = DB::table('activation_code_usages')
                ->select([
                    'school_branch_id',
                    DB::raw("SUM(CASE WHEN actorable_type = 'App\\\\Models\\\\Student' THEN 1 ELSE 0 END) as student_count"),
                    DB::raw("SUM(CASE WHEN actorable_type = 'App\\\\Models\\\\Teacher' THEN 1 ELSE 0 END) as teacher_count"),
                    DB::raw("COUNT(*) as total_count")
                ])
                ->whereDate('expires_at', $targetExpiry)
                ->whereNotNull('activated_at')
                ->whereNotNull('actorable_id')
                ->whereNotNull('actorable_type')
                ->groupBy('school_branch_id')
                ->havingRaw('student_count > 0 OR teacher_count > 0')
                ->get();

            if ($expiringByBranch->isEmpty()) {
                continue;
            }

            $branchIds = $expiringByBranch->pluck('school_branch_id')->unique()->all();

            $adminsByBranch = SchoolAdmin::query()
                ->whereIn('school_branch_id', $branchIds)
                ->get()
                ->groupBy('school_branch_id');

            foreach ($expiringByBranch as $stats) {
                $branchId = $stats->school_branch_id;

                $accounts = [];

                if ($stats->student_count > 0) {
                    $accounts[] = [
                        'type'     => 'student',
                        'quantity' => (int) $stats->student_count,
                    ];
                }

                if ($stats->teacher_count > 0) {
                    $accounts[] = [
                        'type'     => 'teacher',
                        'quantity' => (int) $stats->teacher_count,
                    ];
                }

                if (empty($accounts)) {
                    continue;
                }

                $branchAdmins = $adminsByBranch->get($branchId, collect());

                foreach ($branchAdmins as $admin) {
                    $admin->notify(
                        new AdminActivationCodeRenewalReminderNotification(
                            daysRemaining: $daysLeft,
                            accounts: $accounts
                        )
                    );
                }
            }
        }
    }
}
