<?php

namespace App\Services;
use App\Jobs\DataCreationJob\CreateAnnouncementReciepientJob;
use App\Jobs\NotificationJobs\SendAdminAnnouncementScheduleReminderNotiJob;
use App\Jobs\NotificationJobs\SendAdminScheduledAnnouncementNotiJob;
use App\Jobs\StatisticalJobs\OperationalJobs\AnnouncementStatJob;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exceptions\AppException;
use App\Models\Announcement;
use App\Models\AnnouncementEngagementStat;
use App\Models\AnnouncementTag;
use Illuminate\Support\Collection;
use Throwable;
use App\Jobs\DataCleanupJobs\UpdateAnnouncementStatusJob;
class UpdateAnnouncementDraftService
{
        public function updateDraftAnnouncement($currentSchool, $authenticatedUser, $data)
    {
        try {
            return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data) {
                $announcement = Announcement::where('id', $data['announcement_id'])
                    ->where('school_branch_id', $currentSchool->id)
                    ->where('status', 'draft')
                    ->first();

                if (!$announcement) {
                    throw new AppException(
                        "Draft Announcement Not Found",
                        404,
                        "Draft Announcement Not Found",
                        "The specified draft announcement could not be found or is not in draft status. Please ensure the announcement exists and is a draft.",
                        null
                    );
                }

                if ($data['status'] === 'draft') {
                    $announcementData = [
                        'title' => $data['title'] ?? $announcement->title,
                        'content' => $data['content'] ?? $announcement->content,
                        'category_id' => $data['category_id'] ?? $announcement->category_id,
                        'label_id' => $data['label_id'] ?? $announcement->label_id,
                        'tags' => !empty($data['tag_ids']) ? json_encode($this->getTags($data)->toArray()) : $announcement->tags,
                    ];

                    $announcement->update($announcementData);

                    return $announcement;
                }

                $recipients = $this->collectRecipients($currentSchool, $data);
                $tags = $this->getTags($data);

                if ($recipients->isEmpty()) {
                    throw new AppException(
                        "No Recipients Found",
                        400,
                        "No Recipients Found",
                        "No valid recipients were found for the selected audience. Please ensure that at least one valid student, teacher, or admin is selected.",
                        null
                    );
                }

                $announcementId = $announcement->id;

                $hasPublishedAt = !empty($data['published_at']);
                $intendedScheduled = $data['status'] === 'scheduled' || ($hasPublishedAt && Carbon::parse($data['published_at'])->isFuture());

                if ($intendedScheduled && !$hasPublishedAt) {
                    throw new AppException(
                        "Published Time Required for Scheduled Announcement",
                        400,
                        "Published Time Required",
                        "You are trying to schedule an announcement, but no published time was provided. Please specify a future date and time.",
                        null
                    );
                }

                $publishedAt = $data['status'] === 'draft' ? null : ($hasPublishedAt ? Carbon::parse($data['published_at']) : Carbon::now());
                $isScheduled = $data['status'] !== 'draft' && $hasPublishedAt && $publishedAt->isFuture();

                if ($data['status'] !== 'draft' && $hasPublishedAt && $publishedAt->isPast()) {
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

                $status = $data['status'] === 'draft' ? 'draft' : ($isScheduled ? 'scheduled' : 'active');

                if ($data['status'] === 'scheduled' && $status !== 'scheduled') {
                    throw new AppException(
                        "Invalid Scheduled Configuration",
                        400,
                        "Scheduled Announcement Misconfigured",
                        "You specified a scheduled status, but the provided published time is not in the future or is invalid. Please correct the published time.",
                        null
                    );
                }

                $expiresAt = null;
                if ($data['status'] !== 'draft') {
                    if (!empty($data['expires_at'])) {
                        $expiresAt = Carbon::parse($data['expires_at']);
                    } else {
                        $expiresAt = $publishedAt->copy()->addDays(7);
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

                $announcementData = [
                    'title' => $data['title'] ?? $announcement->title,
                    'content' => $data['content'] ?? $announcement->content,
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => $expiresAt,
                    'category_id' => $data['category_id'] ?? $announcement->category_id,
                    'label_id' => $data['label_id'] ?? $announcement->label_id,
                    'notification_sent_at' => null,
                    'tags' => json_encode($tags->toArray()),
                    'audiences' => json_encode(array_filter([
                        'teachers' => $data['teacher_ids'] ?? null,
                        'admins' => $data['school_admin_ids'] ?? null,
                        'students' => $data['student_audience'] ?? null,
                    ])),
                    'school_branch_id' => $currentSchool->id,
                ];

                $announcement->update($announcementData);

                if ($status !== 'draft') {
                    $totalStudents = $recipients->whereInstanceOf(Student::class)->count();
                    $totalTeachers = $recipients->whereInstanceOf(Teacher::class)->count();
                    $totalAdmins   = $recipients->whereInstanceOf(Schooladmin::class)->count();
                    $totalRecipients = $totalStudents + $totalTeachers + $totalAdmins;

                    $engagementStat = AnnouncementEngagementStat::where('announcement_id', $announcementId)
                        ->where('school_branch_id', $currentSchool->id)
                        ->first();

                    if ($engagementStat) {
                        $engagementStat->update([
                            'total_reciepient' => $totalRecipients,
                            'total_student' => $totalStudents,
                            'total_school_admin' => $totalAdmins,
                            'total_teacher' => $totalTeachers,
                            'total_unseen' => $totalRecipients,
                            'total_seen' => 0,
                        ]);
                    } else {
                        AnnouncementEngagementStat::create([
                            'total_reciepient' => $totalRecipients,
                            'total_student' => $totalStudents,
                            'total_school_admin' => $totalAdmins,
                            'total_teacher' => $totalTeachers,
                            'total_seen' => 0,
                            'total_unseen' => $totalRecipients,
                            'announcement_id' => $announcementId,
                            'school_branch_id' => $currentSchool->id
                        ]);
                    }

                    if ($status === 'active') {
                        AnnouncementStatJob::dispatch( $currentSchool->id, $announcementId);
                        UpdateAnnouncementStatusJob::dispatch($announcementId, $currentSchool->id)->delay($expiresAt);
                        CreateAnnouncementReciepientJob::dispatch($currentSchool->id, $recipients, $announcementId);
                    } elseif ($status === 'scheduled') {
                        AnnouncementStatJob::dispatch( $currentSchool->id, $announcementId);
                        CreateAnnouncementReciepientJob::dispatch($currentSchool->id, $recipients, $announcementId)
                            ->delay($publishedAt);
                        SendAdminScheduledAnnouncementNotiJob::dispatch($announcementId, $authenticatedUser, $currentSchool->id);
                        if($publishedAt->greaterThan(now()->addMinutes(10))){
                           SendAdminAnnouncementScheduleReminderNotiJob::dispatch($announcementId, $authenticatedUser, $currentSchool->id)
                           ->delay($publishedAt->copy()->subMinutes(5));
                        }
                        UpdateAnnouncementStatusJob::dispatch($announcementId, $currentSchool->id)->delay($expiresAt);

                    }
                }

                return $announcement;
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
        $tags = AnnouncementTag::whereIn("id", $tagIds)->get();

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
