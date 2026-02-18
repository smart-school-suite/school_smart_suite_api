<?php

namespace App\Services\Constraint;

use App\Exceptions\AppException;
use App\Models\Constraint\SemesterTimetableConstraint;

class SemesterTimetableConstraintService
{
    public function getSemesterTimetableConstraints()
    {
        return SemesterTimetableConstraint::with(['constraintType', 'constraintCategory'])->get();
    }

    public function getSemesterTimetableConstraintById(string $constraintId)
    {
        return SemesterTimetableConstraint::with(['constraintType', 'constraintCategory'])->findOrFail($constraintId);
    }

    public function activateSemesterTimetableConstraint(string $constraintId)
    {
        $constraint  = SemesterTimetableConstraint::find($constraintId);
        if (!$constraint) {
            throw new AppException(
                "Constraint Not Found",
                404,
                "The specified constraint does not exist.",
                "The Constraint Was Not Found Please Ensure that the constraint exists and try again"
            );
        }

        if($constraint->status === 'active') {
            throw new AppException(
                "Constraint Already Active",
                400,
                "The specified constraint is already active.",
                "The Constraint is Already Active Please Ensure that the constraint is not already active and try again"
            );
        }

        $constraint->status = 'active';
        $constraint->save();
        return $constraint;
    }

    public function deactivateSemesterTimetableConstraint(string $constraintId)
    {
        $constraint  = SemesterTimetableConstraint::find($constraintId);
        if (!$constraint) {
            throw new AppException(
                "Constraint Not Found",
                404,
                "The specified constraint does not exist.",
                "The Constraint Was Not Found Please Ensure that the constraint exists and try again"
            );
        }

        if($constraint->status === 'inactive') {
            throw new AppException(
                "Constraint Already Inactive",
                400,
                "The specified constraint is already inactive.",
                "The Constraint is Already Inactive Please Ensure that the constraint is not already inactive and try again"
            );
        }

        $constraint->status = 'inactive';
        $constraint->save();
        return $constraint;
    }
}
