<?php

namespace App\Schedular\ExamTimetable\Context;

abstract class ExamTimetableContext
{
    protected static array $requestPayload;
    protected static array $examDateSlot;
    public static function setRequestPayload(array $requestPayload)
    {
        self::$requestPayload = $requestPayload;
    }
    public static function setExamDateSlot(array $examDateSlot)
    {
        self::$examDateSlot = $examDateSlot;
    }
}
