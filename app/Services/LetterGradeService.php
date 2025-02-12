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

    public function deleteLetterGrade($letter_grade_id)
    {
        $letterGrade = LetterGrade::findOrFail($letter_grade_id);
        $letterGrade->delete();
        return $letterGrade;
    }

    public function updateLetterGrade($id, array $data)
    {
        $letterGrade = LetterGrade::findOrFail($id);
        $letterGrade->update($data);

        return $letterGrade;
    }
}
