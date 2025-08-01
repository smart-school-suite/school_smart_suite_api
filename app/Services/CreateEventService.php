<?php

namespace App\Services;

use App\Jobs\EmailNotificationJobs\EmailEventNotificationJob;
use App\Models\EventAuthor;
use App\Models\EventInvitedCustomGroups;
use App\Models\EventInvitedMember;
use App\Models\EventInvitedPresetGroup;
use App\Models\Parents;
use App\Models\PresetAudiences;
use App\Models\SchoolAdmin;
use App\Models\SchoolEvent;
use App\Models\SchoolSetAudienceGroups;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CreateEventService
{
    /**
     * Creates a new school event and dispatches notifications if applicable.
     *
     * @param array $schoolEventData Data for the school event.
     * @param object $currentSchool The current school object.
     * @param array $authUserData Authenticated user data.
     * @return array An array containing event details and receiver count.
     * @throws Throwable If an error occurs during event creation.
     */
    public function createEvent(array $schoolEventData, object $currentSchool, array $authUserData, $eventBackgroundImg): array
    {
        $targetUserRecords = [];
        $customGroupDataToInsert = [];
        $presetGroupDataToInsert = [];

        try {
            DB::beginTransaction();

            $eventId = Str::uuid()->toString();
            $status = $schoolEventData['status'];
            $publishedAt = Arr::get($schoolEventData, 'published_at');

            $this->addIndividualTargets($schoolEventData, $currentSchool, $eventId, $targetUserRecords);

            if (!empty($schoolEventData['school_set_group_ids'])) {
                $this->processCustomGroupTargets($schoolEventData['school_set_group_ids'], $currentSchool, $eventId, $targetUserRecords, $customGroupDataToInsert);
            }

            if (!empty($schoolEventData['preset_group_ids'])) {
                $this->processPresetGroupTargets($schoolEventData['preset_group_ids'], $currentSchool, $eventId, $targetUserRecords, $presetGroupDataToInsert);
            }
            $uniqueTargetRecords = collect($targetUserRecords)
                ->unique(fn($item) => $item['actorable_id'] . '_' . $item['actorable_type'])
                ->values()
                ->all();

            $targetUserRecords = $uniqueTargetRecords;

            $inviteeCount = count($targetUserRecords);

            $this->createSchoolEvent($schoolEventData, $eventId, $currentSchool, $inviteeCount, $eventBackgroundImg);

            $this->insertUniqueEventInvitedMembers($targetUserRecords, $eventId, $currentSchool);

            if (!empty($customGroupDataToInsert)) {
                EventInvitedCustomGroups::insert($customGroupDataToInsert);
            }

            if (!empty($presetGroupDataToInsert)) {
                EventInvitedPresetGroup::insert($presetGroupDataToInsert);
            }

            $this->createEventAuthor($authUserData, $eventId, $currentSchool);

            DB::commit();

            $this->dispatchNotificationJob($status, $publishedAt, $eventId);

            return [
                'event_title' => $schoolEventData['title'],
                'event_description' => $schoolEventData['description'] ?? null,
                'number_of_recipients' => count($targetUserRecords) ?? 0,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    /**
     * Adds individual targets (Parents, Students, Teachers, School Admins) to the targetUserRecords array.
     *
     * @param array $schoolEventData
     * @param object $currentSchool
     * @param string $eventId
     * @param array $targetUserRecords
     * @return void
     */
    private function addIndividualTargets(array $schoolEventData, object $currentSchool, string $eventId, array &$targetUserRecords): void
    {
        $targetTypes = [
            'parent_ids' => Parents::class,
            'student_ids' => Student::class,
            'teacher_ids' => Teacher::class,
            'school_admin_ids' => SchoolAdmin::class,
        ];

        foreach ($targetTypes as $key => $modelClass) {
            $ids = Arr::get($schoolEventData, $key, []);
            $this->addTargets($currentSchool, $ids, $modelClass, $eventId, $targetUserRecords);
        }
    }

    /**
     * Processes custom group targets and adds their members to targetUserRecords.
     * It now collects the data for EventInvitedCustomGroups to be inserted later.
     *
     * @param array $schoolSetGroupIds
     * @param object $currentSchool
     * @param string $eventId
     * @param array $targetUserRecords
     * @param array $customGroupDataToInsert
     * @return void
     */
    private function processCustomGroupTargets(array $schoolSetGroupIds, object $currentSchool, string $eventId, array &$targetUserRecords, array &$customGroupDataToInsert): void
    {
        $groupMembers = SchoolSetAudienceGroups::whereIn('id', $schoolSetGroupIds)->get();

        foreach ($groupMembers as $groupMember) {
            $this->addTargets(
                $currentSchool,
                [$groupMember->audienceable_id],
                $groupMember->audienceable_type,
                $eventId,
                $targetUserRecords
            );
        }
        foreach ($schoolSetGroupIds as $schoolSetGroupId) {
            $customGroupDataToInsert[] = [
                'school_set_audience_group_id' => $schoolSetGroupId,
                'event_id' => $eventId,
                'school_branch_id' => $currentSchool->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    /**
     * Processes preset group targets and adds their members to targetUserRecords.
     * It now collects the data for EventInvitedPresetGroup to be inserted later.
     *
     * @param array $presetGroupIds
     * @param object $currentSchool
     * @param string $eventId
     * @param array $targetUserRecords
     * @param array $presetGroupDataToInsert
     * @return void
     */
    private function processPresetGroupTargets(array $presetGroupIds, object $currentSchool, string $eventId, array &$targetUserRecords, array &$presetGroupDataToInsert): void
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
                    $this->addTargets($currentSchool, SchoolAdmin::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), SchoolAdmin::class, $eventId, $targetUserRecords);
                    $this->addTargets($currentSchool, Teacher::where('school_branch_id', $currentSchool->id)->pluck('id')->toArray(), Teacher::class, $eventId, $targetUserRecords);
                    $this->addTargets($currentSchool, Student::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Student::class, $eventId, $targetUserRecords);
                    $this->addTargets($currentSchool, Parents::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Parents::class, $eventId, $targetUserRecords);
                    continue 2;
                case "level-one-students":
                case "level-two-students":
                case "level-three-students":
                case "bachelor-students":
                case "masters-one-students":
                case "masters-two-students":
                    $levelName = $this->mapPresetTargetToLevelName($presetTarget);
                    $ids = Student::whereHas('level', fn($query) => $query->where('name', $levelName))
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
                $this->addTargets(
                    $currentSchool,
                    $ids,
                    $modelClass,
                    $eventId,
                    $targetUserRecords
                );
            }
        }

        foreach ($presetGroupIds as $presetGroupId) {
            $presetGroupDataToInsert[] = [
                'id' => Str::uuid(),
                'preset_group_id' => $presetGroupId,
                'event_id' => $eventId,
                'school_branch_id' => $currentSchool->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    /**
     * Maps a preset target string to its corresponding level name.
     *
     * @param string $presetTarget
     * @return string
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
     * Adds target user records to the provided array.
     *
     * @param object $currentSchool
     * @param array $ids
     * @param string $userType
     * @param string $eventId
     * @param array $targetUserRecords
     * @return void
     */
    private function addTargets(
        object $currentSchool,
        array $ids,
        string $userType,
        string $eventId,
        array &$targetUserRecords
    ): void {
        foreach ($ids as $id) {
            $targetUserRecords[] = [
                'id' => Str::uuid(),
                'actorable_id' => $id,
                'actorable_type' => $userType,
                'event_id' => $eventId,
                'school_branch_id' => $currentSchool->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    /**
     * Inserts unique event invited members into the database.
     *
     * @param array $targetUserRecords
     * @param string $eventId
     * @param object $currentSchool
     * @return void
     */
    private function insertUniqueEventInvitedMembers(array &$targetUserRecords, string $eventId, object $currentSchool): void
    {
        if (empty($targetUserRecords)) {
            return;
        }
        foreach (array_chunk($targetUserRecords, 1000) as $chunk) {
            EventInvitedMember::insert($chunk);
        }
    }

    /**
     * Creates the SchoolEvent record.
     *
     * @param array $schoolEventData
     * @param string $eventId
     * @param object $currentSchool
     * @param int $inviteeCount
     * @return void
     */
    private function createSchoolEvent(array $schoolEventData, string $eventId, object $currentSchool, int $inviteeCount, $eventBackgroundImg): void
    {
        $status = $schoolEventData['status'];
        $publishedAt = null;

        if ($status === 'active') {
            $publishedAt = now();
        } elseif ($status === 'scheduled') {
            $publishedAt = Arr::get($schoolEventData, 'published_at');
        }

        SchoolEvent::create([
            'id' => $eventId,
            'title' => $schoolEventData['title'],
            'description' => $schoolEventData['description'] ?? null,
            'organizer' => $schoolEventData['organizer'] ?? null,
            'location' => $schoolEventData['location'] ?? null,
            'status' => $status,
            'background_image' => $this->handlePictureUpload($eventBackgroundImg) ?? null,
            'start_date' => $schoolEventData['start_date'] ?? null,
            'end_date' => $schoolEventData['end_date'] ?? null,
            'invitee_count' => $inviteeCount ?? null,
            'published_at' => $publishedAt ?? null,
            'notification_sent_at' => null,
            'expires_at' => $schoolEventData['end_date'] ?? null,
            'school_branch_id' => $currentSchool->id,
            'event_category_id' => Arr::get($schoolEventData, 'event_category_id') ?? null,
            'tag_id' => Arr::get($schoolEventData, 'tag_id') ?? null,
        ]);
    }

    private function handlePictureUpload($eventImage)
    {
        if($eventImage){
          $fileName = time() . '.' . $eventImage->getClientOriginalExtension();
          $eventImage->storeAs('public/EventBackgroundImg', $fileName);
          return $fileName;
        }
        return null;

    }

    /**
     * Creates the EventAuthor record.
     *
     * @param array $authUserData
     * @param string $eventId
     * @return void
     */
    private function createEventAuthor(array $authUserData, string $eventId, object $currentSchool): void
    {
        EventAuthor::create([
            'authorable_id' => $authUserData['userId'],
            'authorable_type' => $authUserData['userType'],
            'school_branch_id' => $currentSchool->id,
            'event_id' => $eventId,
        ]);
    }

    /**
     * Dispatches the email notification job if the event is scheduled or active.
     *
     * @param string $status
     * @param string|null $publishedAt
     * @param string $eventId
     * @return void
     */
    private function dispatchNotificationJob(string $status, ?string $publishedAt, string $eventId): void
    {
        if ($status === 'scheduled' || $status === 'active') {
            $delayInSeconds = 0;
            if ($publishedAt) {
                $scheduledTime = Carbon::parse($publishedAt);
                $delayInSeconds = now()->diffInSeconds($scheduledTime, false);
                if ($delayInSeconds < 0) {
                    $delayInSeconds = 0;
                }
            }

            Log::info("Dispatching SendEventEmailNotificationJob for Event ID: {$eventId} with delay: {$delayInSeconds} seconds.");

            EmailEventNotificationJob::dispatch($eventId)
                ->delay(now()->addSeconds($delayInSeconds));
        }
    }
}
