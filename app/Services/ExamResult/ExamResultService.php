<?php

namespace App\Services\ExamResult;

use App\Exceptions\AppException;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Exams;
use App\Models\Student;
use App\Models\StudentResults;
use Exception;

class ExamResultService
{
    public function getMyResults($currentSchool, $examId, $studentId)
    {
        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->where("student_id", $studentId)
            ->with(['student', 'specialty', 'level', 'exam.examtype'])
            ->get();
        return $examResults;
    }

    public function getResultDetails($currentSchool, $resultId)
    {
        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->with(['student', 'specialty', 'level', 'exam.examtype'])
            ->where('id', $resultId)
            ->first();
        return $examResults;
    }

    public function getExamStandings($examId, $currentSchool)
    {
        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->orderBy('gpa', 'desc')
            ->with(['student', 'specialty', 'level'])
            ->get();
        return $examResults;
    }

    public function generateExamStandingsResultPdf($examId, $currentSchool)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'specialty'])
            ->findorFail($examId);

        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->orderBy('gpa', 'desc')
            ->with(['student', 'specialty', 'level', 'exam.examtype'])
            ->get();
        $pdf = Pdf::loadView('pdf.exam_standings', [
            'examResults' => $examResults,
            "exam" => $exam,
            'currentSchool' => $currentSchool
        ]);
        return response()->stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="document.pdf"',
            ]
        );
    }

    public function generateStudentResultsPdf($examId, $studentId, $currentSchool)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)
            ->with(['examtype', 'specialty'])
            ->findOrFail($examId);
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->findOrFail($studentId);
        $studentResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam->id)
            ->where("student_id", $student->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("student_batch_id", $student->student_batch_id)
            ->first();
        $pdf = Pdf::loadView('pdf.StudentResults', [
            $student,
            $exam,
            $studentResults
        ]);
        return response()->stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="document.pdf"',
            ]
        );
    }

    public function getAllStudentResults($currentSchool)
    {
        try {
            $results = StudentResults::where("school_branch_id", $currentSchool->id)
                ->with(['specialty', 'exam.examtype', 'level', 'student'])
                ->get();

            if ($results->isEmpty()) {
                throw new AppException(
                    "No student results were found for this school branch.",
                    404,
                    "No Results Found",
                    "The system could not find any student results. This may be because results have not been uploaded yet.",
                    null
                );
            }

            return $results;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving student results.",
                500,
                "Results Retrieval Error",
                "A server-side issue prevented the results from being retrieved. Please try again later.",
                null
            );
        }
    }
}
