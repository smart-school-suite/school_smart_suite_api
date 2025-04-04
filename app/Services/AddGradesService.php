<?php

namespace App\Services;
use App\Models\Grades;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Facades\DB;
use Exception;
class AddGradesService
{
    public function makeGradeForExam(array $grades, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $insertedGrades = [];

            foreach ($grades as $grade) {
                $newGrade = Grades::create([
                    'school_branch_id' => $currentSchool->id,
                    'letter_grade_id' => $grade['letter_grade_id'],
                    'grade_points' => $grade['grade_points'],
                    'minimum_score' => $grade['minimum_score'],
                    'maximum_score' => $grade['maximum_score'],
                    'grade_status' => $grade['grade_status'],
                    'determinant' => $grade['determinant'],
                    'grades_category_id' => $grade['grades_category_id'],
                ]);

                $insertedGrades[] = $newGrade;
            }
            $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $grades[0]['grades_category_id']) // Corrected
                ->first();

            if ($schoolGradesConfig) {
                $schoolGradesConfig->isgrades_configured = true;
                $schoolGradesConfig->max_score = $grades[0]['max_score'];
                $schoolGradesConfig->save();
            }
            DB::commit();

            return $insertedGrades;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function configureByOtherGrades($configId, $currentSchool, $targetConfigId)
{
    $insertedGrades = [];
    //targetConfigId is the grades to be configured
    //configId is the grades to be used
    // Begin a database transaction
    DB::beginTransaction();

    try {
        // Fetching the source and target grade configurations
        $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
        $targetGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($targetConfigId);

        // Check if the source config exists and the target config does not
        if (!$schoolGradesConfig || !$targetGradesConfig) {
            return ApiResponseService::error("School Grades Configurations not found", null, 404);
        }

        // Fetching grades from the source configuration
        $grades = Grades::where("school_branch_id", $currentSchool->id)
                        ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                        ->get();

        // Creating new grades based on the fetched grades
        foreach ($grades as $grade) {
            $newGrade = Grades::create([
                'school_branch_id' => $currentSchool->id,
                'letter_grade_id' => $grade->letter_grade_id,
                'grade_points' => $grade->grade_points,
                'minimum_score' => $grade->minimum_score,
                'maximum_score' => $grade->maximum_score,
                'grade_status' => $grade->grade_status,
                'determinant' => $grade->determinant,
                'grades_category_id' => $targetGradesConfig->grades_category_id,
            ]);

            $insertedGrades[] = $newGrade;
        }

        // Updating the target configuration after successful insertion
        $targetGradesConfig->isgrades_configured = true;
        $targetGradesConfig->max_score = $schoolGradesConfig->max_score;
        $targetGradesConfig->save();

        // Commit the transaction
        DB::commit();

        // Return the newly created grades
        return $insertedGrades;

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        DB::rollBack();
        throw $e;
    }
}
}
