<?php

namespace App\Http\Requests\SchoolAdmin;

use App\Support\SchoolAdmin\UsernameRules;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolAdminProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge(['username' => UsernameRules::normalize($this->input('username'))]);
        }
    }

    public function rules(): array
    {
        $schoolAdminId = auth()->guard('schooladmin')->id();

        return [
            'name' => 'sometimes|nullable|string|max:255',
            'first_name' => 'sometimes|nullable|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'username' => [
                'sometimes',
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($schoolAdminId) {
                    $message = UsernameRules::failedMessage(UsernameRules::checks($value, $schoolAdminId));
                    if ($message) {
                        $fail($message);
                    }
                },
            ],
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:100',
            'address' => 'sometimes|nullable|string|max:225',
        ];
    }
}
