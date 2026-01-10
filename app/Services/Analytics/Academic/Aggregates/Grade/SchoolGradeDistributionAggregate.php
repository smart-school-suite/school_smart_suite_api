<?php

namespace App\Services\Analytics\Academic\Aggregates\Grade;

use App\Models\LetterGrade;
use Illuminate\Support\Collection;

class SchoolGradeDistributionAggregate
{
    public static function calculate(Collection $query)
    {
        $letterGrades = LetterGrade::all();
        return  $letterGrades->map(function ($letterGrade) use ($query) {
            $totalGrade = $query->where("letter_grade_id", $letterGrade->id)->sum("value");
            return [
                "letter_grade_id" => $letterGrade->id,
                "letter_grade" => $letterGrade->letter_grade ?? "unknown",
                "total_grade" => $totalGrade
            ];
        });
    }
}
