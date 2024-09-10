<?php

namespace App\Listeners;

use App\Events\TransferrequestEvent;
use App\Models\Schooladmin;
use App\Notifications\TransferrequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TransferrequestListener
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
    public function handle(TransferrequestEvent $event): void
    {
        //
        $schooladmin = Schooladmin::Where('school_branch_id', $event->school_branch_id)->get();

        foreach ($schooladmin as $admin){
            $admin->notify(new TransferrequestNotification());
        }
    }
}
