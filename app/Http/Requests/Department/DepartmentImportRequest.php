<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\Import\ImportFileRequest;

class DepartmentImportRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return ['department_name' => 'department name'];
    }
}
