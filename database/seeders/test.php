<?php

namespace Database\Seeders;

use App\Models\InstructorAvailability;
use App\Models\InstructorAvailabilitySlot;
use App\Models\RegistrationFee;
use App\Models\Schooladmin;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\NotificationJobs\SendAdminResitExamCreatedNotificationJob;
use App\Models\Exams;
use App\Models\AccessedStudent;
use App\Models\Examtype;
class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examDetails = Exams::with(['examtype'])->find("3d5d4337-06cb-4e8a-8b9e-90e7ffc87998");
        $resitExamDetails =  Examtype::where('type', 'resit')
            ->where('semester', $examDetails->examtype->semester)
            ->first();
        $examCandidates = AccessedStudent::where('exam_id', $examDetails->id)->with('student')->get();
       //SendExamResultsReleasedNotificationJob::dispatch($examCandidates, $examDetails);
       SendAdminResitExamCreatedNotificationJob::dispatch($examDetails->school_branch_id, $resitExamDetails, $examDetails);
    }
}
