<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Student;
use App\Notifications\TimetableAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendTimetableAvailableNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected string $schoolBranchId;
    protected string $specialtyId;
    protected array $timetableData;
    public function __construct(string $schoolBranchId, string $specialtyId, array $timetableData)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
        $this->timetableData = $timetableData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $students = $this->getStudents($this->schoolBranchId, $this->specialtyId);
        Notification::send($students, new TimetableAvailable($this->timetableData));
    }

    protected function getStudents($schoolBranchId, $specialtyId){
        $students = Student::where("school_branch_id", $schoolBranchId)
                            ->where("specialty_id", $specialtyId)
                            ->get();
        return $students;
    }
}
