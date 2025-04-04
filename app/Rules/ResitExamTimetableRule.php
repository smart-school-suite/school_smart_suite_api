<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ResitExamTimetableRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $examsEntries; // Store exam entries

    public function __construct($examsEntries)
    {
        $this->examsEntries = $examsEntries;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $conflicts = []; // Store conflicting appointment details
        $entryCount = count($this->examsEntries);

        // Check for overlapping entries
        foreach ($this->examsEntries as $i => $entry1) {
            for ($j = $i + 1; $j < $entryCount; $j++) {
                $entry2 = $this->examsEntries[$j];

                // Check for date equality
                if ($entry1['date'] === $entry2['date']) {
                    // Check if they overlap
                    if ($this->hasOverlap($entry1, $entry2)) {
                        $conflicts[] = [
                            'date' => $entry1['date'],
                            'conflict_between' => [
                                'exam_1' => [
                                    'start_time' => $entry1['start_time'],
                                    'end_time' => $entry1['end_time'],
                                ],
                                'exam_2' => [
                                    'start_time' => $entry2['start_time'],
                                    'end_time' => $entry2['end_time'],
                                ],
                            ],
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
                    'Conflict on %s: Exam overlaps between Exam 1 (%s - %s) and Exam 2 (%s - %s)',
                    $conflict['date'],
                    $conflict['conflict_between']['exam_1']['start_time'],
                    $conflict['conflict_between']['exam_1']['end_time'],
                    $conflict['conflict_between']['exam_2']['start_time'],
                    $conflict['conflict_between']['exam_2']['end_time']
                );
            }

            $fail(implode(' | ', $messages));
        }
    }

    private function hasOverlap($exam1, $exam2)
    {
        return (
            ($exam1['start_time'] < $exam2['end_time'] && $exam1['end_time'] > $exam2['start_time'])
        );
    }
}
