<?php

namespace App\Console\Commands;

use App\Models\SchoolSemester;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdatePastSchoolSemesters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-past-school-semesters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of past semesters';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $now = Carbon::now();
        SchoolSemester::where("end_date", '<', $now)
                       ->where('status', 'active')
                       ->update(['status' => 'inactive']);
        $this->info('Expired Records Updated Successfully');
    }
}
