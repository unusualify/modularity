<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait ApiResponses
{
    /**
     * Respond with success message
     *
     * @param string $message
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    protected function respondWithMessage(string $message, array $data = [], int $status = 200): JsonResponse
    {
        $response = [
            'message' => $message,
            'success' => true,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response, $status);
    }

    /**
     * Respond with error
     *
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return JsonResponse
     */
    protected function respondWithError(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $response = [
            'message' => $message,
            'success' => false,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return Response::json($response, $status);
    }

    /**
     * Respond with not found error
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->respondWithError($message, 404);
    }

    /**
     * Respond with validation error
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithValidationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->respondWithError($message, 422, $errors);
    }

    /**
     * Respond with unauthorized error
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->respondWithError($message, 401);
    }

    /**
     * Respond with forbidden error
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->respondWithError($message, 403);
    }
}
