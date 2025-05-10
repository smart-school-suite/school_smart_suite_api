<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Exams;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\StudentResults;
use App\Models\Courses;
use App\Models\Examtype;
use Illuminate\Support\Collection;
class UpdateExamScoreService
{
    /**
     * Updates the main exam scores for a batch of students, considering CA scores.
     *
     * @param array $updateData An array of student exam score update data. Each element should contain
     * 'mark_id' and 'new_score'.
     * @param object $currentSchool The current school object.
     * @return array An array of the updated marks data.
     * @throws Exception If any error occurs during the update process.
     */
    public function updateExamScore(array $updateData, $currentSchool): array
    {
        $results = [];
        $exam = null;
        $student = null;

        DB::beginTransaction();
        try {
            foreach ($updateData as $data) {
                // Retrieve the specific marks record to update.
                $score = Marks::where("school_branch_id", $currentSchool->id)->findOrFail($data['mark_id']);

                // Load exam and student if not already loaded in the loop.
                if (!$exam) {
                    $exam = Exams::findOrFail($score->exam_id);
                }
                if (!$student) {
                    $student = Student::findOrFail($score->student_id);
                }

                // Retrieve the course for updating the returned result.
                $course = Courses::findOrFail($score->courses_id);

                // Calculate the total score (CA + Exam).
                $totalScore = $this->calculateTotalScoreForExam(
                    $currentSchool->id,
                    $student,
                    $data['new_score'],
                    $exam,
                    $course
                );

                // Determine the letter grade based on the total score.
                $letterGradeData = $this->determineExamLetterGrade($totalScore, $currentSchool->id, $student, $exam->id, $course->id);

                // Update the marks record with the new exam score and determined grade.
                $updatedScore = $this->updateMarkRecord($letterGradeData, $score, $course);

                $results[] = $updatedScore;
            }

            // Retrieve all relevant scores for the student in this exam.
            $this->getAllStudentScoresForExam($student, $exam, $currentSchool, $results);

            // Recalculate GPA based on all the student's scores for this exam.
            $totalScoreAndGpa = $this->calculateGpaAndTotalScore($results);

            // Determine the overall exam result status.
            $examStatus = $this->determineExamResultStatus($results);

            // Update the student results record.
            $this->updateStudentResultsRecord(
                $student,
                $currentSchool,
                $totalScoreAndGpa,
                $exam,
                $results,
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
     * @return void A collection of Marks model instances.
     */
    private function getAllStudentScoresForExam(Student $student, Exams $exam, $currentSchool, array &$results): void
    {
        $marks = Marks::with('course')
            ->where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student->id)
            ->where('student_batch_id', $student->student_batch_id)
            ->where('exam_id', $exam->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->get();
        foreach ($marks as $mark) {
            $courseId = $mark->course->id;
            $courseAlreadyExists = false;

            foreach ($results as $result) {
                if ($result['course_id'] === $courseId) {
                    $courseAlreadyExists = true;
                    break;
                }
            }

            if (!$courseAlreadyExists) {
                $results[] = [
                    'course_id' => $courseId,
                    'course_name' => $mark->course->course_title,
                    'course_code' => $mark->course->course_code,
                    'score' => $mark->score,
                    'grade' => $mark->grade,
                    'grade_status' => $mark->grade_status,
                    'gratification' => $mark->gratification,
                    'grade_points' => $mark->grade_points,
                    'resit_status' => $mark->resit_status,
                    'course_credit' => $mark->course->credit,
                ];
            }
        }
    }

    /**
     * Finds a related continuous assessment (CA) exam based on the main exam criteria.
     *
     * @param string $examId The ID of the main exam.
     * @return Exams The related CA exam object.
     * @throws Exception If the exam type is invalid or if the corresponding CA exam is not found.
     */
    private function findExamsBasedOnCriteria(string $examId): Exams
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester_id', $exam->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();

        return Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where("student_batch_id", $exam->student_batch_id)
            ->where('semester_id', $exam->semester_id)
            ->firstOrFail();
    }

    /**
     * Retrieves the continuous assessment (CA) score for a specific student, course, and exam.
     *
     * @param string $schoolId The ID of the school Branch.
     * @param Student $student The student object.
     * @param string $courseId The ID of the course.
     * @param string $examId The ID of the CA exam.
     * @return Marks The Marks model instance containing the CA score.
     * @throws Exception If the CA mark is not found.
     */
    private function retrieveCaScore(string $schoolId, Student $student, string $courseId, string $examId): Marks
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('courses_id', $courseId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->firstOrFail();
    }

    /**
     * Calculates the total score for an exam by adding the current exam score with the CA score.
     *
     * @param string $schoolId The ID of the school Branch.
     * @param Student $student The student object.
     * @param float $newExamScore The new score for the main exam.
     * @param object $exam The main exam object.
     * @param object $course The course object.
     * @return float The total score.
     * @throws Exception If the CA mark is not found or if the total score exceeds the maximum.
     */
    private function calculateTotalScoreForExam(string $schoolId, Student $student, float $newExamScore, Exams $exam, Courses $course): float
    {
        $additionalExam = $this->findExamsBasedOnCriteria($exam->id);
        $caScoreRecord = $this->retrieveCaScore($schoolId, $student, $course->id, $additionalExam->id);
        $totalScore = $newExamScore + $caScoreRecord->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    /**
     * Determines the letter grade and other grade-related information for a given total score in an exam.
     *
     * @param float $score The student's total score.
     * @param string $schoolId The ID of the school Branch.
     * @param Student $student The student object.
     * @param string $examId The ID of the exam.
     * @param string $courseId The ID of the course.
     * @return array An array containing the letter grade, grade status, gratification, grade points, resit status, and score.
     * @throws Exception If no grades are found for the given school and exam.
     */
    private function determineExamLetterGrade(float $score, string $schoolId, Student $student, string $examId, string $courseId): array
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
                if ($grade->resit_status === 'resit') {
                    $this->createResitableCourse($courseId, $examId, $student, $schoolId);
                }
                if ($grade->resit_status === 'no_resit') {
                    $this->checkAndRemoveStudentResit($courseId, $examId, $student, $schoolId);
                }
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'gradeStatus' => $grade->grade_status,
                    'gratification' => $grade->determinant,
                    'gradePoints' => $grade->grade_points,
                    'resitStatus' => $grade->resit_status,
                    'score' => $score,
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'fail',
            'gratification' => 'poor',
            'gradePoints' => 0.0,
            'resitStatus' => 'resit',
            'score' => $score,
        ];
    }

    /**
     * Creates a record for a course that requires a resit for a specific student and exam.
     *
     * @param string $courseId The ID of the course.
     * @param string $examId The ID of the exam.
     * @param Student $student The student object.
     * @param string $schoolId The ID of the school Branch.
     */
    public function createResitableCourse(string $courseId, string $examId, Student $student, string $schoolId): void
    {
        if (
            !Studentresit::where("student_id", $student->id)
                ->where("level_id", $student->level_id)
                ->where("specialty_id", $student->specialty_id)
                ->where("course_id", $courseId)
                ->exists()
        ) {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,
            ]);
        }
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
        $score->resit_status = $updateScoreData['resitStatus'];
        $score->grade_points = $updateScoreData['gradePoints'];
        $score->save();

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
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
     * Recalculates the Grade Point Average (GPA) from an array of results.
     *
     * @param array $results A collection of course results with 'course_credit' and 'grade_points'.
     * @return array An array containing the 'totalScore' and 'gpa'.
     */
    private function calculateGpaAndTotalScore(array $results): array
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
    private function updateStudentResultsRecord(Student $student, $currentSchool, array $totalScoreAndGpa, Exams $exam, array $results, array $examStatus): StudentResults
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
        $studentResult->exam_status = $examStatus['passed'] ? 'Passed' : 'Failed';
        $studentResult->save();

        return $studentResult;
    }

    /**
     * Determines the overall exam result status based on an array of course results.
     *
     * @param array $results A collection of course results with 'grade_status'.
     * @return array An array containing the 'exam_status' and boolean flags for pass/fail.
     */
    private function determineExamResultStatus(array $results): array
    {
        $failedCourses = array_filter($results, function ($result) {
            return ($result['grade_status'] ?? '') === 'failed';
        });

        if (empty($failedCourses)) {
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

    private function checkAndRemoveStudentResit($courseId, $examId, $student, $schoolId)
    {
        $studentResit = Studentresit::where("student_id", $student->id)
            ->where("level_id", $student->level_id)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->first();
        if ($studentResit) {
            $studentResit->delete();
        }
    }
}
