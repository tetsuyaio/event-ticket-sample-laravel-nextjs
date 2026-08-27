<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    /** @param array<string, mixed>|null $errors */
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $status,
        public readonly ?array $errors = null,
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
            'errors' => $this->errors,
        ], $this->status);
    }
}
