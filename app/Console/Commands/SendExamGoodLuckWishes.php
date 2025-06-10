<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Jobs\EmailNotificationJobs\EmailExamGoodLuckNotificationJob;
use App\Models\Exams;
class SendExamGoodLuckWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:good-luck-wish-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends good luck notifications for exams starting within the next 3 hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $threeHoursFromNow = $now->copy()->addHours(3);

        $this->info("Checking for exams between " . $now->format('Y-m-d H:i') . " and " . $threeHoursFromNow->format('Y-m-d H:i'));

        $upcomingExams = Exams::whereDate('start_date', '>=', $now->toDateString())
                             ->whereDate('start_date', '<=', $threeHoursFromNow->toDateString())
                             ->with([ 'examtype']) // Checks up to 3 hours into next day if current time is late
                             ->get();

        foreach ($upcomingExams as $exam) {
            $examStartDateTime = Carbon::parse($exam->start_date . ' 09:30:00');

            if ($examStartDateTime->isBetween($now, $threeHoursFromNow, false)) {
                $this->info("Found upcoming exam: " . $exam->title . " at " . $examStartDateTime->format('Y-m-d H:i'));

                if (method_exists($exam, 'student')) {
                    foreach ($exam->student as $student) {
                        $this->info("- Dispatching good luck for student: $student->name");
                        EmailExamGoodLuckNotificationJob::dispatch($student->email, $student->name, $exam->examtype->exam_name, $examStartDateTime);
                    }
                } else {
                     $this->warn(" Exam model does not have a 'students' relationship defined.");
                }
            }
        }

        $this->info("Exam good luck notification dispatch process completed.");
    }
}
