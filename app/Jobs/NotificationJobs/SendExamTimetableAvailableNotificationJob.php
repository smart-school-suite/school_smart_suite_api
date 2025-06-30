<?php

namespace App\Jobs\NotificationJobs;

use App\Models\Student;
use App\Notifications\ExamTimetableAvialable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendExamTimetableAvailableNotificationJob implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected string $specialtyId;
    protected string $schoolBranchId;
    protected array $examData;
    public function __construct(string $specialtyId, string $schoolBranchId, array $examData)
    {
        $this->specialtyId = $specialtyId;
        $this->schoolBranchId = $schoolBranchId;
        $this->examData = $examData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       $students = $this->getStudents($this->specialtyId, $this->schoolBranchId);
       Notification::send($students, new ExamTimetableAvialable($this->examData));
    }

    public function getStudents($specialtyId, $schoolBranchId){
        $students = Student::where("school_branch_id", $schoolBranchId)
                             ->where("specialty_id", $specialtyId)
                             ->get();
        return $students;
    }
}
