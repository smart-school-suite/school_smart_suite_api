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
                    'resit_status' => $grade['resit_status'],
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
        DB::beginTransaction();

        try {

            $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
            $targetGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($targetConfigId);


            if (!$schoolGradesConfig || !$targetGradesConfig) {
                throw new Exception("School Grades Configurations not found", 404);
            }


            $grades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                ->get();


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


            $targetGradesConfig->isgrades_configured = true;
            $targetGradesConfig->max_score = $schoolGradesConfig->max_score;
            $targetGradesConfig->save();

            DB::commit();

            return $insertedGrades;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getGradeConfigDetails($currentSchool, $configId){
       try{
         $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
        if(!$schoolGradesConfig){
            throw new Exception("School Grades Configuration Not Found", 404);
        }
        $grades = Grades::where("school_branch_id", $currentSchool->id)
                        ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                        ->get();
        return $grades;
       } catch(Exception $e){
          throw $e;
       }
    }

    public function deleteGradesConfig($currentSchool, $configId){
         try{
             DB::beginTransaction();
             $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
        if(!$schoolGradesConfig){
            throw new Exception("School Grades Configuration Not Found", 404);
        }
        $grades = Grades::where("school_branch_id", $currentSchool->id)
                        ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                        ->get();
         foreach($grades as $grade){
            $grade->delete();
        }
        $schoolGradesConfig->isgrades_configured = false;
        $schoolGradesConfig->max_score = null;
        $schoolGradesConfig->save();
        DB::commit();
         } catch(Exception $e){
            throw $e;
         }
    }
}
