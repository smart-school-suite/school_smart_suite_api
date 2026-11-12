<?php

namespace App\Services\Election\Support;

use App\Exceptions\AppException;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps the public participant type keys used by the API to model classes.
 */
final class ElectionParticipantType
{
    public const STUDENT = 'student';
    public const TEACHER = 'teacher';
    public const SCHOOL_ADMIN = 'school_admin';

    public const MAP = [
        self::STUDENT => Student::class,
        self::TEACHER => Teacher::class,
        self::SCHOOL_ADMIN => Schooladmin::class,
    ];

    public static function keys(): array
    {
        return array_keys(self::MAP);
    }

    public static function classFor(string $key): string
    {
        if (!isset(self::MAP[$key])) {
            throw new AppException(
                "Invalid participant type",
                422,
                "Invalid Participant Type",
                "Participant type '{$key}' is not supported. Use one of: " . implode(', ', self::keys()) . ".",
                null
            );
        }
        return self::MAP[$key];
    }

    public static function keyFor(string|Model $classOrModel): ?string
    {
        $class = $classOrModel instanceof Model ? $classOrModel->getMorphClass() : $classOrModel;
        $key = array_search($class, self::MAP, true);
        return $key === false ? null : $key;
    }

    public static function isSupported(?Model $model): bool
    {
        return $model !== null && self::keyFor($model) !== null;
    }

    /**
     * Find a participant inside a school branch.
     */
    public static function find(string $key, string $id, string $schoolBranchId): Model
    {
        $class = self::classFor($key);
        $model = $class::where('school_branch_id', $schoolBranchId)->find($id);

        if (!$model) {
            throw new AppException(
                "Participant not found",
                404,
                "Participant Not Found",
                "The selected {$key} could not be found in this school branch.",
                null
            );
        }
        return $model;
    }

    public static function displayName(?Model $model): ?string
    {
        if (!$model) {
            return null;
        }
        return $model->name
            ?? trim(($model->first_name ?? '') . ' ' . ($model->last_name ?? ''))
            ?: null;
    }
}
