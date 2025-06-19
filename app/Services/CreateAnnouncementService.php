<?php

namespace App\Services;

use App\Jobs\EmailNotificationJobs\EmailAnnouncementNotificationJob;
use App\Jobs\StatisticalJobs\OperationalJobs\AnnouncementStatJob;
use App\Models\Announcement;
use App\Models\AnnouncementAuthor;
use App\Models\AnnouncementTargetUser;
use App\Models\Parents;
use App\Models\PresetAudiences;
use App\Models\Schooladmin;
use App\Models\SchoolAnnouncementSetting;
use App\Models\SchoolSetAudienceGroups;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

class CreateAnnouncementService
{
    /**
     * Creates a new announcement and assigns target users.
     * It now dispatches the email notification job with a delay if scheduled.
     *
     * @param object $currentSchool The current school branch object (e.g., SchoolBranch model instance).
     * @param array $announcementData Array containing announcement details and target IDs.
     * Expected keys for announcementData: 'title', 'content', 'status', 'published_at',
     * 'expires_at', 'category_id', 'label_id', 'tag_id',
     * 'parent_Ids' (optional), 'student_Ids' (optional), 'teacher_Ids' (optional),
     * 'school_admin_Ids' (optional), 'school_set_group_ids' (optional),
     * 'preset_group_ids' (optional).
     * @param array $authUserData Array containing authenticated user's ID and type.
     * Expected keys for authUserData: 'userId', 'userType' (e.g., 'App\Models\Admin').
     * @return array
     * @throws Throwable
     */
    public function createAnnouncement(object $currentSchool, array $announcementData, array $authUserData): array
    {
        $targetUserRecords = [];

        try {
            DB::beginTransaction();

            $announcementId = Str::uuid()->toString();

            $userStatus = $announcementData['status'] ?? null;
            $publishedAtInput = $announcementData['published_at'] ?? now();

            $publishedAt = null; // Initialize publishedAt to null by default
            $status = 'draft';   // Initialize status to 'draft' by default

            if ($userStatus === 'draft') {
                $publishedAt = null;
                $status = 'draft';
            } else {
                try {
                    $publishedAt = Carbon::parse($publishedAtInput);
                } catch (\Exception $e) {
                    $publishedAt = now();
                }

                $status = match (true) {
                    $publishedAt->isFuture() => 'scheduled',
                    $publishedAt->lessThanOrEqualTo(now()) => 'active',
                    default => 'draft', // Fallback for any unexpected case, though Carbon::parse usually handles this
                };
            }

            Announcement::create([
                'id' => $announcementId,
                'title' => $announcementData['title'],
                'content' => $announcementData['content'],
                'status' => $status,
                'published_at' => $publishedAt,
                'expires_at' => $announcementData['expires_at'] ?? $this->announcementDefaults($currentSchool),
                'notification_sent_at' => null,
                'category_id' => $announcementData['category_id'],
                'label_id' => $announcementData['label_id'],
                'tag_id' => $announcementData['tag_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            AnnouncementAuthor::create([
                'authorable_id' => $authUserData['userId'],
                'authorable_type' => $authUserData['userType'],
                'announcement_id' => $announcementId,
            ]);


            $usersForNotification = []; // This array is actually for populating targetUserRecords now.

            $this->addTargets(
                $currentSchool,
                $announcementData['parent_ids'] ?? [],
                Parents::class,
                $announcementId,
                $usersForNotification,
                $targetUserRecords
            );
            $this->addTargets(
                $currentSchool,
                $announcementData['student_ids'] ?? [],
                Student::class,
                $announcementId,
                $usersForNotification,
                $targetUserRecords
            );
            $this->addTargets(
                $currentSchool,
                $announcementData['teacher_ids'] ?? [],
                Teacher::class,
                $announcementId,
                $usersForNotification,
                $targetUserRecords
            );
            $this->addTargets(
                $currentSchool,
                $announcementData['school_admin_ids'] ?? [],
                Schooladmin::class,
                $announcementId,
                $usersForNotification,
                $targetUserRecords
            );

            if (!empty($announcementData['school_set_group_ids'])) {
                $groupMembers = SchoolSetAudienceGroups::whereIn('school_set_audience_group_id', $announcementData['school_set_group_ids'])->get();

                foreach ($groupMembers as $groupMember) {
                    $this->addTargets(
                        $currentSchool,
                        [$groupMember->audienceable_id],
                        $groupMember->audienceable_type,
                        $announcementId,
                        $usersForNotification,
                        $targetUserRecords
                    );
                }
            }

            if (!empty($announcementData['preset_group_ids'])) {
                $this->processPresetGroupTargets(
                    $announcementId,
                    $usersForNotification,
                    $targetUserRecords,
                    $announcementData['preset_group_ids'],
                    $currentSchool
                );
            }

            if (!empty($targetUserRecords)) {
                $uniqueTargetRecords = collect($targetUserRecords)
                    ->unique(function ($item) {
                        return $item['actorable_id'] . '_' . $item['actorable_type'];
                    })
                    ->values()
                    ->all();
                AnnouncementTargetUser::insert($uniqueTargetRecords);
            }

            DB::commit();

            if ($status === 'scheduled' || $status === 'active') {

                $delayInSeconds = now()->diffInSeconds($publishedAt, false);
                if ($delayInSeconds < 0) {
                    $delayInSeconds = 0;
                }
                EmailAnnouncementNotificationJob::dispatch($announcementId)
                    ->delay(Carbon::now()->addSeconds($delayInSeconds));
            }

            AnnouncementStatJob::dispatch($currentSchool->id, $announcementId);
            return [
                'annoucement_title' => $announcementData['title'],
                'announcement_content' => $announcementData['content'],
                'number_of_recievers' => count($targetUserRecords) // Use targetUserRecords for more accuracy
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    /**
     * Helper method to add target users and prepare for batch insertion.
     *
     * @param object $currentSchool The current school branch object.
     * @param array $ids Array of user IDs to target.
     * @param string $userType The fully qualified class name of the user model (e.g., `Parents::class`).
     * @param string $announcementId The ID of the newly created announcement.
     * @param array $usersForNotification A reference to the array collecting all users for notification (not directly used for dispatch anymore).
     * @param array $targetUserRecords A reference to the array for batch inserting AnnouncementTargetUser records.
     * @return void
     */
    private function addTargets(
        object $currentSchool, // Added this to match the calls
        array $ids,
        string $userType,
        string $announcementId,
        array &$usersForNotification,
        array &$targetUserRecords
    ): void {
        foreach ($ids as $id) {
            $targetUserRecords[] = [
                'id' => Str::uuid(), // Ensure UUID for target user records as well
                'actorable_id' => $id,
                'actorable_type' => $userType,
                'announcement_id' => $announcementId,
                'school_branch_id' => $currentSchool->id, // Make sure this is stored if needed
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    /**
     * Processes preset group targets and adds them to the notification and target records.
     *
     * @param string $announcementId The ID of the announcement.
     * @param array $usersForNotification A reference to the array collecting all users for notification (not directly used for dispatch anymore).
     * @param array $targetUserRecords A reference to the array for batch inserting AnnouncementTargetUser records.
     * @param array $presetGroupIds Array of preset audience group IDs.
     * @param object $currentSchool The current school branch object.
     * @return void
     */
    private function processPresetGroupTargets(
        string $announcementId,
        array &$usersForNotification,
        array &$targetUserRecords,
        array $presetGroupIds,
        object $currentSchool
    ): void {
        $presetTargets = PresetAudiences::whereIn('id', $presetGroupIds)->pluck('target')->toArray();

        foreach ($presetTargets as $presetTarget) {
            $ids = [];
            $modelClass = '';

            switch ($presetTarget) {
                case "school-admins":
                    $ids = Schooladmin::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray();
                    $modelClass = Schooladmin::class;
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
                    $this->addTargets($currentSchool, Schooladmin::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Schooladmin::class, $announcementId, $usersForNotification, $targetUserRecords);
                    $this->addTargets($currentSchool, Teacher::where('school_branch_id', $currentSchool->id)->pluck('id')->toArray(), Teacher::class, $announcementId, $usersForNotification, $targetUserRecords);
                    $this->addTargets($currentSchool, Student::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Student::class, $announcementId, $usersForNotification, $targetUserRecords);
                    $this->addTargets($currentSchool, Parents::where("school_branch_id", $currentSchool->id)->pluck('id')->toArray(), Parents::class, $announcementId, $usersForNotification, $targetUserRecords);
                    continue 2;
                case "level-one-students":
                case "level-two-students":
                case "level-three-students":
                case "bachelor-students":
                case "masters-one-students":
                case "masters-two-students":
                    $levelName = str_replace(['-students', '-one', '-two', '-three'], ['', ' One', ' Two', ' Three'], $presetTarget);
                    if (str_contains($presetTarget, 'bachelor')) $levelName = "Bachelor's Degree Programs";
                    if (str_contains($presetTarget, 'masters-one')) $levelName = "Master's Degree One";
                    if (str_contains($presetTarget, 'masters-two')) $levelName = "Master's Degree Two";

                    $ids = Student::whereHas('level', function ($query) use ($levelName) {
                        $query->where('name', $levelName);
                    })->pluck('id')->toArray();
                    $modelClass = Student::class;
                    break;
                default:
                    Log::warning("Unknown preset audience target: {$presetTarget}");
                    continue 2;
            }

            if (!empty($ids) && !empty($modelClass)) {
                $this->addTargets(
                    $currentSchool, $ids, $modelClass, $announcementId, $usersForNotification, $targetUserRecords);
            }
        }
    }

    /**
     * Calculates the default expiration date for an announcement.
     *
     * @param object $currentSchool The current school branch object.
     * @return string The calculated expiration date in 'Y-m-d H:i:s' format.
     */
    public function announcementDefaults($currentSchool): string
    {
        $announcementDefaultSettings = SchoolAnnouncementSetting::with(['announcementSetting'])->where('school_branch_id', $currentSchool->id)->get();
        // Assuming announcementSetting has a 'title' field now, based on the previous context suggesting 'name'
        $defaultExpireDate = $announcementDefaultSettings->where('announcementSetting.title', 'Default Expire Time')->first();
        $expiresAt = Carbon::now()->addDays(intval($defaultExpireDate->value ?? 7));
        $expiresAtForDB = $expiresAt->toDateTimeString();
        return $expiresAtForDB;
    }
}
