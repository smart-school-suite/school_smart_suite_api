<?php

namespace App\Services\ExamEvaluation;

use App\Models\Exam\ExamCandidate;
use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use App\Models\StudentResults;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A candidate's overall result in a CA or exam, against the school's
 * "Minimum Passing GPA" (grade.passing_gpa).
 *
 *  - GPA: course grade points weighted by course credit (a course without
 *    a credit counts once), on the exam's own grade scale.
 *  - Result: passed when the GPA reaches the passing GPA, failed below it.
 *    Failing a course still creates its resit; the passing GPA decides the
 *    candidate's overall result.
 *
 * The result is stored in student_results (gpa, total_score, exam_status,
 * score_details) whenever scores are added or updated, and removed with
 * the scores. Stats and the exam update rules read it from there.
 */
class CandidateResultService
{
    public const PASSING_GPA = 'grade.passing_gpa';

    /** The candidate's result from their recorded scores, without storing it. */
    public function evaluate(ExamCandidate $candidate): array
    {
        $candidate->loadMissing(['examScores.grade.grade', 'examScores.course', 'exam']);
        $passingGpa = $this->passingGpa($candidate->school_branch_id);

        return self::summarise($candidate->examScores, $passingGpa);
    }

    /** Evaluates the candidate and stores the result; returns the summary. */
    public function record(ExamCandidate $candidate): array
    {
        $candidate->unsetRelation('examScores');
        $candidate->loadMissing(['student', 'exam.schoolYear.specialty']);
        $summary = $this->evaluate($candidate);

        if ($summary['courses'] === 0) {
            $this->forget($candidate);
            return $summary;
        }

        $student = $candidate->student;
        $specialty = $candidate->exam?->schoolYear?->specialty;
        if (!$student?->student_batch_id || !$specialty?->level_id) {
            // student_results needs the batch and level; the summary is still returned.
            Log::warning('Candidate result not stored: missing student batch or level.', ['candidate_id' => $candidate->id]);
            return $summary;
        }

        $existing = StudentResults::where('school_branch_id', $candidate->school_branch_id)
            ->where('student_id', $candidate->student_id)
            ->where('exam_id', $candidate->exam_id)
            ->first();

        $values = [
            'gpa' => $summary['gpa'],
            'total_score' => $summary['total_score'],
            'exam_status' => $summary['result'],
            'score_details' => json_encode($summary['score_details']),
            'specialty_id' => $specialty->id,
            'level_id' => $specialty->level_id,
            'student_batch_id' => $student->student_batch_id,
        ];

        if ($existing) {
            $existing->update($values);
        } else {
            $result = new StudentResults($values + [
                'school_branch_id' => $candidate->school_branch_id,
                'student_id' => $candidate->student_id,
                'exam_id' => $candidate->exam_id,
            ]);
            $result->id = (string) Str::uuid();
            $result->save();
        }

        return $summary;
    }

    /** Removes the candidate's stored result, e.g. when their scores are deleted. */
    public function forget(ExamCandidate $candidate): void
    {
        StudentResults::where('school_branch_id', $candidate->school_branch_id)
            ->where('student_id', $candidate->student_id)
            ->where('exam_id', $candidate->exam_id)
            ->delete();
    }

    /**
     * { gpa, passing_gpa, result, gpa_shortfall, courses, courses_passed,
     *   courses_failed, total_credits, total_score, score_details }
     */
    public static function summarise(Collection $scores, float $passingGpa): array
    {
        $weighted = 0.0;
        $credits = 0.0;
        $details = [];

        foreach ($scores as $score) {
            $credit = (float) ($score->course?->credit ?? 0);
            $credit = $credit > 0 ? $credit : 1.0;
            $points = (float) ($score->grade?->grade_points ?? 0);
            $weighted += $points * $credit;
            $credits += $credit;

            $details[] = [
                'course_id' => $score->course_id,
                'course_code' => $score->course?->course_code,
                'credit' => $credit,
                'score' => (float) $score->score,
                'grade_points' => $points,
                'letter_grade' => $score->grade?->grade?->letter_grade,
                'result' => $score->grade?->result,
            ];
        }

        $gpa = $credits > 0 ? round($weighted / $credits, 2) : 0.0;
        $results = collect($details)->pluck('result')->map(fn($result) => strtolower((string) $result));
        $passed = $gpa >= $passingGpa;

        return [
            'gpa' => $gpa,
            'passing_gpa' => $passingGpa,
            'result' => $passed ? 'passed' : 'failed',
            'gpa_shortfall' => $passed ? 0.0 : round($passingGpa - $gpa, 2),
            'courses' => count($details),
            'courses_passed' => $results->filter(fn($result) => in_array($result, ['passed', 'pass'], true))->count(),
            'courses_failed' => $results->filter(fn($result) => in_array($result, ['failed', 'fail'], true))->count(),
            'total_credits' => $credits,
            'total_score' => round(collect($details)->sum('score'), 2),
            'score_details' => $details,
        ];
    }

    /** The summary without the per-course details, for API responses. */
    public static function withoutDetails(array $summary): array
    {
        unset($summary['score_details']);
        return $summary;
    }

    /** grade.passing_gpa for the branch, else the setting's default, else 2.00. */
    public function passingGpa(string $schoolBranchId): float
    {
        $value = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', self::PASSING_GPA))
            ->first()?->value;

        if (!is_numeric($value)) {
            $default = SettingDefination::where('key', self::PASSING_GPA)->value('default_value');
            $value = is_string($default) && json_validate($default) ? json_decode($default, true) : $default;
        }

        return is_numeric($value) ? round((float) $value, 2) : 2.0;
    }
}
