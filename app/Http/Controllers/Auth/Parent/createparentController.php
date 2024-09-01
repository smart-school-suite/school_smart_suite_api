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
            'phone_number' => 'required|string|max:15|unique:users',
            'language_preference' => 'required|string|max:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Parents::create([
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'phone_number' => $validatedData['phone_number'],
            'language_preference' => $validatedData['language_preference'],
            'school_branch_id' => $currentSchool->id,
            'password' => Hash::make($validatedData['password']), // Hash the password
        ]);

        return response()->json(['message' => 'Account creation was successful.' ], 201);
    }
}
