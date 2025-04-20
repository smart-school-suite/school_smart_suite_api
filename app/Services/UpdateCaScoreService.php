<?php

namespace App\Services;
use App\Models\Courses;
use Exception;
use App\Models\Marks;
use Illuminate\Support\Facades\DB;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Student;
use App\Models\StudentResults;
class UpdateCaScoreService
{
    public function updateCaScore(array $updateData, $currentSchool)
    {
        // Initialize results and variables
        $results = [];
        $exam = null;
        $student = null;

        try {
            DB::beginTransaction();

            foreach ($updateData as $data) {
                $score = Marks::where("school_branch_id", $currentSchool->id)->findOrFail($data['mark_id']);

                if ($exam === null) {
                    $exam = Exams::findOrFail($score->exam_id);
                }
                if ($student === null) {
                    $student = Student::findOrFail($score->student_id);
                }

                $course = Courses::findOrFail($data['course_id']);

                $letterGrades = $this->determinLetterGrade($data['new_score'], $exam->id, $currentSchool->id);
                $updatedScore = $this->updateNewScore($letterGrades, $score, $course);

                $results[] = $updatedScore;
            }

            $this->getStudentScores($student, $exam, $currentSchool, $results);

            $totalScoreAndGpa = $this->recalculateGpa($results);

            $this->updateStudentResults($student, $currentSchool, $totalScoreAndGpa, $exam, $results);

            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function getStudentScores($student, $exam, $currentSchool, $results)
    {
        $marks = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("student_batch_id", $student->student_batch_id)
            ->where("exam_id", $exam->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("level_id", $student->level_id)
            ->with(['course'])
            ->get();
        foreach ($marks as $score) {
            foreach ($results as $result) {
                if ($score->course_id !== $result['course_id']) {
                    $results[] = [
                        'course_id' => $score->course_id,
                        'course_name' => $score->course->name,
                        'course_code' => $score->course->code,
                        'score' => $score->score,
                        'grade' => $score->grade,
                        'grade_status' => $score->grade_status,
                        'gratification' => $score->gratification,
                        'resit_status' => $score->resit_status,
                        'grade_points' => $score->grade_points,
                        'course_credit' => $score->course->credit,
                    ];
                }
            }
        }
    }
    private function determinLetterGrade($score, $examId, $schoolId): array
    {
        $exam = Exams::findOrFail($examId);
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('grades_category_id', $exam->grades_category_id)
            ->orderBy('minimum_score', 'desc')
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$schoolId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'gradeStatus' => $grade->grade_status,
                    'gradePoints' => $grade->grade_points,
                    'resitStatus' => $grade->resit_status,
                    'gratification' => $grade->determinant,
                    'score' => $score
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'fail',
            'gratification' => 'poor',
            'score' => $score,
            'resitStatus' => 'high_resit_potential',
            'gradePoints' => 0.0,
        ];
    }
    private function updateNewScore(array $updateScoreData, $score, $course)
    {
        $score->score = $updateScoreData['score'];
        $score->grade_status = $updateScoreData['gradeStatus'];
        $score->gratification = $updateScoreData['gratification'];
        $score->grade = $updateScoreData['letterGrade'];
        $score->grade_points = $updateScoreData['gradePoints'];
        $score->resit_status = $updateScoreData['resitStatus'];
        $score->save();

        return [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'score' => $updateScoreData['score'],
            'grade' => $updateScoreData['letterGrade'],
            'grade_status' => $updateScoreData['gradeStatus'],
            'gratification' => $updateScoreData['gratification'],
            'grade_points' => $updateScoreData['gradePoints'],
            'resit_status' => $updateScoreData['resitStatus'],
            'course_credit' => $course->credit
        ];
    }
    private function recalculateGpa(array $results)
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;

        foreach ($results as $mark) {
            $credits = $mark['course_credit'];
            $gradePoints = $mark['grade_points']; // Corrected key to match results
            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        $gpa = $totalCredits ? $totalWeightedPoints / $totalCredits : 0;

        return [
            'totalScore' => array_sum(array_column($results, 'score')),
            'gpa' => $gpa,
        ];
    }
    private function updateStudentResults($student, $currentSchool, $totalScoreAndGpa, $exam, $results)
    {
        $studentResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("level_id", $student->level_id)
            ->where("student_batch_id", $student->student_batch_id)
            ->firstOrFail();

        $studentResult->gpa = $totalScoreAndGpa['gpa'];
        $studentResult->total_score = $totalScoreAndGpa['totalScore'];
        $studentResult->score_details = json_encode($results);
        $studentResult->save();

        return $studentResult;
    }
}
