<?php

namespace App\Http\Requests\SchoolAdmin;

use App\Http\Requests\Import\ImportFileRequest;

class ImportSchoolAdminRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'email' => 'email',
            'first_name' => 'first name',
            'last_name' => 'last name',
        ];
    }
}
