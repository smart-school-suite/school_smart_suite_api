<?php

namespace App\Services;
use App\Models\LetterGrade;
class LetterGradeService
{
    // Implement your logic here
    public function createLetterGrade(array $data)
    {
        return LetterGrade::create($data);
    }

    public function getAllLetterGrades()
    {
        return LetterGrade::all();
    }

    public function deleteLetterGrade($letterGradeId)
    {
        $letterGrade = LetterGrade::findOrFail($letterGradeId);
        $letterGrade->delete();
        return $letterGrade;
    }

    public function updateLetterGrade($letterGradeId, array $data)
    {
        $letterGrade = LetterGrade::findOrFail($letterGradeId);
        $letterGrade->update($data);

        return $letterGrade;
    }
}
