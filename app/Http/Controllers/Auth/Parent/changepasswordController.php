<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\Auth\Guardian\ChangeGaurdianPasswordService;

class ChangePasswordController extends Controller
{
    //
    protected ChangeGaurdianPasswordService $changeGaurdianPasswordService;
    public function __construct(ChangeGaurdianPasswordService $changeGaurdianPasswordService){
        $this->changeGaurdianPasswordService = $changeGaurdianPasswordService;
    }
    public function changeParentPassword(ChangePasswordRequest $request){
       $this->changeGaurdianPasswordService->changePasswordParent($request->validated());
    }

}
