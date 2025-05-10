<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Student;
use App\Models\AccessedStudent;
use App\Models\Courses;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\StudentResults;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AddCaScoresService
{
    // Implement your logic here
    /**
     * Processes and adds continuous assessment (CA) scores for a batch of students.
     *
     * @param array $studentScores An array of student CA score data. Each element is expected to be an array
     * containing keys like 'student_id', 'exam_id', 'course_id', 'score',
     * and 'accessment_id'. All IDs within these arrays are strings.
     * @param object $currentSchool The current school object.
     * @return array An array of the created marks data.
     * @throws Exception If any error occurs during the process, such as duplicate entry, student/exam not found,
     * score exceeding maximum, or database transaction failure.
     */
    public function addCaScore(array $studentScores, $currentSchool): array
    {
        $results = [];
        $examDetails = null;

        DB::beginTransaction();
        try {
            foreach ($studentScores as $scoreData) {
                // Retrieve the student based on school and student ID.
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                // Retrieve the exam details.
                $exam = Exams::findOrFail($scoreData['exam_id']);
                $examDetails = $exam;

                // Ensure both student and exam exist.
                $this->validateStudentAndExam($student, $exam);

                // Check for duplicate entry before proceeding.
                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }

                // Validate if the CA mark exceeds the maximum allowed for the exam.
                $this->validateCaMark($exam, $scoreData['score']);

                // Create a new marks record for the CA score.
                $marks = $this->createCaMarks($scoreData, $student, $currentSchool->id, $exam);

                // Update the accessed student record.
                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                $this->updateAccessedStudent($accessedStudent);

                $results[] = $marks;
            }

            // Recalculate GPA and total score based on the CA scores (though these might be preliminary).
            $totalScoreAndGpa = $this->calculateGpaAndTotalScore(collect($results));
            // Determine the exam result status based on CA scores (might not be final).
            $examStatus = $this->determineExamResultsStatus(collect($results));

            // Add a record to the student results table (this might be an initial record or updated later).
            $this->addStudentResultRecords($student, $currentSchool, $totalScoreAndGpa, $examDetails, $results, $examStatus);

            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Creates a new marks record for continuous assessment in the database.
     *
     * @param array $scoreData An array containing score data with 'course_id' and 'score'.
     * @param Student $student The student object.
     * @param string $schoolId The ID of the current school.
     * @param object $exam The exam object (should be a CA exam).
     * @return array An array containing relevant course and grade information.
     */
    private function createCaMarks(array $scoreData, Student $student, string $schoolId, object $exam): array
    {
        $gradeData = $this->determineCaLetterGrade($scoreData['score'], $schoolId, $exam->id);
        $course = Courses::findOrFail($scoreData['course_id']);
        Marks::create([
            'student_batch_id' => $student->student_batch_id,
            'courses_id' => $course->id,
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'level_id' => $student->level_id,
            'score' => $scoreData['score'],
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $schoolId,
            'grade' => $gradeData['letterGrade'] ?? null,
            'grade_status' => $gradeData['gradeStatus'] ?? null,
            'resit_status' => $gradeData['resitStatus'] ?? null,
            'grade_points' => $gradeData['gradePoints'] ?? null,
            'gratification' => $gradeData['gratification'] ?? null,
        ]);

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
            'score' => $scoreData['score'],
            'grade' => $gradeData['letterGrade'] ?? 'N/A',
            'grade_status' => $gradeData['gradeStatus'] ?? 'N/A',
            'gratification' => $gradeData['gratification'] ?? 'N/A',
            'grade_points' => $gradeData['gradePoints'] ?? 0.0,
            'resit_status' => $gradeData['resitStatus'] ?? 'N/A',
            'course_credit' => $course->credit,
        ];
    }

    /**
     * Updates the accessed student record to mark grades as submitted and student as accessed.
     *
     * @param object $accessedStudent The accessed student model instance.
     */
    private function updateAccessedStudent(object $accessedStudent): void
    {
        if (!$accessedStudent->grades_submitted || $accessedStudent->student_accessed === 'pending') {
            $accessedStudent->grades_submitted = true;
            $accessedStudent->student_accessed = 'accessed';
            $accessedStudent->save();
        }
    }

    /**
     * Validates if both the student and the exam objects are not null.
     *
     * @param object|null $student The student object.
     * @param object|null $exam The exam object.
     * @throws Exception If either the student or the exam is not found.
     */
    private function validateStudentAndExam(?object $student, ?object $exam): void
    {
        if (!$student || !$exam) {
            throw new Exception('Student or Exam not found', 404);
        }
    }

    /**
     * Determines the letter grade and other grade-related information for a CA score.
     *
     * @param float $score The student's CA score.
     * @param string $schoolId The ID of the school.
     * @param string $examId The ID of the CA exam.
     * @return array An array containing the letter grade, grade status, grade points, gratification, resit status, and score.
     * @throws Exception If no grades are found for the given school and exam.
     */
    private function determineCaLetterGrade(float $score, string $schoolId, string $examId): array
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
                    'gratification' => $grade->determinant,
                    'resitStatus' => $grade->resit_status,
                    'score' => $score,
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'fail',
            'gratification' => 'poor',
            'score' => $score,
            'resitStatus' => 'not_applicable', // CA failure might not directly lead to resit
            'gradePoints' => 0.0,
        ];
    }

    /**
     * Retrieves a student based on the school ID and student ID.
     *
     * @param string $schoolId The ID of the school.
     * @param string $studentId The ID of the student (string).
     * @return Student The student object if found.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the student is not found.
     */
    private function getStudent(string $schoolId, string $studentId): Student
    {
        return Student::where('school_branch_id', $schoolId)->findOrFail($studentId);
    }

    /**
     * Checks if a duplicate marks entry exists for the given student, course, and exam.
     *
     * @param string $schoolId The ID of the school.
     * @param array $scoreData An array containing score data with 'course_id' and 'exam_id'.
     * @param Student $student The student object.
     * @return bool True if a duplicate entry exists, false otherwise.
     */
    private function isDuplicateEntry(string $schoolId, array $scoreData, Student $student): bool
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('courses_id', $scoreData['course_id'])
            ->where('exam_id', $scoreData['exam_id'])
            ->where('level_id', $student->level_id)
            ->where('specialty_id', $student->specialty_id)
            ->where('student_id', $student->id)
            ->exists();
    }

    /**
     * Validates if the continuous assessment (CA) mark does not exceed the maximum weighted mark for the exam.
     *
     * @param object $exam The exam object.
     * @param float $score The CA score.
     * @throws Exception If the score exceeds the maximum weighted mark.
     */
    private function validateCaMark(object $exam, float $score): void
    {
        if ($score > $exam->weighted_mark) {
            throw new Exception('Score exceeds maximum exam mark.', 400);
        }
    }

    /**
     * Calculates the Grade Point Average (GPA) and total score from a collection of marks.
     *
     * @param Collection $marks A collection of marks data (arrays or objects with relevant properties).
     * @return array An array containing the 'totalScore' and 'gpa'.
     */
    private function calculateGpaAndTotalScore(Collection $marks): array
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $overallTotalScore = 0;

        foreach ($marks as $mark) {
            $credits = $mark['course_credit'] ?? ($mark->course->credit ?? 0); // Handle array or object
            $gradePoints = $mark['grade_points'] ?? 0;
            $score = $mark['score'] ?? 0;

            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
            $overallTotalScore += $score;
        }

        $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;

        return [
            'totalScore' => round($overallTotalScore, 2),
            'gpa' => $gpa,
        ];
    }

    /**
     * Adds a record to the student results table.
     *
     * @param Student $student The student object.
     * @param object $currentSchool The current school object.
     * @param array $totalScoreAndGpa An array containing 'totalScore' and 'gpa'.
     * @param object $exam The exam object.
     * @param array $result An array of individual course results.
     * @param array $examStatus An array containing the overall 'exam_status' and boolean flags.
     * @return StudentResults The created StudentResults model instance.
     */
    private function addStudentResultRecords(Student $student, $currentSchool, array $totalScoreAndGpa, object $exam, array $result, array $examStatus): StudentResults
    {
        $studentResult = StudentResults::create([
            'student_id' => $student->id,
            'specialty_id' => $student->specialty_id,
            'student_batch_id' => $student->student_batch_id,
            'level_id' => $student->level_id,
            'exam_id' => $exam->id,
            'school_branch_id' => $currentSchool->id,
            'total_score' => $totalScoreAndGpa['totalScore'],
            'gpa' => $totalScoreAndGpa['gpa'],
            'exam_status' => $examStatus['passed'] ? 'Passed' : 'Failed',
            'score_details' => json_encode($result),
        ]);
        return $studentResult;
    }

    /**
     * Determines the overall exam result status based on individual course grades.
     *
     * @param Collection $marks A collection of marks data.
     * @return array An array containing the 'exam_status' and boolean flags for pass/fail.
     */
    private function determineExamResultsStatus(Collection $marks): array
    {
        $failedCourses = $marks->filter(fn ($mark) => ($mark['grade_status'] ?? $mark->grade_status ?? '') === 'failed');

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
