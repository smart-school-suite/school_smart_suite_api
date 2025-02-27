<?php

namespace App\Services\Auth\Guardian;
use Illuminate\Support\Facades\Hash;
use App\Models\Parents;
use App\Services\ApiResponseService;

class CreateGuardianService
{
    // Implement your logic here
    public function createGuardian($guardianData, $currentSchool){
       $createParent = Parents::create([
            'name' => $guardianData['name'],
            'address' => $guardianData['address'],
            'email' => $guardianData['email'],
            'phone_one' => $guardianData['phone_one'],
            'relationship_to_student' => $guardianData['relationship_to_student'],
            'language_preference' => $guardianData['language_preference'],
            'school_branch_id' => $currentSchool->id,
            'password' => Hash::make($guardianData['password']),
        ]);

       return $createParent;
    }
}
