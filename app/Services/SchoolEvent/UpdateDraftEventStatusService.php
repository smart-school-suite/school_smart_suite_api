<?php

namespace App\Services\SchoolEvent;

use App\Exceptions\AppException;
use App\Models\SchoolEvent;
use App\Services\Asset\SchoolEventImageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateDraftEventStatusService
{
    private const CONTENT_FIELDS = ['title', 'description', 'organizer', 'location', 'start_date', 'end_date', 'event_category_id'];

    public function __construct(
        private EventPublishingService $publishing,
        private SchoolEventImageService $imageService
    ) {}

    public function updateDraftSchoolEvent(object $currentSchool, array $authenticatedUser, array $data)
    {
        $uploadedImages = collect();

        try {
            return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data, &$uploadedImages) {
                $schoolEvent = SchoolEvent::where('id', $data['school_event_id'])
                    ->where('school_branch_id', $currentSchool->id)
                    ->where('status', 'draft')
                    ->lockForUpdate()
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

                $updates = array_filter(
                    array_intersect_key($data, array_flip(self::CONTENT_FIELDS)),
                    fn($value) => !is_null($value) && $value !== ''
                );

                if (array_key_exists('tag_ids', $data)) {
                    $updates['tags'] = $this->publishing->getTags($data)->toArray();
                }

                if ($this->publishing->hasAudienceInput($data)) {
                    $updates['audience'] = $this->publishing->audiencePayload($data);
                }

                $schoolEvent->fill($updates);

                $this->imageService->removeImages($schoolEvent, $currentSchool->id, $data['remove_image_ids'] ?? []);
                $uploadedImages = $this->imageService->uploadImages($schoolEvent, $data['images'] ?? [], $currentSchool->id);

                if (($data['status'] ?? 'draft') === 'draft') {
                    $schoolEvent->save();
                    return $schoolEvent->load(['currentImages', 'eventCategory']);
                }

                $this->ensurePublishable($schoolEvent);

                $audience = $this->publishing->resolveAudience($currentSchool, $this->storedAudience($schoolEvent->audience));

                [$status, $publishedAt] = $this->publishing->resolveTiming(
                    $data['status'],
                    $data['published_at'] ?? null,
                    $schoolEvent->start_date
                );

                $schoolEvent->fill([
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => $schoolEvent->end_date,
                    'notification_sent_at' => null,
                ])->save();

                $this->publishing->seedInvitees($currentSchool, $schoolEvent->id, $audience);
                $this->publishing->dispatchLifecycleJobs($schoolEvent, $authenticatedUser, $currentSchool);

                return $schoolEvent->load(['currentImages', 'eventCategory']);
            });
        } catch (Throwable $exception) {
            $this->imageService->deleteObjects($uploadedImages->pluck('object_key')->all());
            throw $exception;
        }
    }

    private function ensurePublishable(SchoolEvent $schoolEvent): void
    {
        $missing = collect(self::CONTENT_FIELDS)
            ->filter(fn($field) => blank($schoolEvent->{$field}))
            ->values();

        if ($missing->isNotEmpty()) {
            throw new AppException(
                "Draft event is missing required fields: " . $missing->implode(', '),
                422,
                "Incomplete Draft Event",
                "Please fill in the following before publishing this event: " . $missing->map(fn($field) => str_replace('_', ' ', $field))->implode(', ') . ".",
                null
            );
        }

        if (Carbon::parse($schoolEvent->end_date)->lessThan(Carbon::parse($schoolEvent->start_date))) {
            throw new AppException(
                "Event end date is before its start date.",
                422,
                "Invalid Event Dates",
                "The event end date must be on or after its start date.",
                null
            );
        }

        if (Carbon::parse($schoolEvent->end_date)->isPast()) {
            throw new AppException(
                "Event End Date In The Past",
                400,
                "Invalid End Date",
                "The event end date has already passed. Please choose an end date in the future.",
                null
            );
        }
    }

    private function storedAudience($audience): array
    {
        if (is_string($audience)) {
            $audience = json_decode($audience, true);
        }

        return is_array($audience) ? $audience : [];
    }
}
