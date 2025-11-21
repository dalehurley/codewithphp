<?php
# filename: src/MultiAgent/DataStructures.php
declare(strict_types=1);

namespace App\MultiAgent;

readonly class Task
{
    public function __construct(
        public string $id,
        public string $type,
        public string $description,
        public string $assignedTo,
        public string $createdBy,
        public string $priority = 'medium',
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'assigned_to' => $this->assignedTo,
            'created_by' => $this->createdBy,
            'priority' => $this->priority,
            'metadata' => $this->metadata
        ];
    }
}

readonly class TaskResult
{
    public function __construct(
        public string $taskId,
        public string $status,
        public mixed $output,
        public array $metadata = []
    ) {}
}

readonly class Message
{
    public function __construct(
        public string $from,
        public string $to,
        public string $type,
        public mixed $content,
        public float $timestamp
    ) {}

    public static function create(
        string $from,
        string $to,
        string $type,
        mixed $content,
        ?float $timestamp = null
    ): self {
        return new self(
            from: $from,
            to: $to,
            type: $type,
            content: $content,
            timestamp: $timestamp ?? microtime(true)
        );
    }
}
