<?php

namespace App\Services\School;

use App\Models\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ApiResponseService;
use Exception;
use App\Events\Actions\AdminActionEvent;

class SchoolService
{
    public function deleteSchool(string $schoolId, $currentSchool, $authAdmin)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $school->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.school.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolManagement",
                "authAdmin" => $authAdmin,
                "data" => $school,
                "message" => "School  Deleted",
            ]
        );
        return $school;
    }
    public function updateSchool(array $data, string $schoolId, $currentSchool, $authAdmin)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $filterData = array_filter($data);
        $school->update($filterData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.school.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolManagement",
                "authAdmin" => $authAdmin,
                "data" => $school,
                "message" => "School Updated",
            ]
        );
        return $school;
    }
    public function getSchoolDetails($schoolId)
    {
        $school = School::with('schoolbranches')->find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        return $school;
    }
    public function uploadSchoolLogo($request, $schoolId, $currentSchool, $authAdmin)
    {

        try {
            $school = School::find($schoolId);
            if (!$school) {
                throw new Exception("School Not Found", 404);
            }
            DB::transaction(function () use ($request, $school) {

                if ($school->school_logo) {
                    Storage::disk('public')->delete('SchoolLogo/' . $school->school_logo);
                }
                $schoolLogo = $request->file('school_logo');
                $fileName = time() . '.' . $schoolLogo->getClientOriginalExtension();
                $schoolLogo->storeAs('public/SchoolLogo', $fileName);

                $school->school_logo = $fileName;
                $school->save();
            });
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.school.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $school,
                    "message" => "School Logo Updated",
                ]
            );
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
