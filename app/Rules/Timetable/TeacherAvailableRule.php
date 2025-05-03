<?php

namespace App\Rules\Timetable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\InstructorAvailability;
use Illuminate\Support\Collection;
class TeacherAvailableRule implements ValidationRule
{
    protected Collection $timetableEntries; // Store timetable entries
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

        if (!$teacherId || !$dayOfWeek || !$startTime || !$endTime) {
            return;
        }

        $overlappingAvailabilities = InstructorAvailability::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->when($levelId, function ($query, $levelId) {
                return $query->where('level_id', $levelId);
            })
            ->when($semesterId, function ($query, $semesterId) {
                return $query->where('semester_id', $semesterId);
            })
            ->when($specialtyId, function ($query, $specialtyId) {
                return $query->where('specialty_id', $specialtyId);
            })
            ->get();

        if ($overlappingAvailabilities->isNotEmpty()) {
            $availabilityDetails = $overlappingAvailabilities->map(function ($availability) {
                $details = "On {$availability->day_of_week} from {$availability->start_time} to {$availability->end_time}";
                if ($availability->level_id) {
                    $details .= " for Level {$availability->level_id}";
                }
                if ($availability->semester_id) {
                    $details .= " in Semester {$availability->semester_id}";
                }
                if ($availability->specialty_id) {
                    $details .= " for Specialty {$availability->specialty_id}";
                }
                return $details;
            })->implode('; ');

            $entryDescription = "the entry for";
            if (isset($currentEntry['course_id'])) {
                $entryDescription .= " Course ID: {$currentEntry['course_id']}";
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

            $this->errors[] = "The scheduled time for {$entryDescription} on {$currentEntry['day_of_week']} from {$startTime} to {$endTime} conflicts with the teacher's existing availability: {$availabilityDetails}.";
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
}
