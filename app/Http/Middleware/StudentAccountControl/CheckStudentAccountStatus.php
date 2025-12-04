<?php

namespace App\Http\Middleware\StudentAccountControl;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;
use Illuminate\Http\JsonResponse;

class CheckStudentAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user();

        if (!$student || !$student instanceof Student) {
            return $this->errorResponse(
                'Unauthorized',
                'Invalid or missing authentication.',
                'Authentication Error',
                'You are not authenticated as a student.',
                401
            );
        }

        if ($student->deactivated == true || $student->status === 'inactive') {
            return $this->errorResponse(
                'Account Deactivated',
                'Your account has been deactivated.',
                'Account Deactivated',
                'Your student account is currently deactivated. Please contact administration.',
                403,
                'ACCOUNT_DEACTIVATED'
            );
        }

        if ($student->dropout_status == true) {
            return $this->errorResponse(
                'Access Restricted',
                'You are marked as dropped out.',
                'Dropped Out',
                'Students marked as dropped out cannot access the system.',
                403,
                'STUDENT_DROPPED_OUT'
            );
        }

        return $next($request);
    }

    private function errorResponse(
        string $message,
        string $description,
        string $title,
        string $errorDescription,
        int $statusCode,
        ?string $errorCode = null
    ): JsonResponse {
        return response()->json([
            "status"  => "error",
            "message" => $message,
            "data"    => null,
            "errors"  => [
                "title"       => $title,
                "description" => $errorDescription,
                "path"        => request()->path()
            ],
            "meta"    => [
                "error_code"  => $errorCode,
                "timestamp"   => now()->toDateTimeString()
            ]
        ], $statusCode);
    }
}
