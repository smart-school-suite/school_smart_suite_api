<?php

namespace App\Http\Controllers;


use App\Services\ApiResponseService;
use App\Http\Resources\ParentResource;
use App\Http\Requests\Parent\UpdateParentRequest;
use App\Http\Requests\Parent\CreateParentRequest;
use App\Http\Requests\Parent\BulkUpdateParentRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\ParentService;
use Exception;
use Illuminate\Http\Request;

class ParentsController extends Controller
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

    public function updateParent(UpdateParentRequest $request, $parent_id){
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

    public function BulkUpdateParents(BulkUpdateParentRequest $request){
       try{
          $bulkUpdateParent = $this->parentService->bulkUpdateParent($request->parents);
          return ApiResponseService::success("Parent Updated Successfully", $bulkUpdateParent, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }

    public function bulkDeleteParents($parentIds){
        $idsArray = explode(',', $parentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_dropout,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
      try{
         $bulkDeleteParent = $this->parentService->bulkDeleteParent($idsArray);
         return ApiResponseService::success("Parents Deleted Successfully", $bulkDeleteParent, null, 200);
      }
      catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
      }
    }

}
