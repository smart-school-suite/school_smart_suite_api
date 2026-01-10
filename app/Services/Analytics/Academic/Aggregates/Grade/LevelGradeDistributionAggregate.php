<?php

namespace App\Services\Analytics\Academic\Aggregates\Grade;

use App\Models\Educationlevels;
use App\Models\LetterGrade;
use Illuminate\Support\Collection;

class LevelGradeDistributionAggregate
{
    public static function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
        $grades = LetterGrade::all();

        return $levels->flatMap(function ($level) use ($grades, $query) {
            $levelData = $query->where("level_id", $level->id);

            return $grades->map(function ($grade) use ($levelData, $level) {
                $gradeTotal = $levelData->where("letter_grade_id", $grade->id)
                    ->sum("value");

                return [
                    "level_id" => $level->id,
                    "level_name" => $level->name ?? "unknown",
                    "level_number" => $level->level ?? "unknown",
                    "letter_grade_id" => $grade->id ?? "unknown",
                    "letter_grade" => $grade->letter_grade ?? "unknown",
                    "total_grade" => $gradeTotal ?? 0
                ];
            });
        })->values();
    }
}
