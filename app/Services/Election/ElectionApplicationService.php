<?php

namespace App\Services\Election;

use App\Jobs\NotificationJobs\SendAdminApplicationApprovedNotification;
use App\Jobs\NotificationJobs\SendCandidacyApprovedNotification;
use App\Jobs\StatisticalJobs\OperationalJobs\ElectionApplicationStatJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ElectionCandidates;
use App\Models\ElectionApplication;
use App\Models\ElectionResults;
use App\Models\Elections;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AppException;
use Throwable;

class ElectionApplicationService
{
    public function createApplication(array $data, $currentSchool)
    {
        $existingApplication = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $data["election_id"])
            ->where("student_id", $data["student_id"])
            ->where("election_role_id", $data["election_role_id"])
            ->exists();

        if ($existingApplication) {
            throw new AppException(
                "Application already submitted",
                409,
                "Duplicate Application",
                "Looks like you already applied for this position in this election.",
                "/elections/" . $data["election_id"] . "/apply"
            );
        }

        try {
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
            $electionApplication->application_status = 'pending';

            $electionApplication->save();

            ElectionApplicationStatJob::dispatch($applicationId, $currentSchool->id);
            return $electionApplication;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to submit application",
                500,
                "Submission Error",
                "An unexpected error occurred while attempting to save your election application.",
                "/elections/" . $data["election_id"] . "/apply"
            );
        }
    }
    public function updateApplication(array $data, $application_id)
    {
        $applcationExists = ElectionApplication::find($application_id);

        if (is_null($applcationExists)) {
            throw new AppException(
                "Application not found for update",
                404,
                "Application Missing",
                "The election application with ID $application_id could not be found.",
                "/elections/applications"
            );
        }

        $filteredData = array_filter($data);

        if (empty($filteredData)) {
            throw new AppException(
                "No valid data provided for update",
                400,
                "Invalid Update Data",
                "The request contained no valid, non-empty fields to update the application.",
                "/elections/applications/" . $application_id . "/edit"
            );
        }

        try {
            $applcationExists->update($filteredData);
            return $applcationExists;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update application",
                500,
                "Update Error",
                "An unexpected error occurred while attempting to save the changes to the application.",
                "/elections/applications/" . $application_id . "/edit"
            );
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

            foreach ($applicationIds as $applicationItem) {
                $applicationId = $applicationItem['election_application_id'] ?? null;

                if (is_null($applicationId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing application ID in bulk deletion list",
                        400,
                        "Invalid Input Structure",
                        "One of the deletion items is missing the required 'election_application_id' key.",
                        "/elections/applications"
                    );
                }

                $application = ElectionApplication::find($applicationId);

                if (is_null($application)) {
                    DB::rollBack();
                    throw new AppException(
                        "Application not found for deletion",
                        404,
                        "Application Missing in Bulk",
                        "The election application with ID $applicationId could not be found, halting bulk deletion.",
                        "/elections/applications"
                    );
                }

                $application->delete();
                $result[] = $application;
            }

            if (empty($result) && !empty($applicationIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk deletion failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful deletions.",
                    "/elections/applications"
                );
            }

            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Bulk deletion failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk deletion process.",
                "/elections/applications"
            );
        }
    }
    public function bulkApproveApplication($applicationIds, $currentSchool)
    {
        $applicationData = [];

        try {
            DB::beginTransaction();

            foreach ($applicationIds as $applicationItem) {
                $applicationId = $applicationItem['election_application_id'] ?? null;

                if (is_null($applicationId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing application ID in bulk approval list",
                        400,
                        "Invalid Input Structure",
                        "One of the approval items is missing the required 'election_application_id' key.",
                        "/elections/applications"
                    );
                }

                $application = ElectionApplication::where("school_branch_id", $currentSchool->id)
                    ->find($applicationId);

                if (is_null($application)) {
                    DB::rollBack();
                    throw new AppException(
                        "Application not found",
                        404,
                        "Application Missing in Bulk",
                        "The election application with ID $applicationId could not be found for approval, halting bulk process.",
                        "/elections/applications"
                    );
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
                    'student_id' => $application->student_id,
                    'application_id' => $application->id,
                ];
            }


            DB::commit();

            SendCandidacyApprovedNotification::dispatch($applicationData, $currentSchool->id);
            SendAdminApplicationApprovedNotification::dispatch($applicationData, $currentSchool->id);

            return true;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Bulk approval failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk application approval and candidate creation process.",
                "/elections/applications"
            );
        }
    }
    public function getApplicationDetails($applicationId, $currentSchool)
    {
        $application = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->with(['student.specialty.level', 'election', 'electionRole'])
            ->find($applicationId);

        if (is_null($application)) {
            throw new AppException(
                "Application details not found",
                404,
                "Application Missing",
                "The election application with ID $applicationId could not be found for this school branch.",
                "/elections/applications"
            );
        }

        return $application;
    }
    public function getApplicationsByElection(string $electionId, $currentSchool)
    {
        try {
            Elections::findOrFail($electionId);
        } catch (ModelNotFoundException $e) {
            throw new AppException("Election not found", 404, "Election Missing", "The election with ID $electionId could not be found.", "/elections");
        }

        $applications = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['student', 'election', 'electionRole'])
            ->get();

        if ($applications->isEmpty()) {
            throw new AppException(
                "No applications found for election ID $electionId",
                404,
                "Applications Missing",
                "There are no election applications available for the specified election.",
                "/elections/" . $electionId . "/applications"
            );
        }

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
    public function getApplicationsByStudent($currentSchool, $studentId)
    {
        if (!Student::where('id', $studentId)->where('school_branch_id', $currentSchool->id)->exists()) {
            throw new AppException(
                "Student not found",
                404,
                "Student Missing",
                "The student with ID $studentId could not be found in this school branch.",
                "/dashboard"
            );
        }

        $studentApplications = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $studentId)
            ->with(['election', 'electionRole'])
            ->get();

        if ($studentApplications->isEmpty()) {
            throw new AppException(
                "No election applications found for this student",
                404,
                "Applications Missing",
                "You have not submitted any election applications yet.",
                "/applications/create"
            );
        }

        return $studentApplications;
    }
    public function approveApplication(string $applicationId, $currentSchool)
    {
        try {
            DB::beginTransaction();
            $applicationData = [];
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
                'student_id' => $application->student_id,
                'application_id' => $application->id,
            ];

            SendCandidacyApprovedNotification::dispatch($applicationData, $currentSchool->id);
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


}
