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
use Illuminate\Support\Collection;

class UpdateCaScoreService
{
    /**
     * Updates continuous assessment (CA) scores for a batch of students.
     *
     * @param array $updateData An array of student CA score update data. Each element is expected to be an array
     * containing keys like 'mark_id', 'course_id', and 'new_score'. All IDs within these arrays are strings.
     * @param object $currentSchool The current school object.
     * @return array An array of the updated marks data.
     * @throws Exception If any error occurs during the process, such as mark not found, exam/student not found,
     * or database transaction failure.
     */
    public function updateCaScore(array $updateData, $currentSchool): array
    {
        $results = [];
        $exam = null;
        $student = null;

        DB::beginTransaction();
        try {
            foreach ($updateData as $data) {
                // Retrieve the specific marks record to update.
                $score = Marks::where("school_branch_id", $currentSchool->id)->findOrFail($data['mark_id']);

                // Fetch exam and student if not already loaded in the loop (for efficiency).
                if (!$exam) {
                    $exam = Exams::findOrFail($score->exam_id);
                }
                if (!$student) {
                    $student = Student::findOrFail($score->student_id);
                }

                // Retrieve the course for updating the returned result.
                $course = Courses::findOrFail($data['course_id']);

                // Determine the letter grade and other grade details based on the new score.
                $letterGrades = $this->determineLetterGrade($data['new_score'], $exam->id, $currentSchool->id);

                // Update the marks record with the new score and grade details.
                $updatedScore = $this->updateMarkRecord($letterGrades, $score, $course);

                $results[] = $updatedScore;
            }

            // Retrieve all relevant scores for the student in this exam.
            $allStudentScores = $this->getAllStudentScoresForExam($student, $exam, $currentSchool);

            // Recalculate GPA based on all the student's scores for this exam.
            $totalScoreAndGpa = $this->recalculateGpa($allStudentScores);

            // Determine the overall exam result status based on all the student's scores.
            $examStatus = $this->determineExamResultStatus($allStudentScores);

            // Update the student results record.
            $this->updateStudentResultsRecord(
                $student,
                $currentSchool,
                $totalScoreAndGpa,
                $exam,
                $allStudentScores,
                $examStatus
            );

            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Retrieves all marks for a given student and exam within a specific school.
     *
     * @param Student $student The student object.
     * @param Exams $exam The exam object.
     * @param object $currentSchool The current school object.
     * @return Collection A collection of Marks model instances.
     */
    private function getAllStudentScoresForExam(Student $student, Exams $exam, $currentSchool): Collection
    {
        return Marks::with('course')
            ->where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student->id)
            ->where('student_batch_id', $student->student_batch_id)
            ->where('exam_id', $exam->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->get();
    }

    /**
     * Determines the letter grade and other grade-related information for a given score.
     *
     * @param float $score The student's score.
     * @param string $examId The ID of the exam.
     * @param string $schoolId The ID of the school.
     * @return array An array containing the letter grade, grade status, grade points, resit status, gratification, and score.
     * @throws Exception If no grades are found for the given school and exam.
     */
    private function determineLetterGrade(float $score, string $examId, string $schoolId): array
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
                    'score' => $score,
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

    /**
     * Updates the score and grade-related fields of a Marks record.
     *
     * @param array $updateScoreData An array containing the updated grade information.
     * @param object $score The Marks model instance to update.
     * @param object $course The Course model instance.
     * @return array An array containing the updated course and grade information.
     */
    private function updateMarkRecord(array $updateScoreData, object $score, object $course): array
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
            'course_credit' => $course->credit,
        ];
    }

    /**
     * Recalculates the Grade Point Average (GPA) from a collection of results.
     *
     * @param Collection $results A collection of course results with 'course_credit' and 'grade_points'.
     * @return array An array containing the 'totalScore' and 'gpa'.
     */
    private function recalculateGpa(Collection $results): array
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $overallTotalScore = 0;

        foreach ($results as $mark) {
            $credits = $mark['course_credit'];
            $gradePoints = $mark['grade_points'];
            $overallTotalScore += $mark['score'];
            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;

        return [
            'totalScore' => round($overallTotalScore, 2),
            'gpa' => $gpa,
        ];
    }

    /**
     * Updates the student results record in the database.
     *
     * @param Student $student The student object.
     * @param object $currentSchool The current school object.
     * @param array $totalScoreAndGpa An array containing the 'totalScore' and 'gpa'.
     * @param Exams $exam The exam object.
     * @param Collection $results A collection of individual course results.
     * @param array $examStatus An array containing the overall 'exam_status' and boolean flags.
     * @return StudentResults The updated StudentResults model instance.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the StudentResults record is not found.
     */
    private function updateStudentResultsRecord(Student $student, $currentSchool, array $totalScoreAndGpa, Exams $exam, Collection $results, array $examStatus): StudentResults
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
        $studentResult->exam_status = $examStatus['passed'] ? 'Passed' : 'Failed';
        $studentResult->score_details = json_encode($results->toArray());
        $studentResult->save();

        return $studentResult;
    }

    /**
     * Determines the overall exam result status based on a collection of course results.
     *
     * @param Collection $results A collection of course results with 'grade_status'.
     * @return array An array containing the 'exam_status' and boolean flags for pass/fail.
     */
    private function determineExamResultStatus(Collection $results): array
    {
        $failedCourses = $results->filter(fn($result) => ($result['grade_status'] ?? '') === 'fail');

        if ($failedCourses->isEmpty()) {
            return [
                'exam_status' => 'Passed',
                'passed' => true,
                'failed' => false,
            ];
        }

        return [
            'exam_status' => 'Failed',
            'passed' => false,
            'failed' => true,
        ];
    }
}
