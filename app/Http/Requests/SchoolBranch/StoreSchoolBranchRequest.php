<?php

namespace App\Http\Requests\SchoolBranch;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'abbreviation' => ['required', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'admin_first_name' => ['required', 'string', 'max:150'],
            'admin_last_name' => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:school_admins,email'],
            'admin_password' => ['required', 'string', 'min:8'],
            'subscription' => ['required', 'in:shared,new'],
            'plan_id' => ['required_if:subscription,new', 'nullable', 'string', 'exists:plans,id'],
            'payment_method_id' => ['required_if:subscription,new', 'nullable', 'string', 'exists:payment_method,id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'promo_code' => ['nullable', 'string', 'exists:affiliates,promo_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'admin_email.unique' => 'An admin account with this email already exists.',
            'plan_id.required_if' => 'Pick a plan for the new branch.',
            'payment_method_id.required_if' => 'Pick how to pay for the new plan.',
        ];
    }
}
