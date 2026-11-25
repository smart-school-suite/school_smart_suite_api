<?php

namespace App\Http\Requests\Specialty;

use App\Http\Requests\Import\ImportFileRequest;

class ImportSpecialtyRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'specialty_name' => 'specialty name',
            'department_name' => 'department',
            'level' => 'level',
        ];
    }
}
