<?php

namespace App\Services\Exam;

use App\Exceptions\AppException;
use App\Models\Exam\ExamScore;
use App\Models\Exam\ExamCandidate;
use App\Models\Studentresit;

class ExamScoreService
{
    public function getExamScoresCandidateId(string $candidateId, object $currentSchool)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'student',
                'exam',
                'examScores.course.types',
                'examScores.grade.grade'
            ])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "Candidate not found.",
                404,
                "Candidate Not Found",
                "The requested exam candidate record could not be found for this school branch.",
                "/candidates"
            );
        }

        $scores = $candidate->examScores;

        if ($scores->isEmpty()) {
            throw new AppException(
                "No marks found for this candidate.",
                404,
                "No Marks Found",
                "There are no marks available for the selected candidate. It might be that the candidate has not been evaluated yet, or the marks have been deleted by mistake.",
                "/candidates"
            );
        }

        $totalScore = 0;
        $totalGradePoints = 0;
        $coursesPassed = 0;
        $coursesFailed = 0;
        $totalCourses = $scores->count();

        $formattedScores = $scores->map(function ($scoreItem) use (&$totalScore, &$totalGradePoints, &$coursesPassed, &$coursesFailed) {
            $scoreValue = (float) $scoreItem->score;
            $totalScore += $scoreValue;

            $gradeScale = $scoreItem->grade;
            $gradePoints = $gradeScale ? (float) $gradeScale->grade_points : 0.0;
            $totalGradePoints += $gradePoints;

            $resultStatus = $gradeScale ? strtolower($gradeScale->result) : null;
            if ($resultStatus === 'pass' || $resultStatus === 'passed') {
                $coursesPassed++;
            } elseif ($resultStatus === 'fail' || $resultStatus === 'failed') {
                $coursesFailed++;
            }

            return [
                'id' => $scoreItem->id,
                'course' => $scoreItem->course,
                'score' => $scoreValue,
                'grade' => $gradeScale ? [
                    'id' => $gradeScale->id,
                    'letter_grade' => $gradeScale->grade ? $gradeScale->grade->letter_grade : null,
                    'grade_points' => $gradePoints,
                    'result' => $gradeScale->result,
                    'performance' => $gradeScale->performance,
                ] : null,
            ];
        });

        $gpa = $totalCourses > 0 ? round($totalGradePoints / $totalCourses, 2) : 0.0;

        return [
            "exam" => $candidate->exam,
            "candidate" => $candidate,
            "scores" => $formattedScores,
            "summary" => [
                "gpa" => $gpa,
                "courses_passed" => $coursesPassed,
                "courses_failed" => $coursesFailed,
                "total_grade_points_earned" => round($totalGradePoints, 2),
                "total_score" => round($totalScore, 2)
            ]
        ];
    }
    public function getCaExamScoresCandidateId(string $candidateId, object $currentSchool)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with([
                'student',
                'exam',
                'examScores.course',
                'examScores.grade.grade'
            ])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "Candidate not found.",
                404,
                "Candidate Not Found",
                "The requested exam candidate record could not be found for this school branch.",
                "/candidates"
            );
        }

        $scores = $candidate->examScores;

        if ($scores->isEmpty()) {
            throw new AppException(
                "No marks found for this candidate.",
                404,
                "No Marks Found",
                "There are no marks available for the selected candidate. It might be that the candidate has not been evaluated yet, or the marks have been deleted by mistake.",
                "/candidates"
            );
        }

        $totalScore = 0;
        $totalGradePoints = 0;
        $coursesPassed = 0;
        $coursesFailed = 0;
        $totalCourses = $scores->count();

        $formattedScores = $scores->map(function ($scoreItem) use (&$totalScore, &$totalGradePoints, &$coursesPassed, &$coursesFailed) {
            $scoreValue = (float) $scoreItem->score;
            $totalScore += $scoreValue;

            $gradeScale = $scoreItem->grade;
            $gradePoints = $gradeScale ? (float) $gradeScale->grade_points : 0.0;
            $totalGradePoints += $gradePoints;

            $resultStatus = $gradeScale ? strtolower($gradeScale->result) : null;
            if ($resultStatus === 'pass' || $resultStatus === 'passed') {
                $coursesPassed++;
            } elseif ($resultStatus === 'fail' || $resultStatus === 'failed') {
                $coursesFailed++;
            }

            return [
                'id' => $scoreItem->id,
                'course' => $scoreItem->course,
                'score' => $scoreValue,
                'grade' => $gradeScale ? [
                    'id' => $gradeScale->id,
                    'letter_grade' => $gradeScale->grade ? $gradeScale->grade->letter_grade : null,
                    'grade_points' => $gradePoints,
                    'result' => $gradeScale->result,
                    'performance' => $gradeScale->performance,
                ] : null,
            ];
        });

        $gpa = $totalCourses > 0 ? round($totalGradePoints / $totalCourses, 2) : 0.0;

        return [
            "exam" => $candidate->exam,
            "candidate" => $candidate,
            "scores" => $formattedScores,
            "summary" => [
                "gpa" => $gpa,
                "courses_passed" => $coursesPassed,
                "courses_failed" => $coursesFailed,
                "total_grade_points_earned" => round($totalGradePoints, 2),
                "total_score" => round($totalScore, 2)
            ]
        ];
    }
    public function deleteExamScoresCandidateId(string $candidateId, object $currentSchool)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with(['examScores', 'student', 'exam'])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "Candidate not found.",
                404,
                "Candidate Not Found",
                "The requested exam candidate record could not be found for this school branch.",
                "/candidates"
            );
        }

        if ($candidate->examScores->isEmpty()) {
            throw new AppException(
                "No marks found for this candidate.",
                404,
                "No Marks Found",
                "There are no marks available to delete for the selected candidate.",
                "/candidates"
            );
        }

        $deletedCount = ExamScore::where('candidate_id', $candidateId)
            ->where('school_branch_id', $currentSchool->id)
            ->delete();

        $resitDeleteCount = Studentresit::
                     where("school_branch_id", $currentSchool->id)
                     ->where("student_id", $candidate->student->id)
                     ->where("exam_id", $candidate->exam->id)
                     ->delete();

        return [
            "message" => "Successfully deleted all exam scores for the candidate.",
            "deleted_count" => $deletedCount,
            "resit_delete_count" =>  $resitDeleteCount
        ];
    }
    public function deleteCaExamScoresCandidateId(string $candidateId, object $currentSchool)
    {
        $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
            ->with('examScores')
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "Candidate not found.",
                404,
                "Candidate Not Found",
                "The requested exam candidate record could not be found for this school branch.",
                "/candidates"
            );
        }

        if ($candidate->examScores->isEmpty()) {
            throw new AppException(
                "No marks found for this candidate.",
                404,
                "No Marks Found",
                "There are no marks available to delete for the selected candidate.",
                "/candidates"
            );
        }

        $deletedCount = ExamScore::where('candidate_id', $candidateId)
            ->where('school_branch_id', $currentSchool->id)
            ->delete();

        return [
            "message" => "Successfully deleted all exam scores for the candidate.",
            "deleted_count" => $deletedCount
        ];
    }
}
