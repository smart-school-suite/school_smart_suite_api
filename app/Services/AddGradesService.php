<?php

namespace App\Services;

use App\Models\Grades;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
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
                ->where("grades_category_id", $grades[0]['grades_category_id'])
                ->first();

            if(!$schoolGradesConfig){
               throw new AppException(
                "School Grades Configuration Not Found",
                404,
                "Configuration Not Found",
                "The specified school grades configuration could not be found. Please ensure the configuration exists before adding grades.",
                null
               );
            }

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
    public function bulkCreateExamGrades(array $data, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $gradesToInsert = [];
            $configsToUpdate = [];
            $grades = collect($data['grades'])->groupBy('grades_category_id');
            $configIds = collect($data['configIds'])->pluck('grade_config_id')->toArray();

            $existingConfigs = SchoolGradesConfig::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $configIds)
                ->get()
                ->keyBy('id');

            foreach ($configIds as $configId) {
                $schoolGradesConfig = $existingConfigs->get($configId);

                if ($schoolGradesConfig && !$schoolGradesConfig->isgrades_configured) {
                    $gradesCategoryId = $schoolGradesConfig->grades_category_id;
                    $gradesForCategory = $grades->get($gradesCategoryId, []);

                    if (!empty($gradesForCategory)) {
                        $maxScore = $gradesForCategory[0]['max_score'];

                        foreach ($gradesForCategory as $grade) {
                            $gradesToInsert[] = [
                                'id' => Str::uuid(),
                                'school_branch_id' => $currentSchool->id,
                                'letter_grade_id' => $grade['letter_grade_id'],
                                'grade_points' => $grade['grade_points'],
                                'minimum_score' => $grade['minimum_score'],
                                'maximum_score' => $grade['maximum_score'],
                                'grade_status' => $grade['grade_status'],
                                'resit_status' => $grade['resit_status'],
                                'determinant' => $grade['determinant'],
                                'grades_category_id' => $gradesCategoryId,
                            ];
                        }

                        $configsToUpdate[] = [
                            'id' => $schoolGradesConfig->id,
                            'isgrades_configured' => true,
                            'max_score' => $maxScore,
                        ];
                    }
                }
            }

            if (!empty($gradesToInsert)) {
                DB::table('grades')->insert($gradesToInsert);
            }

            if (!empty($configsToUpdate)) {
                SchoolGradesConfig::whereIn('id', array_column($configsToUpdate, 'id'))
                    ->upsert($configsToUpdate, ['id'], ['isgrades_configured', 'max_score']);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function updateExamGrades(array $grades, $currentSchool): bool
    {
        if (empty($grades)) {
            return true;
        }

        try {
            DB::beginTransaction();

            foreach ($grades as $grade) {
                Grades::where('school_branch_id', $currentSchool->id)
                    ->where('id', $grade['grade_id'])
                    ->update([
                        'grade_points' => $grade['grade_points'],
                        'letter_grade_id' => $grade['letter_grade_id'],
                        'minimum_score' => $grade['minimum_score'],
                        'maximum_score' => $grade['maximum_score'],
                        'grade_status' => $grade['grade_status'],
                        'resit_status' => $grade['resit_status'],
                        'determinant' => $grade['determinant'],
                    ]);
            }

            DB::commit();

            return true;
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
    public function bulkConfigureByOtherGrades($data, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $configIds = $data['configIds'];
            $sourceConfigId = $data['target_config_id'];

            $sourceConfig = SchoolGradesConfig::where('school_branch_id', $currentSchool->id)
                ->find($sourceConfigId);

            if (!$sourceConfig || !$sourceConfig->isgrades_configured) {
                DB::rollBack();
                return;
            }

            $targetConfigs = SchoolGradesConfig::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $configIds)
                ->where('isgrades_configured', false)
                ->get();

            if ($targetConfigs->isEmpty()) {
                DB::rollBack();
                return;
            }

            $sourceGrades = Grades::where('school_branch_id', $currentSchool->id)
                ->where('grades_category_id', $sourceConfig->grades_category_id)
                ->get();

            if ($sourceGrades->isEmpty()) {
                DB::rollBack();
                return;
            }

            $gradesToInsert = [];
            foreach ($targetConfigs as $targetConfig) {
                foreach ($sourceGrades as $grade) {
                    $gradesToInsert[] = [
                        'id' => Str::uuid(),
                        'school_branch_id' => $currentSchool->id,
                        'letter_grade_id' => $grade->letter_grade_id,
                        'grade_points' => $grade->grade_points,
                        'minimum_score' => $grade->minimum_score,
                        'maximum_score' => $grade->maximum_score,
                        'grade_status' => $grade->grade_status,
                        'resit_status' => $grade->resit_status,
                        'determinant' => $grade->determinant,
                        'grades_category_id' => $targetConfig->grades_category_id,
                    ];
                }
            }

            if (!empty($gradesToInsert)) {
                DB::table('grades')->insert($gradesToInsert);
            }

            SchoolGradesConfig::whereIn('id', $targetConfigs->pluck('id'))
                ->update([
                    'isgrades_configured' => true,
                    'max_score' => $sourceConfig->max_score,
                ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getGradeConfigDetails($currentSchool, $configId)
    {
        try {
            $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
            if (!$schoolGradesConfig) {
                throw new Exception("School Grades Configuration Not Found", 404);
            }
            $grades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                ->with(['lettergrade'])
                ->get();
            return $grades;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function deleteGradesConfig($currentSchool, $configId)
    {
        try {
            DB::beginTransaction();
            $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->find($configId);
            if (!$schoolGradesConfig) {
                throw new Exception("School Grades Configuration Not Found", 404);
            }
            $grades = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
                ->get();
            foreach ($grades as $grade) {
                $grade->delete();
            }
            $schoolGradesConfig->isgrades_configured = false;
            $schoolGradesConfig->max_score = null;
            $schoolGradesConfig->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteGradesConfig($currentSchool, $data)
    {
        try {
            DB::beginTransaction();
            $configIds = collect($data['configIds'])->pluck('grade_config_id')->toArray();

            $schoolGradesConfigs = SchoolGradesConfig::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $configIds)
                ->where('isgrades_configured', true)
                ->get();
            if ($schoolGradesConfigs->isNotEmpty()) {
                $gradesCategoryIds = $schoolGradesConfigs->pluck('grades_category_id')->toArray();
                Grades::where('school_branch_id', $currentSchool->id)
                    ->whereIn('grades_category_id', $gradesCategoryIds)
                    ->delete();

                SchoolGradesConfig::where('school_branch_id', $currentSchool->id)
                    ->whereIn('id', $configIds)
                    ->update([
                        'isgrades_configured' => false,
                        'max_score' => null,
                    ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
