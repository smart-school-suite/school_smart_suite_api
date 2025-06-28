<?php

namespace App\Http\Requests\TuitionFeeSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateFeeScheduleSlotsRequest extends FormRequest
{
        /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Typically, you'd implement authorization logic here.
        // For example, check if the authenticated user has permission to create fee schedule slots.
        // For now, returning true allows all authenticated users to proceed.
        // If your application requires authorization, uncomment and implement the logic below:
        // return $this->user()->can('create-fee-schedule-slots');

        return true; // Set to true for development, but implement proper authorization in production
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.slot_id' => ['required', 'string', Rule::exists('fee_schedule_slots', 'id')],
            'slots.*.due_date' => ['somtimes', 'nullable', 'date', 'after_or_equal:today'],
            'slots.*.fee_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0.01', 'max:100.00'],
            'slots.*.amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'slots.*.installment_id' => [
                'sometimes',
                'nullable',
                'string',
                Rule::exists('installments', 'id'),
            ],
            'fee_schedule_id' => [
                'nullable',
                'sometimes',
                'string',
                Rule::exists('fee_schedules', 'id'),
            ],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slots.required' => 'At least one slot must be provided.',
            'slots.array' => 'The slots must be an array.',
            'slots.min' => 'At least one fee schedule slot is required.',
            'slots.*.due_date.required' => 'Each slot must have a due date.',
            'slots.*.due_date.date' => 'Each slot\'s due date must be a valid date.',
            'slots.*.due_date.after_or_equal' => 'Each slot\'s due date cannot be in the past.',
            'slots.*.fee_percentage.required' => 'Each slot must have a fee percentage.',
            'slots.*.fee_percentage.numeric' => 'The fee percentage must be a number.',
            'slots.*.fee_percentage.min' => 'The fee percentage must be at least 0.01.',
            'slots.*.fee_percentage.max' => 'The fee percentage cannot exceed 100.',
            'slots.*.amount.required' => 'Each slot must have an amount.',
            'slots.*.amount.numeric' => 'The amount must be a number.',
            'slots.*.amount.min' => 'The amount cannot be negative.',
            'slots.*.installment_id.required' => 'Each slot must have an installment ID.',
            'slots.*.installment_id.string' => 'The installment ID must be a string.',
            'slots.*.installment_id.exists' => 'The selected installment ID is invalid.',
            'fee_schedule_id.required' => 'The fee schedule ID is required.',
            'fee_schedule_id.string' => 'The fee schedule ID must be a string.',
            'fee_schedule_id.exists' => 'The selected fee schedule ID is invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Example: If you expect fee_percentage and amount to be strings from frontend,
        // you might cast them to float/int here before validation runs.
        // This can also be done in a DTO or directly in the controller if preferred.
        // foreach ($this->input('slots', []) as $key => $slot) {
        //     if (isset($slot['fee_percentage'])) {
        //         $this->merge([
        //             "slots.$key.fee_percentage" => (float) $slot['fee_percentage'],
        //         ]);
        //     }
        //     if (isset($slot['amount'])) {
        //         $this->merge([
        //             "slots.$key.amount" => (float) $slot['amount'],
        //         ]);
        //     }
        // }
    }
}
