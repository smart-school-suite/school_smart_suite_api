<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Http\Requests\GraduationBatch\UpdateGraduationBatchRequest;
use App\Http\Resources\Graduation\GraduationBatchResource;
use App\Services\ApiResponseService;
use App\Services\Graduation\GraduationBatchService;
use Illuminate\Http\Request;

class GraduationBatchController extends Controller
{
    protected GraduationBatchService $graduationBatchService;
    public function __construct(GraduationBatchService $graduationBatchService)
    {
        $this->graduationBatchService = $graduationBatchService;
    }

    public function updateGraduationBatch(UpdateGraduationBatchRequest $request, string $batchId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateBatch = $this->graduationBatchService->updateGraduationBatch($currentSchool, $request->validated(), $batchId);
        return ApiResponseService::success("Graduation Batch Updated Successfully", $updateBatch, null, 200);
    }

    public function getGraduationBatchDetails(Request $request, string $batchId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $details = $this->graduationBatchService->getGraduationBatchDetails($currentSchool, $batchId);
        return ApiResponseService::success("Graduation Batch Details Fetched Successfully", $details, null, 200);
    }

    public function getGraduationBatches(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $graduationBatches = $this->graduationBatchService->getGraduationBatches($currentSchool);
        return ApiResponseService::success("Graduation Batches Fetched Successfully", GraduationBatchResource::collection($graduationBatches), null, 200);
    }
}
