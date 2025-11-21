<?php
/**
 * Chapter 02: Modern PHP - Enums (PHP 8.1+)
 * 
 * Enums provide type-safe alternatives to string/int constants.
 * They can have methods and provide exhaustive matching.
 */

declare(strict_types=1);

// ============ BASIC ENUM ============

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

// Using the enum
$postStatus = Status::Published;
echo "Status value: " . $postStatus->value . "\n";  // "published"
echo "Status name: " . $postStatus->name . "\n";    // "Published"

// ============ ENUM WITH METHODS ============

enum Priority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    // Method on enum
    public function color(): string
    {
        return match ($this) {
            self::Low => '#10B981',      // Green
            self::Medium => '#F59E0B',   // Yellow
            self::High => '#EF4444',     // Red
            self::Critical => '#7C3AED', // Purple
        };
    }

    // Another method
    public function shouldNotify(): bool
    {
        return $this === self::Critical || $this === self::High;
    }

    // Static method
    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 90 => self::Critical,
            $score >= 70 => self::High,
            $score >= 40 => self::Medium,
            default => self::Low,
        };
    }
}

echo "\n=== Enum with Methods ===\n";
$priority = Priority::High;
echo "Priority: {$priority->value}\n";
echo "Color: {$priority->color()}\n";
echo "Should notify: " . ($priority->shouldNotify() ? 'Yes' : 'No') . "\n";

$criticalPriority = Priority::fromScore(95);
echo "\nScore 95 = {$criticalPriority->value}\n";
echo "Notification needed: " . ($criticalPriority->shouldNotify() ? 'Yes' : 'No') . "\n";

// ============ INTEGER ENUM ============

enum HttpStatus: int
{
    case OK = 200;
    case Created = 201;
    case BadRequest = 400;
    case Unauthorized = 401;
    case NotFound = 404;
    case ServerError = 500;

    public function isSuccess(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }

    public function isError(): bool
    {
        return $this->value >= 400;
    }

    public function message(): string
    {
        return match ($this) {
            self::OK => 'OK',
            self::Created => 'Created',
            self::BadRequest => 'Bad Request',
            self::Unauthorized => 'Unauthorized',
            self::NotFound => 'Not Found',
            self::ServerError => 'Internal Server Error',
        };
    }
}

echo "\n=== Integer Enum ===\n";
$status = HttpStatus::NotFound;
echo "Status: {$status->value}\n";
echo "Message: {$status->message()}\n";
echo "Is success: " . ($status->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Is error: " . ($status->isError() ? 'Yes' : 'No') . "\n";

// ============ TYPE-SAFE FUNCTION ============

function sendNotification(Priority $priority): string
{
    return match ($priority) {
        Priority::Low => 'Email notification sent',
        Priority::Medium => 'Email + SMS notification sent',
        Priority::High => 'Email + SMS + Push notification sent',
        Priority::Critical => 'Email + SMS + Push + Alert notification sent',
    };
}

echo "\n=== Type-Safe Function ===\n";
echo sendNotification(Priority::Low) . "\n";
echo sendNotification(Priority::Critical) . "\n";

// This would cause a compile error:
// sendNotification("high");  // TypeError: high is not a Priority

// ============ LISTING ALL CASES ============

echo "\n=== All Cases ===\n";
echo "All statuses:\n";
foreach (Status::cases() as $status) {
    echo "  - {$status->name}: {$status->value}\n";
}

echo "\nAll priorities:\n";
foreach (Priority::cases() as $priority) {
    echo "  - {$priority->name}: {$priority->value} (color: {$priority->color()})\n";
}

// ============ BACKED ENUM VALUES ============

echo "\n=== Backed Enum Values ===\n";
$draft = Status::Draft;
echo "Draft enum value: " . $draft->value . "\n";

// Get enum by value
$status = Status::tryFrom('published');
if ($status !== null) {
    echo "Found status: {$status->name}\n";
}

echo "\n✓ All enum examples completed successfully!\n";







