<?php

namespace App\Services\SchoolEvent;

use App\Jobs\DataCleanupJobs\CleanSchoolEventData;
use App\Jobs\DataCleanupJobs\UpdateSchoolEventStatusJob;
use App\Jobs\DataCreationJob\CreateSchoolEventLikeStatusJob;
use App\Jobs\NotificationJobs\SendAdminEventScheduleReminderNotiJob;
use App\Events\AdminSchoolEvent\AdminSchoolEventStatusUpdatedEvent;
use App\Models\EventTag;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use App\Jobs\DataCleanupJobs\UpdateSchoolEventVisibilityStatusJob;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\Actions\AdminActionEvent;
use Throwable;

class UpdateDraftEventStatusService
{
    public function updateDraftSchoolEvent($currentSchool, $authenticatedUser, $data)
    {
        try {
            return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data) {
                $schoolEvent = SchoolEvent::where('id', $data['school_event_id'])
                    ->where('school_branch_id', $currentSchool->id)
                    ->where('status', 'draft')
                    ->first();

                if (!$schoolEvent) {
                    throw new AppException(
                        "Draft Event Not Found",
                        404,
                        "Draft Event Not Found",
                        "The specified draft event could not be found or is not in draft status. Please ensure the event exists and is a draft.",
                        null
                    );
                }

                // Filter out null or empty values
                $eventData = array_filter($data, function ($value) {
                    return !is_null($value) && $value !== '';
                });

                // Handle tags
                if (isset($eventData['tag_ids'])) {
                    if (!empty($eventData['tag_ids'])) {
                        $tags = $this->getTags($eventData);
                        $eventData['tags'] = json_encode($tags->toArray());
                    } else {
                        $eventData['tags'] = '[]';
                    }
                }

                // Handle background image
                if (isset($eventData['background_image'])) {
                    if ($schoolEvent->background_image) {
                        Storage::disk('public')->delete('school_events/' . $schoolEvent->background_image);
                    }
                    if ($eventData['background_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '.' . $eventData['background_image']->getClientOriginalExtension();
                        $eventData['background_image']->storeAs('public/school_events', $fileName);
                        $eventData['background_image'] = $fileName;
                    } else {
                        $eventData['background_image'] = '';
                    }
                }

                if ($data['status'] === 'draft') {
                    $eventDataToUpdate = [
                        'title' => $eventData['title'] ?? $schoolEvent->title,
                        'description' => $eventData['description'] ?? $schoolEvent->description,
                        'event_category_id' => $eventData['event_category_id'] ?? $schoolEvent->category_id,
                        'tags' => $eventData['tags'] ?? $schoolEvent->tags,
                        'location' => $eventData['location'] ?? $schoolEvent->location,
                        'organizer' => $eventData['organizer'] ?? $schoolEvent->organizer,
                        'start_date' => $eventData['start_date'] ?? $schoolEvent->start_date,
                        'end_date' => $eventData['end_date'] ?? $schoolEvent->end_date,
                        'background_image' => $eventData['background_image'] ?? $schoolEvent->background_image,
                    ];

                    $schoolEvent->update($eventDataToUpdate);

                    return $schoolEvent;
                }

                // Collect recipients for non-draft status
                $recipients = $this->collectRecipients($currentSchool, $eventData);
                $tags = isset($eventData['tag_ids']) ? $this->getTags($eventData) : collect(json_decode($schoolEvent->tags, true));

                if ($recipients->isEmpty()) {
                    throw new AppException(
                        "No Recipients Found",
                        400,
                        "No Recipients Found",
                        "No valid recipients were found for the selected audience. Please ensure that at least one valid student, teacher, or admin is selected.",
                        null
                    );
                }

                $hasPublishedAt = !empty($eventData['published_at']);
                $intendedScheduled = $eventData['status'] === 'scheduled' || ($hasPublishedAt && Carbon::parse($eventData['published_at'])->isFuture());

                if ($intendedScheduled && !$hasPublishedAt) {
                    throw new AppException(
                        "Published Time Required for Scheduled Event",
                        400,
                        "Published Time Required",
                        "You are trying to schedule an event, but no published time was provided. Please specify a future date and time.",
                        null
                    );
                }

                $publishedAt = $eventData['status'] === 'draft' ? null : ($hasPublishedAt ? Carbon::parse($eventData['published_at']) : Carbon::now());
                $isScheduled = $eventData['status'] !== 'draft' && $hasPublishedAt && $publishedAt->isFuture();

                if ($eventData['status'] !== 'draft' && $hasPublishedAt && $publishedAt->isPast()) {
                    throw new AppException(
                        "Published Time Cannot Be in the Past",
                        400,
                        "Invalid Published Time",
                        "The provided published time is in the past. Please provide a future date or leave it blank for immediate publication.",
                        null
                    );
                }

                if ($isScheduled && $publishedAt->diffInDays(Carbon::now()) > 30) {
                    throw new AppException(
                        "Scheduled Time Too Far in the Future",
                        400,
                        "Scheduled Time Limit Exceeded",
                        "The scheduled publication time is more than 30 days in the future. Please choose a closer date to avoid long queue delays.",
                        null
                    );
                }

                $status = $eventData['status'] === 'draft' ? 'draft' : ($isScheduled ? 'scheduled' : 'active');

                if ($eventData['status'] === 'scheduled' && $status !== 'scheduled') {
                    throw new AppException(
                        "Invalid Scheduled Configuration",
                        400,
                        "Scheduled Event Misconfigured",
                        "You specified a scheduled status, but the provided published time is not in the future or is invalid. Please correct the published time.",
                        null
                    );
                }

                $expiresAt = null;
                if ($eventData['status'] !== 'draft') {
                    if (!empty($eventData['expires_at'])) {
                        $expiresAt = Carbon::parse($eventData['expires_at']);
                    } else {
                        $expiresAt = Carbon::parse($eventData['end_date'] ?? $schoolEvent->end_date);
                    }

                    if ($expiresAt->lte($publishedAt)) {
                        throw new AppException(
                            "Expires Time Must Be After Published Time",
                            400,
                            "Invalid Expires Time",
                            "The expiration time must be after the published time. Please adjust the dates accordingly.",
                            null
                        );
                    }

                    if ($expiresAt->diffInDays($publishedAt) > 365) {
                        throw new AppException(
                            "Expires Time Too Far in the Future",
                            400,
                            "Expires Time Limit Exceeded",
                            "The expiration time is more than 1 year after publication. Please choose a shorter duration.",
                            null
                        );
                    }
                }

                $totalStudents = $recipients->whereInstanceOf(Student::class)->count();
                $totalTeachers = $recipients->whereInstanceOf(Teacher::class)->count();
                $totalAdmins = $recipients->whereInstanceOf(Schooladmin::class)->count();
                $totalRecipients = $totalStudents + $totalTeachers + $totalAdmins;

                $eventDataToUpdate = [
                    'title' => $eventData['title'] ?? $schoolEvent->title,
                    'description' => $eventData['description'] ?? $schoolEvent->description,
                    'background_image' => $eventData['background_image'] ?? $schoolEvent->background_image,
                    'location' => $eventData['location'] ?? $schoolEvent->location,
                    'organizer' => $eventData['organizer'] ?? $schoolEvent->organizer,
                    'start_date' => $eventData['start_date'] ?? $schoolEvent->start_date,
                    'end_date' => $eventData['end_date'] ?? $schoolEvent->end_date,
                    'invitee_count' => $totalRecipients,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => $expiresAt,
                    'category_id' => $eventData['category_id'] ?? $schoolEvent->category_id,
                    'notification_sent_at' => null,
                    'tags' => $eventData['tags'] ?? $schoolEvent->tags,
                    'audience' => json_encode(array_filter([
                        'teachers' => $eventData['teacher_ids'] ?? json_decode($schoolEvent->audience, true)['teachers'] ?? null,
                        'admins' => $eventData['school_admin_ids'] ?? json_decode($schoolEvent->audience, true)['admins'] ?? null,
                        'students' => $eventData['student_audience'] ?? json_decode($schoolEvent->audience, true)['students'] ?? null,
                    ])),
                    'school_branch_id' => $currentSchool->id,
                ];

                $schoolEvent->update($eventDataToUpdate);

                if ($status !== 'draft') {
                    $startDate = Carbon::parse($eventData['start_date'] ?? $schoolEvent->start_date);
                    $endDate = Carbon::parse($eventData['end_date'] ?? $schoolEvent->end_date);

                    if ($status === 'active') {
                        UpdateSchoolEventStatusJob::dispatch($schoolEvent->id, $currentSchool->id, $authenticatedUser)->delay($endDate->add('minute', 1));
                        CreateSchoolEventLikeStatusJob::dispatch($currentSchool->id, $recipients, $schoolEvent->id);
                        CleanSchoolEventData::dispatch($schoolEvent->id, $currentSchool->id)->delay($endDate);
                        UpdateSchoolEventVisibilityStatusJob::dispatch($currentSchool->id, $schoolEvent->id);
                        AdminActionEvent::dispatch(
                            [
                                "permissions" =>  ["schoolAdmin.event.create"],
                                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                                "schoolBranch" =>  $currentSchool->id,
                                "feature" => "schoolEventManagement",
                                "authAdmin" => $authenticatedUser['authUser'],
                                "data" =>  $schoolEvent,
                                "message" => "School Event Published",
                            ]
                        );
                    } elseif ($status === 'scheduled') {
                        CreateSchoolEventLikeStatusJob::dispatch($currentSchool->id, $recipients, $schoolEvent->id)->delay($publishedAt);
                        UpdateSchoolEventStatusJob::dispatch($schoolEvent->id, $currentSchool->id, $authenticatedUser)->delay($endDate);
                        UpdateSchoolEventStatusJob::dispatch($schoolEvent->id, $currentSchool->id, $authenticatedUser)->delay($startDate);
                        UpdateSchoolEventVisibilityStatusJob::dispatch($currentSchool->id, $schoolEvent->id)->delay($publishedAt);
                        AdminActionEvent::dispatch(
                            [
                                "permissions" =>  ["schoolAdmin.event.create"],
                                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                                "schoolBranch" =>  $currentSchool->id,
                                "feature" => "schoolEventManagement",
                                "authAdmin" => $authenticatedUser['authUser'],
                                "data" =>  $schoolEvent,
                                "message" => "School Event Scheduled",
                            ]
                        );
                        if ($publishedAt->greaterThan(now()->addMinutes(10))) {
                            SendAdminEventScheduleReminderNotiJob::dispatch($schoolEvent->id, $authenticatedUser, $currentSchool->id)
                                ->delay($publishedAt->copy()->subMinutes(5));
                        }
                    }
                }

                return $schoolEvent;
            });
        } catch (Throwable $e) {
            throw $e;
        }
    }

    protected function getTags(array $data): Collection
    {
        if (empty($data['tag_ids'])) {
            return collect();
        }

        $tagIds = collect($data['tag_ids'])->pluck('tag_id')->unique()->toArray();
        $tags = EventTag::whereIn("id", $tagIds)->get();

        if ($tags->count() < count($tagIds)) {
            throw new AppException(
                "Some Tags Not Found",
                404,
                "Some Tags Not Found",
                "One or more selected tags could not be found. Please check that the tags exist and have not been deleted.",
                null
            );
        }

        return $tags;
    }

    protected function collectRecipients($currentSchool, array $data): Collection
    {
        $recipients = collect();

        if (!empty($data['student_audience'])) {
            $studentAudienceIds = collect($data['student_audience'])->pluck('student_audience_id')->unique()->toArray();
            $students = $this->studentAudience($currentSchool, $studentAudienceIds);
            $recipients = $recipients->merge($students);
        }

        if (!empty($data['school_admin_ids'])) {
            $adminIds = collect($data['school_admin_ids'])->pluck('school_admin_id')->unique()->toArray();
            $admins = $this->schoolAdminAudience($currentSchool, $adminIds);

            if ($admins->count() < count($adminIds)) {
                throw new AppException(
                    "Some School Admins Not Found",
                    404,
                    "Some School Admins Not Found",
                    "One or more selected school admins could not be found. Please check that the admins exist and have not been deleted.",
                    null
                );
            }

            $recipients = $recipients->merge($admins);
        }

        if (!empty($data['teacher_ids'])) {
            $teacherIds = collect($data['teacher_ids'])->pluck('teacher_id')->unique()->toArray();
            $teachers = $this->teacherAudience($currentSchool, $teacherIds);

            if ($teachers->count() < count($teacherIds)) {
                throw new AppException(
                    "Some Teachers Not Found",
                    404,
                    "Some Teachers Not Found",
                    "One or more selected teachers could not be found. Please check that the teachers exist and have not been deleted.",
                    null
                );
            }

            $recipients = $recipients->merge($teachers);
        }

        return $recipients->unique('id');
    }

    protected function studentAudience($currentSchool, $studentAudienceIds)
    {
        $students = Student::where("school_branch_id", $currentSchool->id)
            ->whereIn("specialty_id", $studentAudienceIds)
            ->get();

        if ($students->isEmpty() && !empty($studentAudienceIds)) {
            throw new AppException(
                "No Students Found for Selected Specialties",
                404,
                "No Students Found",
                "No students were found for the selected specialties. Please ensure the specialties exist and have enrolled students.",
                null
            );
        }

        return $students;
    }

    protected function schoolAdminAudience($currentSchool, $schoolAdminIds)
    {
        return Schooladmin::where("school_branch_id", $currentSchool->id)
            ->whereIn("id", $schoolAdminIds)
            ->get();
    }

    protected function teacherAudience($currentSchool, $teacherIds)
    {
        return Teacher::where("school_branch_id", $currentSchool->id)
            ->whereIn("id", $teacherIds)
            ->get();
    }
}
