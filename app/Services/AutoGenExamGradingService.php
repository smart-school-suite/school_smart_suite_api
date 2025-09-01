<?php

namespace App\Services;

use App\Models\LetterGrade;

class AutoGenExamGradingService
{
    public function autoGenerateExamGrading($data)
    {
        $maxScore = $data['max_score'];
        $examType = $data['exam_type'];
        $letterGrades = LetterGrade::all(); // letter_grade, id
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

        // Standard academic grade ranges (mapped to percentages, adjusted to maxScore)
        $gradeRanges = [
            'A+' => ['min' => 97, 'max' => 100, 'points' => 4.00],
            'A'  => ['min' => 93, 'max' => 96.99, 'points' => 4.00],
            'A-' => ['min' => 90, 'max' => 92.99, 'points' => 3.67],
            'B+' => ['min' => 87, 'max' => 89.99, 'points' => 3.33],
            'B'  => ['min' => 83, 'max' => 86.99, 'points' => 3.00],
            'B-' => ['min' => 80, 'max' => 82.99, 'points' => 2.67],
            'C+' => ['min' => 77, 'max' => 79.99, 'points' => 2.33],
            'C'  => ['min' => 73, 'max' => 76.99, 'points' => 2.00],
            'C-' => ['min' => 70, 'max' => 72.99, 'points' => 1.67],
            'D+' => ['min' => 67, 'max' => 69.99, 'points' => 1.33],
            'D'  => ['min' => 63, 'max' => 66.99, 'points' => 1.00],
            'D-' => ['min' => 60, 'max' => 62.99, 'points' => 0.67],
            'F'  => ['min' => 0,  'max' => 59.99, 'points' => 0.00],
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
            $currentGradeStatus = ($minScore >= ($maxScore * 0.6)) ? $gradeStatus[0] : $gradeStatus[1];

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
