<?php

namespace App\Services\Analytics\Academic\Aggregates\Grade;

use App\Models\Examtype;
use App\Models\LetterGrade;
use Illuminate\Support\Collection;

class ExamTypeGradeDistributionAggregate
{
    public static function calculate(Collection $query)
    {
        $examTypes = Examtype::all();
        $grades = LetterGrade::all();

        return $examTypes->flatMap(function ($examType) use ($grades, $query) {
            $examTypeData = $query->where("exam_type_id", $examType->id);

            return $grades->map(function ($grade) use ($examTypeData, $examType) {
                $gradeTotal = $examTypeData->where("letter_grade_id", $grade->id)
                    ->sum("value");
                return [
                    "exam_type_id" => $examType->id,
                    "exam_type_name" => $examType->exam_name ?? "unknown",
                    "semester" => $examType->semester ?? "unknown",
                    "letter_grade_id" => $grade->id ?? "unknown",
                    "letter_grade" => $grade->letter_grade ?? "unknown",
                    "total_grade" => $gradeTotal ?? 0
                ];
            });
        })->values();
    }
}
