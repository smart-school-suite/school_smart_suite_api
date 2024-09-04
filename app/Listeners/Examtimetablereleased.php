<?php

namespace App\Listeners;

use App\Events\Examtimetablereleased as Examtimetableevent;
use App\Notifications\Examtimetablenotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Student;
use Illuminate\Queue\InteractsWithQueue;

class Examtimetablereleased
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
    public function handle(Examtimetableevent $event): void
    {
        //
        $students_to_be_mailed = Student::where('school_branch_id', $event->school_branch_id)
                                 ->where('specialty_id', $event->specialty_id)
                                 ->where('level_id', $event->level_id)
                                 ->get();
         foreach ($students_to_be_mailed as $students){
             $students->notify(new Examtimetablenotification());
         }
    }
}
