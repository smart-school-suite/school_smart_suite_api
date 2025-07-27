<?php

namespace App\Rules\Timetable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Collection;
use App\Models\InstructorAvailabilitySlot;
class TeacherAvailableRule implements ValidationRule
{
    protected array $errors = [];

    protected $scheduleEntries;
     public function __construct($scheduleEntries)
    {
        $this->scheduleEntries = $scheduleEntries;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute // This will be 'scheduleEntries'
     * @param  mixed   $value     // This will be the entire array of submitted schedule entries
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $scheduleEntries = collect($value);

        if ($scheduleEntries->isEmpty()) {
            return; // No entries to validate
        }

        foreach ($scheduleEntries as $currentIndex => $currentEntry) {
            if (!isset(
                $currentEntry['teacher_id'],
                $currentEntry['day_of_week'],
                $currentEntry['start_time'],
                $currentEntry['end_time']
            )) {
                continue;
            }

            $teacherId = $currentEntry['teacher_id'];
            $dayOfWeek = strtolower($currentEntry['day_of_week']);
            $startTime = $currentEntry['start_time'];
            $endTime = $currentEntry['end_time'];

            $levelId = $currentEntry['level_id'] ?? null;
            $semesterId = $currentEntry['semester_id'] ?? null;
            $specialtyId = $currentEntry['specialty_id'] ?? null;

            $matchingAvailabilityCount = InstructorAvailabilitySlot::query()
                ->where('teacher_id', $teacherId)
                ->where('day_of_week', $dayOfWeek)
                ->where('start_time', '<=', $startTime)
                ->where('end_time', '>=', $endTime)
                ->where("school_semester_id", $semesterId)
                ->where("specialty_id", $specialtyId)
                ->where("level_id", $levelId)
                ->count();

            if ($matchingAvailabilityCount === 0) {
                $entryDescription = "the entry";
                if (isset($currentEntry['course_id'])) {
                    $entryDescription .= " for Course ID: {$currentEntry['course_id']}";
                }
                if (isset($currentEntry['level_id'])) {
                    $entryDescription .= ", Level: {$currentEntry['level_id']}";
                }
                if (isset($currentEntry['semester_id'])) {
                    $entryDescription .= ", Semester: {$currentEntry['semester_id']}";
                }
                if (isset($currentEntry['specialty_id'])) {
                    $entryDescription .= ", Specialty: {$currentEntry['specialty_id']}";
                }

                $this->errors[] = "Schedule Entry #{$currentIndex}: The teacher (ID: {$teacherId}) is NOT available on {$currentEntry['day_of_week']} from {$startTime} to {$endTime} for {$entryDescription}. Please ensure this time slot is fully contained within their defined availabilities.";
            }
        }

        if (!empty($this->errors)) {
            $fail(implode(' | ', $this->errors));
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
}
