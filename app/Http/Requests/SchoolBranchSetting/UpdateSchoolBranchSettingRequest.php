<?php

namespace App\Http\Requests\SchoolBranchSetting;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\SchoolBranchSetting;
class UpdateSchoolBranchSettingRequest extends FormRequest
{public function rules(): array
    {
        return [
            'school_branch_setting_id' => 'required|string|exists:school_branch_settings,id',
            'value' => 'required'
        ];
    }
}
