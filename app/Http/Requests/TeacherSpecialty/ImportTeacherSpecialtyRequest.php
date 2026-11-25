<?php

namespace App\Http\Requests\TeacherSpecialty;

use App\Http\Requests\Import\ImportFileRequest;

class ImportTeacherSpecialtyRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'teacher' => 'teacher name',
            'specialty' => 'specialty',
        ];
    }
}
