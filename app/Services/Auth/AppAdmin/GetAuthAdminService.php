<?php

namespace App\Services\Auth\AppAdmin;

class GetAuthAdminService
{
    // Implement your logic here
    public function getAuthAppAdmin(){
        $getAuthAdmin = auth()->guard('edumanageadmin')->user();
        return $getAuthAdmin;
    }
}
