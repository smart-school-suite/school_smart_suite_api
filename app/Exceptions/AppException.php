<?php

namespace App\Exceptions;

use Exception;

class AppException extends Exception
{
    protected $title;
    protected $description;
    protected $path;

    public function __construct(string $message = "", int $code = 0, $title = null, $description = null, $path = null, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->title = $title;
        $this->description = $description;
        $this->path = $path;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
