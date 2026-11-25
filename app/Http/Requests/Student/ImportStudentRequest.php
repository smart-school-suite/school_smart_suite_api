<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\Import\ImportFileRequest;

class ImportStudentRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'email' => 'email',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'gender' => 'gender',
            'specialty' => 'specialty',
            'batch' => 'student batch',
            'guardian' => 'guardian',
            'relationship' => 'relationship',
            'student_source' => 'student source',
        ];
    }
}
