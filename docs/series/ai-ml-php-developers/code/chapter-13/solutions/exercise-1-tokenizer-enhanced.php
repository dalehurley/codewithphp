<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13\Solutions;

/**
 * Exercise 1 Solution: Enhanced Tokenizer with URL and Email Preservation
 *
 * Extends the base tokenizer to preserve special tokens like URLs and email addresses.
 */
class EnhancedTokenizer
{
    /**
     * Tokenize text while preserving URLs and email addresses
     *
     * @param string $text Input text
     * @param bool $lowercase Convert to lowercase (preserves URLs/emails)
     * @return array Array of tokens
     */
    public function tokenizePreserving(string $text, bool $lowercase = true): array
    {
        $tokens = [];
        $position = 0;
        $length = mb_strlen($text);

        // Patterns for special tokens
        $patterns = [
            'url' => '/\b(?:https?|ftp):\/\/[^\s<>"{}|\\^`\[\]]+/i',
            'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        ];

        // Find all special tokens with their positions
        $specialTokens = [];
        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $specialTokens[] = [
                        'token' => $match[0],
                        'start' => $match[1],
                        'end' => $match[1] + strlen($match[0]),
                        'type' => $type
                    ];
                }
            }
        }

        // Sort by position
        usort($specialTokens, fn($a, $b) => $a['start'] <=> $b['start']);

        // Process text, extracting normal words and preserving special tokens
        $lastEnd = 0;

        foreach ($specialTokens as $special) {
            // Get text before special token and tokenize normally
            if ($special['start'] > $lastEnd) {
                $beforeText = mb_substr($text, $lastEnd, $special['start'] - $lastEnd);
                $normalTokens = $this->tokenizeNormal($beforeText, $lowercase);
                $tokens = array_merge($tokens, $normalTokens);
            }

            // Add special token (preserve original case for URLs/emails)
            $tokens[] = $special['token'];

            $lastEnd = $special['end'];
        }

        // Process remaining text after last special token
        if ($lastEnd < $length) {
            $remainingText = mb_substr($text, $lastEnd);
            $normalTokens = $this->tokenizeNormal($remainingText, $lowercase);
            $tokens = array_merge($tokens, $normalTokens);
        }

        return $tokens;
    }

    /**
     * Normal word tokenization (helper method)
     */
    private function tokenizeNormal(string $text, bool $lowercase): array
    {
        if ($lowercase) {
            $text = mb_strtolower($text);
        }

        // Remove punctuation except apostrophes
        $text = preg_replace("/[^\p{L}\p{N}\s']/u", ' ', $text);

        // Split on whitespace
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Identify token type
     */
    public function identifyTokenType(string $token): string
    {
        if (preg_match('/^(?:https?|ftp):\/\//i', $token)) {
            return 'url';
        }
        if (preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$/', $token)) {
            return 'email';
        }
        if (preg_match('/^\d+$/', $token)) {
            return 'number';
        }
        if (preg_match('/^[\p{L}]+$/u', $token)) {
            return 'word';
        }
        return 'other';
    }

    /**
     * Tokenize with type annotations
     */
    public function tokenizeWithTypes(string $text): array
    {
        $tokens = $this->tokenizePreserving($text);
        $result = [];

        foreach ($tokens as $token) {
            $result[] = [
                'token' => $token,
                'type' => $this->identifyTokenType($token)
            ];
        }

        return $result;
    }
}

// =============================================================================
// DEMONSTRATION AND VALIDATION
// =============================================================================

echo "=================================================================\n";
echo "Exercise 1 Solution: Enhanced Tokenizer\n";
echo "=================================================================\n\n";

$tokenizer = new EnhancedTokenizer();

// Test case 1: Email and URL preservation
$text1 = "Contact us at info@example.com or visit https://example.com for more details.";

echo "Test 1: Email and URL Preservation\n";
echo "-----------------------------------\n";
echo "Input: \"$text1\"\n\n";

$tokens = $tokenizer->tokenizePreserving($text1);
echo "Tokens:\n";
foreach ($tokens as $token) {
    echo "  - \"$token\"\n";
}

// Validation
$hasEmail = in_array('info@example.com', $tokens);
$hasUrl = in_array('https://example.com', $tokens);
echo "\n✓ Validation:\n";
echo "  Email preserved: " . ($hasEmail ? "✓ YES" : "✗ NO") . "\n";
echo "  URL preserved: " . ($hasUrl ? "✓ YES" : "✗ NO") . "\n\n";

// Test case 2: Multiple emails and URLs
$text2 = "Email john@company.org and jane@startup.io or check http://blog.example.com and https://docs.example.org";

echo "\nTest 2: Multiple Special Tokens\n";
echo "-----------------------------------\n";
echo "Input: \"$text2\"\n\n";

$tokens2 = $tokenizer->tokenizePreserving($text2);
echo "Tokens:\n";
foreach ($tokens2 as $token) {
    echo "  - \"$token\"\n";
}
echo "\n";

// Test case 3: Token type identification
$text3 = "Visit https://github.com or email support@company.com about issue 12345.";

echo "\nTest 3: Token Type Identification\n";
echo "-----------------------------------\n";
echo "Input: \"$text3\"\n\n";

$typedTokens = $tokenizer->tokenizeWithTypes($text3);
echo "Tokens with Types:\n";
foreach ($typedTokens as $item) {
    echo sprintf("  %-30s [%s]\n", $item['token'], $item['type']);
}

// Test case 4: Mixed content
$text4 = "The API documentation is at https://api.example.com/docs. Questions? Contact dev@example.com!";

echo "\nTest 4: Mixed Content\n";
echo "-----------------------------------\n";
echo "Input: \"$text4\"\n\n";

$tokens4 = $tokenizer->tokenizePreserving($text4);
echo "Tokens:\n";
foreach ($tokens4 as $token) {
    echo "  - \"$token\"\n";
}

echo "\n=================================================================\n";
echo "Exercise 1 Complete!\n";
echo "=================================================================\n";
echo "\nKey Features Implemented:\n";
echo "  ✓ URL preservation (http://, https://, ftp://)\n";
echo "  ✓ Email address preservation (user@domain.com)\n";
echo "  ✓ Normal word tokenization for other text\n";
echo "  ✓ Token type identification\n";
echo "  ✓ Lowercase conversion (preserves special tokens)\n\n";

echo "Usage Example:\n";
echo "  \$tokenizer = new EnhancedTokenizer();\n";
echo "  \$tokens = \$tokenizer->tokenizePreserving(\$text);\n";
echo "  \$typed = \$tokenizer->tokenizeWithTypes(\$text);\n\n";
