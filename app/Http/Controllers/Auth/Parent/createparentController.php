<?php

namespace App\Http\Controllers\Auth\Parent;
use App\Services\Auth\Guardian\CreateGuardianService;
use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\CreateGuardianRequest;
use Illuminate\Http\Request;

class CreateParentController extends Controller
{
    //
    protected CreateGuardianService $createGuardianService;
    public function __construct(CreateGuardianService $createGuardianService){
        $this->createGuardianService = $createGuardianService;
    }
    public function createParent(CreateGuardianRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createParent = $this->createGuardianService->createGuardian($request->validated(), $currentSchool);
        return ApiResponseService::success("Parent Created Succesfully", $createParent, null, 200);
    }
}
