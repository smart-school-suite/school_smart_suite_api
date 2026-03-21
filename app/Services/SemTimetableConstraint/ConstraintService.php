<?php

namespace App\Services\SemTimetableConstraint;

use App\Exceptions\AppException;
use App\Models\Constraint\SemTimetableConstraint;
use App\Models\Constraint\SemTimetableConstraintCategory;

class ConstraintService
{
    public function getConstraintsByCategory()
    {
        $categories = SemTimetableConstraintCategory::with('constraints.constraintType')->get();

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'category' => $category->name ?? null,
                'key' => $category->key ?? null,
                'constraints' => $category->constraints->map(function ($constraint) {
                    return [
                        'id' => $constraint->id,
                        'name' => $constraint->name ?? null,
                        'key' => $constraint->key ?? null,
                        'description' => $constraint->description ?? null,
                        'type' => $constraint->constraintType->name ?? null
                    ];
                })
            ];
        }

        return $result;
    }

    public function getAllConstraints()
    {
        $constraints = SemTimetableConstraint::with('constraintType', 'constraintCategory')->get();

        return $constraints->map(function ($constraint) {
            return [
                'name' => $constraint->name,
                'key' => $constraint->key,
                'description' => $constraint->description,
                'type' => $constraint->constraintType->name,
                'category' => $constraint->constraintCategory->name
            ];
        });
    }

    public function getConstraintById(string $constraintId)
    {
        $constraint = SemTimetableConstraint::with('constraintType', 'constraintCategory')->find($constraintId);
        if (!$constraint) {
            throw new AppException(
                "Constraint not found",
                404,
                "Constraint Not Found",
                "The specified constraint does not exist in the system."
            );
        }

        return $constraint;
    }
}
