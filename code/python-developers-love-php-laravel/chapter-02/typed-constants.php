<?php

declare(strict_types=1);

// PHP 8.4 typed class constants
// Provides type safety for class constants

class Status
{
    public const string PENDING = 'pending';
    public const string APPROVED = 'approved';
    public const string REJECTED = 'rejected';
    public const int MAX_RETRIES = 3;
    public const float TAX_RATE = 0.1;
    public const bool ENABLED = true;
}

// Using typed constants
echo "Status: " . Status::PENDING . "\n";  // Output: Status: pending
echo "Max Retries: " . Status::MAX_RETRIES . "\n";  // Output: Max Retries: 3
echo "Tax Rate: " . Status::TAX_RATE . "\n";  // Output: Tax Rate: 0.1

function processStatus(string $status): string
{
    return match ($status) {
        Status::PENDING => 'Processing...',
        Status::APPROVED => 'Completed!',
        Status::REJECTED => 'Failed.',
        default => 'Unknown status.',
    };
}

echo processStatus(Status::PENDING) . "\n";  // Output: Processing...
echo processStatus(Status::APPROVED) . "\n";  // Output: Completed!

// Typed constants in interfaces
interface HttpStatus
{
    public const int OK = 200;
    public const int NOT_FOUND = 404;
    public const int SERVER_ERROR = 500;
}

class Response
{
    public function __construct(
        public int $statusCode
    ) {}

    public function isSuccess(): bool
    {
        return $this->statusCode === HttpStatus::OK;
    }
}

$response = new Response(HttpStatus::OK);
echo "Is Success: " . ($response->isSuccess() ? 'Yes' : 'No') . "\n";  // Output: Is Success: Yes

