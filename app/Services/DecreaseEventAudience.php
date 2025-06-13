<?php

namespace App\Services;

use App\Models\EventInvitedCustomGroups;
use App\Models\EventInvitedMember;
use App\Models\EventInvitedPresetGroup;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\PresetAudiences;
use App\Models\Schooladmin;
use App\Models\Teacher;
use App\Models\Parents;
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\SchoolSetAudienceGroups;
use Illuminate\Support\Facades\Log;

class DecreaseEventAudience
{
    /**
     * Decreases the audience for an event.
     *
     * @param array $audienceData Contains 'preset_group_ids' and 'school_set_group_ids'.
     * @param string $eventId The ID of the event.
     * @param object $currentSchool The current school object.
     * @throws Exception If the event is expired or an error occurs during the process.
     */
    public function decreaseEventAudience(array $audienceData, string $eventId, object $currentSchool)
    {
        $targetUserRecords = [];

        try {
            DB::beginTransaction();

            $event = SchoolEvent::where("school_branch_id", $currentSchool->id)->findOrFail($eventId);

            if ($event->status === 'expired') {
                throw new Exception("Cannot reduce or add audience for {$event->title}");
            }

            if (!empty($audienceData['preset_group_ids'])) {
                $this->processEventPresetGroups($audienceData['preset_group_ids'], $currentSchool, $eventId, $targetUserRecords);
            }

            if (!empty($audienceData['school_set_group_ids'])) {
                $this->processEventCustomGroups($audienceData['school_set_group_ids'], $currentSchool, $eventId, $targetUserRecords);
            }

            $uniqueTargetUsers = $this->getUniqueTargetUsers($targetUserRecords);
            $this->removeInvitedMembers($uniqueTargetUsers, $eventId);


            if (!empty($audienceData['school_set_group_ids'])) {
                $this->removeCustomGroups($audienceData['school_set_group_ids'], $eventId);
            }

            if (!empty($audienceData['preset_group_ids'])) {
                $this->removeEventPresetGroups($audienceData['preset_group_ids'], $eventId);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to decrease event audience for event ID {$eventId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processes preset groups to identify members to be removed.
     *
     * @param array $presetGroupIds Array of preset group IDs.
     * @param object $currentSchool The current school object.
     * @param string $eventId The event ID.
     * @param array $targetUserRecords Reference to the array to store target user records.
     */
    private function processEventPresetGroups(array $presetGroupIds, object $currentSchool, string $eventId, array &$targetUserRecords): void
    {
        $presetTargets = PresetAudiences::whereIn('id', $presetGroupIds)->pluck('target')->toArray();

        foreach ($presetTargets as $presetTarget) {
            $ids = [];
            $modelClass = '';

            switch ($presetTarget) {
                case "school-admins":
                    $ids = SchoolAdmin::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray();
                    $modelClass = SchoolAdmin::class;
                    break;
                case "teachers":
                    $ids = Teacher::where('school_branch_id', $currentSchool->id)->pluck('id')->toArray();
                    $modelClass = Teacher::class;
                    break;
                case "students":
                    $ids = Student::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray();
                    $modelClass = Student::class;
                    break;
                case "parents":
                    $ids = Parents::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray();
                    $modelClass = Parents::class;
                    break;
                case "all-users":
                    $this->addTargets(SchoolAdmin::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), SchoolAdmin::class, $eventId, $targetUserRecords);
                    $this->addTargets(Teacher::where('school_branch_id', $currentSchool->id)->pluck('id')->toArray(), Teacher::class, $eventId, $targetUserRecords);
                    $this->addTargets(Student::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Student::class, $eventId, $targetUserRecords);
                    $this->addTargets(Parents::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Parents::class, $eventId, $targetUserRecords);
                    continue 2;
                case "level-one-students":
                case "level-two-students":
                case "level-three-students":
                case "bachelor-students":
                case "masters-one-students":
                case "masters-two-students":
                    $levelName = $this->mapPresetTargetToLevelName($presetTarget);
                    $ids = Student::whereHas('level', fn ($query) => $query->where('name', $levelName))
                        ->where('school_branch_id', $currentSchool->id)
                        ->pluck('id')
                        ->toArray();
                    $modelClass = Student::class;
                    break;
                default:
                    Log::warning("Unknown preset audience target: {$presetTarget}");
                    continue 2;
            }

            if (!empty($ids) && !empty($modelClass)) {
                $this->addTargets($ids, $modelClass, $eventId, $targetUserRecords);
            }
        }
    }

    /**
     * Processes custom groups to identify members to be removed.
     *
     * @param array $schoolSetGroupIds Array of school set group IDs.
     * @param object $currentSchool The current school object.
     * @param string $eventId The event ID.
     * @param array $targetUserRecords Reference to the array to store target user records.
     */
    private function processEventCustomGroups(array $schoolSetGroupIds, object $currentSchool, string $eventId, array &$targetUserRecords): void
    {
        $groupMembers = SchoolSetAudienceGroups::whereIn('id', $schoolSetGroupIds)->get();

        foreach ($groupMembers as $groupMember) {
            $this->addTargets(
                [$groupMember->audienceable_id],
                $groupMember->audienceable_type,
                $eventId,
                $targetUserRecords
            );
        }
    }

    /**
     * Adds target user records to the array.
     *
     * @param array $ids Array of user IDs.
     * @param string $userType The type of user (e.g., SchoolAdmin::class).
     * @param string $eventId The event ID.
     * @param array $targetUserRecords Reference to the array to store target user records.
     */
    private function addTargets(array $ids, string $userType, string $eventId, array &$targetUserRecords): void
    {
        foreach ($ids as $id) {
            $targetUserRecords[] = [
                'actorable_id' => $id,
                'actorable_type' => $userType,
                'event_id' => $eventId,
            ];
        }
    }

    /**
     * Maps a preset target string to its corresponding level name.
     *
     * @param string $presetTarget The preset target string.
     * @return string The mapped level name.
     */
    private function mapPresetTargetToLevelName(string $presetTarget): string
    {
        return match ($presetTarget) {
            'level-one-students' => 'Level One',
            'level-two-students' => 'Level Two',
            'level-three-students' => 'Level Three',
            'bachelor-students' => "Bachelor's Degree Programs",
            'masters-one-students' => "Master's Degree One",
            'masters-two-students' => "Master's Degree Two",
            default => '',
        };
    }

    /**
     * Filters and returns unique target user records.
     *
     * @param array $targetUserRecords The array of target user records.
     * @return array An array of unique target user records.
     */
    private function getUniqueTargetUsers(array $targetUserRecords): array
    {
        $uniqueRecords = [];
        $seen = [];

        foreach ($targetUserRecords as $record) {
            $key = $record['actorable_id'] . '|' . $record['actorable_type'];
            if (!isset($seen[$key])) {
                $uniqueRecords[] = $record;
                $seen[$key] = true;
            }
        }
        return $uniqueRecords;
    }

    /**
     * Removes invited members from the event.
     *
     * @param array $targetUsers The array of unique target users to remove.
     * @param string $eventId The event ID.
     */
    private function removeInvitedMembers(array $targetUsers, string $eventId): void
    {
        foreach ($targetUsers as $targetUser) {
            EventInvitedMember::where("event_id", $eventId)
                ->where("actorable_id", $targetUser['actorable_id'])
                ->where("actorable_type", $targetUser['actorable_type']) // Added actorable_type for more specific deletion
                ->delete();
        }
    }

    /**
     * Removes custom groups from the event.
     *
     * @param array $schoolSetGroupIds Array of school set group IDs to remove.
     * @param string $eventId The event ID.
     */
    private function removeCustomGroups(array $schoolSetGroupIds, string $eventId): void
    {
        foreach ($schoolSetGroupIds as $schoolSetGroupId) {
            $customInvitedGroup = EventInvitedCustomGroups::where("school_set_audience_group_id", $schoolSetGroupId)
                ->where("event_id", $eventId)
                ->first();

            if ($customInvitedGroup) { // Check if the group exists before deleting
                $customInvitedGroup->delete();
            } else {
                Log::warning("Attempted to remove non-existent custom invited group with ID {$schoolSetGroupId} for event {$eventId}.");
            }
        }
    }

    /**
     * Removes event preset groups.
     *
     * @param array $presetGroupIds Array of preset group IDs to remove.
     * @param string $eventId The event ID.
     */
    private function removeEventPresetGroups(array $presetGroupIds, string $eventId): void
    {
        foreach ($presetGroupIds as $presetGroupId) {
            $presetInvitedGroup = EventInvitedPresetGroup::where("preset_group_id", $presetGroupId)
                ->where("event_id", $eventId)
                ->first();

            if ($presetInvitedGroup) { // Check if the group exists before deleting
                $presetInvitedGroup->delete();
            } else {
                Log::warning("Attempted to remove non-existent preset invited group with ID {$presetGroupId} for event {$eventId}.");
            }
        }
    }
}
