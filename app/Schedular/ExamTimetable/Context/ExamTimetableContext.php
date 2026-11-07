<?php

namespace App\Schedular\ExamTimetable\Context;

/**
 * Holds the scheduler input for the run in progress. The engine sets it once,
 * then every part of the scheduler reads it through RequestContext::fromPayload().
 */
abstract class ExamTimetableContext
{
    protected static array $requestPayload = [];
    protected static ?RequestContext $context = null;

    public static function setRequestPayload(array $requestPayload): void
    {
        self::$requestPayload = $requestPayload;
        self::$context = null;
    }

    public static function requestPayload(): array
    {
        return self::$requestPayload;
    }
}
