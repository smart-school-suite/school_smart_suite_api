<?php

namespace App\Services;

use App\Models\ElectionCandidates;
use App\Models\ElectionApplication;

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

    public function deleteApplication(string $application_id)
    {
        $applcationExists = ElectionApplication::find($application_id);
        if (!$applcationExists) {
            return ApiResponseService::error("Application not found", null, 404);
        }
        $applcationExists->delete();
        return $applcationExists;
    }

    public function approveApplication(string $application_id, $currentSchool)
    {
        $applcationExists = ElectionApplication::where("school_branch_id", $currentSchool->id)->find($application_id);
        if (!$applcationExists) {
            return ApiResponseService::error("Application not found", null, 404);
        }
        $applcationExists->isApproved = true;
        $applcationExists->save();
        ElectionCandidates::create([
            "election_status" => "pending",
            "isActive" => true,
            "application_id" => $application_id,
            "school_branch_id" => $currentSchool->id,
            "student_id" => $applcationExists->student_id
        ]);

        return $applcationExists;
    }

    public function getApplications(string $election_id, $currentSchool)
    {
        $applications = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->where("election_id", $election_id)
            ->get();
        return $applications;
    }
}
