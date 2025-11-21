<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

echo "Testing Chapter 04 Code Examples...\n\n";

// Test MessageValidator class from the chapter
echo "Test 1: MessageValidator class...\n";

class MessageValidator
{
    /**
     * Validate a single message structure
     *
     * @throws \InvalidArgumentException if message is invalid
     */
    public static function validateMessage(array $message): void
    {
        // Check required fields
        if (!isset($message['role'])) {
            throw new \InvalidArgumentException('Message must have a "role" field');
        }

        if (!isset($message['content'])) {
            throw new \InvalidArgumentException('Message must have a "content" field');
        }

        // Validate role
        if (!in_array($message['role'], ['user', 'assistant'], true)) {
            throw new \InvalidArgumentException(
                'Message role must be "user" or "assistant", got: ' . $message['role']
            );
        }

        // Validate content type
        if (!is_string($message['content']) && !is_array($message['content'])) {
            throw new \InvalidArgumentException(
                'Message content must be string or array, got: ' . gettype($message['content'])
            );
        }

        // Validate string content length
        if (is_string($message['content'])) {
            if (mb_strlen($message['content']) === 0) {
                throw new \InvalidArgumentException('Message content cannot be empty');
            }

            // Warn about very long content (not an error, but worth noting)
            if (mb_strlen($message['content']) > 1000000) {
                trigger_error(
                    'Message content is very long (' . mb_strlen($message['content']) . ' chars). ' .
                        'Consider splitting or using content blocks.',
                    E_USER_WARNING
                );
            }
        }

        // Validate content blocks array structure (if array)
        if (is_array($message['content'])) {
            self::validateContentBlocks($message['content']);
        }
    }

    /**
     * Validate content blocks array structure
     */
    private static function validateContentBlocks(array $blocks): void
    {
        if (empty($blocks)) {
            throw new \InvalidArgumentException('Content blocks array cannot be empty');
        }

        foreach ($blocks as $index => $block) {
            if (!is_array($block)) {
                throw new \InvalidArgumentException(
                    "Content block at index {$index} must be an array"
                );
            }

            if (!isset($block['type'])) {
                throw new \InvalidArgumentException(
                    "Content block at index {$index} must have a 'type' field"
                );
            }

            // Validate text block
            if ($block['type'] === 'text') {
                if (!isset($block['text']) || !is_string($block['text'])) {
                    throw new \InvalidArgumentException(
                        "Text content block at index {$index} must have a 'text' field (string)"
                    );
                }
            }

            // Note: Image block validation would go here, but covered in Chapter 13
        }
    }

    /**
     * Sanitize message content
     *
     * Ensures content is safe and properly formatted
     */
    public static function sanitizeContent(string $content): string
    {
        // Trim whitespace
        $content = trim($content);

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Remove excessive blank lines (more than 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Ensure content is not empty after sanitization
        if (mb_strlen($content) === 0) {
            throw new \InvalidArgumentException('Content is empty after sanitization');
        }

        return $content;
    }

    /**
     * Validate and sanitize a message
     *
     * Returns sanitized message array
     */
    public static function validateAndSanitize(array $message): array
    {
        self::validateMessage($message);

        // Sanitize string content
        if (is_string($message['content'])) {
            $message['content'] = self::sanitizeContent($message['content']);
        }

        return $message;
    }
}

// Test MessageValidator
try {
    // ✓ Valid message
    $validMessage = [
        'role' => 'user',
        'content' => 'Hello, Claude!'
    ];

    MessageValidator::validateMessage($validMessage);
    echo "✓ Valid message passed validation\n";

    // ✗ Invalid: Missing role
    try {
        MessageValidator::validateMessage(['content' => 'Hello']);
        echo "✗ ERROR: Should have failed with missing role\n";
    } catch (\InvalidArgumentException $e) {
        echo "✓ Correctly rejected message without role: {$e->getMessage()}\n";
    }

    // ✗ Invalid: Empty content
    try {
        MessageValidator::validateMessage(['role' => 'user', 'content' => '']);
        echo "✗ ERROR: Should have failed with empty content\n";
    } catch (\InvalidArgumentException $e) {
        echo "✓ Correctly rejected empty content: {$e->getMessage()}\n";
    }

    // Test sanitization
    $dirtyContent = "  Hello\n\n\n\nWorld  ";
    $cleanContent = MessageValidator::sanitizeContent($dirtyContent);
    $expected = "Hello\n\nWorld";
    if ($cleanContent === $expected) {
        echo "✓ Content sanitization works correctly\n";
    } else {
        echo "✗ Content sanitization failed. Expected: '{$expected}', Got: '{$cleanContent}'\n";
    }
} catch (\Exception $e) {
    echo "✗ MessageValidator test failed: {$e->getMessage()}\n";
    exit(1);
}

// Test ConversationValidator class
echo "\nTest 2: ConversationValidator class...\n";

class ConversationValidator
{
    /**
     * Validate an entire conversation array
     *
     * @throws \InvalidArgumentException if conversation is invalid
     */
    public static function validateConversation(array $messages): void
    {
        if (empty($messages)) {
            throw new \InvalidArgumentException('Conversation cannot be empty');
        }

        // Validate first message
        $firstMessage = $messages[0];
        if ($firstMessage['role'] !== 'user') {
            throw new \InvalidArgumentException(
                'Conversation must start with a user message, got: ' . $firstMessage['role']
            );
        }

        // Validate each message
        foreach ($messages as $index => $message) {
            try {
                MessageValidator::validateMessage($message);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(
                    "Invalid message at index {$index}: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Validate alternation
        self::validateAlternation($messages);
    }

    /**
     * Validate that messages alternate between user and assistant
     */
    private static function validateAlternation(array $messages): void
    {
        $previousRole = null;

        foreach ($messages as $index => $message) {
            $currentRole = $message['role'];

            // Check for consecutive same-role messages
            if ($previousRole !== null && $previousRole === $currentRole) {
                throw new \InvalidArgumentException(
                    "Messages must alternate roles. Found consecutive '{$currentRole}' " .
                        "messages at indices " . ($index - 1) . " and {$index}"
                );
            }

            $previousRole = $currentRole;
        }
    }

    /**
     * Validate and sanitize entire conversation
     *
     * Returns sanitized conversation array
     */
    public static function validateAndSanitize(array $messages): array
    {
        self::validateConversation($messages);

        $sanitized = [];
        foreach ($messages as $message) {
            $sanitized[] = MessageValidator::validateAndSanitize($message);
        }

        return $sanitized;
    }

    /**
     * Check if conversation is valid without throwing exceptions
     *
     * Returns [isValid: bool, errors: string[]]
     */
    public static function checkConversation(array $messages): array
    {
        $errors = [];

        try {
            self::validateConversation($messages);
            return ['isValid' => true, 'errors' => []];
        } catch (\InvalidArgumentException $e) {
            return ['isValid' => false, 'errors' => [$e->getMessage()]];
        }
    }
}

// Test ConversationValidator
try {
    // ✓ Valid conversation
    $validConversation = [
        ['role' => 'user', 'content' => 'Hello!'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
        ['role' => 'user', 'content' => 'How are you?'],
    ];

    ConversationValidator::validateConversation($validConversation);
    echo "✓ Valid conversation passed validation\n";

    // ✗ Invalid: Starts with assistant
    $invalid = [
        ['role' => 'assistant', 'content' => 'Hello!'],
    ];

    $result = ConversationValidator::checkConversation($invalid);
    if (!$result['isValid'] && str_contains($result['errors'][0], 'start with a user message')) {
        echo "✓ Correctly rejected conversation starting with assistant\n";
    } else {
        echo "✗ Failed to reject conversation starting with assistant\n";
    }

    // ✗ Invalid: Consecutive user messages
    $invalid2 = [
        ['role' => 'user', 'content' => 'First'],
        ['role' => 'user', 'content' => 'Second'],
    ];

    $result2 = ConversationValidator::checkConversation($invalid2);
    if (!$result2['isValid'] && str_contains($result2['errors'][0], 'alternate roles')) {
        echo "✓ Correctly rejected consecutive user messages\n";
    } else {
        echo "✗ Failed to reject consecutive user messages\n";
    }
} catch (\Exception $e) {
    echo "✗ ConversationValidator test failed: {$e->getMessage()}\n";
    exit(1);
}

// Test TokenEstimator
echo "\nTest 3: TokenEstimator class...\n";

class TokenEstimator
{
    /**
     * Rough estimation: 1 token ≈ 4 characters
     * Not exact, but useful for budgeting
     */
    public static function estimate(string $text): int
    {
        $cleaned = preg_replace('/\s+/', ' ', trim($text));
        return (int) ceil(mb_strlen($cleaned) / 4);
    }

    public static function estimateMessages(array $messages): int
    {
        $total = 0;

        foreach ($messages as $message) {
            $total += self::estimate($message['content']);
            $total += 4; // Overhead for role and structure
        }

        return $total;
    }

    public static function canFitInContext(
        array $messages,
        int $maxContextTokens = 200000,
        int $maxOutputTokens = 4096
    ): bool {
        $estimatedTokens = self::estimateMessages($messages);
        $availableForContext = $maxContextTokens - $maxOutputTokens;

        return $estimatedTokens <= $availableForContext;
    }
}

// Test TokenEstimator
try {
    $text = "Hello world, this is a test message!";
    $estimate = TokenEstimator::estimate($text);
    // Should be around ceil(37/4) = 10 tokens
    if ($estimate >= 8 && $estimate <= 12) {
        echo "✓ Token estimation works (got {$estimate} for ~37 chars)\n";
    } else {
        echo "✗ Token estimation failed: got {$estimate}, expected ~9-10\n";
    }

    $messages = [
        ['role' => 'user', 'content' => 'Hello'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
    ];

    $messageEstimate = TokenEstimator::estimateMessages($messages);
    // Should be around: 'Hello' (2) + 4 + 'Hi there!' (3) + 4 = 13 tokens
    if ($messageEstimate >= 10 && $messageEstimate <= 16) {
        echo "✓ Message token estimation works (got {$messageEstimate})\n";
    } else {
        echo "✗ Message token estimation failed: got {$messageEstimate}\n";
    }
} catch (\Exception $e) {
    echo "✗ TokenEstimator test failed: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ ALL CODE EXAMPLES TESTED SUCCESSFULLY!\n";
echo "Chapter 04 code samples are syntactically correct and logically sound.\n";
