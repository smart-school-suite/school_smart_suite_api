<?php

namespace App\Services\Resit;
use App\Models\Courses;
use App\Models\Marks;
use App\Models\Studentresit;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\LetterGrade;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\ResitMarks;
use App\Models\ResitResults;
use App\Models\Student;
use App\Models\StudentResults;
use Exception;
class AddResitScoreService
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
     * @param string $candidateId The ID of the resit candidate.
     * @return array An array of the stored resit score details.
     * @throws Exception If any database operation fails.
     */
    public function submitStudentResitScores(array $entries, object $currentSchool, string $candidateId): array
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
                $examResults[] = $this->updateExamScore($entry, $newExamScores, $currentSchool, $exam);
                $this->updateStudentResitStatus($entry, $currentSchool, $letterGradeDetails);
            }

            $this->mergeStudentScores($resitCandidate, $caExam, $exam,  $caResults, $examResults);
            $caGpaAndExamGpa = $this->retrieveCaAndExamGpa($exam, $caExam, $currentSchool, $resitCandidate);
            $newCaGpa = $this->calculateGpaAndTotalScore($caResults);
            $this->updateStudentResults($caResults, $caExam, $resitCandidate, $currentSchool, $newCaGpa);
            $newExamGpa = $this->calculateGpaAndTotalScore($examResults);
            $this->updateStudentResults($examResults, $exam, $resitCandidate, $currentSchool, $newExamGpa);
            $this->storeResitResult(
                $caGpaAndExamGpa,
                $results,
                $currentSchool,
                $newCaGpa,
                $newExamGpa,
                $resitCandidate,
                $exam
            );
            $this->updateAccessmentStatus($candidateId);
            DB::commit();

            return [
                "results" => $results,
                "ca_results" => $caResults,
                "exam_results" => $examResults,
                "former_gp" => $caGpaAndExamGpa,
                "new_gpa" => $newExamGpa
            ];
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
            'courses_id' => $entry['course_id'],
            'resit_exam_id' => $candidate->resit_exam_id,
            'level_id' => $student->level_id,
            'score' => $entry['score'],
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $currentSchool->id,
            'student_batch_id' => $student->student_batch_id,
            'grade' => $letterGradeDetails['letter_grade'] ?? null,
            'grade_status' => $letterGradeDetails['grade_status'] ?? null,
            'grade_points' => $letterGradeDetails['grade_points'] ?? null,
            'gratification' => $letterGradeDetails['gratification'] ?? null,
        ]);

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
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
    private function storeResitResult(array $gpaDetails, array $results, object $currentSchool, array $newCaGpa, array $newExamGpa, $resitCandidate, $exam): void
    {
        $failedCourses = collect($results)->filter(function ($mark) {
            return ($mark['grade_status'] ?? ($mark['grade_status'] ?? '')) === 'failed';
        });
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
            'failed_exam_id' => $exam->id,
            'exam_status' => empty($failedCourses) ? "passed" : "failed",
            'student_batch_id' => $gpaDetails['ca_results']->student_batch_id ?? null,
            'score_details' => json_encode($results),
        ]);
    }

    /**
     * Updates the assessment status of a resit candidate.
     *
     * @param string $candidateId The ID of the resit candidate.
     * @return object The updated ResitCandidates model.
     */
    private function updateAccessmentStatus(string $candidateId): object
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
            if ($resitLetterGrade['grade_status'] === "failed") {
                $studentResit->increment('attempt_number');
                $studentResit->iscarry_over = true;
                $studentResit->paid_status = 'unpaid';
                $studentResit->save();
                return $studentResit;
            } else {
                $studentResit->delete();
                return null;
            }
        }

        return null;
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
            ->where("courses_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->where("level_id", $caExam->level_id)
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->firstOrFail();

        $mark->update([
            'score' => $updatCaScoreData['new_score'],
            'grade_points' => $updatCaScoreData['new_grade_point'],
            'grade_status' => $updatCaScoreData['new_grade_status'],
            'grade' => $updatCaScoreData['new_letter_grade'],
            'resit_status' => $updatCaScoreData['new_grade_status'] === 'failed' ? 'high_resit_potential' : 'low_resit_potential',
            'gratification' => $updatCaScoreData['new_gratification'],
        ]);

        return [
            'course_id' => $mark->course->id,
            'course_name' => $mark->course->course_title,
            'course_code' => $mark->course->course_code,
            'course_credit' => $mark->course->credit,
            'score' => (float) $updatCaScoreData['new_score'],
            'grade_points' => (float) $updatCaScoreData['new_grade_point'],
            'grade_status' => $updatCaScoreData['new_grade_status'],
            'grade' => $updatCaScoreData['new_letter_grade'],
            'resit_status' => $updatCaScoreData['new_grade_status'] === 'failed' ? 'high_resit_potential' : 'low_resit_potential',
            'gratification' => $updatCaScoreData['new_gratification'],
            'exam_id' => $caExam->id
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
    private function updateExamScore(array $entry, array $updateExamScoreData, object $currentSchool, object $exam): array
    {
        $mark = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("courses_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->where("level_id", $exam->level_id)
            ->where("exam_id", $entry['exam_id'])
            ->with(['course'])
            ->firstOrFail();

        $mark->update([
            'score' => $updateExamScoreData['new_score'],
            'grade_points' => $updateExamScoreData['new_grade_point'],
            'grade_status' => $updateExamScoreData['new_grade_status'],
            'grade' => $updateExamScoreData['new_letter_grade'],
            'resit_status' => $updateExamScoreData['new_grade_status'] === 'failed' ? 'resit' : 'no_resit',
            'gratification' => $updateExamScoreData['new_gratification'],
        ]);

        return [
            'course_id' => $mark->course->id,
            'course_name' => $mark->course->course_title,
            'course_code' => $mark->course->course_code,
            'course_credit' => $mark->course->credit,
            'score' => (float) $updateExamScoreData['new_score'],
            'grade_points' => (float) $updateExamScoreData['new_grade_point'],
            'grade_status' => $updateExamScoreData['new_grade_status'],
            'grade' => $updateExamScoreData['new_letter_grade'],
            'resit_status' => $updateExamScoreData['new_grade_status'] === 'failed' ? 'resit' : 'no_resit',
            'gratification' => $updateExamScoreData['new_gratification'],
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

        $letterGrade = LetterGrade::where("letter_grade", $resitLetterGrade)->firstOrFail();
        $examGrades = Grades::where("letter_grade_id", $letterGrade->id)
            ->where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $failedExam->grades_category_id)
            ->firstorFail();
        if (!$examGrades) {
            throw new Exception("No grades found for school ID: {$currentSchool->id} and grades category ID: {$failedExam->grades_category_id} with letter grade: {$resitLetterGrade}");
        }

        $newExamScore = mt_rand($examGrades->minimum_score, $examGrades->maximum_score);

        return [
            'new_score' => $newExamScore,
            'new_grade_point' => $examGrades->grade_points,
            'new_grade_status' => $examGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_gratification' => $examGrades->determinant,
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
        $letterGrade = LetterGrade::where("letter_grade", $resitLetterGrade)->firstOrFail();
        $caGrades = Grades::where("letter_grade_id", $letterGrade->id)
            ->where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $failedCa->grades_category_id)
            ->firstorFail();
        if (!$caGrades) {
            throw new Exception("No grades found for school ID: {$currentSchool->id} and grades category ID: {$failedCa->grades_category_id} with letter grade: {$resitLetterGrade}");
        }

        $newCaScore = mt_rand($caGrades->minimum_score, $caGrades->maximum_score);
        return [
            'new_score' => $newCaScore,
            'new_grade_point' => $caGrades->grade_points,
            'new_grade_status' => $caGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_gratification' => $caGrades->determinant,
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
        $resitExam = ResitExam::findOrFail($candidate->resit_exam_id);
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
                    'score' => $score,
                ];
            }
        }

        return [
            'letter_grade' => 'F',
            'grade_status' => 'fail',
            'gratification' => 'poor',
            'grade_points' => 0,
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

       $caExamType = ExamType::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();

        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $caExam = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->firstOrFail();

        return $caExam;
    }

    /**
     * Merges the student's original scores with the resit results.
     *
     * @param object $resitCandidate The resit candidate object.
     * @param object|null $exam The original exam object.
     * @param object|null $caExam The ca exam object
     * @param object $currentSchool The current school object.
     * @param array $caResults An array of updated CA results.
     * @param array $examResults An array of updated exam results.
     */
    private function mergeStudentScores(object $resitCandidate, ?object $caExam, ?object $exam, array &$caResults, array &$examResults)
    {
        $existingMarks = Marks::query()
            ->where('school_branch_id', $resitCandidate->school_branch_id)
            ->where('student_id', $resitCandidate->student_id)
            ->whereIn('exam_id', [$exam->id, $caExam->id])
            ->with(['course'])
            ->get()
            ->groupBy('exam_id');


        $existingCaCourseIds = collect();
        $existingExamCourseIds = collect();


        foreach ($caResults as $result) {
            $existingCaCourseIds->add($result['course_id']);
        }
        foreach ($examResults as $result) {
            $existingExamCourseIds->add($result['course_id']);
        }


        $existingCaMarks = $existingMarks->get($caExam->id);
        if ($existingCaMarks) {
            foreach ($existingCaMarks as $mark) {
                if (!$existingCaCourseIds->contains($mark->course->id)) {
                    $caResults[] = [
                        'course_id' => $mark->course->id,
                        'course_name' => $mark->course->course_title,
                        'course_code' => $mark->course->course_code,
                        'course_credit' => $mark->course->credit,
                        'score' => (float) $mark->score,
                        'grade_points' => (float) $mark->grade_points,
                        'grade_status' => $mark->grade_status,
                        'grade' => $mark->grade,
                        'resit_status' => $mark->resit_status,
                        'gratification' => $mark->gratification,
                    ];
                }
            }
        }

        $existingExamMarks = $existingMarks->get($exam->id);
        if ($existingExamMarks) {
            foreach ($existingExamMarks as $mark) {
                if (!$existingExamCourseIds->contains($mark->course->id)) {
                    $examResults[] = [
                        'course_id' => $mark->course->id,
                        'course_name' => $mark->course->course_title,
                        'course_code' => $mark->course->course_code,
                        'course_credit' => $mark->course->credit,
                        'score' => (float) $mark->score,
                        'grade_points' => (float) $mark->grade_points,
                        'grade_status' => $mark->grade_status,
                        'grade' => $mark->grade,
                        'resit_status' => $mark->resit_status,
                        'gratification' => $mark->gratification,
                    ];
                }
            }
        }
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
        $failedCourses = array_filter($results, function ($result) {
            return ($result['grade_status'] ?? '') === 'failed';
        });
        if ($examDetails) {
            $updatedResults = StudentResults::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $examDetails->specialty_id)
                ->where("exam_id", $examDetails->id)
                ->where("level_id", $examDetails->level_id)
                ->where("student_id", $resitCandidate->student_id)
                ->firstorFail();

            if ($updatedResults) {
                $updatedResults->gpa = $gpaAndTotalScores['gpa'];
                $updatedResults->total_score = $gpaAndTotalScores['totalScore'];
                $updatedResults->exam_status = empty($failedCourses) ? 'passed' : 'failed';
                $updatedResults->score_details = json_encode($results);
                $updatedResults->save();
            }
        }
    }
}
