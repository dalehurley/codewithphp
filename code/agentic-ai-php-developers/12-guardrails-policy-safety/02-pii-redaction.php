<?php

declare(strict_types=1);

/**
 * PII Redaction and Masking
 * 
 * Demonstrates how to automatically detect and redact personally identifiable
 * information (PII) from agent inputs and outputs to protect user privacy.
 */

// Simple string helper (mirrors ClaudeAgents\Support\StringHelper)
if (!class_exists('SimpleStringHelper2')) {
class SimpleStringHelper2
{
    public static function mask(string $string, int $visibleStart = 4, int $visibleEnd = 4, string $mask = '*'): string
    {
        $length = strlen($string);

        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat($mask, $length);
        }

        $start = substr($string, 0, $visibleStart);
        $end = substr($string, -$visibleEnd);
        $maskedLength = $length - $visibleStart - $visibleEnd;

        return $start . str_repeat($mask, $maskedLength) . $end;
    }
}
}

// Use SimpleStringHelper if available, otherwise use SimpleStringHelper2
if (!function_exists('maskString')) {
    function maskString(string $string, int $visibleStart = 4, int $visibleEnd = 4, string $mask = '*'): string
    {
        if (class_exists('SimpleStringHelper') && method_exists('SimpleStringHelper', 'mask')) {
            return SimpleStringHelper::mask($string, $visibleStart, $visibleEnd, $mask);
        }
        return SimpleStringHelper2::mask($string, $visibleStart, $visibleEnd, $mask);
    }
}

class PIIRedactor
{
    /** @var array<string, array{pattern: string, replacement: callable|string}> */
    private array $redactionRules = [];

    public function __construct()
    {
        $this->initializeRules();
    }

    /**
     * Initialize redaction rules
     */
    private function initializeRules(): void
    {
        // Email addresses
        $this->redactionRules['email'] = [
            'pattern' => '/\b[\w\.-]+@[\w\.-]+\.\w{2,}\b/',
            'replacement' => '[EMAIL_REDACTED]',
        ];

        // Social Security Numbers (US)
        $this->redactionRules['ssn'] = [
            'pattern' => '/\b\d{3}-\d{2}-\d{4}\b/',
            'replacement' => '[SSN_REDACTED]',
        ];

        // Phone numbers (various formats)
        $this->redactionRules['phone'] = [
            'pattern' => '/\b(?:\+?1[-.]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}\b/',
            'replacement' => '[PHONE_REDACTED]',
        ];

        // Credit card numbers
        $this->redactionRules['credit_card'] = [
            'pattern' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/',
            'replacement' => function ($matches) {
                // Keep last 4 digits
                $full = preg_replace('/[\s-]/', '', $matches[0]);
                return '****-****-****-' . substr($full, -4);
            },
        ];

        // IP addresses
        $this->redactionRules['ip'] = [
            'pattern' => '/\b(?:\d{1,3}\.){3}\d{1,3}\b/',
            'replacement' => '[IP_REDACTED]',
        ];

        // API keys (common formats)
        $this->redactionRules['api_key'] = [
            'pattern' => '/\b(?:sk|pk)_(?:test|live)_[a-zA-Z0-9]{20,}\b/',
            'replacement' => function ($matches) {
                return maskString($matches[0], 8, 4);
            },
        ];

        // URLs with auth tokens
        $this->redactionRules['url_token'] = [
            'pattern' => '/(https?:\/\/[^\s]+[?&](?:token|key|auth|api_key)=)([^\s&]+)/',
            'replacement' => '$1[TOKEN_REDACTED]',
        ];
    }

    /**
     * Redact PII from text
     *
     * @param string $text Text to redact
     * @param array<string> $rulesToApply Specific rules to apply (empty = all)
     * @return array{redacted: string, found: array<string>, count: int}
     */
    public function redact(string $text, array $rulesToApply = []): array
    {
        $redacted = $text;
        $foundTypes = [];
        $totalCount = 0;

        $rules = empty($rulesToApply)
            ? $this->redactionRules
            : array_intersect_key($this->redactionRules, array_flip($rulesToApply));

        foreach ($rules as $type => $rule) {
            $count = 0;
            
            if (is_callable($rule['replacement'])) {
                $redacted = preg_replace_callback(
                    $rule['pattern'],
                    function ($matches) use ($rule, &$count, &$foundTypes, $type) {
                        $count++;
                        if (!in_array($type, $foundTypes)) {
                            $foundTypes[] = $type;
                        }
                        return $rule['replacement']($matches);
                    },
                    $redacted
                ) ?? $redacted;
            } else {
                $before = $redacted;
                $redacted = preg_replace($rule['pattern'], $rule['replacement'], $redacted) ?? $redacted;
                
                // Count replacements
                $count = $before !== $redacted ? 1 : 0;
                if ($count > 0) {
                    preg_match_all($rule['pattern'], $before, $matches);
                    $count = count($matches[0]);
                    $foundTypes[] = $type;
                }
            }

            $totalCount += $count;
        }

        return [
            'redacted' => $redacted,
            'found' => array_unique($foundTypes),
            'count' => $totalCount,
        ];
    }

    /**
     * Mask sensitive data while keeping it partially readable
     *
     * @param string $text Text to mask
     * @param int $visibleStart Visible characters at start
     * @param int $visibleEnd Visible characters at end
     * @return string Masked text
     */
    public function mask(string $text, int $visibleStart = 4, int $visibleEnd = 4): string
    {
        return maskString($text, $visibleStart, $visibleEnd);
    }

    /**
     * Add custom redaction rule
     *
     * @param string $name Rule name
     * @param string $pattern Regex pattern
     * @param callable|string $replacement Replacement
     */
    public function addRule(string $name, string $pattern, callable|string $replacement): void
    {
        $this->redactionRules[$name] = [
            'pattern' => $pattern,
            'replacement' => $replacement,
        ];
    }
}

// Example usage
echo "=== PII Redaction Demo ===\n\n";

$redactor = new PIIRedactor();

// Test 1: Email redaction
echo "Test 1: Email Redaction\n";
$text1 = "Please contact me at john.doe@example.com or jane.smith@company.org";
$result1 = $redactor->redact($text1);
echo "Original: {$text1}\n";
echo "Redacted: {$result1['redacted']}\n";
echo "Found: " . implode(', ', $result1['found']) . " ({$result1['count']} instances)\n\n";

// Test 2: Multiple PII types
echo "Test 2: Multiple PII Types\n";
$text2 = "My phone is 555-123-4567, email is user@test.com, and SSN is 123-45-6789";
$result2 = $redactor->redact($text2);
echo "Original: {$text2}\n";
echo "Redacted: {$result2['redacted']}\n";
echo "Found: " . implode(', ', $result2['found']) . " ({$result2['count']} instances)\n\n";

// Test 3: Credit card masking
echo "Test 3: Credit Card Masking\n";
$text3 = "My card number is 4532-1234-5678-9010";
$result3 = $redactor->redact($text3);
echo "Original: {$text3}\n";
echo "Redacted: {$result3['redacted']}\n\n";

// Test 4: API key masking
echo "Test 4: API Key Masking\n";
$text4 = "Use API key: sk_test_1234567890abcdefghij1234567890";
$result4 = $redactor->redact($text4);
echo "Original: {$text4}\n";
echo "Redacted: {$result4['redacted']}\n\n";

// Test 5: URL with token
echo "Test 5: URL Token Redaction\n";
$text5 = "Access the API: https://api.example.com/data?token=secret123456&user=john";
$result5 = $redactor->redact($text5);
echo "Original: {$text5}\n";
echo "Redacted: {$result5['redacted']}\n\n";

// Test 6: Custom rule
echo "Test 6: Custom Rule (Employee ID)\n";
$redactor->addRule(
    'employee_id',
    '/\bEMP\d{6}\b/',
    '[EMP_ID_REDACTED]'
);
$text6 = "Employee EMP123456 reported the issue";
$result6 = $redactor->redact($text6, ['employee_id']);
echo "Original: {$text6}\n";
echo "Redacted: {$result6['redacted']}\n\n";

// Test 7: Selective redaction
echo "Test 7: Selective Redaction (Email only)\n";
$text7 = "Contact: john@test.com or call 555-1234";
$result7 = $redactor->redact($text7, ['email']);
echo "Original: {$text7}\n";
echo "Redacted: {$result7['redacted']}\n";
echo "Note: Phone number preserved (not in ruleset)\n\n";

// Test 8: Clean text
echo "Test 8: Clean Text (No PII)\n";
$text8 = "The weather is nice today and the project deadline is Friday.";
$result8 = $redactor->redact($text8);
echo "Original: {$text8}\n";
echo "Redacted: {$result8['redacted']}\n";
echo "Found: " . (empty($result8['found']) ? 'None' : implode(', ', $result8['found'])) . "\n";

echo "\n✅ PII redaction complete!\n";
