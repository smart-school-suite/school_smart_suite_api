<?php

namespace App\Services\School;
use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ApiResponseService;
use Exception;
class SchoolService
{
        public function deleteSchool(string $schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $school->delete();
        return $school;
    }

    public function updateSchool(array $data, string $schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $filterData = array_filter($data);
        $school->update($filterData);
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

    public function uploadSchoolLogo($request, $schoolId)
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
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
