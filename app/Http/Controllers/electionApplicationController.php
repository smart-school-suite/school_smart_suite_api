<?php

namespace App\Http\Controllers;

use App\Models\ElectionApplication;
use App\Models\ElectionCandidates;
use Illuminate\Http\Request;

class electionApplicationController extends Controller
{

    private const SUCCESS_STATUS = 'ok';
    private const ERROR_STATUS = 'error';

    public function createElectionApplication(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");

        $request->validate([
            'manifesto' => 'required|string',
            'personal_vision' => 'required|string',
            'commitment_statement' => 'required|string',
            'election_id' => 'required|string',
            'election_role_id' => 'required|string',
            'student_id' => 'required|string',
        ]);

        // Check if the application already exists
        $applicationExists = ElectionApplication::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $request->election_id)
            ->where("student_id", $request->student_id)
            ->where("election_role_id", $request->election_role_id)
            ->exists();

        if ($applicationExists) {
            return $this->responseError('Looks like you already applied for this position');
        }

        $newApplication = ElectionApplication::create($request->only([
            'manifesto',
            'personal_vision',
            'commitment_statement',
            'election_id',
            'election_role_id',
            'student_id'
        ]));

        return $this->responseSuccess('Congratulations! Application created successfully.', $newApplication);
    }

    public function approveApplication(Request $request)
    {
        $applicationId = $request->route('application_id');
        $currentSchool = $request->attributes->get('currentSchool');

        $application = ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->find($applicationId);

        if (!$application) {
            return $this->responseError('Application not found');
        }

        $application->isApproved = true;
        $application->save();

        ElectionCandidates::create([
            "election_status" => "pending",
            "isActive" => true,
            "application_id" => $applicationId,
            "school_branch_id" => $currentSchool->id,
            "student_id" => $application->student_id
        ]);

        return $this->responseSuccess('Application approved successfully.', $application);
    }

    public function deleteApplication(Request $request)
    {
        $applicationId = $request->route('application_id');
        $application = ElectionApplication::find($applicationId);

        if (!$application) {
            return $this->responseError('Application not found');
        }

        $application->delete();

        return $this->responseSuccess('Application deleted successfully.', $application);
    }

    public function getApplications(Request $request, $election_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $election_id = $request->route('election_id');
        $application = ElectionApplication::where('school_branch_id', $currentSchool->id)
                                            ->where("election_id", $election_id)
                                            ->get();
        if($application->isEmpty()){
            return $this->responseError("Looks like no one has applied");
        }

        return $this->responseSuccess("Application data fetched succefully", $application);

    }

    public function updateApplication(Request $request)
    {
        $applicationId = $request->route('application_id');
        $application = ElectionApplication::find($applicationId);

        if (!$application) {
            return $this->responseError('Application not found');
        }

        $application->fill($request->only(['commitment_statement', 'manifesto', 'personal_vision']));
        $application->save();

        return $this->responseSuccess('Election application updated successfully.', $application);
    }

    private function responseSuccess(string $message, $data = null)
    {
        return response()->json([
            'status' => self::SUCCESS_STATUS,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    private function responseError(string $message)
    {
        return response()->json([
            'status' => self::ERROR_STATUS,
            'message' => $message
        ], 401);
    }

}
