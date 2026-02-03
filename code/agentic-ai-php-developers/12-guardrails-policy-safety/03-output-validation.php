<?php

declare(strict_types=1);

/**
 * Output Validation and Safety Checks
 * 
 * Demonstrates how to validate agent outputs for safety, accuracy,
 * and compliance before returning them to users.
 */

// Simple JSON helper (mirrors ClaudeAgents\Support\JsonHelper)
if (!class_exists('SimpleJsonHelper')) {
class SimpleJsonHelper
{
    public static function isValid(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
}

class OutputValidator
{
    /** @var array<string> Banned words/phrases */
    private array $bannedContent = [
        'suicide',
        'self-harm',
        'violence',
        'illegal',
        'hack',
        'exploit',
    ];

    /** @var array<string> Phrases requiring citation */
    private array $requiresCitation = [
        'research shows',
        'studies indicate',
        'according to',
        'statistics show',
        'data proves',
    ];

    /**
     * Validate agent output
     *
     * @param string $output Agent output text
     * @param array<string, mixed> $options Validation options
     * @return array{valid: bool, score: float, issues: array<string>, warnings: array<string>}
     */
    public function validate(string $output, array $options = []): array
    {
        $issues = [];
        $warnings = [];
        $score = 1.0;

        // Check length
        if (empty(trim($output))) {
            $issues[] = 'Output is empty';
            $score = 0.0;
        }

        // Check for banned content
        $bannedCheck = $this->checkBannedContent($output);
        if (!$bannedCheck['safe']) {
            $issues[] = 'Contains banned content: ' . implode(', ', $bannedCheck['found']);
            $score -= 0.5;
        }

        // Check for uncited claims
        $citationCheck = $this->checkCitations($output);
        if (!empty($citationCheck['uncited'])) {
            $warnings[] = 'Contains uncited claims: ' . implode(', ', $citationCheck['uncited']);
            $score -= 0.1;
        }

        // Check for PII in output
        if ($options['check_pii'] ?? true) {
            $piiCheck = $this->checkPII($output);
            if ($piiCheck['found']) {
                $warnings[] = 'Output contains PII: ' . implode(', ', $piiCheck['types']);
                $score -= 0.2;
            }
        }

        // Check for structured data validity
        if ($options['expect_json'] ?? false) {
            if (!SimpleJsonHelper::isValid($output)) {
                $issues[] = 'Expected JSON output but received invalid format';
                $score -= 0.3;
            }
        }

        // Check for code injection attempts in output
        $injectionCheck = $this->checkInjection($output);
        if (!$injectionCheck['safe']) {
            $issues[] = 'Output contains potential injection: ' . implode(', ', $injectionCheck['found']);
            $score -= 0.4;
        }

        // Ensure score doesn't go negative
        $score = max(0.0, min(1.0, $score));

        return [
            'valid' => empty($issues) && $score > 0.5,
            'score' => round($score, 2),
            'issues' => $issues,
            'warnings' => $warnings,
        ];
    }

    /**
     * Check for banned content
     *
     * @param string $text Text to check
     * @return array{safe: bool, found: array<string>}
     */
    private function checkBannedContent(string $text): array
    {
        $found = [];
        $lowerText = strtolower($text);

        foreach ($this->bannedContent as $banned) {
            if (str_contains($lowerText, $banned)) {
                $found[] = $banned;
            }
        }

        return [
            'safe' => empty($found),
            'found' => $found,
        ];
    }

    /**
     * Check for claims requiring citations
     *
     * @param string $text Text to check
     * @return array{cited: bool, uncited: array<string>}
     */
    private function checkCitations(string $text): array
    {
        $uncited = [];
        $lowerText = strtolower($text);

        foreach ($this->requiresCitation as $phrase) {
            if (str_contains($lowerText, $phrase)) {
                // Check if there's a URL or reference nearby
                $pattern = '/' . preg_quote($phrase, '/') . '.{0,100}(?:http|\\[\\d+\\]|\\(source|reference)/i';
                if (!preg_match($pattern, $text)) {
                    $uncited[] = $phrase;
                }
            }
        }

        return [
            'cited' => empty($uncited),
            'uncited' => $uncited,
        ];
    }

    /**
     * Check for PII in output
     *
     * @param string $text Text to check
     * @return array{found: bool, types: array<string>}
     */
    private function checkPII(string $text): array
    {
        $patterns = [
            'email' => '/\b[\w\.-]+@[\w\.-]+\.\w{2,}\b/',
            'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
            'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
            'credit_card' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/',
        ];

        $found = [];
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $text)) {
                $found[] = $type;
            }
        }

        return [
            'found' => !empty($found),
            'types' => $found,
        ];
    }

    /**
     * Check for potential injection attacks
     *
     * @param string $text Text to check
     * @return array{safe: bool, found: array<string>}
     */
    private function checkInjection(string $text): array
    {
        $patterns = [
            'script_tag' => '/<script[\s>]/i',
            'javascript' => '/javascript:/i',
            'event_handler' => '/on\w+\s*=/i',
            'sql_injection' => '/(\bUNION\b|\bSELECT\b.*\bFROM\b|\bDROP\b|\bINSERT\b)/i',
        ];

        $found = [];
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $text)) {
                $found[] = $type;
            }
        }

        return [
            'safe' => empty($found),
            'found' => $found,
        ];
    }

    /**
     * Sanitize output for safe display
     *
     * @param string $output Raw output
     * @return string Sanitized output
     */
    public function sanitize(string $output): string
    {
        // HTML encode
        $sanitized = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

        // Remove any control characters
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $sanitized) ?? $sanitized;

        return $sanitized;
    }
}

// Example usage
echo "=== Output Validation Demo ===\n\n";

$validator = new OutputValidator();

// Test 1: Clean output
echo "Test 1: Clean Output\n";
$output1 = "The capital of France is Paris. It's a beautiful city with rich history.";
$result1 = $validator->validate($output1);
echo "Output: {$output1}\n";
echo "Valid: " . ($result1['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result1['score']}\n";
echo "Issues: " . (empty($result1['issues']) ? 'None' : implode(', ', $result1['issues'])) . "\n";
echo "Warnings: " . (empty($result1['warnings']) ? 'None' : implode(', ', $result1['warnings'])) . "\n\n";

// Test 2: Uncited claim
echo "Test 2: Uncited Claim\n";
$output2 = "Research shows that 80% of users prefer this approach. Studies indicate it's more effective.";
$result2 = $validator->validate($output2);
echo "Output: {$output2}\n";
echo "Valid: " . ($result2['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result2['score']}\n";
echo "Warnings: " . implode(', ', $result2['warnings']) . "\n\n";

// Test 3: Banned content
echo "Test 3: Banned Content\n";
$output3 = "You can hack into the system by exploiting this vulnerability.";
$result3 = $validator->validate($output3);
echo "Output: {$output3}\n";
echo "Valid: " . ($result3['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result3['score']}\n";
echo "Issues: " . implode(', ', $result3['issues']) . "\n\n";

// Test 4: PII in output
echo "Test 4: PII in Output\n";
$output4 = "You can contact the user at john.doe@example.com or 555-123-4567.";
$result4 = $validator->validate($output4, ['check_pii' => true]);
echo "Output: {$output4}\n";
echo "Valid: " . ($result4['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result4['score']}\n";
echo "Warnings: " . implode(', ', $result4['warnings']) . "\n\n";

// Test 5: XSS attempt in output
echo "Test 5: XSS Attempt in Output\n";
$output5 = "Here's a helpful link: <script>alert('xss')</script>";
$result5 = $validator->validate($output5);
echo "Output: {$output5}\n";
echo "Valid: " . ($result5['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result5['score']}\n";
echo "Issues: " . implode(', ', $result5['issues']) . "\n\n";

// Test 6: JSON validation
echo "Test 6: JSON Validation\n";
$output6Valid = '{"status": "success", "data": {"count": 42}}';
$output6Invalid = '{invalid json}';

$result6a = $validator->validate($output6Valid, ['expect_json' => true]);
echo "Valid JSON: {$output6Valid}\n";
echo "Valid: " . ($result6a['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result6a['score']}\n\n";

$result6b = $validator->validate($output6Invalid, ['expect_json' => true]);
echo "Invalid JSON: {$output6Invalid}\n";
echo "Valid: " . ($result6b['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result6b['score']}\n";
echo "Issues: " . implode(', ', $result6b['issues']) . "\n\n";

// Test 7: Empty output
echo "Test 7: Empty Output\n";
$output7 = "   ";
$result7 = $validator->validate($output7);
echo "Output: '{$output7}'\n";
echo "Valid: " . ($result7['valid'] ? 'Yes' : 'No') . "\n";
echo "Score: {$result7['score']}\n";
echo "Issues: " . implode(', ', $result7['issues']) . "\n\n";

// Test 8: Sanitization
echo "Test 8: Output Sanitization\n";
$unsafe = "<script>alert('test')</script>Hello";
$safe = $validator->sanitize($unsafe);
echo "Unsafe: {$unsafe}\n";
echo "Safe: {$safe}\n";

echo "\n✅ Output validation complete!\n";
