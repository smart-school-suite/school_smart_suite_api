<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\ElectionOffice\AppointOfficeHolderRequest;
use App\Http\Requests\ElectionOffice\ReplaceOfficeHolderRequest;
use App\Http\Requests\ElectionOffice\TerminateOfficeHolderRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionOfficeHolderService;
use Illuminate\Http\Request;

class ElectionOfficeController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionOfficeHolderService $officeHolderService) {}

    public function getOfficeHolders(Request $request)
    {
        $holders = $this->officeHolderService->getOfficeHolders($this->currentSchool($request), $request->only(['election_role_id', 'election_id', 'status', 'appointment_type']));
        return ApiResponseService::success("Office Holders Fetched Successfully", $holders, null, 200);
    }

    public function getCurrentOfficeHolders(Request $request)
    {
        return ApiResponseService::success("Current Office Holders Fetched Successfully", $this->officeHolderService->getCurrentOfficeHolders($this->currentSchool($request)), null, 200);
    }

    public function getOfficeHolderDetails(Request $request, string $holderId)
    {
        return ApiResponseService::success("Office Holder Details Fetched Successfully", $this->officeHolderService->getOfficeHolderDetails($holderId, $this->currentSchool($request)), null, 200);
    }

    public function appointOfficeHolder(AppointOfficeHolderRequest $request)
    {
        $this->adminUser();
        $holder = $this->officeHolderService->appointHolder($request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Office Holder Appointed Successfully", $this->officeHolderService->present($holder), null, 201);
    }

    public function replaceOfficeHolder(ReplaceOfficeHolderRequest $request, string $holderId)
    {
        $this->adminUser();
        $holder = $this->officeHolderService->replaceHolder($holderId, $request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Office Holder Replaced Successfully", $this->officeHolderService->present($holder), null, 201);
    }

    public function terminateOfficeHolder(TerminateOfficeHolderRequest $request, string $holderId)
    {
        $this->adminUser();
        $holder = $this->officeHolderService->terminateHolder($holderId, $request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Office Holder Terminated Successfully", $this->officeHolderService->present($holder), null, 200);
    }

    public function getParticipantHistory(Request $request, string $participantType, string $participantId)
    {
        $history = $this->officeHolderService->getParticipantHistory($this->currentSchool($request), $participantType, $participantId);
        return ApiResponseService::success("Election History Fetched Successfully", $history, null, 200);
    }
}
