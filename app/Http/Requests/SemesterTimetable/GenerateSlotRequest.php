<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSlotRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day' => ["required", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],
            "start_time" => ["required", "date_format:H:i"],
            "end_time" => ["required", "date_format:H:i", "after:start_time"],
            "period_duration_id" => ["required", "exists:period_durations,id"]
        ];
    }
}
