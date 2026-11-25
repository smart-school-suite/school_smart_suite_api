<?php

namespace App\Http\Requests\SpecialtyHall;

use App\Http\Requests\Import\ImportFileRequest;

class ImportSpecialtyHallRequest extends ImportFileRequest
{
    protected function requiredFields(): array
    {
        return [
            'hall' => 'hall',
            'specialty' => 'specialty',
        ];
    }
}
