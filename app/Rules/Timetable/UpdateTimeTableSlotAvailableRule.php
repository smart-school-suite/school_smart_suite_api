<?php

namespace App\Rules\Timetable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Timetable;
use App\Models\Courses;
use App\Models\Educationlevels;
class UpdateTimeTableSlotAvailableRule implements ValidationRule
{
     // The constructor now receives the entire array of schedule entries
    protected $scheduleEntries;
    protected array $errors = []; // To collect all custom error messages

    public function __construct(array $scheduleEntries)
    {
        $this->scheduleEntries = $scheduleEntries;
    }

      public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $scheduleEntries = collect($value);

        if ($scheduleEntries->isEmpty()) {
            return;
        }

        foreach ($scheduleEntries as $currentIndex => $currentEntry) {
            if (!isset(
                $currentEntry['teacher_id'],
                $currentEntry['day_of_week'],
                $currentEntry['start_time'],
                $currentEntry['end_time'],
                $currentEntry['entry_id']
            )) {
                continue;
            }

            $teacherId = $currentEntry['teacher_id'];
            $dayOfWeek = strtolower($currentEntry['day_of_week']);
            $startTime = $currentEntry['start_time'];
            $endTime = $currentEntry['end_time'];
            $slotId = $currentEntry['entry_id'];

            $clashingExistingEntries = Timetable::query()
                ->where('teacher_id', $teacherId)
                ->where('day_of_week', $dayOfWeek)
                ->where('id', '!=', $slotId)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($clashingExistingEntries) {
                $entryDescription = $this->describeTimetableEntry($currentEntry);
                $this->errors[] = "Schedule Entry #{$currentIndex}: The proposed time for {$entryDescription} on {$currentEntry['day_of_week']} from {$startTime} to {$endTime} **clashes with an existing timetable entry**. This time slot is already occupied.";
            }
        }

        if (!empty($this->errors)) {
            $fail(implode(' | ', $this->errors));
        }
    }

    /**
     * Get the validation error messages.
     * This method is automatically called by Laravel when validation fails.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->errors; // Return all collected custom error messages.
    }

    /**
     * Helper function to create a human-readable description of a timetable entry.
     * Fetches related model names for better context in error messages.
     *
     * @param  array  $entry
     * @return string
     */
    protected function describeTimetableEntry(array $entry): string
    {
        $description = "an entry"; // Start generic

        // Add Course name if available
        if (isset($entry['course_id'])) {
            try {
                $course = Courses::find($entry['course_id']);
                if ($course) {
                    $description .= " for Course: **{$course->course_name}**";
                } else {
                    $description .= " for Unknown Course ID: {$entry['course_id']}";
                }
            } catch (\Exception $e) {
                $description .= " for Course ID: {$entry['course_id']} (Error fetching name)";
            }
        }

        // Add Level name and number if available
        if (isset($entry['level_id'])) {
            try {
                $level = Educationlevels::find($entry['level_id']);
                if ($level) {
                    $description .= ", Level: **{$level->name} {$level->level}**";
                } else {
                    $description .= ", Unknown Level ID: {$entry['level_id']}";
                }
            } catch (\Exception $e) {
                $description .= ", Level ID: {$entry['level_id']} (Error fetching name)";
            }
        }

        if (isset($entry['semester_id'])) {
            $description .= ", Semester ID: **{$entry['semester_id']}**";
        }

        if (isset($entry['specialty_id'])) {
            $description .= ", Specialty ID: **{$entry['specialty_id']}**";
        }

        if (isset($entry['teacher_id'])) {
            $description .= ", Teacher ID: **{$entry['teacher_id']}**";
        }

        if (isset($entry['student_batch_id'])) {
            $description .= ", Batch ID: **{$entry['student_batch_id']}**";
        }

        if (isset($entry['school_branch_id'])) {
            $description .= ", Branch ID: **{$entry['school_branch_id']}**";
        }

        return $description;
    }
}
