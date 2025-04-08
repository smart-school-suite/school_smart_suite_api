<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Http\Resources\ParentResource;
use App\Services\ParentService;
use Illuminate\Http\Request;

class parentsController extends Controller
{
    protected ParentService $parentService;
    public function __construct(ParentService $parentService){
        $this->parentService = $parentService;
    }

    //review the resource and the update functionality
    public function getAllParents(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $parentData = $this->parentService->getAllParents($currentSchool);
        return ApiResponseService::success("Parents Data fetched Sucessfully",  ParentResource::collection($parentData), null, 200);
    }

    public function deleteParent(Request $request, string $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteParent = $this->parentService->deleteParent($parent_id, $currentSchool);
        return ApiResponseService::success("Parent Deleted Successfully", $deleteParent, null,200);
    }

    public function updateParent(Request $request, $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateParent = $this->parentService->updateParent($request->all(),$parent_id, $currentSchool);
        return ApiResponseService::success('Parent Updated Sucessfully', $updateParent, null,200);
    }

    public function getParentDetails(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $parent_id = $request->route("parent_id");
         $parentDetails = $this->parentService->getParentDetails( $parent_id, $currentSchool);
         return ApiResponseService::success("Parent Details Fetched Sucessfully", $parentDetails, null,200);
    }

}
