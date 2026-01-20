<?php

namespace App\Console\Commands\ActivationCode;

use Illuminate\Console\Command;
use App\Jobs\ActivationCode\SendAdminActivationCodeRenewalReminderNotificationJob;

class SendAdminActivationCodeRenewalReminderNotificationCommand extends Command
{
    protected $signature = 'admin:activation-code-renewal-reminder-notification';
    protected $description = 'Send renewal reminder notifications for activation codes';

    public function handle()
    {
        $this->info('Dispatching admin activation code renewal reminder notification job...');

        SendAdminActivationCodeRenewalReminderNotificationJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
