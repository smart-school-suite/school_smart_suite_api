<?php

namespace App\Services;

use App\Models\ElectionCandidates;
use App\Models\ElectionApplication;
use App\Models\ElectionResults;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class ElectionApplicationService
{
    // Implement your logic here

    public function createApplication(array $data, $currentSchool)
    {
        $applicationExists = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $data["election_id"])
            ->where("student_id", $data["student_id"])
            ->where("election_role_id", $data["election_role_id"])
            ->exists();
        if ($applicationExists) {
            return ApiResponseService::error("Looks like you already applied for this position", null, 404);
        }

        $electionApplication = new ElectionApplication();
        $electionApplication->manifesto = $data["manifesto"];
        $electionApplication->personal_vision = $data["personal_vision"];
        $electionApplication->commitment_statement = $data["commitment_statement"];
        $electionApplication->election_id = $data["election_id"];
        $electionApplication->election_role_id = $data["election_role_id"];
        $electionApplication->student_id = $data["student_id"];
        $electionApplication->school_branch_id = $currentSchool->id;
        $electionApplication->isApproved = false;
        $electionApplication->save();
        return $electionApplication;
    }

    public function updateApplication(array $data, $application_id)
    {
        $applcationExists = ElectionApplication::find($application_id);
        if (!$applcationExists) {
            return ApiResponseService::error("Application not found", null, 404);
        }
        $filteredData = array_filter($data);
        $applcationExists->update($filteredData);
        return $applcationExists;
    }

    public function bulkUpdateApplication(array $updateApplicationList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateApplicationList as $updateApplication) {
                $electionApplication = ElectionApplication::find($updateApplication['applicaiton_id']);
                $filteredData = array_filter($updateApplication);
                $electionApplication->update($filteredData);
                $result[] = [
                    $electionApplication
                ];
            }
            DB::commit();
            $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteApplication(string $application_id)
    {
        $applcationExists = ElectionApplication::find($application_id);
        if (!$applcationExists) {
            return ApiResponseService::error("Application not found", null, 404);
        }
        $applcationExists->delete();
        return $applcationExists;
    }

    public function bulkDeleteApplication($applicationIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($applicationIds as $applicationId) {
                $application = ElectionApplication::find($applicationId['election_application_id']);
                $application->delete();
                $result[] = $application;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveApplication(string $applicationId, $currentSchool)
    {
        try {
            DB::beginTransaction();
            $application = ElectionApplication::where("school_branch_id", $currentSchool->id)->find($applicationId);
            if (!$application) {
                return ApiResponseService::error("Application not found", null, 404);
            }
            $application->isApproved = true;
            $application->save();
            $randomId = Str::uuid()->toString();
            ElectionCandidates::create([
                'id' => $randomId,
                "election_status" => "pending",
                "isActive" => true,
                "application_id" => $applicationId,
                "school_branch_id" => $currentSchool->id,
                'election_id' => $application->election_id,
                "student_id" => $application->student_id
            ]);
            ElectionResults::create([
                'vote_count' => 0,
                'election_id' => $application->election_id,
                'position_id' => $application->election_role_id,
                'candidate_id' => $randomId,
                'school_branch_id' => $currentSchool->id
            ]);
            DB::commit();
            return $application;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //add constrain to prevent multiple same student for multiple or same roles
    public function bulkApproveApplication($applicationIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($applicationIds as $applicationId) {
                $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                    ->find($applicationId['election_application_id']);
                if (!$application) {
                    return ApiResponseService::error("Application not found", null, 404);
                }
                $application->isApproved = true;
                $application->save();
                $randomId = Str::uuid()->toString();
                ElectionCandidates::create([
                    'id' => $randomId,
                    "election_status" => "pending",
                    "isActive" => true,
                    "application_id" => $applicationId['election_application_id'],
                    "election_id" => $application->election_id,
                    "school_branch_id" => $currentSchool->id,
                    "student_id" => $application->student_id
                ]);
                ElectionResults::create([
                    'vote_count' => 0,
                    'election_id' => $application->election_id,
                    'position_id' => $application->election_role_id,
                    'candidate_id' => $randomId,
                    'school_branch_id' => $currentSchool->id
                ]);
                $result[] = $application;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getApplications(string $electionId, $currentSchool)
    {
        $applications = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['student', 'election', 'electionRole'])
            ->get();
        return $applications;
    }

    public function getAllApplications($currentSchool)
    {
        $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->with(['student.level', 'election', 'electionRole'])
            ->get();
        return $application;
    }
}
