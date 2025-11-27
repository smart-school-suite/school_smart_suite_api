<?php

namespace App\Jobs\NotificationJobs;

use App\Models\AdditionalFees;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use App\Notifications\AdminAdditionalFeeReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class SendAdminAdditionalFeeReminderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    protected array $additionalFeeIds;
    protected string $schoolBranchId;

    public function __construct(array $additionalFeeIds, string $schoolBranchId)
    {
        $this->additionalFeeIds = $additionalFeeIds;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if (empty($this->additionalFeeIds)) {
            return;
        }

        $additionalFees = AdditionalFees::where('school_branch_id', $this->schoolBranchId)
            ->whereIn('id', $this->additionalFeeIds)
            ->where('status', 'unpaid')
            ->with(['specialty.level', 'feeCategory'])
            ->get();

        if ($additionalFees->isEmpty()) {
            return;
        }

        $specialties = $additionalFees->map(function ($fee) {
            return [
                'specialty_name' => "{$fee->specialty?->specialty_name} {$fee->specialty?->level?->level_name}",
            ];
        })->unique('specialty_name')->values()->toArray();

        $totalUnpaidAmount = $additionalFees->sum('amount');
        $feeName = $additionalFees->first()->feeCategory?->name ?? 'Additional Fee';
        $description = $additionalFees->first()->feeCategory?->description;
        $reason = $additionalFees->first()->description;
        $dueDate = $additionalFees->first()->due_date;

        $messageContent = [
            'unpaid_amount'   => $totalUnpaidAmount,
            'paid_amount'     => 0,
            'paid_number'     => 0,
            'unpaid_number'   => $additionalFees->count(),
            'fee_name'        => $feeName,
            'description'     => $description,
            'reason'         => $reason,
            'due_date'       => $dueDate?->format('Y-m-d'),
            'specialties'     => $specialties,
        ];

        $managers = $this->getAdditionalFeeManagers($this->schoolBranchId);

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new AdminAdditionalFeeReminder($messageContent));
        }
    }

    /**
     * Get school admins with "Additional Fee Manager" permissions
     */
    private function getAdditionalFeeManagers(string $schoolBranchId): Collection
    {
        $permissionNames = PermissionCategory::with('permission')
            ->where('title', 'Additional Fee Manager')
            ->first()
            ?->permission
            ->pluck('name')
            ->toArray();

        if (empty($permissionNames)) {
            return collect();
        }

        return Schooladmin::where('school_branch_id', $schoolBranchId)
            ->whereHas('permissions', function ($query) use ($permissionNames) {
                $query->whereIn('name', $permissionNames);
            })
            ->get();
    }
}
