<?php

namespace App\Services\Promotion;

use App\Models\Promotion\StudentPromotionRecord;

/**
 * Whether an admin can decide a student's promotion record (approve the
 * promotion, or have the student repeat), and why not. The single and bulk
 * promote / repeat endpoints enforce it, and the record list returns it per
 * record so the UI offers only what the server will accept.
 *
 * - A record can be decided once: a completed record is final.
 * - Not while its run is scheduled, queued or processing: the run would
 *   overwrite the decision when it reaches the student.
 * - Promote also needs a next level: a student in a graduation-level
 *   specialty graduates instead.
 *
 * A promotion also needs the same specialty at the next level; that needs
 * the school's level setup, so the service checks it when it promotes.
 */
class PromotionRecordDecisionPolicy
{
    public const ACTIONS = ['promote', 'repeat'];

    /** Null when the action is allowed, else ['title', 'description', 'code']. */
    public function refusal(StudentPromotionRecord $record, string $action): ?array
    {
        if (!in_array($action, self::ACTIONS, true)) {
            return $this->deny('Unknown Action', "Unknown promotion record action '{$action}'.", 422);
        }
        if (!$record->student) {
            return $this->deny('Student Not Found', 'The student on this record no longer exists.', 404);
        }
        if ($record->status === 'completed') {
            return $this->deny('Already Decided', "This student's promotion was already decided: " . $this->outcomeLabel($record->outcome) . '.');
        }
        if (in_array($record->promotionRun?->status, PromotionRunActionPolicy::IN_PROGRESS, true)) {
            return $this->deny('Run In Progress', 'The promotion run is still evaluating students. Decide once it finishes.');
        }
        if ($action === 'promote' && $record->fromSpecialty?->graduation_level) {
            return $this->deny('Final Level', 'This student is at the final level, so they graduate instead of being promoted.');
        }

        return null;
    }

    /** Every action with whether it is allowed and why not, for the record list. */
    public function describe(StudentPromotionRecord $record): array
    {
        return collect(self::ACTIONS)->mapWithKeys(function (string $action) use ($record) {
            $refusal = $this->refusal($record, $action);
            return [$action => ['allowed' => $refusal === null, 'reason' => $refusal['description'] ?? null]];
        })->all();
    }

    public function outcomeLabel(?string $outcome): string
    {
        return [
            'promoted' => 'promoted',
            'repeated' => 'repeating the level',
            'graduated' => 'graduated',
            'pending_review' => 'pending review',
            'excluded' => 'excluded',
        ][$outcome] ?? (string) $outcome;
    }

    private function deny(string $title, string $description, int $code = 409): array
    {
        return ['title' => $title, 'description' => $description, 'code' => $code];
    }
}
