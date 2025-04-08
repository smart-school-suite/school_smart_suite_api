<?php

namespace App\Services\Auth\Guardian;
use Illuminate\Support\Facades\Hash;
use App\Models\Parents;

class CreateGuardianService
{
    // Implement your logic here
    public function createGuardian($guardianData, $currentSchool){
        $randomPassword = $this->generateRandomPassword(10);
       $createParent = Parents::create([
            'name' => $guardianData['name'],
            'address' => $guardianData['address'],
            'email' => $guardianData['email'],
            'phone_one' => $guardianData['phone_one'],
            'relationship_to_student' => $guardianData['relationship_to_student'],
            'preferred_language' => $guardianData['preferred_language'],
            'occupation' => $guardianData['occupation'],
            'school_branch_id' => $currentSchool->id,
            'password' => Hash::make($randomPassword)
        ]);

       return $createParent;
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
