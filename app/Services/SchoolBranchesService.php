<?php

namespace App\Services;

use App\Models\GradesCategory;
use App\Models\Schoolbranches;
use App\Models\SchoolGradesConfig;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolBranchesService
{
    // Implement your logic here

    /**
     * Creates a new school branch with associated grade configurations.
     *
     * @param array $data The data for the new school branch.
     * Expected keys: school_id, branch_name, address, city, state,
     * postal_code, phone_two, phone_one, website,
     * email, semester_count, max_gpa, abbreviation.
     * @return string The newly created school branch Id.
     * @throws \Exception If an error occurs during the creation process.
     */
    public function createSchoolBranch(array $data): string
    {
        $branchId = Str::uuid()->toString();

        DB::beginTransaction();

        try {
            $schoolBranch = Schoolbranches::create(array_merge($data, ['id' => $branchId]));

            $gradeCategories = GradesCategory::all();
            $configs = $gradeCategories->map(function ($gradeCategory) use ($branchId) {
                return [
                    'id' => Str::uuid(),
                    'school_branch_id' => $branchId,
                    'grades_category_id' => $gradeCategory->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            SchoolGradesConfig::insert($configs);

            DB::commit();

            return $branchId;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Updates an existing school branch.
     *
     * @param array $data The data to update the school branch with.
     * @param string $schoolBranchId The ID of the school branch to update.
     */
    public function updateSchoolBranch(array $data, $schoolBranchId)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $schoolBranchExist->update($filterData);
        return $schoolBranchExist;
    }

    public function getSchoolBranches()
    {
        $schoolBranches = Schoolbranches::with('schools')->get();
        return $schoolBranches;
    }

    public function deleteSchoolBranch($schoolBranchId)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch not found", null, 404);
        }
        $schoolBranchExist->delete();
        return $schoolBranchExist;
    }
}
