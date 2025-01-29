<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use App\Models\Examtype;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class utilityController extends Controller
{
    //
    public function findRelatedExamCa(string $examId)
    {
        // Step 1: Attempt to find the exam by ID
        $exam = Exams::with('examType')->find($examId);

        if (!$exam) {
            throw new ModelNotFoundException('Exam not found');
        }

        // Step 2: Get the corresponding Exam Type from the joined relation
        $examType = $exam->examType; // Eager loaded examType relationship

        if (!$examType || $examType->type !== 'exam') {
            throw new ModelNotFoundException('Exam type is not of type exam or not found');
        }

        // Step 3: Retrieve the semester directly from the exam type
        $semester = $examType->semester; // Assuming semester is a string (e.g., 'first', 'second')

        // Step 4: Attempt to find an exam type with the same semester of type 'ca'
        $caExamType = Examtype::where('semester', $semester)
                                ->where('type', 'ca')
                                ->first();

        if (!$caExamType) {
            throw new ModelNotFoundException('Corresponding CA exam type not found');
        }

        // Step 5: Find additional exams based on the gathered attributes
        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id) // Using the CA exam type ID
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->get(); // Get all matching exams

        // Return the found additional exams
        return $additionalExams;
    }
}
