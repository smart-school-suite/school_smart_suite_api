<?php

namespace App\Jobs\Promotion;

use App\Models\Promotion\PromotionRun;
use App\Models\SchoolBranchSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StudentPromotionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $timeout = 300;
    protected string $schoolBranchId;
    protected string $runId;
    public function __construct(string $runId, string $schoolBranchId)
    {
        $this->runId = $runId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $runId = $this->runId;
        $promotionRun = PromotionRun::where("school_branch_id", $schoolBranchId)
            ->where("id", $runId)
            ->with(['schoolYear.specialty.student', 'schoolYear.specialty.level'])
            ->first();

        $students = $promotionRun->schoolYear->specialty->student;


        $specialty = $promotionRun->schoolYear->specialty;
        $promotionSetting = $this->getPromotionSetting($schoolBranchId);


    }
    private function getPromotionSetting(string $schoolBranchId)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination.settingCategory'])
            ->whereHas('settingDefination.settingCategory', function ($query) {
                $query->where("key", "setting.category.promotion");
            })
            ->get();
    }
}
