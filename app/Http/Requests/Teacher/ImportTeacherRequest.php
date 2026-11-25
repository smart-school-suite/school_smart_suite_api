<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\Import\ImportFileRequest;

class ImportTeacherRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'email' => 'email',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'gender' => 'gender',
        ];
    }

    protected function requiredGroups(): array
    {
        return [
            'qualifications' => 'qualifications',
            'allowed_levels' => 'levels the teacher may teach',
        ];
    }
}
