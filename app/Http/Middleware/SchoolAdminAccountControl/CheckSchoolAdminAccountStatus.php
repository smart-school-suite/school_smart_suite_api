<?php

namespace App\Http\Middleware\SchoolAdminAccountControl;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Schooladmin;
use Illuminate\Http\JsonResponse;

class CheckSchoolAdminAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $schoolAdmin = $request->user();

        if (!$schoolAdmin || !$schoolAdmin instanceof Schooladmin) {
            return $this->errorResponse(
                'Unauthorized',
                'Invalid or missing authentication.',
                'Authentication Error',
                'You are not authenticated as a School Admin',
                401
            );
        }

        if ($schoolAdmin->status === 'inactive') {
            return $this->errorResponse(
                'Account Deactivated',
                'Your account has been deactivated.',
                'Account Deactivated',
                'Your School Admin account is currently deactivated. Please contact administration.',
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
