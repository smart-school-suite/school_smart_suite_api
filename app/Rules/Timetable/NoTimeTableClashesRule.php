<?php

namespace App\Rules\Timetable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoTimeTableClashesRule implements ValidationRule
{
    //This rule checks if there are any clashes within the timetable entries
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

        $conflicts = [];
        foreach ($this->scheduleEntries as $i => $entry1) {
            for ($j = $i + 1; $j < count($this->scheduleEntries); $j++) {
                $entry2 = $this->scheduleEntries[$j];
                if ($entry1['day_of_week'] === $entry2['day_of_week'] &&
                    $entry1['teacher_id'] === $entry2['teacher_id']) {
                    if ($this->hasOverlap($entry1, $entry2)) {
                        $conflicts[] = [
                            'teacher_id' => $entry1['teacher_id'],
                            'day_of_week' => $entry1['day_of_week'],
                            'conflict_between' => [
                                'appointment_1' => [
                                    'start_time' => $entry1['start_time'],
                                    'end_time' => $entry1['end_time'],
                                ],
                                'appointment_2' => [
                                    'start_time' => $entry2['start_time'],
                                    'end_time' => $entry2['end_time'],
                                ]
                            ]
                        ];
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            $messages = [];
            foreach ($conflicts as $conflict) {
                $messages[] = sprintf(
                    'Conflict for teacher ID %d on %s: Appointment overlaps between Appointment 1 (%s - %s) and Appointment 2 (%s - %s)',
                    $conflict['teacher_id'],
                    $conflict['day_of_week'],
                    $conflict['conflict_between']['appointment_1']['start_time'],
                    $conflict['conflict_between']['appointment_1']['end_time'],
                    $conflict['conflict_between']['appointment_2']['start_time'],
                    $conflict['conflict_between']['appointment_2']['end_time']
                );
            }

            $fail(implode(' | ', $messages));
        }
    }

    private function hasOverlap($appt1, $appt2)
    {
        return $appt1['start_time'] < $appt2['end_time'] && $appt1['end_time'] > $appt2['start_time'];
    }
}
