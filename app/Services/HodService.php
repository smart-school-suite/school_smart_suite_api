<?php

namespace App\Services;

use App\Models\Department;
use App\Models\HOD;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Schooladmin;
use App\Models\SchoolBranches;
use App\Notifications\AppointedAsHOD;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
class HodService
{
    /**
     * Assigns a Head of Department to a specific department within a school branch.
     * If an HOD is already assigned to that department in the school branch, the existing assignment is replaced.
     *
     * @param array $hodData An array containing 'department_id' and 'hodable_id'.
     * @param SchoolBranches $currentSchool The current school branch object.
     * @return HOD The newly created or updated HOD assignment.
     * @throws NotFoundHttpException If the provided hodable_id does not correspond to a Teacher or SchoolAdmin.
     * @throws Exception If an unexpected error occurs during the assignment.
     */
    public function assignHeadOfDepartment(array $hodData, SchoolBranches $currentSchool): HOD
    {
        $hodable = Teacher::find($hodData["hodable_id"]);
        $hodableType = null;

        if ($hodable) {
            $hodableType = Teacher::class;
        } else {
            $hodable = SchoolAdmin::find($hodData["hodable_id"]);
            if ($hodable) {
                $hodableType = Schooladmin::class;
            }
        }

        if (!$hodableType) {
            throw new NotFoundHttpException("The provided credentials for HOD assignment are incorrect.");
        }

        DB::beginTransaction();
        try {
            HOD::where("school_branch_id", $currentSchool->id)
                ->where('department_id', $hodData["department_id"])
                ->delete();

            $assignedHod = HOD::create([
                'department_id' => $hodData["department_id"],
                'hodable_id' => $hodData["hodable_id"],
                'school_branch_id' => $currentSchool->id,
                'hodable_type' => $hodableType,
            ]);

            DB::commit();
            $hodable->notify(new AppointedAsHOD(Department::find($hodData['department_id'])->department_name));
            return $assignedHod;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to assign Head of Department: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Removes a Head of Department assignment.
     *
     * @param int $hodId The ID of the HOD assignment to remove.
     * @param SchoolBranches $currentSchool The current school branch object to scope the deletion.
     * @return HOD The deleted HOD model instance.
     * @throws NotFoundHttpException If the HOD assignment is not found.
     * @throws Exception If an unexpected error occurs during deletion.
     */
    public function removeHod(int $hodId, SchoolBranches $currentSchool): HOD
    {
        $findHod = HOD::where("school_branch_id", $currentSchool->id)->find($hodId);

        if (!$findHod) {
            throw new NotFoundHttpException("HOD assignment not found for this school branch.");
        }

        try {
            $findHod->delete();
            return $findHod;
        } catch (Exception $e) {
            throw new Exception("Failed to remove HOD assignment: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retrieves all assigned HODs for a given school branch with their related hodable and department details.
     *
     * @param SchoolBranches $currentSchool The current school branch object.
     * @return Collection<HOD> A collection of HOD models.
     */
    public function getAssignedHods(SchoolBranches $currentSchool): Collection
    {
       return HOD::where("school_branch_id", $currentSchool->id)
            ->with([
                'hodable' => function (MorphTo $morphTo) {
                    $morphTo->select('id', 'profile_picture', 'first_name', 'last_name', 'full_names');
                },
                'department' => function (BelongsTo $belongsTo) {
                    $belongsTo->select('id', 'department_name');
                }
            ])
            ->get();
    }

    /**
     * Retrieves the details of a specific HOD assignment by its ID.
     *
     * @param string $hodId The ID of the HOD assignment.
     * @return HOD|null The HOD model instance or null if not found.
     */
    public function getHodDetails(string $hodId, $currentSchool): ?HOD
    {
        return HOD::where("school_branch_id", $currentSchool->id)
        ->with(['hodable', 'department'])->find($hodId);
    }

    /**
     * Bulk removes multiple HOD assignments by their IDs.
     *
     * @param array $removeData An array of HOD IDs to be removed. Each element should ideally be just the ID.
     * Example: [1, 5, 10]
     * @return void The number of HOD assignments deleted.
     * @throws Exception If an error occurs during the bulk deletion.
     */
    public function bulkRemoveHod(array $hods, $currentSchool): void
    {
        try {
            DB::beginTransaction();
            foreach($hods as $hod){
                $hod = HOD::where("school_branch_id", $currentSchool->id)->find($hod['hod_id']);
                $hod->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to perform bulk removal of HOD assignments: " . $e->getMessage(), 0, $e);
        }
    }
}
