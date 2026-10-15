<?php

namespace App\Console\Commands\Promotion;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\AcademicYear\SchoolAcademicYear;
use Illuminate\Console\Command;

class PromoteStudent extends Command
{
    protected $signature = 'students:promote';
    protected $description = 'Promote students to the next class/level';

    public function handle(): void
    {
        $academicYears = SchoolAcademicYear::with([
            'schoolBranch',
            'promotionRun' => function ($query) {
                $query->whereIn('status', ['pending', 'failed']);
            },
        ])
            ->whereHas('promotionRun', function ($query) {
                $query->whereIn('status', ['pending', 'failed']);
            })
            ->whereDate('end_date', '<', today())
            ->get();

        foreach ($academicYears as $academicYear) {
            foreach ($academicYear->promotionRun as $run) {
                StudentPromotionJob::dispatch(
                    $run->id,
                    $academicYear->schoolBranch->id
                );
            }
        }
    }
}
