<?php

namespace App\Services\Import;

use RuntimeException;

/**
 * Stops a whole import before any row is saved: the file cannot be read, a
 * mapped column is missing, the file is empty or too large. The message is
 * shown to the person as the job's error, so write it for them.
 */
class ImportFailedException extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'import_failed')
    {
        parent::__construct($message);
    }
}
