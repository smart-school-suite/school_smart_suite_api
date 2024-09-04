<?php

namespace App\Listeners;

use App\Events\Examresultsreleased;
use App\Notifications\Examresultsreleased as NotificationsExamresultsreleased;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExamReleasedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Examresultsreleased $event): void
    {
        //
        $student = $event->currentSchool->student()->find($event->student_id);
        if($student){
          $student->notify( new NotificationsExamresultsreleased($event->exam_id, $student));
        }
        
    }
}
