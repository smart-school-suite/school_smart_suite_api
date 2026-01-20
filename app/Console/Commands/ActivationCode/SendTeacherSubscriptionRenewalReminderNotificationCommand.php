<?php

namespace App\Console\Commands\ActivationCode;

use Illuminate\Console\Command;
use App\Jobs\ActivationCode\SendTeacherSubscriptionRenewalReminderNotificationJob;

class SendTeacherSubscriptionRenewalReminderNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teacher:subscription-renewal-reminder-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder notifications for teacher subscriptions';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching teacher subscription renewal reminder notification job...');

        SendTeacherSubscriptionRenewalReminderNotificationJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
