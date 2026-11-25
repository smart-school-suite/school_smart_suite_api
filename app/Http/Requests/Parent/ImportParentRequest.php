<?php

namespace App\Http\Requests\Parent;

use App\Http\Requests\Import\ImportFileRequest;

class ImportParentRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'name' => 'guardian name',
            'phone' => 'phone',
            'address' => 'address',
        ];
    }
}
