<?php

namespace App\Http\Controllers\ReportAnalytics;

use App\Http\Controllers\Controller;
use App\Models\Reportcard;
use Illuminate\Http\Request;

class StudentperformancereportController extends Controller
{
    //
    public function student_gpa_analysis(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route('student_id');

        $student_records_data = Reportcard::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student_id)
            ->get();
        if ($student_records_data->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No student data found for this student',
            ], 400);
        }

        $totalGPA = 0;
        $totalScore = 0;
        $allTimeHighGPA = PHP_INT_MIN;
        $allTimeLowGPA = PHP_INT_MAX;
        $gpaChanges = [];

        foreach ($student_records_data as $record) {
            $gpa = $record->gpa;
            $totalGPA += $gpa;
            $totalScore += $record->total_score;

            // Track all-time high and low GPA
            if ($gpa > $allTimeHighGPA) {
                $allTimeHighGPA = $gpa;
            }

            if ($gpa < $allTimeLowGPA) {
                $allTimeLowGPA = $gpa;
            }

            // Store changes for calculating percentage changes later
            $gpaChanges[] = $gpa;
        }

        // Calculate average GPA
        $averageGPA = $totalGPA / count($student_records_data);

        // Calculate the percentage change from the previous GPA if at least 2 records exist
        $previousGPA = count($gpaChanges) > 1 ? $gpaChanges[count($gpaChanges) - 2] : null;
        $percentageChange = $previousGPA ? (($averageGPA - $previousGPA) / $previousGPA) * 100 : null;

        // Calculate percentage increase from all-time low
        $percentageIncreaseFromLow = $allTimeLowGPA ? (($averageGPA - $allTimeLowGPA) / $allTimeLowGPA) * 100 : null;

        // Calculate GPA variance
        $gpaVariance = $this->calculateVariance($gpaChanges);

        // Track GPA improvement
        $improvementStatus = $this->trackGPAImprovement($gpaChanges);

        // Check for GPA drop warning
        $gpaDropWarning = $this->checkGpaDropWarning($gpaChanges);

        $response = [
            'student_id' => $student_id,
            'average_gpa' => round($averageGPA, 2),
            'percentage_change_from_previous' => $percentageChange,
            'all_time_high_gpa' => $allTimeHighGPA,
            'all_time_low_gpa' => $allTimeLowGPA,
            'percentage_increase_from_all_time_low' => $percentageIncreaseFromLow,
            'total_score' => $totalScore,
            'gpa_variance' => round($gpaVariance, 2),
            'gpa_improvement_status' => $improvementStatus,
            'gpa_drop_warning' => $gpaDropWarning,
            'records' => $student_records_data,
        ];

        return response()->json($response);
    }


    private function calculateVariance(array $gpaChanges)
    {
        if (count($gpaChanges) < 2) return 0;

        $mean = array_sum($gpaChanges) / count($gpaChanges);
        $variance = array_reduce($gpaChanges, function ($carry, $gpa) use ($mean) {
            return $carry + pow($gpa - $mean, 2);
        }, 0) / (count($gpaChanges) - 1); // Use N-1 for sample variance

        return $variance;
    }

    private function trackGPAImprovement(array $gpaChanges)
    {
        if (count($gpaChanges) < 2) return 'N/A';

        $latestGPA = end($gpaChanges);
        $previousGPA = $gpaChanges[count($gpaChanges) - 2];

        if ($latestGPA > $previousGPA) {
            return 'Improved';
        } elseif ($latestGPA < $previousGPA) {
            return 'Declined';
        }

        return 'Stable';
    }

    private function checkGpaDropWarning(array $gpaChanges)
    {
        if (count($gpaChanges) < 2) return false; // Not enough data

        $latestGPA = end($gpaChanges);
        $previousGPA = $gpaChanges[count($gpaChanges) - 2];

        return ($previousGPA - $latestGPA) >= 0.5; // Warning if the drop is 0.5 or more
    }

}
