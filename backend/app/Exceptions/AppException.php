<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AppException extends Exception
{
    /**
     * Create a new AppException instance.
     *
     * @param string $message
     * @param int $statusCode
     * @param Exception|null $previous
     */
    public function __construct(
        string $message,
        int $statusCode = 400,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }
}
