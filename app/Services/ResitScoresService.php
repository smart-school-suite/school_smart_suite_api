<?php

namespace App\Services;

use App\Models\AccessedResitStudent;
use App\Models\Courses;
use App\Models\Marks;
use App\Models\Studentresit;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\ResitMarks;
use App\Models\ResitResults;
use App\Models\Student;
use App\Models\StudentResults;
use Exception;

class ResitScoresService
{
    /**
     * Submits resit scores for a student.
     *
     * @param array $entries Array of resit entry data. Each entry should contain:
     * - 'exam_id': The ID of the original exam.
     * - 'course_id': The ID of the course.
     * - 'student_id': The ID of the student.
     * - 'score': The resit score.
     * @param object $currentSchool The current school object.
     * @param int $candidateId The ID of the resit candidate.
     * @return array An array of the stored resit score details.
     * @throws Exception If any database operation fails.
     */
    public function submitStudentResitScores(array $entries, object $currentSchool, int $candidateId): array
    {
        DB::beginTransaction();

        try {
            $resitCandidate = ResitCandidates::findOrFail($candidateId);
            $results = [];
            $caExam = null;
            $exam = null;
            $caResults = [];
            $examResults = [];

            foreach ($entries as $entry) {
                if ($exam === null) {
                    $exam = Exams::findOrFail($entry['exam_id']);
                }

                $letterGradeDetails = $this->determineResitLetterGrade(
                    $entry['score'],
                    $currentSchool,
                    $resitCandidate
                );

                $results[] = $this->storeResitScore(
                    $currentSchool,
                    $resitCandidate,
                    $letterGradeDetails,
                    $entry
                );

                $caExamRecord = $this->findCaExamForExam($entry['exam_id']);
                if ($caExam === null) {
                    $caExam = $caExamRecord;
                }

                $newExamScores = $this->determineNewExamScores(
                    $currentSchool,
                    $letterGradeDetails['letter_grade'],
                    $entry['exam_id']
                );

                $newCaScores = $this->determineNewCaScore(
                    $currentSchool,
                    $letterGradeDetails['letter_grade'],
                    $caExamRecord->id
                );

                $caResults[] = $this->updateCaScore($entry, $newCaScores, $currentSchool, $caExamRecord);
                $examResults[] = $this->updateExamScore($entry, $newExamScores, $currentSchool); // Collect exam results
                $this->updateStudentResitStatus($entry, $currentSchool, $letterGradeDetails);
            }

            DB::commit();

            $allResults = $this->mergeStudentScores($resitCandidate, $caExam, $currentSchool, $caResults, $examResults);
            $caGpaAndExamGpa = $this->retrieveCaAndExamGpa($exam, $caExam, $currentSchool, $resitCandidate);
            $newCaGpa = $this->calculateGpaAndTotalScore($caResults);
            $this->updateStudentResults($caResults, $caExam, $resitCandidate, $currentSchool, $newCaGpa);
            $newExamGpa = $this->calculateGpaAndTotalScore($examResults);
            $this->updateStudentResults($examResults, $exam, $resitCandidate, $currentSchool, $newExamGpa);
            $this->storeResitResult($caGpaAndExamGpa, $allResults, $currentSchool, $newCaGpa, $newExamGpa, $resitCandidate);
            $this->updateAccessmentStatus($resitCandidate->id);

            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
        /**
     * Stores a single resit score.
     *
     * @param object $currentSchool The current school object.
     * @param object $candidate The resit candidate object.
     * @param array $letterGradeDetails An array containing letter grade details.
     * @param array $entry An array containing the resit entry details.
     * @return array An array containing the stored resit score details.
     */
    private function storeResitScore(object $currentSchool, object $candidate, array $letterGradeDetails, array $entry): array
    {
        $course = Courses::findOrFail($entry['course_id']);
        $student = Student::findOrFail($entry['student_id']);

        ResitMarks::create([
            'student_id' => $candidate->student_id,
            'course_id' => $entry['course_id'],
            'resit_exam_id' => $candidate->resit_exam_id,
            'level_id' => $student->level_id,
            'score' => $entry['score'],
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $currentSchool->id,
            'grade' => $letterGradeDetails['letter_grade'] ?? null, // Use null coalescing operator for safety
            'grade_status' => $letterGradeDetails['grade_status'] ?? null,
            'grade_points' => $letterGradeDetails['grade_points'] ?? null,
            'determinant' => $letterGradeDetails['gratification'] ?? null, // Use 'gratification' as determinant
        ]);

        return [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'score' => $entry['score'],
            'grade' => $letterGradeDetails['letter_grade'] ?? 'N/A',
            'grade_status' => $letterGradeDetails['grade_status'] ?? 'N/A',
            'gratification' => $letterGradeDetails['gratification'] ?? 'N/A',
            'grade_points' => $letterGradeDetails['grade_points'] ?? 0,
            'course_credit' => $course->credit,
        ];
    }

      /**
     * Stores the overall resit result for a student.
     *
     * @param array $gpaDetails An array containing former CA and exam GPA details.
     * @param array $results An array of individual resit score details.
     * @param object $currentSchool The current school object.
     * @param array $newCaGpa An array containing the new CA GPA and total score.
     * @param array $newExamGpa An array containing the new exam GPA and total score.
     */
    private function storeResitResult(array $gpaDetails, array $results, object $currentSchool, array $newCaGpa, array $newExamGpa, $resitCandidate): void
    {
        ResitResults::create([
            'former_exam_gpa' => $gpaDetails['exam_results']->gpa ?? null,
            'new_exam_gpa' => $newExamGpa['gpa'] ?? null,
            'new_ca_gpa' => $newCaGpa['gpa'] ?? null,
            'former_ca_gpa' => $gpaDetails['ca_results']->gpa ?? null,
            'student_id' => $gpaDetails['ca_results']->student_id ?? null,
            'school_branch_id' => $currentSchool->id,
            'specialty_id' => $gpaDetails['ca_results']->specialty_id ?? null,
            'level_id' => $gpaDetails['ca_results']->level_id ?? null,
            'resit_exam_id' => $resitCandidate->resit_exam_id ?? null,
            'student_batch_id' => $gpaDetails['ca_results']->student_batch_id ?? null,
            'score_details' => json_encode($results),
        ]);
    }

    /**
     * Updates the assessment status of a resit candidate.
     *
     * @param int $candidateId The ID of the resit candidate.
     * @return object The updated ResitCandidates model.
     */
    private function updateAccessmentStatus(int $candidateId): object
    {
        $candidate = ResitCandidates::findOrFail($candidateId);
        $candidate->grades_submitted = true;
        $candidate->student_accessed = true;
        $candidate->save();
        return $candidate;
    }
   /**
     * Updates the resit status of a student for a specific course.
     *
     * @param array $entry An array containing the entry details.
     * @param object $currentSchool The current school object.
     * @param array $resitLetterGrade An array containing the determined resit letter grade details.
     * @return object|null The updated or deleted Studentresit model.
     */
    private function updateStudentResitStatus(array $entry, object $currentSchool, array $resitLetterGrade): ?object
    {
        $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("course_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->first();

        if ($studentResit) {
            if ($resitLetterGrade['grade_status'] === "fail") {
                $studentResit->increment('attempt_number');
                $studentResit->iscarry_over = true;
                $studentResit->paid_status = 'unpaid';
                $studentResit->save();
                return $studentResit;
            } else {
                $studentResit->delete();
                return null; // Indicate deletion
            }
        }

        return null; // Return null if Studentresit record not found
    }

    /**
     * Updates the CA score for a student in a specific course and exam.
     *
     * @param array $entry An array containing the entry details.
     * @param array $updatCaScoreData An array containing the new CA score data.
     * @param object $currentSchool The current school object.
     * @param object $caExam The CA exam object.
     * @return array An array containing the updated CA score details.
     */
    private function updateCaScore(array $entry, array $updatCaScoreData, object $currentSchool, object $caExam): array
    {
        $mark = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("course_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->where("level_id", $entry['level_id'])
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->firstOrFail(); // Use firstOrFail to handle cases where the record doesn't exist

        $mark->update([
            'score' => $updatCaScoreData['new_score'], // Use 'new_score'
            'grade_points' => $updatCaScoreData['new_grade_point'],
            'grade_status' => $updatCaScoreData['new_grade_status'],
            'grade' => $updatCaScoreData['new_letter_grade'], // Use 'new_letter_grade'
            'resit_status' => $updatCaScoreData['new_grade_status'] === 'fail' ? 'high_resit_potential' : 'no_resit', // Improved logic
            'determinant' => $updatCaScoreData['new_determinant'], // Use 'new_determinant'
        ]);

        return [
            'course_id' => $mark->course->id,
            'course_name' => $mark->course->name,
            'course_code' => $mark->course->code,
            'course_credit' => $mark->course->credit,
            'score' => $mark->score,
            'grade_points' => $mark->grade_points,
            'grade_status' => $mark->grade_status,
            'grade' => $mark->grade,
            'resit_status' => $mark->resit_status,
            'determinant' => $mark->determinant,
        ];
    }

    /**
     * Updates the exam score for a student in a specific course and exam.
     *
     * @param array $entry An array containing the entry details.
     * @param array $updateExamScoreData An array containing the new exam score data.
     * @param object $currentSchool The current school object.
     * @return array An array containing the updated exam score details.
     */
    private function updateExamScore(array $entry, array $updateExamScoreData, object $currentSchool): array
    {
        $mark = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("course_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->where("level_id", $entry['level_id'])
            ->where("exam_id", $entry['exam_id'])
            ->with(['course'])
            ->firstOrFail(); // Use firstOrFail

        $mark->update([
            'score' => $updateExamScoreData['new_score'],
            'grade_points' => $updateExamScoreData['new_grade_point'],
            'grade_status' => $updateExamScoreData['new_grade_status'],
            'grade' => $updateExamScoreData['new_letter_grade'], // Use 'new_letter_grade'
            'resit_status' => $updateExamScoreData['new_grade_status'] === 'fail' ? 'resit' : 'no_resit',
            'determinant' => $updateExamScoreData['new_determinant'], // Use 'new_determinant'
        ]);

        return [
            'course_id' => $mark->course->id,
            'course_name' => $mark->course->name,
            'course_code' => $mark->course->code,
            'course_credit' => $mark->course->credit,
            'score' => $mark->score,
            'grade_points' => $mark->grade_points,
            'grade_status' => $mark->grade_status,
            'grade' => $mark->grade,
            'resit_status' => $mark->resit_status,
            'determinant' => $mark->determinant,
        ];
    }

    /**
     * Determines the new exam scores based on the resit letter grade.
     *
     * @param object $currentSchool The current school object.
     * @param string $resitLetterGrade The letter grade obtained in the resit.
     * @param string $failedExamId The ID of the original failed exam.
     * @return array An array containing the new exam score details.
     * @throws Exception If no grades are found.
     */
    private function determineNewExamScores(object $currentSchool, string $resitLetterGrade, string $failedExamId): array
    {
        $failedExam = Exams::findOrFail($failedExamId);
        $examGrades = Grades::whereHas('lettergrade', function ($query) use ($resitLetterGrade) {
            $query->where('letter_grade', $resitLetterGrade);
        })
            ->where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $failedExam->grades_category_id)
            ->first();

        if (!$examGrades) {
            throw new Exception("No grades found for school ID: {$currentSchool->id} and grades category ID: {$failedExam->grades_category_id} with letter grade: {$resitLetterGrade}");
        }

        $newExamScore = mt_rand($examGrades->minimum_score, $examGrades->maximum_score);

        return [
            'new_score' => $newExamScore,
            'new_grade_points' => $examGrades->grade_points,
            'new_grade_status' => $examGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_determinant' => $examGrades->determinant,
        ];
    }

    /**
     * Determines the new CA score based on the resit letter grade.
     *
     * @param object $currentSchool The current school object.
     * @param string $resitLetterGrade The letter grade obtained in the resit.
     * @param string $failedCaId The ID of the corresponding CA exam.
     * @return array An array containing the new CA score details.
     * @throws Exception If no grades are found.
     */
    private function determineNewCaScore(object $currentSchool, string $resitLetterGrade, string $failedCaId): array
    {
        $failedCa = Exams::findOrFail($failedCaId);
        $caGrades = Grades::whereHas('lettergrade', function ($query) use ($resitLetterGrade) {
            $query->where('letter_grade', $resitLetterGrade);
        })
            ->where('school_branch_id', $currentSchool->id)
            ->where('grades_category_id', $failedCa->grades_category_id)
            ->first();

        if (!$caGrades) {
            throw new Exception("No grades found for school ID: {$currentSchool->id} and grades category ID: {$failedCa->grades_category_id} with letter grade: {$resitLetterGrade}");
        }

        $newCaScore = mt_rand($caGrades->minimum_score, $caGrades->maximum_score);
        return [
            'new_score' => $newCaScore,
            'new_grade_point' => $caGrades->grade_points,
            'new_grade_status' => $caGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_determinant' => $caGrades->determinant,
        ];
    }

    /**
     * Determines the letter grade details based on the resit score.
     *
     * @param float $score The resit score.
     * @param object $currentSchool The current school object.
     * @param object $candidate The resit candidate object.
     * @return array An array containing the letter grade details.
     * @throws Exception If no grades are found for the given criteria.
     */
    private function determineResitLetterGrade(float $score, object $currentSchool, object $candidate): array
    {
        $resitExam = ResitExam::findOrFail($candidate->resit_id);
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $currentSchool->id)
            ->where('grades_category_id', $resitExam->grades_category_id)
            ->orderBy('minimum_score', 'asc')
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$currentSchool->id} and grades category ID: {$resitExam->grades_category_id}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return [
                    'letter_grade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'grade_status' => $grade->grade_status ?? null,
                    'gratification' => $grade->determinant ?? null,
                    'grade_points' => $grade->grade_points ?? 0,
                    'determinant' => $grade->determinant ?? null,
                    'score' => $score,
                ];
            }
        }

        return [
            'letter_grade' => 'F',
            'grade_status' => 'fail',
            'gratification' => 'poor',
            'grade_points' => 0,
            'determinant' => 'poor',
            'score' => $score,
        ];
    }

     /**
     * Finds the corresponding CA exam for a given exam.
     *
     * @param string $examId The ID of the exam.
     * @return object The corresponding CA Exam model.
     * @throws Exception If the exam type is invalid or the CA exam is not found.
     */
    private function findCaExamForExam(string $examId): object
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester', $exam->examType->semester)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $caExam = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->firstOrFail();

        return $caExam;
    }

     /**
     * Merges the student's original scores with the resit results.
     *
     * @param object $resitCandidate The resit candidate object.
     * @param object|null $exam The original exam object.
     * @param object $currentSchool The current school object.
     * @param array $caResults An array of updated CA results.
     * @param array $examResults An array of updated exam results.
     * @return array An array containing all relevant student scores.
     */
    private function mergeStudentScores(object $resitCandidate, ?object $exam, object $currentSchool, array $caResults, array $examResults): array
    {
        $allScores = [];
        $existingMarks = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $resitCandidate->student_id)
            ->with(['course'])
            ->get();
        foreach ($existingMarks as $mark) {
            $isResitCourse = false;
            foreach ($caResults as $resitCaResult) {
                if ($mark->course_id === $resitCaResult['course_id']) {
                    $allScores[] = $resitCaResult;
                    $isResitCourse = true;
                    break;
                }
            }
            if (!$isResitCourse) {
                foreach ($examResults as $resitExamResult) {
                    if ($mark->course_id === $resitExamResult['course_id']) {
                        $allScores[] = $resitExamResult;
                        $isResitCourse = true;
                        break;
                    }
                }
            }
            if (!$isResitCourse) {
                $allScores[] = [
                    'course_id' => $mark->course_id,
                    'course_name' => $mark->course->name,
                    'course_code' => $mark->course->code,
                    'score' => $mark->score,
                    'grade' => $mark->grade,
                    'grade_status' => $mark->grade_status,
                    'resit_status' => $mark->resit_status,
                    'gratification' => $mark->determinant,
                    'grade_points' => $mark->grade_points,
                    'course_credit' => $mark->course->credit,
                    'is_resit' => false,
                ];
            }
        }

        // Add new resit marks
        foreach ($caResults as $resitCaResult) {
            if (!collect($allScores)->where('course_id', $resitCaResult['course_id'])->isNotEmpty()) {
                $allScores[] = array_merge($resitCaResult, ['is_resit' => true]);
            }
        }
        foreach ($examResults as $resitExamResult) {
            if (!collect($allScores)->where('course_id', $resitExamResult['course_id'])->isNotEmpty()) {
                $allScores[] = array_merge($resitExamResult, ['is_resit' => true]);
            }
        }

        return $allScores;
    }
     /**
     * Retrieves the former CA and exam GPA for the student.
     *
     * @param object|null $exam The original exam object.
     * @param object|null $caExam The corresponding CA exam object.
     * @param object $currentSchool The current school object.
     * @param object $resitCandidate The resit candidate object.
     * @return array An array containing the CA and exam StudentResults.
     */
    private function retrieveCaAndExamGpa(?object $exam, ?object $caExam, object $currentSchool, object $resitCandidate): array
    {
        $caGpa = null;
        if ($caExam) {
            $caGpa = StudentResults::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $resitCandidate->student_id)
                ->where("specialty_id", $caExam->specialty_id)
                ->where("level_id", $caExam->level_id)
                ->where("exam_id", $caExam->id)
                ->first();
        }

        $examGpa = null;
        if ($exam) {
            $examGpa = StudentResults::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $resitCandidate->student_id)
                ->where("specialty_id", $exam->specialty_id)
                ->where("level_id", $exam->level_id)
                ->where("exam_id", $exam->id)
                ->first();
        }

        return [
            'ca_results' => $caGpa,
            'exam_results' => $examGpa,
            'student_id' => $resitCandidate->student_id,
            'specialty_id' => $resitCandidate->specialty_id,
            'level_id' => $resitCandidate->level_id,
            'student_batch_id' => $resitCandidate->student_batch_id,
        ];
    }
 /**
     * Calculates the GPA and total score from an array of results.
     *
     * @param array $results An array of student mark objects or arrays.
     * @return array An array containing the total score and GPA.
     */
    private function calculateGpaAndTotalScore(array $results): array
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $totalScore = 0;

        foreach ($results as $result) {
            $credits = $result['course_credit'] ?? $result->course->credit ?? 0;
            $gradePoints = $result['grade_points'] ?? $result->grade_points ?? 0;
            $score = $result['score'] ?? $result->score ?? 0;

            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
            $totalScore += $score;
        }

        $gpa = $totalCredits > 0 ? $totalWeightedPoints / $totalCredits : 0;

        return [
            'totalScore' => $totalScore,
            'gpa' => round($gpa, 2), // Round GPA to 2 decimal places
        ];
    }

    /**
     * Updates the student results record with the new GPA and total score.
     *
     * @param array $results An array of updated course results.
     * @param object|null $examDetails The exam details object.
     * @param object $resitCandidate The resit candidate object.
     * @param object $currentSchool The current school object.
     * @param array $gpaAndTotalScores An array containing the GPA and total score.
     */
     private function updateStudentResults(array $results, ?object $examDetails, object $resitCandidate, object $currentSchool, array $gpaAndTotalScores): void
     {
         if ($examDetails) {
             $updatedResults = StudentResults::where("school_branch_id", $currentSchool->id)
                 ->where("specialty_id", $examDetails->specialty_id)
                 ->where("exam_id", $examDetails->id)
                 ->where("student_id", $resitCandidate->student_id)
                 ->first();

             if ($updatedResults) {
                 $updatedResults->gpa = $gpaAndTotalScores['gpa'];
                 $updatedResults->total_scores = $gpaAndTotalScores['totalScore'];
                 $updatedResults->score_details = json_encode($results);
                 $updatedResults->save();
             }
         }
     }


}
