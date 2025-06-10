<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Schooladmin;
use Carbon\Carbon;
use App\Jobs\EmailNotificationJobs\EmailBirthDayNotificationJob;
class SendBirthdayWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends birthday wishes to clients whose birthday is today.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now();
        $this->info("Checking for birthdays on: " . $today->toDateString());

        // Process Students
        $students = Student::whereMonth('date_of_birth', $today->month)
                           ->whereDay('date_of_birth', $today->day)
                           ->get();
        foreach ($students as $student) {
            $this->info("Found student birthday: " . $student->name);
            EmailBirthDayNotificationJob::dispatch($student->email, $student->name, 'Student');
        }

        // Process Teachers
        $teachers = Teacher::whereMonth('date_of_birth', $today->month)
                           ->whereDay('date_of_birth', $today->day)
                           ->get();
        foreach ($teachers as $teacher) {
            $this->info("Found teacher birthday: " . $teacher->name);
            EmailBirthDayNotificationJob::dispatch($teacher->email, $teacher->name, 'Teacher');
        }

        // Process School Admins
        $schoolAdmins = SchoolAdmin::whereMonth('date_of_birth', $today->month)
                                   ->whereDay('date_of_birth', $today->day)
                                   ->get();
        foreach ($schoolAdmins as $admin) {
            $this->info("Found school admin birthday: " . $admin->name);
            EmailBirthDayNotificationJob::dispatch($admin->email, $admin->name, 'School Admin');
        }

        $this->info("Birthday wish dispatch process completed.");
    }
}
