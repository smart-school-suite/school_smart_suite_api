<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolSetAudienceGroup\AddAudienceGroupMembersRequest;
use App\Http\Requests\SchoolSetAudienceGroup\CreateSchoolSetAudienceGroupRequest;
use App\Http\Requests\SchoolSetAudienceGroup\RemoveAudienceGroupMembersRequest;
use App\Http\Requests\SchoolSetAudienceGroup\UpdateSchoolSetAudienceGroupRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolSetAudienceGroupService;
use Illuminate\Database\QueryException;
use Throwable;

class SchoolSetAudienceGroupController extends Controller
{
    //
    protected $schoolSetAudienceGroupService;
    public function __construct(SchoolSetAudienceGroupService $schoolSetAudienceGroupService)
    {
        $this->schoolSetAudienceGroupService = $schoolSetAudienceGroupService;
    }

    public function createAudienceGroup(CreateSchoolSetAudienceGroupRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $createAudienceGroup = $this->schoolSetAudienceGroupService->createAudienceGroup($request->validated(), $currentSchool);
            return ApiResponseService::success(
                'Audience group created successfully',
                $createAudienceGroup,
                null,
                201
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function updateAudienceGroup(UpdateSchoolSetAudienceGroupRequest $request, $schoolSetAudienceGroupId)
    {
        try {
            $updateAudienceGroup = $this->schoolSetAudienceGroupService->updateAudienceGroup($request->validated(), $schoolSetAudienceGroupId, );
            return ApiResponseService::success(
                'Audience group updated successfully',
                $updateAudienceGroup,
                null,
                200
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function deleteAudienceGroup($schoolSetAudienceGroupId)
    {
        try {
            $deleteAudienceGroup = $this->schoolSetAudienceGroupService->deleteAudienceGroup($schoolSetAudienceGroupId);
            return ApiResponseService::success(
                'Audience group deleted successfully',
                $deleteAudienceGroup,
                null,
                200
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function getAudienceGroups(Request $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $getSchoolSetAudienceGroups = $this->schoolSetAudienceGroupService->getAudienceGroups($currentSchool);
            return ApiResponseService::success(
                'Audience groups retrieved successfully',
                $getSchoolSetAudienceGroups,
                null,
                200
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function getAudienceGroupDetails($schoolSetAudienceGroupId)
    {
        try {
            $getAudienceGroupDetails = $this->schoolSetAudienceGroupService->getAudienceGroupDetails($schoolSetAudienceGroupId);
            return ApiResponseService::success(
                'Audience group details retrieved successfully',
                $getAudienceGroupDetails,
                null,
                200
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }
    public function getAudienceGroupMembers($schoolSetAudienceGroupId)
    {
        try {
            $getAudienceGroupMembers = $this->schoolSetAudienceGroupService->getAudienceGroupMembers($schoolSetAudienceGroupId);
            return ApiResponseService::success(
                'Audience group members retrieved successfully',
                $getAudienceGroupMembers,
                null,
                200
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function removeAudienceGroupMembers(RemoveAudienceGroupMembersRequest $request)
    {
        try {
            $removeAudienceGroupMembers = $this->schoolSetAudienceGroupService->removeMembersFromAudienceGroup($request->validated());
            return ApiResponseService::success(
                'Audience group members removed successfully',
                $removeAudienceGroupMembers,
                null,
                201
            );
        } catch (QueryException $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    public function addAudienceGroupMembers(AddAudienceGroupMembersRequest $request)
    {
        try {
            $addAudienceGroupMembers = $this->schoolSetAudienceGroupService->addMembersToAudienceGroup($request->validated());
            return ApiResponseService::success(
                'Audience group members added successfully',
                $addAudienceGroupMembers,
                null,
                201
            );
        } catch (QueryException $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        } catch (Throwable $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }
}
