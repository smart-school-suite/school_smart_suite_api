<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponseService
{
    /**
     * Success Response
     *
     * @param string $message
     * @param mixed $data
     * @param array|null $meta
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(string $message, $data = null, array $meta = null, int $statusCode = 200)
    {
        if ($data instanceof LengthAwarePaginator) {
            $meta = [
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ]
            ];
            $data = $data->items();
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta
        ], $statusCode);
    }

    /**
     * Error Response
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @param array|null $structuredError (New parameter for structured errors)
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message, array $errors = null, int $statusCode = 400, array $structuredError = null)
    {
        Log::error($message, [
            'errors' => $errors,
            'status_code' => $statusCode,
            'structured_error' => $structuredError, // Log the structured error as well
        ]);

        $responseBody = [
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => null
        ];

        // Override the 'errors' key with the structured error if provided
        if ($structuredError) {
            $responseBody['errors'] = $structuredError;
        }

        return response()->json($responseBody, $statusCode);
    }

    /**
     * Validation Error Response
     *
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validationError(array $errors)
    {
        return self::error('Validation failed', $errors, 422);
    }
}
