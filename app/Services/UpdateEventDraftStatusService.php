<?php

namespace App\Services;

use App\Jobs\EmailNotificationJobs\EmailEventNotificationJob;
use App\Models\SchoolEvent;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class UpdateEventDraftStatusService
{
    /**
     * Updates the status of a school event.
     *
     * @param array $statusUpdateData An associative array containing the new status and optionally 'published_at'.
     * @param string $eventId The ID of the school event to update.
     * @return array Returns an array with a message and the event object, especially if the event is expired.
     * @throws Throwable If any error occurs during the update process.
     * @throws Exception If the event has no invitees and cannot be updated.
     */
    public function updateEventDraftStatus(array $statusUpdateData, string $eventId): array
    {
        $newStatus = Arr::get($statusUpdateData, 'status');

        try {
            $event = SchoolEvent::findOrFail($eventId);

            if ($event->status === 'expired') {
                return [
                    'message' => "The event '{$event->title}' has expired and cannot be updated.",
                    'event' => $event,
                ];
            }

            if($event->status != 'draft'){
                return [
                     'message' => "The event '{$event->title}' is not a draft and cannot be updated.",
                     'event' => $event,
                ];
            }

            if ($event->invitee_count === 0) {
                throw new Exception("Event '{$event->title}' (ID: {$eventId}) cannot be updated because it has no invitees.");
            }

            DB::beginTransaction();

            $publishedAt = $this->determinePublishedAt($newStatus, $statusUpdateData);

            $event->status = $newStatus;
            $event->published_at = $publishedAt;
            $event->save();

            DB::commit();

            $this->dispatchNotificationJob($newStatus, $publishedAt, $eventId);

            return [
                'message' => "Event '{$event->title}' status updated to '{$newStatus}' successfully.",
                'event' => $event,
            ];

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update event status for Event ID: {$eventId}. Error: {$e->getMessage()}", [
                'exception' => $e,
                'eventId' => $eventId,
                'statusUpdateData' => $statusUpdateData,
            ]);
            throw $e;
        }
    }

    /**
     * Determines the 'published_at' timestamp based on the event status.
     *
     * @param string $status The new status of the event.
     * @param array $statusUpdateData The data containing potential 'published_at' information.
     * @return Carbon|null Returns a Carbon instance if 'published_at' is relevant, otherwise null.
     */
    private function determinePublishedAt(string $status, array $statusUpdateData): ?Carbon
    {
        if ($status === 'active') {
            return now();
        } elseif ($status === 'scheduled') {
            $publishedAtString = Arr::get($statusUpdateData, 'published_at');
            return $publishedAtString ? Carbon::parse($publishedAtString) : null;
        }

        return null;
    }

    /**
     * Dispatches the email notification job if the event status is 'scheduled' or 'active'.
     *
     * @param string $status The new status of the event.
     * @param Carbon|null $publishedAt The timestamp when the event is published.
     * @param string $eventId The ID of the school event.
     */
    private function dispatchNotificationJob(string $status, ?Carbon $publishedAt, string $eventId): void
    {
        if (in_array($status, ['scheduled', 'active'])) {
            $delay = 0;

            if ($publishedAt) {
                $delay = now()->diffInSeconds($publishedAt, false);
                if ($delay < 0) {
                    $delay = 0;
                }
            }

            Log::info("Dispatching EmailEventNotificationJob for Event ID: {$eventId} with delay: {$delay} seconds.", [
                'status' => $status,
                'publishedAt' => $publishedAt?->toDateTimeString(),
                'delay' => $delay,
            ]);

            EmailEventNotificationJob::dispatch($eventId)->delay(now()->addSeconds($delay));
        }
    }
}
