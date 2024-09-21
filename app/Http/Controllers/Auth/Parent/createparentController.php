<?php

namespace App\Http\Controllers\Auth\Parent;
use App\Models\Parents;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class createparentController extends Controller
{
    //
    public function create_parent(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_one' => 'string',
            'phone_two' => 'string',
            'language_preference' => 'required|string|max:10',
            'password' => 'required|string|min:8',
        ]);

        Parents::create([
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'phone_one' => $validatedData['phone_one'],
            'phone_two' => $validatedData['phone_two'],
            'language_preference' => $validatedData['language_preference'],
            'school_branch_id' => $currentSchool->id,
            'password' => Hash::make($validatedData['password']), // Hash the password
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Account creation was successful.'
         ], 201);
    }
}
