<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Specialty;
use App\Notifications\FeeScheduleAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendFeeScheduleNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $schoolBranchId;
    protected $specialtyId;
    protected $scheduleData;
    public function __construct($schoolBranchId, $specialtyId, $scheduleData)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
        $this->scheduleData = $scheduleData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $students = $this->getStudents($this->specialtyId, $this->schoolBranchId);
        Notification::send($students, new FeeScheduleAvailable(
            $this->scheduleData['schoolYear'],
            $this->scheduleData['semester']
        ));
    }

    public function getStudents($specialtyId, $schoolBranchId){
        $specialty = Specialty::where("school_branch_id", $schoolBranchId)
                               ->with(['student'])
                               ->find($specialtyId);
        return $specialty->student;
    }
}
