<?php

namespace App\Console\Commands\ActivationCode;

use Illuminate\Console\Command;
use App\Jobs\ActivationCode\SendAdminActivationCodeExpireReminderNotificationJob;

class SendAdminActivationCodeExpireReminderNotificationCommand extends Command
{
    protected $signature = 'admin:activation-code-expire-reminder-notification';

    protected $description = 'Send reminders for activation codes that are about to expire';

    public function handle()
    {
        $this->info('Dispatching admin activation code expire reminder job...');

        SendAdminActivationCodeExpireReminderNotificationJob::dispatch();

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
