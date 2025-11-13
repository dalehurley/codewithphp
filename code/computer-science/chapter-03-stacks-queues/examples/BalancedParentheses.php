<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03;

/**
 * Balanced Parentheses Checker
 *
 * Real-world application of stacks: Checking if parentheses, brackets,
 * and braces are properly balanced in an expression.
 *
 * Examples:
 * - "({[]})" → balanced
 * - "({[})" → NOT balanced
 * - "((())" → NOT balanced
 *
 * Algorithm:
 * 1. Push opening brackets onto stack
 * 2. For closing brackets, check if they match the top of stack
 * 3. Stack should be empty at the end
 *
 * Time Complexity: O(n) where n is the length of the string
 * Space Complexity: O(n) in worst case (all opening brackets)
 */
class BalancedParentheses
{
    /** @var array<string, string> Mapping of closing to opening brackets */
    private const PAIRS = [
        ')' => '(',
        ']' => '[',
        '}' => '{',
    ];

    /** @var array<string> Opening brackets */
    private const OPENING = ['(', '[', '{'];

    /** @var array<string> Closing brackets */
    private const CLOSING = [')', ']', '}'];

    /**
     * Check if brackets in a string are balanced
     *
     * @param string $expression The string to check
     * @return bool True if balanced, false otherwise
     */
    public static function isBalanced(string $expression): bool
    {
        $stack = new Stack();

        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];

            // If opening bracket, push to stack
            if (in_array($char, self::OPENING, true)) {
                $stack->push($char);
                continue;
            }

            // If closing bracket, check if it matches
            if (in_array($char, self::CLOSING, true)) {
                // No matching opening bracket
                if ($stack->isEmpty()) {
                    return false;
                }

                $top = $stack->pop();

                // Mismatched brackets
                if ($top !== self::PAIRS[$char]) {
                    return false;
                }
            }

            // Ignore other characters
        }

        // Stack should be empty if all brackets are balanced
        return $stack->isEmpty();
    }

    /**
     * Get detailed analysis of bracket matching
     *
     * @param string $expression The string to analyze
     * @return array{balanced: bool, error: ?string, position: ?int}
     */
    public static function analyze(string $expression): array
    {
        $stack = new Stack();

        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];

            if (in_array($char, self::OPENING, true)) {
                $stack->push(['char' => $char, 'position' => $i]);
                continue;
            }

            if (in_array($char, self::CLOSING, true)) {
                if ($stack->isEmpty()) {
                    return [
                        'balanced' => false,
                        'error' => "Unexpected closing bracket '$char'",
                        'position' => $i,
                    ];
                }

                $top = $stack->pop();

                if ($top['char'] !== self::PAIRS[$char]) {
                    return [
                        'balanced' => false,
                        'error' => "Mismatched brackets: expected '" .
                                  self::getClosing($top['char']) .
                                  "' but found '$char'",
                        'position' => $i,
                    ];
                }
            }
        }

        if (!$stack->isEmpty()) {
            $unclosed = $stack->pop();
            return [
                'balanced' => false,
                'error' => "Unclosed bracket '{$unclosed['char']}'",
                'position' => $unclosed['position'],
            ];
        }

        return [
            'balanced' => true,
            'error' => null,
            'position' => null,
        ];
    }

    /**
     * Get the closing bracket for an opening bracket
     *
     * @param string $opening The opening bracket
     * @return string The corresponding closing bracket
     */
    private static function getClosing(string $opening): string
    {
        return match ($opening) {
            '(' => ')',
            '[' => ']',
            '{' => '}',
            default => '',
        };
    }

    /**
     * Visual representation of bracket matching
     *
     * @param string $expression The expression to visualize
     * @return string ASCII art showing bracket matching
     */
    public static function visualize(string $expression): string
    {
        $result = $expression . "\n";
        $stack = [];
        $connections = [];

        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];

            if (in_array($char, self::OPENING, true)) {
                $stack[] = ['char' => $char, 'position' => $i];
            } elseif (in_array($char, self::CLOSING, true)) {
                if (!empty($stack)) {
                    $opening = array_pop($stack);
                    $connections[] = [$opening['position'], $i];
                }
            }
        }

        // Draw connections
        foreach ($connections as [$start, $end]) {
            $line = str_repeat(' ', strlen($expression));
            $line[$start] = '└';
            $line[$end] = '┘';

            for ($j = $start + 1; $j < $end; $j++) {
                if ($line[$j] === ' ') {
                    $line[$j] = '─';
                }
            }

            $result .= $line . "\n";
        }

        return $result;
    }
}
