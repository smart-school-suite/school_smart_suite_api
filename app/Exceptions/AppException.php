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

    /**
     * A failure the code didn't foresee, e.g. a database error. The user gets
     * a plain message; the original error is kept as the previous exception,
     * so the logs and Sentry show its real cause and stack trace.
     *
     *   } catch (Throwable $e) {
     *       throw AppException::unexpected('load the tuition fees', $e);
     *   }
     */
    public static function unexpected(string $action, \Throwable $previous): self
    {
        return new self(
            "An unexpected error occurred while trying to {$action}.",
            500,
            'Something Went Wrong',
            "We couldn't {$action} because of a server problem. Please try again; if it keeps happening, contact support.",
            null,
            $previous
        );
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
