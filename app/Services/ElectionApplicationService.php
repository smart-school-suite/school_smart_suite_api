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
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        if (is_null($applcationExists)) {
            throw new AppException(
                "Application not found for deletion",
                404,
                "Application Missing",
                "The election application with ID $application_id could not be found.",
                "/elections/applications"
            );
        }

        try {
            $applcationExists->delete();
            return $applcationExists;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to delete application",
                500,
                "Deletion Error",
                "An unexpected error occurred while attempting to delete the application.",
                "/elections/applications"
            );
        }
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
            $applicationData = [];
            DB::beginTransaction();

            $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                ->with(['election.electionType', 'electionRole', 'student'])
                ->find($applicationId);

            if (is_null($application)) {
                DB::rollBack();
                throw new AppException(
                    "Application not found",
                    404,
                    "Application Missing",
                    "The election application with ID $applicationId could not be found for this school branch.",
                    "/elections/applications"
                );
            }

            if ($application->application_status === 'approved') {
                DB::rollBack();
                throw new AppException(
                    "Application is already approved",
                    409,
                    "Duplicate Approval",
                    "The election application with ID $applicationId has already been approved.",
                    "/elections/applications"
                );
            }

            // Approve the application
            $application->update([
                'application_status' => 'approved',
            ]);

            // Create candidate and result entries
            $candidateId = Str::uuid()->toString();

            ElectionCandidates::create([
                'id' => $candidateId,
                'election_status' => 'pending',
                'isActive' => true,
                'application_id' => $application->id,
                'election_role_id' => $application->election_role_id,
                'school_branch_id' => $currentSchool->id,
                'election_id' => $application->election_id,
                'student_id' => $application->student_id,
            ]);

            ElectionResults::create([
                'vote_count' => 0,
                'election_id' => $application->election_id,
                'position_id' => $application->election_role_id,
                'candidate_id' => $candidateId,
                'school_branch_id' => $currentSchool->id,
            ]);

            DB::commit();

            $applicationData[] = [
                'student_id' => $application->student_id,
                'application_id' => $application->id,
            ];

            // Dispatch notifications
            $student = Student::where("school_branch_id", $currentSchool->id)
                ->find($application->student_id);

            if ($student) {
                $application->loadMissing(['election.electionType', 'electionRole']);
                SendCandidacyApprovedNotification::dispatch($applicationData, $currentSchool->id);
            }

            SendAdminApplicationApprovedNotification::dispatch($applicationData, $currentSchool->id);

            return $application;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Failed to approve application",
                500,
                "Approval Error",
                "An unexpected error occurred during the application approval and candidate creation process.",
                "/elections/applications"
            );
        }
    }

    public function bulkApproveApplication(array $applicationIds, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $approvedApplications = [];
            $applicationDataList = [];

            foreach ($applicationIds as $item) {
                $applicationId = $item['election_application_id'] ?? null;

                if (!$applicationId) {
                    continue; // skip invalid entries
                }

                $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                    ->with(['election.electionType', 'electionRole', 'student'])
                    ->find($applicationId);

                if (!$application) {
                    // Skip missing applications but don’t break the entire loop
                    continue;
                }

                if ($application->application_status === 'approved') {
                    // Skip already approved ones
                    continue;
                }

                // Approve application
                $application->update(['application_status' => 'approved']);

                // Create Candidate & Result
                $candidateId = Str::uuid()->toString();

                ElectionCandidates::create([
                    'id' => $candidateId,
                    'election_status' => 'pending',
                    'isActive' => true,
                    'application_id' => $application->id,
                    'election_role_id' => $application->election_role_id,
                    'election_id' => $application->election_id,
                    'school_branch_id' => $currentSchool->id,
                    'student_id' => $application->student_id,
                ]);

                ElectionResults::create([
                    'vote_count' => 0,
                    'election_id' => $application->election_id,
                    'position_id' => $application->election_role_id,
                    'candidate_id' => $candidateId,
                    'school_branch_id' => $currentSchool->id,
                ]);

                $applicationDataList[] = [
                    'student_id' => $application->student_id,
                    'application_id' => $application->id,
                ];

                $approvedApplications[] = $application;
            }

            DB::commit();

            if (!empty($applicationDataList)) {
                SendCandidacyApprovedNotification::dispatch($applicationDataList, $currentSchool->id);
                SendAdminApplicationApprovedNotification::dispatch($applicationDataList, $currentSchool->id);
            }

            return $approvedApplications;
        } catch (Throwable $e) {
            DB::rollBack();

            throw new AppException(
                "Bulk approval failed",
                500,
                "Bulk Approval Error",
                "An unexpected error occurred while processing multiple election applications.",
                "/elections/applications/bulk"
            );
        }
    }


    public function getApplicationDetails($applicationId, $currentSchool)
    {
        $application = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->with(['student.specialty.level', 'election', 'electionRole'])
            ->find($applicationId);
        return $application;
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
            ->with(['student', 'election.electionType', 'electionRole'])
            ->get();

        if ($application->isEmpty()) {
            throw new AppException(
                "No election applications found",
                404,
                "Applications Missing",
                "There are no election applications available for this school branch.",
                "/elections/applications"
            );
        }

        return $application;
    }
    public function getMyApplications($currentSchool, $studentId)
    {
        $studentApplications = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $studentId)
            ->get();
        return $studentApplications;
    }
}
