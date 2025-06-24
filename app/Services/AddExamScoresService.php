<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateResitCandidateJob;
use App\Jobs\StatisticalJobs\AcademicJobs\ExamStatsJob;
use App\Jobs\StatisticalJobs\AcademicJobs\StudentExamStatsJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\StudentResults;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Marks;
use App\Models\Examtype;
use App\Models\Courses;
use App\Models\AccessedStudent;
use App\Models\Studentresit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AddExamScoresService
{
    // Implement your logic here

     /**
     * Processes and adds exam scores for a batch of students.
     *
     * @param array $studentScores An array of student score data. Each element is expected to be an array
     * containing keys like 'student_id', 'exam_id', 'course_id', 'score',
     * and 'accessment_id'. All IDs within these arrays are strings.
     * @param object $currentSchool The current school object.
     * @return array An array of the created marks data.
     * @throws Exception If any error occurs during the process, such as duplicate entry, student/exam not found,
     * CA mark not found, total score exceeding maximum, or database transaction failure.
     */
    public function addExamScores(array $studentScores, $currentSchool): array
    {
        $results = [];
        $examDetails = null;
        $targetStudent = null;
        $allStudentsEvaluated = false;
        DB::beginTransaction();
        try {
            foreach ($studentScores as $scoreData) {
                // Retrieve the student based on school and student ID.
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                // Retrieve the exam details with its type.
                $targetStudent = $student;
                Log::info($scoreData['exam_id']);
                $exam = Exams::with('examtype')->findOrFail($scoreData['exam_id']);
                $examDetails = $exam;


                // Ensure both student and exam exist.
                $this->validateStudentAndExam($student, $exam);

                // Check for duplicate entry before proceeding.
                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }

                // Calculate the total score by combining the current exam score with the CA score.
                $totalScore = $this->calculateTotalScore($currentSchool->id, $student, $scoreData, $exam);
                // Determine the letter grade and other grade-related information based on the total score.
                $gradeData = $this->determineExamLetterGrade(
                    $totalScore,
                    $currentSchool->id,
                    $student,
                    $exam->id,
                    $scoreData['course_id']
                );

                // Create a new marks record in the database.
                $marks = $this->createMarks(
                    $gradeData,
                    $totalScore,
                    $student,
                    $currentSchool->id,
                    $exam,
                    $scoreData['course_id']
                );

                // Update the accessed student record to indicate that grades have been submitted.
                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                $this->updateAccessedStudent($accessedStudent);

                $results[] = $marks;
            }

            // Calculate the overall GPA and total score for the exam.
            $totalScoreAndGpa = $this->calculateGpaAndTotalScore(collect($results));
            // Determine the overall exam result status (Passed or Failed).
            $examStatus = $this->determineExamResultsStatus(collect($results));

            // Add a record to the student results table.
            $this->addStudentResultRecords(
                $student,
                $currentSchool,
                $totalScoreAndGpa,
                $examDetails,
                $results,
                $examStatus
            );

            // Update the count of evaluated students for the exam and potentially trigger a resit exam job.
            $allStudentsEvaluated = $this->updateEvaluatedStudentCount($examDetails);
            DB::commit();
            StudentExamStatsJob::dispatch($examDetails, $targetStudent);
             if ($allStudentsEvaluated) {
                dispatch(new CreateResitCandidateJob($examDetails)); // Use $examDetails here
                ExamStatsJob::dispatch($examDetails); // Use $examDetails here
            }
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Creates a new marks record in the database.
     *
     * @param array $gradeData An array containing grade information like 'letterGrade', 'gradeStatus', 'gradePoints', etc.
     * @param float $score The student's score for the course in the exam.
     * @param Student $student The student object.
     * @param string $schoolId The ID of the current school.
     * @param object $exam The exam object.
     * @param string $courseId The ID of the course (string).
     * @return array An array containing relevant course and grade information.
     */
    private function createMarks(array $gradeData, float $score, Student $student, string $schoolId, object $exam, string $courseId): array
    {
        $course = Courses::findOrFail($courseId);
        Marks::create([
            'student_batch_id' => $student->student_batch_id,
            'courses_id' => $courseId,
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'level_id' => $student->level_id,
            'score' => $score,
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $schoolId,
            'grade' => $gradeData['letterGrade'],
            'grade_status' => $gradeData['gradeStatus'],
            'grade_points' => $gradeData['gradePoints'],
            'resit_status' => $gradeData['resitStatus'],
            'gratification' => $gradeData['gratification'],
        ]);

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
            'score' => $gradeData['score'],
            'grade' => $gradeData['letterGrade'],
            'grade_status' => $gradeData['gradeStatus'],
            'gratification' => $gradeData['gratification'],
            'grade_points' => $gradeData['gradePoints'],
            'resit_status' => $gradeData['resitStatus'],
            'course_credit' => $course->credit,
        ];
    }

    /**
     * Increments the evaluated candidate number for an exam and dispatches a resit exam job if all expected candidates have been evaluated.
     *
     * @param object $exam The exam object.
     * @param  $allStudentsEvaluated
     */
     private function updateEvaluatedStudentCount(Exams $exam): bool
    {
        $exam->increment('evaluated_candidate_number');
        // Reload the exam to get the latest evaluated_candidate_number
        $exam->refresh();
        return $exam->evaluated_candidate_number >= $exam->expected_candidate_number;
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
     * Checks if a duplicate marks entry exists for the given student, course, and exam.
     *
     * @param string $schoolId The ID of the school.
     * @param array $scoreData An array containing score data with 'course_id' and 'exam_id' (both strings).
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
     * Retrieves the continuous assessment (CA) score for a student in a specific course and exam.
     *
     * @param string $schoolId The ID of the school.
     * @param Student $student The student object.
     * @param string $courseId The ID of the course (string).
     * @param string $examId The ID of the CA exam (string).
     * @return Marks The marks object containing the CA score.
     * @throws Exception If the CA mark is not found.
     */
    private function retrieveCaScore(string $schoolId, Student $student, string $courseId, string $examId): Marks
    {
        $caScore = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('courses_id', $courseId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->first();

        if (!$caScore) {
            throw new Exception('CA mark not found for this course', 400);
        }

        return $caScore;
    }

    /**
     * Calculates the total score for a student by adding the current exam score and the CA score.
     *
     * @param string $schoolId The ID of the school.
     * @param Student $student The student object.
     * @param array $scoreData An array containing the current exam score and 'exam_id' (string).
     * @param object $exam The current exam object.
     * @return float The total score.
     * @throws Exception If the total score exceeds the maximum allowed score.
     */
    private function calculateTotalScore(string $schoolId, Student $student, array $scoreData, object $exam): float
    {
        // Find the corresponding CA exam for the current exam.
        $additionalExam = $this->findCaExamForCurrentExam($scoreData['exam_id']);
        // Retrieve the CA score for the student in the specified course and CA exam.
        $caScore = $this->retrieveCaScore($schoolId, $student, $scoreData['course_id'], $additionalExam->id);
        $totalScore = $scoreData['score'] + $caScore->score;

        // Check if the total score exceeds the maximum possible score.
        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    /**
     * Finds the continuous assessment (CA) exam corresponding to a given exam.
     *
     * @param string $examId The ID of the current exam (string).
     * @return Exams The corresponding CA exam object.
     * @throws Exception If the exam type is invalid or not found, the CA exam type is not found,
     * or the corresponding CA exam record is not found.
     */
    private function findCaExamForCurrentExam(string $examId): Exams
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();
        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('student_batch_id', $exam->student_batch_id)
            ->where('semester_id', $exam->semester_id)
            ->first();
        return $additionalExams;
    }

    /**
     * Determines the letter grade and other grade-related information based on the student's score.
     *
     * @param float $score The student's total score.
     * @param string $schoolId The ID of the school.
     * @param Student $student The student object.
     * @param string $examId The ID of the exam (string).
     * @param string $courseId The ID of the course (string).
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

                return [
                    'letterGrade' => $grade->lettergrade->letter_grade,
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
     * Creates a record for a student needing to resit a course if one doesn't already exist.
     *
     * @param string $courseId The ID of the course to be resat (string).
     * @param string $examId The ID of the original exam (string).
     * @param Student $student The student object.
     * @param string $schoolId The ID of the school.
     */
    private function createResitableCourse(string $courseId, string $examId, Student $student, string $schoolId): void
    {

        if (!Studentresit::where("student_id", $student->id)
            ->where("level_id", $student->level_id)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->exists()) {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'specialty_id' => $student->specialty_id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,
            ]);
        }
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
     * Calculates the Grade Point Average (GPA) and
     * /**
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
     * @param object $student The student object.
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
