<?php

namespace App\Console\Commands\ActivationCode;

use Illuminate\Console\Command;
use App\Jobs\ActivationCode\SendStudentSubscriptionRenewalReminderNotificationJob;
class SendStudentSubscriptionRenewalReminderNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:subscription-renewal-reminder-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder notifications for student subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching student subscription renewal reminder notification job...');

        SendStudentSubscriptionRenewalReminderNotificationJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
