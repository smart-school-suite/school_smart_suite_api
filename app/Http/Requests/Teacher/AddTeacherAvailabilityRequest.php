<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon; // Import Carbon for time manipulation

class AddTeacherAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'availability_slots' => 'required|array',
            'availability_slots.*.teacher_id' => [
                'required',
                'string',
                'exists:teacher,id',
            ],
            'availability_slots.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availability_slots.*.start_time' => 'required|date_format:H:i',
            'availability_slots.*.end_time' => 'required|date_format:H:i|after:availability_slots.*.start_time',
            'availability_slots.*.school_semester_id' => 'required|string|exists:school_semesters,id',
            'availability_slots.*.teacher_availability_id' => 'required|string|exists:teacher_availabilities,id',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $availabilityEntries = $this->input('availability_slots');

            if (!is_array($availabilityEntries) || empty($availabilityEntries)) {
                return;
            }

            $count = count($availabilityEntries);

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $entry1 = $availabilityEntries[$i];
                    $entry2 = $availabilityEntries[$j];
                    if (
                        $entry1['teacher_id'] === $entry2['teacher_id'] &&
                        $entry1['day_of_week'] === $entry2['day_of_week'] &&
                        $entry1['school_semester_id'] === $entry2['school_semester_id']
                    ) {
                        $start1 = Carbon::createFromFormat('H:i', $entry1['start_time']);
                        $end1 = Carbon::createFromFormat('H:i', $entry1['end_time']);
                        $start2 = Carbon::createFromFormat('H:i', $entry2['start_time']);
                        $end2 = Carbon::createFromFormat('H:i', $entry2['end_time']);

                        if ($start1->lessThan($end2) && $end1->greaterThan($start2)) {
                            $validator->errors()->add(
                                "availability_slots.{$i}",
                                "Overlap detected for Teacher ID {$entry1['teacher_id']} on {$entry1['day_of_week']} in semester {$entry1['school_semester_id']}. " .
                                "Entry {$i} ({$entry1['start_time']}-{$entry1['end_time']}) clashes with Entry {$j} ({$entry2['start_time']}-{$entry2['end_time']})."
                            );
                            $validator->errors()->add(
                                "availability_slots.{$j}",
                                "Overlap detected for Teacher ID {$entry2['teacher_id']} on {$entry2['day_of_week']} in semester {$entry2['school_semester_id']}. " .
                                "Entry {$j} ({$entry2['start_time']}-{$entry2['end_time']}) clashes with Entry {$i} ({$entry1['start_time']}-{$entry1['end_time']})."
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'availability_slots.*.end_time.after' => 'The end time must be after the start time for each availability slot.',
            'availability_slots.*.day_of_week.in' => 'The day of week must be one of: monday, tuesday, wednesday, thursday, friday, saturday, sunday.',
        ];
    }
}
