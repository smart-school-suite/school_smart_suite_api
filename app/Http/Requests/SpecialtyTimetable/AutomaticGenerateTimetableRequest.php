<?php

namespace App\Http\Requests\SpecialtyTimetable;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AutomaticGenerateTimetableRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * Updated to support variable slot lengths with min/max slot constraints and increments.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['required', 'string', 'distinct', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],

            // Slot constraints
            'min_day_slots' => ['required', 'integer', 'min:1'],
            'max_day_slots' => ['required', 'integer', 'min:1'],
            'min_week_slots' => ['required', 'integer', 'min:1'],
            'max_week_slots' => ['required', 'integer', 'min:1'],

            // Variable slot length constraints
            'min_slot_length' => ['required', 'integer', 'min:1', 'max:1440'],
            'max_slot_length' => ['required', 'integer', 'min:1', 'max:1440'],
            'slot_increment' => ['required', 'integer', 'min:1'],

            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i', 'after:start'],

            // Constraints relative to max_day_slots
            'consecutive_limit' => ['required', 'integer', 'min:1', 'lte:max_day_slots'],
            'max_week_sessions' => ['required', 'integer', 'min:1'],
            'allow_doubles' => ['required', 'boolean'],
            'min_gap' => ['required', 'integer', 'min:0', 'lte:max_day_slots'],
            'max_courses_day' => ['required', 'integer', 'min:1', 'lte:max_day_slots'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Days
            'days.required' => 'Please select at least one working day.',
            'days.min' => 'You must select at least one working day.',
            'days.*.in' => 'Each working day must be a valid weekday.',
            'days.*.distinct' => 'You cannot select the same day more than once.',

            // Slot constraints
            'min_day_slots.required' => 'Please specify the minimum number of slots a teacher must have per day.',
            'min_day_slots.min' => 'Minimum daily slots must be at least 1.',
            'max_day_slots.required' => 'Please specify the maximum number of slots a teacher can have per day.',
            'max_day_slots.min' => 'Maximum daily slots must be at least 1.',
            'min_week_slots.required' => 'Please specify the minimum number of slots a teacher must have per week.',
            'min_week_slots.min' => 'Minimum weekly slots must be at least 1.',
            'max_week_slots.required' => 'Please specify the maximum number of slots a teacher can have per week.',
            'max_week_slots.min' => 'Maximum weekly slots must be at least 1.',

            // Variable slot length constraints
            'min_slot_length.required' => 'Please specify the minimum slot length (in minutes).',
            'min_slot_length.min' => 'Minimum slot length must be at least 1 minute.',
            'min_slot_length.max' => 'Minimum slot length cannot exceed 1440 minutes (24 hours).',
            'max_slot_length.required' => 'Please specify the maximum slot length (in minutes).',
            'max_slot_length.min' => 'Maximum slot length must be at least 1 minute.',
            'max_slot_length.max' => 'Maximum slot length cannot exceed 1440 minutes (24 hours).',
            'slot_increment.required' => 'Please specify the slot length increment (in minutes).',
            'slot_increment.min' => 'Slot length increment must be at least 1 minute.',

            // Times
            'start.required' => 'Please provide a start time.',
            'start.date_format' => 'Start time must be in HH:MM format.',
            'end.required' => 'Please provide an end time.',
            'end.date_format' => 'End time must be in HH:MM format.',
            'end.after' => 'End time must be later than start time.',

            // Limits
            'consecutive_limit.required' => 'Please set the maximum consecutive classes allowed.',
            'consecutive_limit.lte' => 'Consecutive classes cannot exceed the maximum daily slots.',
            'max_week_sessions.required' => 'Please set the maximum weekly sessions allowed for each course.',
            'allow_doubles.required' => 'Please specify whether double periods are allowed.',
            'allow_doubles.boolean' => 'Double periods must be true or false.',
            'min_gap.required' => 'Please set the minimum gap between classes of the same course.',
            'min_gap.lte' => 'Minimum gap cannot be larger than the maximum daily slots.',
            'max_courses_day.required' => 'Please set the maximum number of courses allowed per day.',
            'max_courses_day.lte' => 'Maximum courses per day cannot exceed the maximum daily slots.',
        ];
    }

    /**
     * Custom validation logic after initial rules.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Check for required fields before proceeding with custom validation
            if (
                !isset(
                    $data['start'],
                    $data['end'],
                    $data['min_slot_length'],
                    $data['max_slot_length'],
                    $data['slot_increment'],
                    $data['days'],
                    $data['min_day_slots'],
                    $data['max_day_slots'],
                    $data['min_week_slots'],
                    $data['max_week_slots']
                )
            ) {
                return;
            }

            $start = Carbon::createFromFormat('H:i', $data['start']);
            $end = Carbon::createFromFormat('H:i', $data['end']);
            $dayMinutes = $start->diffInMinutes($end);

            if ($dayMinutes <= 0) {
                $validator->errors()->add(
                    'end',
                    "End time ({$data['end']}) must be after start time ({$data['start']})."
                );
                return;
            }

            // 1. Min/Max slot length consistency
            if ($data['min_slot_length'] > $data['max_slot_length']) {
                $validator->errors()->add(
                    'min_slot_length',
                    'Minimum slot length cannot be greater than maximum slot length.'
                );
            }

            // 2. Slot increment must allow valid slot lengths
            if ($data['slot_increment'] > $data['max_slot_length']) {
                $validator->errors()->add(
                    'slot_increment',
                    'Slot increment cannot be greater than maximum slot length.'
                );
            }
            if (($data['max_slot_length'] - $data['min_slot_length']) % $data['slot_increment'] !== 0) {
                $validator->errors()->add(
                    'slot_increment',
                    'Slot increment must evenly divide the difference between max_slot_length and min_slot_length.'
                );
            }

            // 3. Min/Max daily and weekly slots consistency
            if ($data['min_day_slots'] > $data['max_day_slots']) {
                $validator->errors()->add(
                    'min_day_slots',
                    'Minimum daily slots cannot be greater than maximum daily slots.'
                );
            }
            if ($data['min_week_slots'] > $data['max_week_slots']) {
                $validator->errors()->add(
                    'min_week_slots',
                    'Minimum weekly slots cannot be greater than maximum weekly slots.'
                );
            }

            // 4. Max daily slots feasibility
            // Use min_slot_length to calculate maximum possible slots per day
            $maxPossibleSlots = floor($dayMinutes / $data['min_slot_length']);
            if ($data['max_day_slots'] > $maxPossibleSlots) {
                $validator->errors()->add(
                    'max_day_slots',
                    "Maximum daily slots ({$data['max_day_slots']}) cannot exceed the total available slots per day ({$maxPossibleSlots}) based on minimum slot length."
                );
            }

            // 5. Max weekly slots feasibility
            $totalAvailableWeekSlots = $maxPossibleSlots * count($data['days']);
            if ($data['max_week_slots'] > $totalAvailableWeekSlots) {
                $validator->errors()->add(
                    'max_week_slots',
                    "Maximum weekly slots ({$data['max_week_slots']}) cannot exceed the total available slots across selected days ({$totalAvailableWeekSlots})."
                );
            }

            // 6. Max weekly sessions feasibility
            if ($data['max_week_sessions'] > $totalAvailableWeekSlots) {
                $validator->errors()->add(
                    'max_week_sessions',
                    "Maximum weekly sessions per course ({$data['max_week_sessions']}) cannot exceed the total available slots across selected days ({$totalAvailableWeekSlots})."
                );
            }
        });
    }
}
