<?php

declare(strict_types=1);

namespace App\Validation;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (empty($value) && $value !== '0') {
            $this->addError($field, $message ?? "The {$field} field is required");
        }

        return $this;
    }

    /**
     * Validate email format
     */
    public function email(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid email address");
        }

        return $this;
    }

    /**
     * Validate minimum length
     */
    public function min(string $field, int $length, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && strlen($value) < $length) {
            $this->addError($field, $message ?? "The {$field} must be at least {$length} characters");
        }

        return $this;
    }

    /**
     * Validate maximum length
     */
    public function max(string $field, int $length, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && strlen($value) > $length) {
            $this->addError($field, $message ?? "The {$field} must not exceed {$length} characters");
        }

        return $this;
    }

    /**
     * Validate numeric value
     */
    public function numeric(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $message ?? "The {$field} must be a number");
        }

        return $this;
    }

    /**
     * Validate integer with range
     */
    public function integer(string $field, ?int $min = null, ?int $max = null, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value)) {
            $options = [];
            if ($min !== null) {
                $options['min_range'] = $min;
            }
            if ($max !== null) {
                $options['max_range'] = $max;
            }

            if (!filter_var($value, FILTER_VALIDATE_INT, ['options' => $options])) {
                $range = '';
                if ($min !== null && $max !== null) {
                    $range = " between {$min} and {$max}";
                } elseif ($min !== null) {
                    $range = " at least {$min}";
                } elseif ($max !== null) {
                    $range = " at most {$max}";
                }
                $this->addError($field, $message ?? "The {$field} must be an integer{$range}");
            }
        }

        return $this;
    }

    /**
     * Validate URL format
     */
    public function url(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid URL");
        }

        return $this;
    }

    /**
     * Validate against regex pattern
     */
    public function regex(string $field, string $pattern, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $message ?? "The {$field} format is invalid");
        }

        return $this;
    }

    /**
     * Validate that two fields match
     */
    public function match(string $field, string $otherField, ?string $message = null): self
    {
        $value = $this->getValue($field);
        $otherValue = $this->getValue($otherField);
        
        if ($value !== $otherValue) {
            $this->addError($field, $message ?? "The {$field} must match {$otherField}");
        }

        return $this;
    }

    /**
     * Check if validation passed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get error for specific field
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->data;
    }

    private function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }
}





