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
            'consecutive_limit' => ['required', 'integer', 'min:1'],
            'max_week_sessions' => ['required', 'integer', 'min:1'],
            'allow_doubles' => ['required', 'boolean'],
            'min_gap' => ['required', 'integer', 'min:0', ],
            'max_courses_day' => ['required', 'integer', 'min:1', ],
        ];
    }

}
