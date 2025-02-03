<?php

namespace App\Http\Controllers;

use App\Models\ElectionCandidates;
use App\Models\ElectionResults;
use App\Models\Elections;
use App\Models\ElectionVotes;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class electionsController extends Controller
{
    //
    public function createElection(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");

        $request->validate([
            'title' => 'required|string',
            'election_start_date' => 'required|date',
            'election_end_date' => 'required|date|after:election_start_date',
            'starting_time' => 'required|date_format:H:i',
            'ending_time' => 'required|date_format:H:i|after:starting_time',
            'description' => 'required|string'
        ]);

        $electionData = $request->only([
            'title',
            'election_start_date',
            'election_end_date',
            'starting_time',
            'ending_time',
            'description'
        ]);

        $electionData['school_branch_id'] = $currentSchool->id;

        $newElection = Elections::create($electionData);

        return response()->json([
            'status' => 'ok',
            'message' => 'Election created successfully',
            'created_election' => $newElection
        ], 201);
    }

    public function deleteElection(Request $request, $election_id)
    {
        $election = Elections::find($election_id);

        if (!$election) {
            return response()->json([
                'status' => 'error',
                'message' => "Election not found"
            ], 404);
        }

        $election->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Election deleted successfully',
        ], 200);
    }

    public function getElections(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $elections = Elections::where('school_branch_id', $currentSchool->id)->get();

        if ($elections->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No elections found'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Elections fetched successfully',
            'elections' => $elections
        ], 200);
    }

    public function updateElection(Request $request, $election_id)
    {
        $election = Elections::find($election_id);

        if (!$election) {
            return response()->json([
                'status' => 'error',
                'message' => "Election not found"
            ], 404);
        }

        $request->validate([
            'title' => 'string',
            'election_start_date' => 'date|sometimes',
            'election_end_date' => 'date|after:election_start_date|sometimes',
            'starting_time' => 'date_format:H:i:s|sometimes',
            'ending_time' => 'date_format:H:i:s|after:starting_time|sometimes',
            'description' => 'string|sometimes'
        ]);

        $election->fill($request->only([
            'title',
            'election_start_date',
            'election_end_date',
            'starting_time',
            'ending_time',
            'description'
        ]));

        if (!$election->save()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Election update failed'
            ], 500);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Election updated successfully',
            'updated_election' => $election
        ], 200);
    }

    public function vote(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        // Validate input
        $validatedData = $request->validate([
            'candidate_id' => 'required|string',
            'student_id' => 'required|string',
            'election_id' => 'required|string',
            'position_id' => 'required|string'
        ]);

        // Find election and validate
        $election = Elections::with(['electionRole'])->find($validatedData['election_id']);
        if (!$election) {
            return $this->respondWithError('Election not found', 404);
        }

        if ($election->is_results_published) {  // Check if results are already published
            return response()->json([
                'status' => 'error',
                'message' => 'Election results already published'
            ], 403);
        }

        // Find student and validate
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($validatedData['student_id']);
        if (!$student) {
            return $this->respondWithError('Student not found', 404);
        }

        // Prevent voting in the same category
        if ($this->hasVotedInSameCategory($currentSchool->id, $validatedData['position_id'], $validatedData['election_id'], $validatedData['student_id'])) {
            return $this->respondWithError("You cannot vote multiple times in the same category", 403);
        }

        // Create vote
        ElectionVotes::create([
            "school_branch_id" => $currentSchool->id,
            "election_id" => $validatedData['election_id'],
            "candidate_id" => $validatedData['candidate_id'],
            "student_id" => $validatedData['student_id'],
            "position_id" => $validatedData['position_id'],
            "voted_at" => Carbon::now(),
        ]);

        // Update election results
        $this->updateElectionResults($currentSchool->id, $validatedData);

        return response()->json([
            'status' => 'ok',
            "message" => 'Vote cast successfully'
        ], 200);
    }

    private function hasVotedInSameCategory($schoolBranchId, $positionId, $electionId, $studentId)
    {
        return ElectionVotes::where('school_branch_id', $schoolBranchId)
            ->where('position_id', $positionId)
            ->where('election_id', $electionId)
            ->where('student_id', $studentId)
            ->exists();
    }

    private function updateElectionResults($schoolBranchId, $validatedData)
    {
        $electionResult = ElectionResults::where("school_branch_id", $schoolBranchId)
            ->where("election_id", $validatedData['election_id'])
            ->where("position_id", $validatedData['position_id'])
            ->where("candidate_id", $validatedData['candidate_id'])
            ->first();

        if (!$electionResult) {
            // No existing result, create a new one
            ElectionResults::create([
                'vote_count' => 1,
                'election_id' => $validatedData['election_id'],
                'position_id' => $validatedData['position_id'],
                'candidate_id' => $validatedData['candidate_id'],
                'school_branch_id' => $schoolBranchId
            ]);
        } else {
            // Increment existing vote count
            $electionResult->increment('vote_count');
        }
    }

    private function respondWithError($message, $statusCode)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}
