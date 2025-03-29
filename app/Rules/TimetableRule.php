<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimetableRule implements ValidationRule
{
    protected $scheduleEntries; // Store scheduleEntries

    public function __construct($scheduleEntries)
    {
        $this->scheduleEntries = $scheduleEntries;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $conflicts = []; // Store conflicting appointment details
        foreach ($this->scheduleEntries as $i => $entry1) {
            for ($j = $i + 1; $j < count($this->scheduleEntries); $j++) {
                $entry2 = $this->scheduleEntries[$j];

                // Check for day and teacher_id equality for the same teacher
                if ($entry1['day_of_week'] === $entry2['day_of_week'] &&
                    $entry1['teacher_id'] === $entry2['teacher_id']) {
                    // Check if they overlap
                    if ($this->hasOverlap($entry1, $entry2)) {
                        $conflicts[] = [
                            'teacher_id' => $entry1['teacher_id'],
                            'day_of_week' => $entry1['day_of_week'],
                            'conflict_between' => [
                                'appointment_1' => [
                                    'starting_time' => $entry1['starting_time'],
                                    'ending_time' => $entry1['ending_time'],
                                ],
                                'appointment_2' => [
                                    'starting_time' => $entry2['starting_time'],
                                    'ending_time' => $entry2['ending_time'],
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        // If conflicts found, build the error message and invoke the fail closure
        if (!empty($conflicts)) {
            $messages = [];
            foreach ($conflicts as $conflict) {
                $messages[] = sprintf(
                    'Conflict for teacher ID %d on %s: Appointment overlaps between Appointment 1 (%s - %s) and Appointment 2 (%s - %s)',
                    $conflict['teacher_id'],
                    $conflict['day_of_week'],
                    $conflict['conflict_between']['appointment_1']['starting_time'],
                    $conflict['conflict_between']['appointment_1']['ending_time'],
                    $conflict['conflict_between']['appointment_2']['starting_time'],
                    $conflict['conflict_between']['appointment_2']['ending_time']
                );
            }

            $fail(implode(' | ', $messages)); // Call fail with the assembled message
        }
    }

    private function hasOverlap($appt1, $appt2)
    {
        return (
            ($appt1['starting_time'] < $appt2['ending_time'] && $appt1['ending_time'] > $appt2['starting_time'])
        );
    }
}
