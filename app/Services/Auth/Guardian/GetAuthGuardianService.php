<?php

namespace App\Services\Auth\Guardian;

class GetAuthGuardianService
{
    // Implement your logic here
    public function getAuthGuardian(){
        $authParent = auth()->guard('parent')->user();
        return $authParent;
    }
}
