<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdminApplicationApprovedNotification;
use App\Jobs\NotificationJobs\SendCandidacyApprovedNotification;
use App\Jobs\StatisticalJobs\OperationalJobs\ElectionApplicationStatJob;
use App\Models\ElectionCandidates;
use App\Models\ElectionApplication;
use App\Models\ElectionResults;
use App\Models\ElectionRoles;
use App\Models\Elections;
use App\Models\Student;
use App\Notifications\CandidacyApproved;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;

class ElectionApplicationService
{
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
        $applicationId = Str::uuid()->toString();
        $electionApplication->id = $applicationId;
        $electionApplication->manifesto = $data["manifesto"];
        $electionApplication->personal_vision = $data["personal_vision"];
        $electionApplication->commitment_statement = $data["commitment_statement"];
        $electionApplication->election_id = $data["election_id"];
        $electionApplication->election_role_id = $data["election_role_id"];
        $electionApplication->student_id = $data["student_id"];
        $electionApplication->school_branch_id = $currentSchool->id;
        $electionApplication->isApproved = false;
        $electionApplication->save();

        ElectionApplicationStatJob::dispatch($applicationId, $currentSchool->id);
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
            $applicationData = [];
            $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                          ->with(['election.electionType', 'electionRole', 'student'])
                          ->find($applicationId);
            if (!$application) {
                return ApiResponseService::error("Application not found", null, 404);
            }
            $application->application_status = "approved";
            $application->save();
            $randomId = Str::uuid()->toString();
            ElectionCandidates::create([
                'id' => $randomId,
                "election_status" => "pending",
                "isActive" => true,
                "application_id" => $applicationId,
                "election_role_id" => $application->election_role_id,
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
            $applicationData[] = [
                    'election' => Elections::with(['electionTypes'])->find($application->election_id),
                    'student' => Student::find($application->student_id),
                    'electionRole' => ElectionRoles::find($application->election_role_id)
            ];
            $student = Student::where("school_branch_id", $currentSchool->id)->find($application->student_id);
            $student->notify(new CandidacyApproved(
                    $application->electionRole->name,
                $application->election->electionType->election_name,
                ));
            SendAdminApplicationApprovedNotification::dispatch($applicationData, $currentSchool->id);
            return $application;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkApproveApplication($applicationIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            $applicationData = [];
            foreach ($applicationIds as $applicationId) {
                $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                    ->find($applicationId['election_application_id']);
                if (!$application) {
                    return ApiResponseService::error("Application not found", null, 404);
                }
                $application->application_status = "approved";
                $application->save();
                $randomId = Str::uuid()->toString();
                ElectionCandidates::create([
                    'id' => $randomId,
                    "election_status" => "pending",
                    "isActive" => true,
                    "application_id" => $applicationId['election_application_id'],
                    "election_role_id" => $application->election_role_id,
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
                $applicationData[] = [
                    'election' => Elections::with(['electionTypes'])->find($application->election_id),
                    'student' => Student::find($application->student_id),
                    'electionRole' => ElectionRoles::find($application->election_role_id)
                ];
                $result[] = $application;
            }
            DB::commit();
            SendCandidacyApprovedNotification::dispatch($applicationData);
            SendAdminApplicationApprovedNotification::dispatch($applicationData, $currentSchool->id);
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
    public function getMyApplications($currentSchool, $studentId){
        $studentApplications = ElectionApplication::where("school_branch_id", $currentSchool->id)
                                                     ->where("student_id", $studentId)
                                                     ->get();
        return $studentApplications;

    }
}
