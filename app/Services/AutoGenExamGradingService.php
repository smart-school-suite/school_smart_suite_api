<?php

namespace App\Services;

use App\Models\LetterGrade;

class AutoGenExamGradingService
{
    public function autoGenerateExamGrading($data)
    {
        $maxScore = $data['max_score'];
        $examType = $data['exam_type'];
        $letterGrades = LetterGrade::all();
        $passRemarks = [
            "Excellent",
            "Great",
            "Good",
            "Well Done",
            "Satisfactory",
            "Pass",
            "Nice",
            "Solid",
            "Adequate",
            "Competent"
        ];
        $failRemarks = [
            "Poor",
            "Unsatisfactory",
            "Fail",
            "Needs Improvement",
            "Weak",
            "Inadequate",
            "Unacceptable",
            "Below Par",
            "Unsatisfactory",
            "Reconsider"
        ];
        $caResitStatus = ['high_resit_potential', 'low_resit_potential'];
        $examResitStatus = ['resit', 'no_resit'];
        $gradeStatus = ['passed', 'failed'];

        $result = [];
        if ($letterGrades->isEmpty()) {
            return $result; // Return empty array if no letter grades are available
        }


        $gradeRanges = [
            'A+' => ['min' => 90, 'max' => 100, 'points' => 4.00],
            'A'  => ['min' => 85, 'max' => 89.99, 'points' => 3.90],
            'A-' => ['min' => 80, 'max' => 84.99, 'points' => 3.70],

            'B+' => ['min' => 75, 'max' => 79.99, 'points' => 3.30],
            'B'  => ['min' => 70, 'max' => 74.99, 'points' => 3.00],
            'B-' => ['min' => 65, 'max' => 69.99, 'points' => 2.70],

            'C+' => ['min' => 60, 'max' => 64.99, 'points' => 2.30],
            'C'  => ['min' => 55, 'max' => 59.99, 'points' => 2.00],
            'C-' => ['min' => 50, 'max' => 54.99, 'points' => 1.70],

            'D+' => ['min' => 45, 'max' => 49.99, 'points' => 1.30],
            'D'  => ['min' => 40, 'max' => 44.99, 'points' => 1.00],
            'D-' => ['min' => 35, 'max' => 39.99, 'points' => 0.70],

            'F'  => ['min' => 0,  'max' => 34.99, 'points' => 0.00],
        ];


        foreach ($letterGrades as $letterGrade) {
            $grade = $letterGrade->letter_grade;
            if (!isset($gradeRanges[$grade])) {
                continue; // Skip if the letter grade isn't in the defined ranges
            }

            // Scale the minimum and maximum scores to the provided maxScore
            $minScore = round(($gradeRanges[$grade]['min'] / 100) * $maxScore);
            $maxScoreForGrade = round(($gradeRanges[$grade]['max'] / 100) * $maxScore);

            // Ensure the highest grade (A+) reaches the maxScore
            if ($grade === 'A+') {
                $maxScoreForGrade = $maxScore;
            }
            // Ensure the lowest grade (F) starts at 0
            if ($grade === 'F') {
                $minScore = 0;
            }

            // Determine grade status (passed/failed, assuming 60% is the passing threshold)
            $currentGradeStatus = ($minScore >= ($maxScore * 0.5)) ? $gradeStatus[0] : $gradeStatus[1];

            // Determine resit status based on exam type
            $resitStatus = ($examType === 'ca')
                ? ($currentGradeStatus === 'failed' ? $caResitStatus[0] : $caResitStatus[1])
                : ($currentGradeStatus === 'failed' ? $examResitStatus[0] : $examResitStatus[1]);

            // Assign remarks based on grade status
            $determinant = ($currentGradeStatus === 'passed')
                ? $passRemarks[array_rand($passRemarks)]
                : $failRemarks[array_rand($failRemarks)];

            // Get grade points from the predefined ranges
            $gradePoints = $gradeRanges[$grade]['points'];

            $result[] = [
                'letter_grade_id' => $letterGrade->id,
                'letter_grade' => $letterGrade->letter_grade,
                'grade_points' => $gradePoints,
                'minimum_score' => $minScore,
                'grade_status' => $currentGradeStatus,
                'resit_status' => $resitStatus,
                'maximum_score' => $maxScoreForGrade,
                'determinant' => $determinant
            ];
        }

        // Sort results by minimum_score in descending order
        usort($result, function ($a, $b) {
            return $b['minimum_score'] <=> $a['minimum_score'];
        });

        return $result;
    }
}
