<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Examtype;
use App\Models\Grades;
use App\Models\Marks;
use App\Models\Courses;
use App\Models\ResitMarks;
use App\Models\ResitExam;
use App\Models\StudentResults;
use App\Models\Studentresit;
use App\Models\LetterGrade;
use App\Models\ResitResults;
use App\Models\ResitCandidates;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateResitScoreService
{
    public function updateResitScores(array $entries, object $currentSchool, string $candidateId){
        DB::beginTransaction();
        try {
            $resitCandidate = ResitCandidates::findOrFail($candidateId);
            $results = [];
            $resitExam = null;
            $caExam = null;
            $exam = null;
            $caResults = [];
            $examResults = [];
            foreach ($entries as $entry) {
                if ($exam === null) {
                    $exam = Exams::findOrFail($entry['exam_id']);
                }
                if ($resitExam === null) {
                    $resitExam = ResitExam::findOrFail($entry['resit_exam_id']);
                }
                $letterGradeDetails = $this->determineResitLetterGrade(
                    $entry['score'],
                    $currentSchool,
                    $resitCandidate
                );

                $results[] = $this->updateResitScore(
                    $currentSchool,
                    $letterGradeDetails,
                    $entry
                );

                $caExamRecord = $this->findCaForExam($entry['exam_id']);
                if ($caExam === null) {
                    $caExam = $caExamRecord;
                }

                $newExamScores = $this->determineNewExamScores(
                    $currentSchool,
                    $letterGradeDetails['letter_grade'],
                    $entry['exam_id']
                );

                $newCaScores = $this->determineNewCaScores(
                    $currentSchool,
                    $letterGradeDetails['letter_grade'],
                    $caExamRecord->id
                );

                $caResults[] = $this->updateCaScore($entry, $newCaScores, $currentSchool, $caExamRecord);
                $examResults[] = $this->updateExamScore($entry, $newExamScores, $currentSchool, $exam);
                $this->updateStudentResitStatus($entry, $currentSchool, $letterGradeDetails, $exam);
            }
            $this->mergeStudentScores($resitCandidate, $caExam, $exam,  $caResults, $examResults);
            $caGpaAndExamGpa = $this->retriveCaAndExamGpa($exam, $caExam, $currentSchool, $resitCandidate);
            $newCaGpa = $this->calculateGpaAndTotalScore($caResults);
            $this->updateStudentResults($caResults, $caExam, $resitCandidate, $currentSchool, $newCaGpa);
            $newExamGpa = $this->calculateGpaAndTotalScore($examResults);
            $this->updateStudentResults($examResults, $exam, $resitCandidate, $currentSchool, $newExamGpa);
            $examStatus = $this->determineExamResultsStatus($results);
            $this->updateResitResults(
                $resitExam,
                $caGpaAndExamGpa,
                $results,
                $examStatus,
                $newCaGpa,
                $newExamGpa,
                $resitCandidate
            );
            DB::commit();
            return [
                'results' => $results,
                'resit_exam' => $resitExam,
                'exam' => $exam,
                'ca_results' => $caResults,
                'exam_results' => $examResults
            ];
        } catch (Exception $e) {
            Log::error('AddExamScoresService error', [
           'message' => $e->getMessage(),
           'file'    => $e->getFile(),
           'line'    => $e->getLine(),
           'trace'   => $e->getTraceAsString(),
            ]);
            DB::rollBack();
            throw $e;
        }
    }
    public function updateResitScore(object $currentSchool, array $letterGradeDetails, array $entry){
        $course = Courses::findOrFail($entry['course_id']);
        $resitMark = ResitMarks::where("school_branch_id", $currentSchool->id)
                                         ->findOrFail($entry['resit_mark_id']);
        $resitMark->score = $entry['score'];
        $resitMark->grade = $letterGradeDetails['letter_grade'] ?? null;
        $resitMark->grade_status = $letterGradeDetails['grade_status'] ?? null;
        $resitMark->grade_points = $letterGradeDetails['grade_points'] ?? null;
        $resitMark->gratification = $letterGradeDetails['gratification'] ?? null;
        $resitMark->save();

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
    public function updateResitResults($resitExam, array $gpaDetails, array $results, $examStatus, array $newCaGpa, array $newExamGpa, $resitCandidate)
    {
        $resitExams = ResitResults::where("school_branch_id", $resitExam->school_branch_id)
                                     ->where("resit_exam_id", $resitCandidate->resit_exam_id)
                                      ->where("student_id", $resitCandidate->student_id)
                                      ->first();
        $resitExams->former_exam_gpa = $gpaDetails['exam_results']->gpa ?? null;
        $resitExams->new_exam_gpa = $newExamGpa['gpa'] ?? null;
        $resitExams->new_ca_gpa = $newCaGpa['gpa'] ?? null;
        $resitExams->former_ca_gpa = $gpaDetails['ca_results']->gpa ?? null;
        $resitExams->student_id = $gpaDetails['ca_results']->student_id ?? null;
        $resitExams->school_branch_id = $resitExam->school_branch_id;
        $resitExams->specialty_id = $gpaDetails['ca_results']->specialty_id ?? null;
        $resitExams->level_id = $gpaDetails['ca_results']->level_id ?? null;
        $resitExams->resit_exam_id = $resitCandidate->resit_exam_id ?? null;
        $resitExams->student_batch_id = $gpaDetails['ca_results']->student_batch_id ?? null;
        $resitExams->exam_status = $examStatus['passed'] ? 'passed' : 'failed';
        $resitExams->score_details = json_encode($results);
        $resitExams->save();
    }
    public function  updateCaScore(array $entry, array $updatCaScoreData, object $currentSchool, object $caExam)
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
            'resit_status' => $updatCaScoreData['new_resit_status'] === 'failed' ? 'high_resit_potential' : 'no_resit',
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
            'resit_status' => $updatCaScoreData['new_resit_status'],
            'gratification' => $updatCaScoreData['new_gratification'],
        ];
    }
    public function updateExamScore(array $entry, array $updateExamScoreData, object $currentSchool, object $exam)
    {
        $mark = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("courses_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->where("level_id", $exam->level_id)
            ->where("exam_id", $exam->id)
            ->with(['course'])
            ->firstOrFail();

        $mark->update([
            'score' => $updateExamScoreData['new_score'],
            'grade_points' => $updateExamScoreData['new_grade_point'],
            'grade_status' => $updateExamScoreData['new_grade_status'],
            'grade' => $updateExamScoreData['new_letter_grade'],
            'resit_status' => $updateExamScoreData['new_resit_status'] === 'failed' ? 'resit' : 'no_resit',
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
            'resit_status' => $updateExamScoreData['new_resit_status'],
            'gratification' => $updateExamScoreData['new_gratification']
        ];
    }
    public function determineNewExamScores(object $currentSchool, string $resitLetterGrade, string $failedExamId)
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
            'new_score' => (float) $newExamScore,
            'new_grade_point' => (float) $examGrades->grade_points,
            'new_grade_status' => $examGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_resit_status' => $examGrades->resit_status,
            'new_gratification' => $examGrades->determinant,
        ];
    }
    public function determineNewCaScores(object $currentSchool, string $resitLetterGrade, string $failedCaId)
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
            'new_score' => (float) $newCaScore,
            'new_grade_point' => (float) $caGrades->grade_points,
            'new_grade_status' =>  $caGrades->grade_status,
            'new_letter_grade' => $resitLetterGrade,
            'new_resit_status' => $caGrades->resit_status,
            'new_gratification' => $caGrades->determinant,
        ];
    }
    public function determineResitLetterGrade(float $score, object $currentSchool, object $candidate)
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
            'grade_status' => 'failed',
            'gratification' => 'poor',
            'grade_points' => 0,
            'score' => $score,
        ];
    }
    public function findCaForExam(string $examId)
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
    public function retriveCaAndExamGpa(object $exam, object $caExam, object $currentSchool, object $resitCandidate)
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
    private function updateStudentResitStatus(array $entry, object $currentSchool, array $resitLetterGrade, object $exam): ?object
    {
        $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $entry['student_id'])
            ->where("course_id", $entry['course_id'])
            ->where("specialty_id", $entry['specialty_id'])
            ->first();
        if($resitLetterGrade['grade_status'] === "passed"){
            if($studentResit){
                $studentResit->delete();
            }
        }
        if($resitLetterGrade['grade_status'] === "failed"){
            if($studentResit){
                $studentResit->increment('attempt_number');
                $studentResit->iscarry_over = true;
                $studentResit->paid_status = 'unpaid';
                $studentResit->save();
                return $studentResit;
            }
            else{
                StudentResit::create([
                   'iscarry_over' => true,
                   'attempt_number' => 1,
                   'resit_fee' => $currentSchool->resit_fee,
                   'school_branch_id' => $currentSchool->id,
                   'specialty_id' => $entry['specialty_id'],
                   'course_id' => $entry['course_id'],
                   'exam_id' => $exam->id,
                   'level_id' => $exam->level_id,
                   'student_id' => $entry['student_id'],
                   'student_batch_id' => $exam->student_batch_id
                ]);
            }
        }

        return null;
    }
    public function calculateGpaAndTotalScore($results)
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
            'gpa' => round($gpa, 2),
        ];
    }
    public function updateStudentResults(array $results, ?object $examDetails, object $resitCandidate, object $currentSchool, array $gpaAndTotalScores)
    {
        $failedCourses = collect($results)->filter(function ($mark) {
            return ($mark['grade_status'] ?? ($mark['grade_status'] ?? '')) === 'failed';
        });
        if ($examDetails) {
            $updatedResults = StudentResults::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $examDetails->specialty_id)
                ->where("exam_id", $examDetails->id)
                ->where("student_id", $resitCandidate->student_id)
                ->first();

            if ($updatedResults) {
                $updatedResults->gpa = $gpaAndTotalScores['gpa'];
                $updatedResults->exam_status = empty($failedCourses) ? "passed": "failed";
                $updatedResults->total_score = $gpaAndTotalScores['totalScore'];
                $updatedResults->score_details = json_encode($results);
                $updatedResults->save();
            }
        }
    }
    private function determineExamResultsStatus(array $results): array
    {
        $failedCourses = collect($results)->filter(function ($mark) {
            return ($mark['grade_status'] ?? ($mark['grade_status'] ?? '')) === 'failed';
        });

        return [
            'exam_status' => $failedCourses->isEmpty() ? 'Passed' : 'Failed',
            'passed' => $failedCourses->isEmpty(),
            'failed' => !$failedCourses->isEmpty(),
        ];
    }
}
