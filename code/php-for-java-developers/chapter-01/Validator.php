<?php

declare(strict_types=1);

class Validator
{
    /**
     * Validate that a value is a non-empty string
     */
    public function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && $value !== "";
    }

    /**
     * Validate that a value is a positive integer
     */
    public function isPositiveInt(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * Validate that a value is within a range
     */
    public function isInRange(int|float $value, int|float $min, int|float $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * Validate email format
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate array has required keys
     *
     * @param array<string, mixed> $data
     * @param array<int, string> $requiredKeys
     */
    public function hasRequiredKeys(array $data, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate and sanitize user data
     *
     * @param array<string, mixed> $data
     * @return array{valid: bool, errors: array<string, string>, data: array<string, mixed>}
     */
    public function validateUser(array $data): array
    {
        $errors = [];

        // Check required fields
        if (!$this->hasRequiredKeys($data, ['name', 'email', 'age'])) {
            $errors['missing'] = 'Missing required fields';
            return ['valid' => false, 'errors' => $errors, 'data' => []];
        }

        // Validate name
        if (!$this->isNonEmptyString($data['name'])) {
            $errors['name'] = 'Name must be a non-empty string';
        }

        // Validate email
        if (!$this->isValidEmail($data['email'])) {
            $errors['email'] = 'Invalid email format';
        }

        // Validate age
        if (!$this->isPositiveInt($data['age'])) {
            $errors['age'] = 'Age must be a positive integer';
        } elseif (!$this->isInRange($data['age'], 1, 120)) {
            $errors['age'] = 'Age must be between 1 and 120';
        }

        $valid = empty($errors);

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $valid ? $data : []
        ];
    }
}

// Usage examples
$validator = new Validator();

echo "=== Valid User Data ===\n";
$userData = [
    'name' => 'Alice',
    'email' => 'alice@example.com',
    'age' => 30
];

$result = $validator->validateUser($userData);

if ($result['valid']) {
    echo "User data is valid!\n";
    print_r($result['data']);
} else {
    echo "Validation errors:\n";
    print_r($result['errors']);
}

echo "\n=== Invalid User Data ===\n";
$invalidData = [
    'name' => '',
    'email' => 'invalid-email',
    'age' => -5
];

$invalidResult = $validator->validateUser($invalidData);
if (!$invalidResult['valid']) {
    echo "Validation errors:\n";
    print_r($invalidResult['errors']);
}
