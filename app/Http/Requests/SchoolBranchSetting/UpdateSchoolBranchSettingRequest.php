<?php

namespace App\Http\Requests\SchoolBranchSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolBranchSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'setting_id' => 'required|string|exists:school_branch_settings,id',
            'value' => ['present']
        ];
    }
}
