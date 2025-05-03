<?php

namespace App\Rules\Timetable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use App\Models\Timetable;
use App\Models\Educationlevels;
use App\Models\Courses;
class TimeSlotAvailableRule implements ValidationRule
{
    protected Collection $timetableEntries;
    protected array $errors = [];

    public function __construct(array $timetableEntries)
    {
        $this->timetableEntries = collect($timetableEntries);
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->timetableEntries->count()) {
            return;
        }

        $currentIndex = $this->extractIndexFromAttribute($attribute);
        if ($currentIndex === null) {
            return;
        }

        $currentEntry = $this->timetableEntries->get($currentIndex);

        if (!$currentEntry) {
            return;
        }

        $teacherId = $currentEntry['teacher_id'] ?? null;
        $dayOfWeek = strtolower($currentEntry['day_of_week'] ?? '');
        $startTime = $currentEntry['start_time'] ?? null;
        $endTime = $currentEntry['end_time'] ?? null;
        $levelId = $currentEntry['level_id'] ?? null;
        $semesterId = $currentEntry['semester_id'] ?? null;
        $specialtyId = $currentEntry['specialty_id'] ?? null;

        if (!$teacherId || !$dayOfWeek || !$startTime || !$endTime || !$levelId || !$semesterId || !$specialtyId ) {
            return; // Missing essential data in the current timetable entry
        }

        $clashingExistingEntries = Timetable::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($clashingExistingEntries) {
            $entryDescription = $this->describeTimetableEntry($currentEntry);
            $this->errors[] = "The scheduled time for {$entryDescription} on {$currentEntry['day_of_week']} from {$startTime} to {$endTime} clashes with existing timetable entries.";
        }
    }

    /**
     * Get the validation error messages.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->errors;
    }

    /**
     * Extracts the index from the attribute name (e.g., 'scheduleEntries.0.teacher_id' => 0).
     *
     * @param  string  $attribute
     * @return int|null
     */
    protected function extractIndexFromAttribute(string $attribute): ?int
    {
        if (preg_match('/\.(\d+)\./', $attribute, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Helper function to create a human-readable description of a timetable entry.
     *
     * @param  array  $entry
     * @return string
     */
    protected function describeTimetableEntry(array $entry): string
    {
        $description = "an entry for";
        if (isset($entry['course_id'])) {
            $course = Courses::findOrFail($entry['course_id']);
            $description .= " Course: {$course->name}";
        }
        if (isset($entry['level_id'])) {
            $level = Educationlevels::findOrFail($entry['level_id']);
            $description .= ", Level: {$level->name} {$level->level}";
        }
        if (isset($entry['semester_id'])) {
            $description .= ", Semester: {$entry['semester_id']}";
        }
        if (isset($entry['specialty_id'])) {
            $description .= ", Specialty: {$entry['specialty_id']}";
        }
        if (isset($entry['teacher_id'])) {
            $description .= ", Teacher ID: {$entry['teacher_id']}";
        }
        if (isset($entry['student_batch_id'])) {
            $description .= ", Batch: {$entry['student_batch_id']}";
        }
        if (isset($entry['school_branch_id'])) {
            $description .= ", Branch ID: {$entry['school_branch_id']}";
        }
        return $description;
    }
}
