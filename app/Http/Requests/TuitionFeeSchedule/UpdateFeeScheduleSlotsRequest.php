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
        // Whole-schedule rules (100% total, dates within the academic year and
        // in installment order) are checked by TuitionFeeScheduleSlotService.
        return [
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.slot_id' => ['nullable', 'string', 'distinct'],
            'slots.*.installment_id' => ['required', 'string', 'distinct', Rule::exists('installments', 'id')],
            'slots.*.due_date' => ['required', 'date_format:Y-m-d'],
            'slots.*.fee_percentage' => ['required', 'numeric', 'gt:0', 'max:100'],
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
            'slots.required' => 'Add at least one installment to the schedule.',
            'slots.min' => 'Add at least one installment to the schedule.',
            'slots.*.installment_id.required' => 'Each row must have an installment.',
            'slots.*.installment_id.distinct' => 'Each installment can only appear once in a schedule.',
            'slots.*.installment_id.exists' => 'The selected installment does not exist.',
            'slots.*.slot_id.distinct' => 'An installment was sent twice. Reload the schedule and try again.',
            'slots.*.due_date.required' => 'Each installment must have a due date.',
            'slots.*.due_date.date_format' => 'Each due date must be a valid date (YYYY-MM-DD).',
            'slots.*.fee_percentage.required' => 'Each installment must have a percentage.',
            'slots.*.fee_percentage.numeric' => 'The percentage must be a number.',
            'slots.*.fee_percentage.gt' => 'Each installment must be more than 0% of the tuition.',
            'slots.*.fee_percentage.max' => 'An installment cannot exceed 100% of the tuition.',
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
