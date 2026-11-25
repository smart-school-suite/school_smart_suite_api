<?php

namespace App\Http\Requests\Hall;

use App\Http\Requests\Import\ImportFileRequest;

class ImportHallRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'name' => 'hall name',
            'capacity' => 'capacity',
        ];
    }

    protected function requiredGroups(): array
    {
        return ['types' => 'hall types'];
    }
}
