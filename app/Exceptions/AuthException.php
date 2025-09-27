<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
     protected $title;
    protected $description;

    public function __construct(string $message = "", int $code = 0, ?string $title = null, ?string $description = null, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->title = $title;
        $this->description = $description;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
