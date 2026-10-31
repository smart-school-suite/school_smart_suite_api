<?php

namespace App\Services\Auth\Student\Concerns;

use App\Exceptions\AuthException;
use App\Models\Student;

trait EnsuresStudentCanAuthenticate
{
    /**
     * Reject students whose account is deactivated or who have been marked as dropped out.
     */
    protected function ensureStudentCanAuthenticate(Student $student): void
    {
        if ($student->status === 'inactive' || (bool) $student->deactivate) {
            throw new AuthException(
                "ACCOUNT_DEACTIVATED",
                403,
                "Account Deactivated",
                "Your student account has been deactivated. Please contact your school administration to resolve this issue."
            );
        }

        if ((bool) $student->dropout_status) {
            throw new AuthException(
                "STUDENT_DROPPED_OUT",
                403,
                "Dropped Out",
                "You have been marked as a dropout. Access to the system is restricted. Please contact administration for clarification."
            );
        }
    }
}
