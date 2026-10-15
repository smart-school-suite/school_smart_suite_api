<?php

namespace Database\Seeders;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use Illuminate\Database\Seeder;

class test extends Seeder
{

    public function run(): void
    {
        $runs =  PromotionRun::with(['schoolYear.specialty.student'])->get();
        foreach ($runs as $run) {
            StudentPromotionJob::dispatch(
                $run->id,
                $run->school_branch_id
            );
        }
    }
}
