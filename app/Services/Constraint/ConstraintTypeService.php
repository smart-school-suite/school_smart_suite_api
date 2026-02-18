<?php

namespace App\Services\Constraint;

use App\Models\Constraint\ConstraintType;
use App\Exceptions\AppException;
class ConstraintTypeService
{
    public function getConstraintTypes()
    {
        $constraintTypes = ConstraintType::all();
        if ($constraintTypes->isEmpty()) {
            throw new AppException(
                "No Constraint Types Found",
                404,
                "No constraint types were found in the system.",
                "No Constraint Types Found Please Ensure that there are constraint types in the system and try again"
            );
        }
        return $constraintTypes;
    }
    public function getConstraintTypeById(string $constraintTypeId)
    {
        $constraintType = ConstraintType::find($constraintTypeId);
        if (!$constraintType) {
            throw new AppException(
                "Constraint Type Not Found",
                404,
                "The specified constraint type does not exist.",
                "The Constraint Type Was Not Found Please Ensure that the constraint type exists and try again"
            );
        }
        return $constraintType;
    }

    public function activateConstraintType(string $constraintTypeId)
    {
        $constraintType  = ConstraintType::find($constraintTypeId);
        if (!$constraintType) {
            throw new AppException(
                "Constraint Type Not Found",
                404,
                "The specified constraint type does not exist.",
                "The Constraint Type Was Not Found Please Ensure that the constraint type exists and try again"
            );
        }

        if ($constraintType->status === 'active') {
            throw new AppException(
                "Constraint Type Already Active",
                400,
                "The specified constraint type is already active.",
                "The Constraint Type is Already Active Please Ensure that the constraint type is not already active and try again"
            );
        }

        $constraintType->status = 'active';
        $constraintType->save();
        return $constraintType;
    }

    public function deactivateConstraintType(string $constraintTypeId)
    {
        $constraintType  = ConstraintType::find($constraintTypeId);
        if (!$constraintType) {
            throw new AppException(
                "Constraint Type Not Found",
                404,
                "The specified constraint type does not exist.",
                "The Constraint Type Was Not Found Please Ensure that the constraint type exists and try again"
            );
        }

        if ($constraintType->status === 'inactive') {
            throw new AppException(
                "Constraint Type Already Inactive",
                400,
                "The specified constraint type is already inactive.",
                "The Constraint Type is Already Inactive Please Ensure that the constraint type is not already inactive and try again"
            );
        }

        $constraintType->status = 'inactive';
        $constraintType->save();
        return $constraintType;
    }
}
