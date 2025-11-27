<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\ParentIdRequest;
use App\Services\ApiResponseService;
use App\Http\Resources\ParentResource;
use App\Http\Requests\Parent\UpdateParentRequest;
use App\Http\Requests\Parent\CreateParentRequest;
use App\Http\Requests\Parent\BulkUpdateParentRequest;
use App\Services\Parent\ParentService;
use Exception;
use Illuminate\Http\Request;

class ParentController extends Controller
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

    public function createParent(CreateParentRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createParent = $this->parentService->createParent($request->validated(), $currentSchool);
        return ApiResponseService::success("Parent Created Successfully", $createParent, null, 201);
    }
    public function deleteParent(Request $request, string $parentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteParent = $this->parentService->deleteParent($parentId, $currentSchool);
        return ApiResponseService::success("Parent Deleted Successfully", $deleteParent, null,200);
    }

    public function updateParent(UpdateParentRequest $request, $parentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateParent = $this->parentService->updateParent($request->all(),$parentId, $currentSchool);
        return ApiResponseService::success('Parent Updated Sucessfully', $updateParent, null,200);
    }

    public function getParentDetails(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $parentId = $request->route("parentId");
         $parentDetails = $this->parentService->getParentDetails( $parentId, $currentSchool);
         return ApiResponseService::success("Parent Details Fetched Sucessfully", $parentDetails, null,200);
    }

    public function BulkUpdateParents(BulkUpdateParentRequest $request){
       try{
          $bulkUpdateParent = $this->parentService->bulkUpdateParent($request->parents);
          return ApiResponseService::success("Parent Updated Successfully", $bulkUpdateParent, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }

    public function bulkDeleteParents(ParentIdRequest $request){
      try{
         $bulkDeleteParent = $this->parentService->bulkDeleteParent($request->parentIds);
         return ApiResponseService::success("Parents Deleted Successfully", $bulkDeleteParent, null, 200);
      }
      catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
      }
    }
}
