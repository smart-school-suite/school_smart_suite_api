<?php

namespace App\Http\Controllers\Auth\Parent;
use App\Models\Parents;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateParentController extends Controller
{
    //
    public function create_parent(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'address' => 'required|string|max:255',
            'phone_one' => 'required|string',
            'relationship_to_student' => 'required|string',
            'language_preference' => 'required|string|max:10',
            'password' => 'required|string|min:8',
        ]);

     $parent =  Parents::create([
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'email' => $validatedData['email'],
            'phone_one' => $validatedData['phone_one'],
            'relationship_to_student' => $validatedData['relationship_to_student'],
            'language_preference' => $validatedData['language_preference'],
            'school_branch_id' => $currentSchool->id,
            'password' => Hash::make($validatedData['password']),
        ]);


        return response()->json([
            'status' => 'ok',
            'message' => 'Account creation was successful.'
         ], 201);
    }
}
