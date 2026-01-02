<?php

namespace App\Constant\Authorization\Permissions\StudentBatch;

class StudentBatchPermissions
{
    public const CREATE = "student_batch.create";
    public const UPDATE = "student_batch.update";
    public const DELETE = "student_batch.delete";
    public const VIEW = "student_batch.view";
    public const DEACTIVATE_STUDENT_BATCH = "student_batch.deactivate";
    public const ACTIVATE_STUDENT_BATCH = "student_batch.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW,
            self::DEACTIVATE_STUDENT_BATCH,
            self::ACTIVATE_STUDENT_BATCH
        ];
    }
}
