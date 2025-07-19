<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Student;
use App\Notifications\NewSemesterAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
class SendNewSemesterAvialableNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected string $specialtyId;
    protected string $schoolBranchId;
    protected array $semesterData;
    public function __construct(string $specialtyId, string $schoolBranchId, array $semesterData)
    {
        $this->specialtyId = $specialtyId;
        $this->schoolBranchId = $schoolBranchId;
        $this->semesterData = $semesterData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $students = $this->getStudents($this->specialtyId, $this->schoolBranchId);
        Notification::send($students, new NewSemesterAvailable($this->semesterData));
    }

    protected function getStudents($specialtyId, $schoolBranchId){
        $students = Student::where("school_branch_id", $schoolBranchId)
                              ->where("specialty_id", $specialtyId)
                              ->get();
        return $students;
    }
}
