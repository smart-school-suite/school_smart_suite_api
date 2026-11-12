<?php

namespace App\Services\Election\Support;

use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait FindsElectionRecords
{
    /**
     * Find a record scoped to the current school branch or throw a 404.
     *
     * @template T of Model
     * @param class-string<T> $modelClass
     * @return T
     */
    protected function findInBranch(string $modelClass, string $id, object $currentSchool, array $with = [], bool $lock = false): Model
    {
        /** @var Builder $query */
        $query = $modelClass::query()->where('school_branch_id', $currentSchool->id)->with($with);
        if ($lock) {
            $query->lockForUpdate();
        }
        $record = $query->find($id);

        if (!$record) {
            $label = strtolower(trim(preg_replace('/(?<!^)[A-Z]/', ' $0', class_basename($modelClass))));
            throw new AppException(
                ucfirst($label) . " not found",
                404,
                ucwords($label) . " Not Found",
                "The requested {$label} could not be found in this school branch.",
                null
            );
        }
        return $record;
    }

    protected function fail(string $title, string $description, int $code = 422): never
    {
        throw new AppException($title, $code, $title, $description, null);
    }
}
