<?php

namespace App\Services\Stats;

use App\Models\StudentResults;
use App\Models\Exams;
use App\Models\Schoolbranches;
use Illuminate\Support\Collection;
class StudentExamStandingStats
{
    /**
     * Retrieves student standings for a specific exam, ordered from top to bottom.
     * Students with the same GPA and total score will share the same rank.
     *
     * @param string $examId The exam object.
     * @param Schoolbranches $currentSchool The current school object.
     * @return \Illuminate\Support\Collection A collection of arrays, each containing
     * 'total_score', 'position', 'student_name', and 'gpa'.
     */
    public function getStudentStandingsByExam(string $examId, Schoolbranches $currentSchool): Collection
    {
        $exam = Exams::find($examId);
        $studentResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam->id)
            ->where("level_id", $exam->level_id)
            ->where("specialty_id", $exam->specialty_id)
            ->with(['student'])
            ->get();

        if ($studentResults->isEmpty()) {
            return new Collection();
        }

        $sortedResults = $studentResults->sortByDesc(function ($result) {

            return $result->gpa . sprintf('%05d', $result->total_score);
        })->values();


        $rankedResults = new Collection();
        $currentRank = 1;
        $previousGpa = null;
        $previousTotalScore = null;

        foreach ($sortedResults as $index => $result) {
            if ($result->gpa === $previousGpa && $result->total_score === $previousTotalScore) {
                $result->rank = $currentRank;
            } else {
                $currentRank = $index + 1;
                $result->rank = $currentRank;
            }
            $previousGpa = $result->gpa;
            $previousTotalScore = $result->total_score;
            $rankedResults->push($result);
        }

        return $rankedResults->map(function ($result) {
            return [
                'total_score' => $result->total_score,
                'position' => $result->rank,
                'student_name' => $result->student->name ?? 'N/A',
                'gpa' => $result->gpa,
            ];
        });
    }
}
