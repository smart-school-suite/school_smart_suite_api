<?php

namespace App\Http\Middleware\TeacherAccountControl;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;

class CheckTeacherAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $teacher = $request->user();

        if (!$teacher || !$teacher instanceof Teacher) {
            return $this->errorResponse(
                'Unauthorized',
                'Invalid or missing authentication.',
                'Authentication Error',
                'You are not authenticated as a Teacher',
                401
            );
        }

        if ($teacher->status === 'inactive') {
            return $this->errorResponse(
                'Account Deactivated',
                'Your account has been deactivated.',
                'Account Deactivated',
                'Your Teacher account is currently deactivated. Please contact administration.',
                403,
                'ACCOUNT_DEACTIVATED'
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
