<?php

namespace App\Services;

use App\Models\Audiences;
use App\Models\SchoolSetAudienceGroups;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SchoolSetAudienceGroupService
{
    // Implement your logic here
    public function createAudienceGroup(array $data, object $currentSchool)
    {
        try {
            $audienceGroup = SchoolSetAudienceGroups::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'school_branch_id' => $currentSchool->id
            ]);
            return $audienceGroup;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function updateAudienceGroup(array $data, $schoolSetAudienceGroupId)
    {
        try {
            $audienceGroup = SchoolSetAudienceGroups::findOrFail($schoolSetAudienceGroupId);
            $cleanData = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });
            $audienceGroup->update($cleanData);
            return $audienceGroup;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function deleteAudienceGroup($schoolSetAudienceGroupId)
    {
        try {
            $audienceGroup = SchoolSetAudienceGroups::findOrFail($schoolSetAudienceGroupId);
            $audienceGroup->delete();
            return $audienceGroup;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getAudienceGroups($currentSchool)
    {
        try {
            $audienceGroups = SchoolSetAudienceGroups::where('school_branch_id', $currentSchool->id)->get();
            return $audienceGroups;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getAudienceGroupDetails($schoolSetAudienceGroupId)
    {
        try {
            $audienceGroup = SchoolSetAudienceGroups::findOrFail($schoolSetAudienceGroupId);
            return $audienceGroup;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getAudienceGroupMembers($schoolSetAudienceGroupId)
    {
        try {
            $audienceGroup = Audiences::where('school_set_audience_group_id', $schoolSetAudienceGroupId)
                ->with('audienceable')
                ->get();
            return [
                'audience_group_id' => $schoolSetAudienceGroupId,
                'members' => $audienceGroup,
                'count' => $audienceGroup->count()
            ];
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function removeMembersFromAudienceGroup(array $audienceData)
    {
        try {
            $removedMembers = [];
            $membersToRemoveIds = $audienceData['member_ids'];
            foreach ($membersToRemoveIds as $memberId) {
              $removedMemeber =  Audiences::where('id', $memberId)
                    ->where('school_set_audience_group_id', $audienceData['audience_group_id'])
                    ->delete();
              $removedMembers[] = $removedMemeber;
            }
            return $removedMembers;
        }
        catch (QueryException $e) {
            throw $e;
        }
        catch (Throwable $e) {
            throw $e;
        }
    }

    public function addMembersToAudienceGroup(array $audienceData)
    {
        try {
            $audienceGroup = SchoolSetAudienceGroups::findOrFail($audienceData['audience_group_id']);
            $membersToAttach = [];
            $memberTypes = [
                'school_admin_ids' => 'App\Models\Schooladmin',
                'parent_ids' => 'App\Models\Parents',
                'student_ids' => 'App\Models\Student',
                'teacher_ids' => 'App\Models\Teacher',
            ];

            return DB::transaction(function () use ($audienceGroup, $audienceData, $memberTypes, $membersToAttach) {

                foreach ($memberTypes as $key => $typeClass) {
                    if (!empty($audienceData[$key])) {
                        $ids = $audienceData[$key];
                        $membersToAttach = [];
                        foreach ($ids as $memberId) {
                            $membersToAttach[] = [
                                'id' => Str::uuid()->toString(),
                                'audienceable_id' => $memberId,
                                'audienceable_type' => $typeClass,
                                'school_set_audience_group_id' => $audienceGroup->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        DB::table('audiences')->insert($membersToAttach);
                    }
                }

                return $membersToAttach;
            });
        } catch (QueryException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        }
    }

}
