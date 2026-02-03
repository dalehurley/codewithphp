<?php

/**
 * Standardized Error Responses
 * 
 * Demonstrates:
 * - Structured error format
 * - Error codes and categories
 * - Retryable vs non-retryable errors
 * - Error details and context
 * - Consistent error handling
 */

declare(strict_types=1);

// ============================================================================
// ERROR CODE CONSTANTS
// ============================================================================

class ToolErrorCode
{
    // Input errors (not retryable)
    public const INVALID_INPUT = 'INVALID_INPUT';
    public const MISSING_REQUIRED_FIELD = 'MISSING_REQUIRED_FIELD';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    
    // Permission errors (not retryable)
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    
    // Resource errors
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const RESOURCE_UNAVAILABLE = 'RESOURCE_UNAVAILABLE'; // retryable
    
    // Execution errors
    public const EXECUTION_TIMEOUT = 'EXECUTION_TIMEOUT'; // retryable
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED'; // retryable
    public const INTERNAL_ERROR = 'INTERNAL_ERROR'; // retryable
    
    // Network errors (retryable)
    public const NETWORK_ERROR = 'NETWORK_ERROR';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    
    /**
     * Check if an error code is retryable.
     */
    public static function isRetryable(string $code): bool
    {
        return in_array($code, [
            self::RESOURCE_UNAVAILABLE,
            self::EXECUTION_TIMEOUT,
            self::RATE_LIMIT_EXCEEDED,
            self::INTERNAL_ERROR,
            self::NETWORK_ERROR,
            self::SERVICE_UNAVAILABLE,
        ]);
    }
}

// ============================================================================
// STANDARDIZED TOOL ERROR
// ============================================================================

class ToolError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly array $details = [],
        public readonly bool $retryable = false,
    ) {}
    
    public function toArray(): array
    {
        return [
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'details' => $this->details,
                'retryable' => $this->retryable,
                'timestamp' => date('c'),
            ],
        ];
    }
    
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
    
    /**
     * Create from an exception.
     */
    public static function fromException(\Throwable $e): self
    {
        // Determine error code based on exception type
        $code = match (true) {
            $e instanceof \InvalidArgumentException => ToolErrorCode::INVALID_INPUT,
            $e instanceof \RuntimeException => ToolErrorCode::INTERNAL_ERROR,
            default => ToolErrorCode::INTERNAL_ERROR,
        };
        
        return new self(
            code: $code,
            message: $e->getMessage(),
            details: [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            retryable: ToolErrorCode::isRetryable($code)
        );
    }
}

// ============================================================================
// EXAMPLE ERRORS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "              STANDARDIZED ERROR RESPONSES DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// ============================================================================
// 1. VALIDATION ERROR (not retryable)
// ============================================================================

echo "1. Validation Error (Not Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$validationError = new ToolError(
    code: ToolErrorCode::VALIDATION_FAILED,
    message: 'Input validation failed',
    details: [
        'fields' => [
            'email' => 'Invalid email format',
            'age' => 'Must be a positive integer',
        ],
    ],
    retryable: false
);

echo $validationError->toJson() . "\n\n";

// ============================================================================
// 2. RATE LIMIT ERROR (retryable)
// ============================================================================

echo "2. Rate Limit Error (Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$rateLimitError = new ToolError(
    code: ToolErrorCode::RATE_LIMIT_EXCEEDED,
    message: 'Rate limit exceeded. Please try again later.',
    details: [
        'limit' => 100,
        'window' => '1 minute',
        'retry_after' => 60,
        'current_usage' => 102,
    ],
    retryable: true
);

echo $rateLimitError->toJson() . "\n\n";

// ============================================================================
// 3. PERMISSION ERROR (not retryable)
// ============================================================================

echo "3. Permission Denied Error (Not Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$permissionError = new ToolError(
    code: ToolErrorCode::PERMISSION_DENIED,
    message: 'User does not have permission to access this resource',
    details: [
        'required_role' => 'admin',
        'user_role' => 'user',
        'resource' => 'database_query',
    ],
    retryable: false
);

echo $permissionError->toJson() . "\n\n";

// ============================================================================
// 4. NETWORK ERROR (retryable)
// ============================================================================

echo "4. Network Error (Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$networkError = new ToolError(
    code: ToolErrorCode::NETWORK_ERROR,
    message: 'Failed to connect to external service',
    details: [
        'service' => 'api.example.com',
        'timeout' => 30,
        'attempt' => 1,
        'max_retries' => 3,
    ],
    retryable: true
);

echo $networkError->toJson() . "\n\n";

// ============================================================================
// 5. TIMEOUT ERROR (retryable)
// ============================================================================

echo "5. Execution Timeout (Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$timeoutError = new ToolError(
    code: ToolErrorCode::EXECUTION_TIMEOUT,
    message: 'Tool execution exceeded maximum allowed time',
    details: [
        'timeout_seconds' => 30,
        'elapsed_seconds' => 31.5,
        'tool' => 'complex_calculation',
    ],
    retryable: true
);

echo $timeoutError->toJson() . "\n\n";

// ============================================================================
// 6. RESOURCE NOT FOUND (not retryable)
// ============================================================================

echo "6. Resource Not Found (Not Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$notFoundError = new ToolError(
    code: ToolErrorCode::RESOURCE_NOT_FOUND,
    message: 'The requested resource does not exist',
    details: [
        'resource_type' => 'user',
        'resource_id' => 12345,
        'searched_in' => 'database',
    ],
    retryable: false
);

echo $notFoundError->toJson() . "\n\n";

// ============================================================================
// 7. SERVICE UNAVAILABLE (retryable)
// ============================================================================

echo "7. Service Unavailable (Retryable)\n";
echo "─────────────────────────────────────────────────────────────────\n";

$serviceUnavailableError = new ToolError(
    code: ToolErrorCode::SERVICE_UNAVAILABLE,
    message: 'External service is temporarily unavailable',
    details: [
        'service' => 'payment_gateway',
        'status_code' => 503,
        'retry_after' => 120,
    ],
    retryable: true
);

echo $serviceUnavailableError->toJson() . "\n\n";

// ============================================================================
// 8. FROM EXCEPTION
// ============================================================================

echo "8. Error from Exception\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    throw new \InvalidArgumentException('Invalid user ID provided');
} catch (\Throwable $e) {
    $exceptionError = ToolError::fromException($e);
    echo $exceptionError->toJson() . "\n\n";
}

// ============================================================================
// RETRYABILITY CHECK
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                      RETRYABILITY SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$errors = [
    'Validation Failed' => ToolErrorCode::VALIDATION_FAILED,
    'Rate Limit Exceeded' => ToolErrorCode::RATE_LIMIT_EXCEEDED,
    'Permission Denied' => ToolErrorCode::PERMISSION_DENIED,
    'Network Error' => ToolErrorCode::NETWORK_ERROR,
    'Execution Timeout' => ToolErrorCode::EXECUTION_TIMEOUT,
    'Resource Not Found' => ToolErrorCode::RESOURCE_NOT_FOUND,
    'Service Unavailable' => ToolErrorCode::SERVICE_UNAVAILABLE,
    'Internal Error' => ToolErrorCode::INTERNAL_ERROR,
];

foreach ($errors as $name => $code) {
    $retryable = ToolErrorCode::isRetryable($code);
    $status = $retryable ? '✓ Retryable' : '✗ Not Retryable';
    printf("%-30s %s\n", $name . ':', $status);
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
