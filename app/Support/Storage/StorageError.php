<?php

namespace App\Support\Storage;

use Aws\Exception\AwsException;
use Throwable;

class StorageError
{
    public static function describe(Throwable $exception): string
    {
        $message = $exception->getMessage();

        for ($cause = $exception->getPrevious(); $cause !== null; $cause = $cause->getPrevious()) {
            if ($cause instanceof AwsException) {
                $code = $cause->getAwsErrorCode() ?: 'NoErrorCode';
                $detail = $cause->getAwsErrorMessage() ?: $cause->getMessage();
                $status = $cause->getStatusCode() ? " (HTTP {$cause->getStatusCode()})" : '';
                return "{$message} Storage said {$code}{$status}: {$detail}";
            }
        }

        $previous = $exception->getPrevious();

        return $previous ? "{$message} Cause: {$previous->getMessage()}" : $message;
    }
}
