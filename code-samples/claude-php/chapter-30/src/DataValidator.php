<?php

declare(strict_types=1);

namespace App;

use Anthropic\Anthropic;
use Anthropic\Resources\Messages;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Data Validator
 *
 * Validates extracted data quality, completeness, and accuracy.
 */
class DataValidator
{
    private Messages $messages;
    private string $model;

    public function __construct(
        private Anthropic $client,
        private LoggerInterface $logger = new NullLogger(),
        private float $minConfidence = 0.85
    ) {
        $this->messages = $this->client->messages();
        $this->model = 'claude-3-5-sonnet-20241022';
    }

    /**
     * Validate extracted data against schema
     */
    public function validate(array $data, array $schema): ValidationResult
    {
        $errors = [];
        $warnings = [];
        $confidence = 1.0;

        // Check required fields
        foreach ($schema as $field => $config) {
            if (($config['required'] ?? false) && !isset($data[$field])) {
                $errors[] = "Missing required field: {$field}";
                $confidence -= 0.1;
            }
        }

        // Validate field types
        foreach ($data as $field => $value) {
            if (!isset($schema[$field])) {
                $warnings[] = "Unexpected field: {$field}";
                continue;
            }

            $expectedType = $schema[$field]['type'] ?? 'string';
            $validationType = $this->validateType($value, $expectedType);

            if (!$validationType['valid']) {
                $errors[] = "Invalid type for {$field}: expected {$expectedType}, got {$validationType['actual']}";
                $confidence -= 0.15;
            }

            // Custom validation rules
            if (isset($schema[$field]['validate'])) {
                $customValidation = $schema[$field]['validate']($value);
                if (!$customValidation['valid']) {
                    $errors[] = "Validation failed for {$field}: {$customValidation['message']}";
                    $confidence -= 0.1;
                }
            }
        }

        // AI-powered validation for complex data
        if ($this->shouldUseAIValidation($schema)) {
            $aiValidation = $this->validateWithAI($data, $schema);
            $errors = array_merge($errors, $aiValidation['errors']);
            $warnings = array_merge($warnings, $aiValidation['warnings']);
            $confidence = min($confidence, $aiValidation['confidence']);
        }

        $confidence = max(0, min(1, $confidence));

        return new ValidationResult(
            valid: empty($errors) && $confidence >= $this->minConfidence,
            errors: $errors,
            warnings: $warnings,
            confidence: $confidence
        );
    }

    /**
     * Validate value type
     */
    private function validateType(mixed $value, string $expectedType): array
    {
        $actualType = gettype($value);

        $valid = match($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL),
            'date' => $this->validateDate($value),
            'phone' => $this->validatePhone($value),
            'numeric' => is_numeric($value),
            default => true
        };

        return [
            'valid' => $valid,
            'actual' => $actualType
        ];
    }

    /**
     * Validate date format
     */
    private function validateDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    /**
     * Validate phone number
     */
    private function validatePhone(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Simple phone validation
        return preg_match('/^\+?[1-9]\d{1,14}$/', preg_replace('/[^0-9+]/', '', $value)) === 1;
    }

    /**
     * Determine if AI validation is needed
     */
    private function shouldUseAIValidation(array $schema): bool
    {
        foreach ($schema as $config) {
            if ($config['ai_validate'] ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate data using Claude AI
     */
    private function validateWithAI(array $data, array $schema): array
    {
        $this->logger->debug('Performing AI validation');

        $schemaDesc = $this->buildSchemaDescription($schema);
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Validate the following extracted data against the schema. Check for:
1. Logical consistency
2. Data completeness
3. Realistic values
4. Potential errors or anomalies

Schema:
{$schemaDesc}

Data to validate:
{$dataJson}

Return a JSON object with:
{
  "valid": true/false,
  "errors": ["list of errors"],
  "warnings": ["list of warnings"],
  "confidence": 0.0-1.0 (confidence in data quality)
}
PROMPT;

        try {
            $response = $this->messages->create([
                'model' => $this->model,
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => 'You are a data validation expert. Analyze data carefully and provide detailed feedback.'
            ]);

            $text = $response->content[0]->text;

            if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
                $text = $matches[1];
            }

            $result = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'errors' => $result['errors'] ?? [],
                    'warnings' => $result['warnings'] ?? [],
                    'confidence' => $result['confidence'] ?? 0.5
                ];
            }
        } catch (\Throwable $e) {
            $this->logger->error('AI validation failed', [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'errors' => [],
            'warnings' => ['AI validation unavailable'],
            'confidence' => 0.7
        ];
    }

    /**
     * Build schema description for AI
     */
    private function buildSchemaDescription(array $schema): string
    {
        $lines = [];
        foreach ($schema as $field => $config) {
            $type = $config['type'] ?? 'string';
            $required = ($config['required'] ?? false) ? 'required' : 'optional';
            $description = $config['description'] ?? '';

            $lines[] = "- {$field} ({$type}, {$required}): {$description}";
        }

        return implode("\n", $lines);
    }

    /**
     * Validate data completeness
     */
    public function validateCompleteness(array $data, array $schema): float
    {
        $totalFields = count($schema);
        $presentFields = 0;
        $requiredFields = 0;
        $requiredPresent = 0;

        foreach ($schema as $field => $config) {
            $isRequired = $config['required'] ?? false;

            if ($isRequired) {
                $requiredFields++;
            }

            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $presentFields++;
                if ($isRequired) {
                    $requiredPresent++;
                }
            }
        }

        // Calculate completeness score
        $requiredScore = $requiredFields > 0 ? ($requiredPresent / $requiredFields) : 1.0;
        $overallScore = $totalFields > 0 ? ($presentFields / $totalFields) : 0.0;

        // Weight required fields more heavily (70% weight)
        return ($requiredScore * 0.7) + ($overallScore * 0.3);
    }

    /**
     * Batch validate multiple records
     */
    public function validateBatch(array $records, array $schema): array
    {
        $results = [];

        foreach ($records as $index => $record) {
            try {
                $results[$index] = $this->validate($record, $schema);
            } catch (\Throwable $e) {
                $this->logger->error("Validation failed for record {$index}", [
                    'error' => $e->getMessage()
                ]);

                $results[$index] = new ValidationResult(
                    valid: false,
                    errors: [$e->getMessage()],
                    warnings: [],
                    confidence: 0.0
                );
            }
        }

        return $results;
    }
}

/**
 * Validation result container
 */
class ValidationResult
{
    public function __construct(
        private bool $valid,
        private array $errors = [],
        private array $warnings = [],
        private float $confidence = 1.0
    ) {}

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'confidence' => $this->confidence
        ];
    }
}
