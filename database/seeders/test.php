<?php

namespace Database\Seeders;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolBranchSetting;
use Illuminate\Database\Seeder;
use App\Models\Educationlevels;
use App\Models\Specialty;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


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

        // Log::info('=== Starting run() ===');

        // $promotionRuns = PromotionRun::with([
        //     'schoolYear.specialty.student',
        //     'schoolYear.specialty.level',
        // ])->get();

        // Log::info('Promotion runs fetched', [
        //     'count' => $promotionRuns->count(),
        //     'run_ids' => $promotionRuns->pluck('id')->toArray(),
        // ]);

        // foreach ($promotionRuns as $promotionRun) {
        //     $this->processPromotionRun($promotionRun);
        // }

        // Log::info('=== Finished run() ===');
    }

    private function processPromotionRun(PromotionRun $promotionRun): void
    {
        $runId = $promotionRun->id;
        $schoolBranchId = $promotionRun->school_branch_id;

        Log::info('--- Processing promotion run ---', [
            'promotion_run_id' => $runId,
            'school_branch_id' => $schoolBranchId,
        ]);

        // Pull the specialty from the run's school year (adjust if the path differs)
        $specialty = $promotionRun->schoolYear?->specialty;

        Log::info('Specialty from promotion run', [
            'promotion_run_id' => $runId,
            'found' => $specialty ? true : false,
            'specialty_id' => $specialty?->id,
            'specialty_name' => $specialty?->specialty_name,
            'school_branch_id' => $specialty?->school_branch_id,
            'level_id' => $specialty?->level_id,
            'level' => $specialty?->level?->level,
        ]);

        if (! $specialty) {
            Log::warning('No specialty found on promotion run, skipping', [
                'promotion_run_id' => $runId,
            ]);
            return;
        }

        $currentLevel = $specialty->level?->level;

        $levelSettings = $this->levelSetting($specialty->school_branch_id);

        Log::info('Level settings resolved', [
            'promotion_run_id' => $runId,
            'levelSettings_found' => $levelSettings ? true : false,
            'levelSettings_id' => $levelSettings?->id,
            'setting_key' => $levelSettings?->settingDefination?->key,
            'setting_value' => $levelSettings?->value,
        ]);

        $levels = Educationlevels::whereHas(
            "levelType",
            fn($query) => $query->where("program_name", $levelSettings?->value)
        )->get();

        Log::info('Education levels fetched', [
            'promotion_run_id' => $runId,
            'count' => $levels->count(),
            'levels' => $levels->map(fn($l) => [
                'id' => $l->id,
                'level' => $l->level,
            ])->toArray(),
        ]);

        $nextSpecialty = $this->getNextSpecialty($specialty, $levels, $runId);

        Log::info('Next specialty resolved', [
            'promotion_run_id' => $runId,
            'current_level' => $currentLevel,
            'next_level' => $nextSpecialty?->level?->level,
            'found' => $nextSpecialty ? true : false,
            'next_specialty_id' => $nextSpecialty?->id,
            'next_specialty_name' => $nextSpecialty?->specialty_name,
            'next_level_id' => $nextSpecialty?->level_id,
        ]);

        Log::info('--- Finished promotion run ---', [
            'promotion_run_id' => $runId,
        ]);
    }

    private function getNextSpecialty(Specialty $specialty, Collection $levels, ?string $runId = null): ?Specialty
    {
        $currentLevel = (int) $specialty->level->level;

        Log::info('getNextSpecialty: starting', [
            'promotion_run_id' => $runId,
            'specialty_id' => $specialty->id,
            'current_level' => $currentLevel,
            'school_branch_id' => $specialty->school_branch_id,
        ]);

        $nextLevelNumber = $levels
            ->pluck('level')
            ->map(fn($level) => (int) $level)
            ->filter(fn($level) => $level > $currentLevel)
            ->sort()
            ->first();

        Log::info('getNextSpecialty: next level number computed', [
            'promotion_run_id' => $runId,
            'current_level' => $currentLevel,
            'nextLevelNumber' => $nextLevelNumber,
        ]);

        if ($nextLevelNumber === null) {
            Log::warning('getNextSpecialty: no next level found, returning null', [
                'promotion_run_id' => $runId,
                'current_level' => $currentLevel,
            ]);
            return null;
        }

        $nextEducationLevel = $levels->first(
            fn($level) => (int) $level->level === $nextLevelNumber
        );

        Log::info('getNextSpecialty: matched education level', [
            'promotion_run_id' => $runId,
            'nextEducationLevel_id' => $nextEducationLevel?->id,
            'nextEducationLevel_level' => $nextEducationLevel?->level,
        ]);

        if (! $nextEducationLevel) {
            Log::warning('getNextSpecialty: nextEducationLevel is null, returning null', [
                'promotion_run_id' => $runId,
            ]);
            return null;
        }

        $result = Specialty::where("school_branch_id", $specialty->school_branch_id)
            ->where("specialty_name", $specialty->specialty_name)
            ->where("level_id", $nextEducationLevel->id)
            ->first();

        Log::info('getNextSpecialty: query result', [
            'promotion_run_id' => $runId,
            'result_found' => $result ? true : false,
            'result_id' => $result?->id,
            'result_level_id' => $result?->level_id,
        ]);

        return $result;
    }

    private function levelSetting(string $schoolBranchId)
    {
        Log::info('levelSetting: querying', ['school_branch_id' => $schoolBranchId]);

        $result = SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->where("key", "level.start");
            })->first();

        Log::info('levelSetting: result', [
            'found' => $result ? true : false,
            'id' => $result?->id,
            'value' => $result?->value,
            'key' => $result?->settingDefination?->key,
        ]);

        return $result;
    }
}
