<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\CreateSchoolAdminRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\CreateSchoolAdminService;


class CreatesSchoolAdminController extends Controller
{
    //createschooladmincontroller
    protected CreateSchoolAdminService $createSchoolAdminService;
    public function __construct(CreateSchoolAdminService $createSchoolAdminService)
    {
        $this->createSchoolAdminService = $createSchoolAdminService;

    }
    public function createSchoolAdmin(CreateSchoolAdminRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createSchoolAdmin = $this->createSchoolAdminService->createSchoolAdmin($request->validated(), $currentSchool);
        return ApiResponseService::success("School Admin Created Successfully", $createSchoolAdmin, null, 200);
    }
}
