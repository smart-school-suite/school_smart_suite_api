<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'exam_candidates_exam_id_student_id_unique';

    /**
     * A student is a candidate of an exam at most once.
     *
     * CreateExamCandidateJob checked for existing candidates before inserting,
     * so two runs at the same time (a retry, or a second dispatch) could both
     * insert. Duplicates are merged first:
     *  - the candidate kept is the one with the most exam scores, then the
     *    oldest;
     *  - the other candidates' scores move to it. Where both scored the same
     *    course, the most recently updated score is kept and the conflict is
     *    logged;
     *  - the other candidates are deleted.
     * Then a unique index on (exam_id, student_id) makes it impossible again.
     *
     * Safe to run on any database: it only touches duplicated pairs.
     */
    public function up(): void
    {
        if (!Schema::hasTable('exam_candidates')) {
            return;
        }

        $duplicates = DB::table('exam_candidates')
            ->select('exam_id', 'student_id')
            ->whereNotNull('exam_id')
            ->whereNotNull('student_id')
            ->groupBy('exam_id', 'student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $pair) {
            DB::transaction(fn() => $this->merge($pair->exam_id, $pair->student_id));
        }

        if (!$this->hasIndex()) {
            Schema::table('exam_candidates', function (Blueprint $table) {
                $table->unique(['exam_id', 'student_id'], self::INDEX);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_candidates') && $this->hasIndex()) {
            Schema::table('exam_candidates', function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        }
    }

    private function merge(string $examId, string $studentId): void
    {
        $candidates = DB::table('exam_candidates')
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->get()
            ->map(function ($candidate) {
                $candidate->scores = DB::table('exam_scores')->where('candidate_id', $candidate->id)->count();
                return $candidate;
            })
            ->sort(fn($a, $b) => [$b->scores, $a->created_at ?? '', $a->id] <=> [$a->scores, $b->created_at ?? '', $b->id])
            ->values();

        $keep = $candidates->first();
        $others = $candidates->slice(1)->pluck('id')->all();

        $scores = DB::table('exam_scores')
            ->whereIn('candidate_id', $candidates->pluck('id'))
            ->get()
            ->groupBy('course_id');

        foreach ($scores as $courseId => $courseScores) {
            // The latest edit wins; on a tie, the kept candidate's score.
            $winner = $courseScores
                ->sort(fn($a, $b) => [$b->updated_at ?? '', $b->candidate_id === $keep->id] <=> [$a->updated_at ?? '', $a->candidate_id === $keep->id])
                ->first();
            $losers = $courseScores->where('id', '!=', $winner->id);

            if ($losers->pluck('score')->map(fn($score) => (float) $score)->contains(fn($score) => $score !== (float) $winner->score)) {
                Log::warning('Merged duplicate exam candidates had different scores for a course; kept the latest.', [
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'kept_score_id' => $winner->id,
                    'kept_score' => $winner->score,
                    'removed_scores' => $losers->map(fn($score) => ['id' => $score->id, 'score' => $score->score])->values()->all(),
                ]);
            }

            if ($losers->isNotEmpty()) {
                DB::table('exam_scores')->whereIn('id', $losers->pluck('id'))->delete();
            }
            if ($winner->candidate_id !== $keep->id) {
                DB::table('exam_scores')->where('id', $winner->id)->update(['candidate_id' => $keep->id]);
            }
        }

        DB::table('exam_candidates')->whereIn('id', $others)->delete();
    }

    private function hasIndex(): bool
    {
        return collect(Schema::getIndexes('exam_candidates'))
            ->contains(fn($index) => $index['name'] === self::INDEX);
    }
};
