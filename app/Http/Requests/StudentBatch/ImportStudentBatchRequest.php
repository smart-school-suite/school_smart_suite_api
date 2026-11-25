<?php

namespace App\Http\Requests\StudentBatch;

use App\Http\Requests\Import\ImportFileRequest;

class ImportStudentBatchRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'name' => 'batch name',
            'description' => 'description',
        ];
    }
}
