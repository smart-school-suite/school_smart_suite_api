<?php

namespace App\Services\ResitExamEvaluation;

use App\Exceptions\AppException;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\ResitCandidates;
use App\Models\ResitMarks;
use App\Models\Studentresit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddResitScoreService
{
    public function addStudentResitScores(array $payload, object $currentSchool): array
    {
        $scores = $payload["scores"] ?? [];
        $candidateId = $payload["candidate_id"] ?? null;

        if (empty($scores)) {
            throw new AppException(
                'No scores submitted.',
                400,
                'No Scores',
                'Please provide at least one course score to submit.',
                '/scores/exam'
            );
        }

        return DB::transaction(function () use ($payload, $scores, $candidateId, $currentSchool) {
            $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
                ->with([
                    'resitExam.examType',
                    'resitExam.examGradeScale.schoolGradeScale.grade',
                    'resitExam.schoolYear.schoolSemester.semester',
                    'resitExam.schoolYear.specialty',
                    'resitScores',
                    'student'
                ])
                ->find($candidateId);

            if (!$candidate) {
                throw new AppException(
                    "The selected candidate could not be found.",
                    404,
                    "Candidate Not Found",
                    "Please verify that the selected candidate exists or has not been removed.",
                    "/candidates"
                );
            }

            if ($candidate->resitScores->isNotEmpty()) {
                throw new AppException(
                    "The Exam Candidate Already Assessed.",
                    400,
                    "Exam Candidate Already Assessed",
                    "The Exam Candidate Has Already Been Assessed. You Cannot Submit Scores Again.",
                    "/accessed-students"
                );
            }

            $resitExam = $candidate->resitExam;

            if (!$resitExam || !$resitExam->examType) {
                throw new AppException(
                    "Exam configuration or type missing.",
                    400,
                    "Invalid Exam Configuration",
                    "The exam or exam type assigned to this candidate is invalid.",
                    "/exams"
                );
            }

            $gradeScales = $resitExam->examGradeScale?->schoolGradeScale;

            if (!$gradeScales || $gradeScales->isEmpty()) {
                throw new AppException(
                    'Exam Grade Scale Not Configured',
                    404,
                    'Grade Scale Not Configured',
                    'The grade scale has been set for this exam, but has not been configured yet.',
                    "/grades-categories"
                );
            }

            $resitIds = collect($scores)->pluck("resit_id")->filter()->toArray();

            // Use withTrashed() so lookups succeed even if the record was previously soft-deleted
            $resits = Studentresit::withTrashed()
                ->where("school_branch_id", $currentSchool->id)
                ->whereIn("id", $resitIds)
                ->with([
                    "courses",
                    "exam.examGradeScale.schoolGradeScale.grade",
                    "exam.examCandidate"
                ])
                ->get()
                ->keyBy('id');

            $now = now();
            $processedScores = [];

            foreach ($scores as $scoreItem) {
                $resitId = $scoreItem["resit_id"];
                $resitScoreValue = (float) $scoreItem["score"];

                $resit = $resits->get($resitId);

                if (!$resit) {
                    throw new AppException(
                        'Resit Record Not Found',
                        404,
                        'Invalid Resit Identification',
                        "The resit entry with ID {$resitId} could not be found.",
                        "/resits"
                    );
                }

                $this->validateScore($resitExam, $resitScoreValue);

                $resitGrade = $this->determineGrade($resitScoreValue, $gradeScales);

                if (!$resitGrade) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Grade Not Matched',
                        "No grade scale range matches the submitted score of {$resitScoreValue}.",
                        "/grades-categories"
                    );
                }

                Log::debug("Resit Marks Payload", [
                    "school_branch_id" => $currentSchool->id,
                    "candidate_id" => $candidate->id,
                    "resit_id" => $resitId,
                    "resit_exam_id" => $resitExam->id,
                    "grade_id" => $resitGrade->id,
                    "score" => $resitScoreValue,
                    "created_at" => $now,
                    "updated_at" => $now,
                ]);

                $resitMark = ResitMarks::create([
                    "school_branch_id" => $currentSchool->id,
                    "candidate_id" => $candidate->id,
                    "resit_id" => $resitId,
                    "resit_exam_id" => $resitExam->id,
                    "grade_id" => $resitGrade->id,
                    "score" => $resitScoreValue,
                    "created_at" => $now,
                    "updated_at" => $now,
                ]);

                if ($resit->exam) {
                    $relatedCa = $this->getRelatedCa($resit->exam, $currentSchool->id);

                    $newCaScale = $this->determineNewScale(
                        $relatedCa->examGradeScale->schoolGradeScale,
                        $resitGrade->grade->letter_grade
                    );

                    $newExamScale = $this->determineNewScale(
                        $resit->exam->examGradeScale->schoolGradeScale,
                        $resitGrade->grade->letter_grade
                    );

                    if ($newCaScale) {
                        $this->updateScoreIfBetter(
                            $relatedCa->id,
                            $candidate->student_id,
                            $resit->course_id,
                            $newCaScale,
                            $currentSchool->id
                        );
                    }

                    if ($newExamScale) {
                        $this->updateScoreIfBetter(
                            $resit->exam->id,
                            $candidate->student_id,
                            $resit->course_id,
                            $newExamScale,
                            $currentSchool->id
                        );
                    }
                }

                $isPassed = strtolower($resitGrade->result ?? '') === 'passed'
                    || strtolower($resitGrade->status ?? '') === 'passed';

                if ($isPassed) {
                    // Soft deletes the record (populates deleted_at column), keeping resit_id foreign key valid in resit_marks
                    $resit->delete();
                } else {
                    $resit->update([
                        'payment_status' => 'unpaid',
                        'updated_at' => $now,
                    ]);
                }

                $processedScores[] = $resitMark;
            }

            return [
                'message' => 'Resit scores submitted successfully.',
                'candidate_id' => $candidate->id,
                'total_processed' => count($processedScores),
            ];
        });
    }

    private function updateScoreIfBetter(
        string $examId,
        string $studentId,
        string $courseId,
        object $newGradeScale,
        string $schoolBranchId
    ): void {
        $existingScore = ExamScore::where("school_branch_id", $schoolBranchId)
            ->where('exam_id', $examId)
            ->where("course_id", $courseId)
            ->whereHas('candidate', function ($query) use ($studentId, $schoolBranchId) {
                $query->where("school_branch_id", $schoolBranchId)
                    ->where("student_id", $studentId);
            })
            ->first();

        if (!$existingScore) {
            return;
        }

        $existingScale = $existingScore->gradeScale;

        $isBetter = false;

        if (!$existingScale) {
            $isBetter = true;
        } else {
            if (
                $newGradeScale->minimum_score > $existingScale->minimum_score ||
                $newGradeScale->maximum_score > $existingScale->maximum_score
            ) {
                $isBetter = true;
            }
        }

        if ($isBetter) {
            $existingScore->update([
                'grade_id' => $newGradeScale->id,
                'updated_at' => now(),
            ]);
        }
    }

    private function getRelatedCa(Exam $exam, string $schoolBranchId): Exam
    {
        if ($exam->examType?->type !== 'exam') {
            throw new AppException(
                'Invalid Exam Type',
                400,
                'Exam Type Error',
                'The selected evaluation is not a final exam.',
                '/exams'
            );
        }

        $caExamType = Examtype::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new AppException(
                'No Continuous Assessment Type Found',
                404,
                'Continuous Assessment Not Configured',
                'No Continuous Assessment type is set up for this semester.',
                '/exam-types'
            );
        }

        $relatedCaExam = Exam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $caExamType->id)
            ->where('school_year_id', $exam->school_year_id)
            ->with(['examGradeScale.schoolGradeScale.grade', "examType"])
            ->first();

        if (!$relatedCaExam) {
            throw new AppException(
                'No Continuous Assessment Found',
                404,
                'Continuous Assessment Missing',
                'No corresponding Continuous Assessment exam exists for this academic year.',
                '/exams'
            );
        }

        return $relatedCaExam;
    }

    private function determineNewScale(Collection $gradeScales, string $resitLetterGrade): ?object
    {
        return $gradeScales->firstWhere(fn($gs) => $gs->grade?->letter_grade === $resitLetterGrade);
    }

    private function validateScore(object $exam, float $score): void
    {
        if ($score < 0 || ($exam->max_score !== null && $score > $exam->max_score)) {
            throw new AppException(
                'Score exceeds maximum exam mark.',
                400,
                'Validation Error',
                "The entered score of {$score} is invalid. The maximum allowed mark is {$exam->max_score}.",
                "/exam"
            );
        }
    }

    private function determineGrade(float $score, Collection $gradeScales): ?object
    {
        foreach ($gradeScales as $gradeScale) {
            if ($score >= $gradeScale->minimum_score && $score <= $gradeScale->maximum_score) {
                return $gradeScale;
            }
        }

        return null;
    }
}
