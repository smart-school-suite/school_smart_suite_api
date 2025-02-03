<?php

namespace App\Http\Controllers;

use App\Models\ElectionRoles;
use App\Models\Elections;
use Illuminate\Http\Request;

class electionRolesController extends Controller
{
    //

    public function createElectionRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'election_id' => 'required|string',
            'description' => 'required|string'
        ]);

        $newElectionRole = ElectionRoles::create($request->only(['name', 'election_id', 'description']));

        return response()->json([
            'status' => 'ok',
            'message' => 'Election Role created successfully',
            'created_election_role' => $newElectionRole
        ], 201);
    }

    public function updateElectionRole(Request $request, $election_role_id)
    {
        $electionRole = ElectionRoles::find($election_role_id);
        if (!$electionRole) {
            return response()->json([
                'status' => 'error',
                'message' => 'Election role not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string'
        ]);

        $electionRole->fill($request->only(['name', 'description']));
        $electionRole->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Election Role updated successfully',
            'updated_election_role' => $electionRole
        ], 200);
    }

    public function deleteElectionRole(Request $request, $election_role_id)
    {
        $electionRole = ElectionRoles::find($election_role_id);
        if (!$electionRole) {
            return response()->json([
                'status' => 'error',
                'message' => 'Election role not found'
            ], 404);
        }

        $electionRole->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Election role deleted successfully'
        ], 200);
    }

    public function getElectionRoles(Request $request, $election_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $election = Elections::find($election_id);
        if (!$election) {
            return response()->json([
                'status' => 'error',
                'message' => 'Election not found'
            ], 404);
        }

        $electionRoles = ElectionRoles::where('school_branch_id', $currentSchool->id)
                                      ->where('election_id', $election_id)
                                      ->get();

        if ($electionRoles->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'No election roles found'
            ], 204); // No Content
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Election Roles fetched successfully',
            'election_roles' => $electionRoles
        ], 200);
    }
}
