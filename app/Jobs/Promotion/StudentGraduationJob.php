<?php

namespace App\Jobs\Promotion;

use App\Models\AdditionalFeesCategory;
use App\Models\Graduation\GraduationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Promotion\PromotionRun;
use App\Models\Promotion\StudentPromotionRecord;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentGraduationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    public $timeout = 300;
    protected string $schoolBranchId;
    protected string $runId;
    /**
     * Create a new job instance.
     */
    public function __construct(string $runId, string $schoolBranchId)
    {
        $this->runId = $runId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $runId = $this->runId;

        $promotionRun = PromotionRun::where('school_branch_id', $schoolBranchId)
            ->where('id', $runId)
            ->with([
                'schoolYear.specialty',
                'schoolYear.systemAcademicYear',
            ])
            ->firstOrFail();

        $academicYear = $promotionRun->schoolYear->systemAcademicYear;
        $specialty = $promotionRun->schoolYear->specialty;

        $batchName = "Class of {$academicYear->year_start}";

        DB::transaction(function () use (
            $schoolBranchId,
            $promotionRun,
            $academicYear,
            $specialty,
            $batchName
        ) {
            $batch = GraduationBatch::firstOrCreate(
                [
                    'school_branch_id' => $schoolBranchId,
                    'school_year_id'   => $academicYear->id,
                    'name'             => $batchName,
                ],
                [
                    'status' => 'draft',
                ]
            );

            $feeSettingStatus = $this->graduationFeeStatus($schoolBranchId);
            $isGraduationFeeEnabled = $feeSettingStatus && (bool) $feeSettingStatus->value;

            $additionalFeeCategory = null;
            $feeAmount = 0;

            if ($isGraduationFeeEnabled) {
                $graduationFeeSetting = $this->graduationFee($schoolBranchId);
                $feeAmount = $graduationFeeSetting ? (float) $graduationFeeSetting->value : 0;

                $additionalFeeCategory = AdditionalFeesCategory::firstOrCreate(
                    [
                        'school_branch_id' => $schoolBranchId,
                        'title'            => 'Graduation Fees',
                    ],
                    [
                        'description' => "Mandatory graduation processing and convocation fee for {$batchName}",
                    ]
                );
            }

            $promotionRecords = StudentPromotionRecord::where('school_branch_id', $schoolBranchId)
                ->where('promotion_run_id', $promotionRun->id)
                ->whereIn('outcome', ['graduated', 'pending_review'])
                ->with('student')
                ->get();

            $processedRecords = [];
            $processedGraduationFees = [];

            foreach ($promotionRecords as $promotionRecord) {
                if (!$promotionRecord->student) {
                    continue;
                }

                $isPendingReview = $promotionRecord->outcome === 'pending_review';

                $processedRecords[] = [
                    'id'                 => Str::uuid()->toString(),
                    'promotion_run_id'   => $promotionRun->id,
                    'student_id'         => $promotionRecord->student->id,
                    'status'             => 'pending',
                    'eligibility_status' => $isPendingReview ? 'pending_review' : 'eligible',
                    'clearance_status'   => $isPendingReview ? 'blocked' : 'pending',
                    'specialty_id'       => $specialty->id,
                    'school_branch_id'   => $schoolBranchId,
                    'batch_id'           => $batch->id,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                if ($isGraduationFeeEnabled && $additionalFeeCategory) {
                    $processedGraduationFees[] = [
                        'id'                        => Str::uuid()->toString(),
                        'reason'                    => "Graduation Fee - {$batchName} ({$specialty->name})",
                        'amount'                    => $feeAmount,
                        'status'                    => 'unpaid',
                        'school_branch_id'          => $schoolBranchId,
                        'specialty_id'              => $specialty->id,
                        'student_id'                => $promotionRecord->student->id,
                        'additionalfee_category_id' => $additionalFeeCategory->id,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ];
                }
            }

            if (!empty($processedRecords)) {
                DB::table('graduation_records')->insert($processedRecords);
            }

            if (!empty($processedGraduationFees)) {
                DB::table('additional_fees')->insert($processedGraduationFees);
            }
        });
    }

    private function graduationFee(string $schoolBranchId)
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', function ($query) {
                $query->where('key', 'graduation.fee');
            })
            ->first();
    }

    private function graduationFeeStatus(string $schoolBranchId)
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', function ($query) {
                $query->where('key', 'graduation.fee_enabled');
            })
            ->first();
    }
}
