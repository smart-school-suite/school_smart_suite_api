<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Import Carbon for time manipulation

class BulkUpdateTeacherAvailabilitySlotsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // IMPORTANT: Implement your actual authorization logic here.
        // For example, ensure the authenticated user is an administrator,
        // or the specific teacher associated with the slots being updated.
        // Example: return auth()->user()->can('update-teacher-availability-slots');
        // Or if the user is a teacher updating their own slots:
        // foreach ($this->input('availability_slots', []) as $slot) {
        //     if (isset($slot['teacher_id']) && $slot['teacher_id'] !== auth()->id()) {
        //         return false; // Not authorized to update other teachers' slots
        //     }
        // }
        return true; // Defaulting to true for demonstration, CHANGE THIS!
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'availability_slots' => 'required|array',
            'availability_slots.*.slot_id' => [
                'required',
                'string',
                'exists:teacher_availability_slots,id',
            ],
            'availability_slots.*.teacher_id' => [
                'required',
                'string',
                'exists:teachers,id',
            ],
            'availability_slots.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availability_slots.*.start_time' => 'required|date_format:H:i',
            'availability_slots.*.end_time' => 'required|date_format:H:i|after:availability_slots.*.start_time',
            'availability_slots.*.teacher_availability_id' => [
                'required',
                'string',
                'exists:teacher_availabilities,id',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * This method adds a custom validation rule to check for time overlaps
     * within the submitted array of availability slots.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $slots = $this->input('availability_slots');

            if (!is_array($slots) || empty($slots)) {
                return;
            }

            $count = count($slots);

            for ($i = 0; $i < $count; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $slot1 = $slots[$i];
                    $slot2 = $slots[$j];

                    if (
                        $slot1['slot_id'] !== $slot2['slot_id'] &&
                        $slot1['teacher_id'] === $slot2['teacher_id'] &&
                        $slot1['day_of_week'] === $slot2['day_of_week'] &&
                        $slot1['teacher_availability_id'] === $slot2['teacher_availability_id']
                    ) {
                        try {

                            $start1 = Carbon::createFromFormat('H:i', $slot1['start_time']);
                            $end1 = Carbon::createFromFormat('H:i', $slot1['end_time']);
                            $start2 = Carbon::createFromFormat('H:i', $slot2['start_time']);
                            $end2 = Carbon::createFromFormat('H:i', $slot2['end_time']);

                            if ($start1->lessThan($end2) && $end1->greaterThan($start2)) {
                                $validator->errors()->add(
                                    "availability_slots.{$i}",
                                    "Overlap detected: Slot ID {$slot1['slot_id']} ({$slot1['start_time']}-{$slot1['end_time']}) for Teacher ID {$slot1['teacher_id']} on {$slot1['day_of_week']} clashes with Slot ID {$slot2['slot_id']} ({$slot2['start_time']}-{$slot2['end_time']})."
                                );

                                $validator->errors()->add(
                                    "availability_slots.{$j}",
                                    "Overlap detected: Slot ID {$slot2['slot_id']} ({$slot2['start_time']}-{$slot2['end_time']}) for Teacher ID {$slot2['teacher_id']} on {$slot2['day_of_week']} clashes with Slot ID {$slot1['slot_id']} ({$slot1['start_time']}-{$slot1['end_time']})."
                                );
                            }
                        } catch (\Exception $e) {

                            $validator->errors()->add(
                                "availability_slots",
                                "An error occurred while validating time formats. Please ensure all times are HH:MM."
                            );
                            Log::error("Time parsing error in BulkUpdateTeacherAvailabilitySlotsRequest: " . $e->getMessage());
                            break 2;
                        }
                    }
                }
            }
        });
    }

    /**
     * Get the custom validation messages for the defined rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'availability_slots.required' => 'At least one availability slot is required.',
            'availability_slots.array' => 'Availability slots must be provided as an array.',

            'availability_slots.*.slot_id.required' => 'Each slot must have a unique identifier.',
            'availability_slots.*.slot_id.string' => 'The slot ID must be a string.',
            'availability_slots.*.slot_id.exists' => 'The provided slot ID :input does not exist in our records.',

            'availability_slots.*.teacher_id.required' => 'Each slot must be assigned to a teacher.',
            'availability_slots.*.teacher_id.string' => 'The teacher ID must be a string.',
            'availability_slots.*.teacher_id.exists' => 'The provided teacher ID :input does not exist.',

            'availability_slots.*.day_of_week.required' => 'Each slot must specify a day of the week.',
            'availability_slots.*.day_of_week.string' => 'The day of week must be a string.',
            'availability_slots.*.day_of_week.in' => 'The day of week for a slot must be one of: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday.',

            'availability_slots.*.start_time.required' => 'Each slot must have a start time.',
            'availability_slots.*.start_time.date_format' => 'The start time for a slot must be in HH:MM format (e.g., 09:00).',

            'availability_slots.*.end_time.required' => 'Each slot must have an end time.',
            'availability_slots.*.end_time.date_format' => 'The end time for a slot must be in HH:MM format (e.g., 17:00).',
            'availability_slots.*.end_time.after' => 'The end time for a slot must be after its start time.',

            'availability_slots.*.teacher_availability_id.required' => 'Each slot must be linked to a parent teacher availability record.',
            'availability_slots.*.teacher_availability_id.string' => 'The parent teacher availability ID must be a string.',
            'availability_slots.*.teacher_availability_id.exists' => 'The provided parent teacher availability ID :input does not exist.',
        ];
    }
}
