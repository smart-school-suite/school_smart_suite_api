<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\Auth\AppAdmin\ChangeAppAdminPasswordService;
use Illuminate\Http\Request;

class ChangeAppAdminPassword extends Controller
{
    //changeedumanagepasswordcontroller
    protected ChangeAppAdminPasswordService $changeAppAdminPasswordService;
    public function __construct(ChangeAppAdminPasswordService $changeAppAdminPasswordService){
        $this->changeAppAdminPasswordService = $changeAppAdminPasswordService;
    }
    public function changeAppAdminPassword(ChangePasswordRequest $request){
       $this->changeAppAdminPasswordService->changeAppAdminPassword($request->validated());
    }

}
