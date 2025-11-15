<?php

declare(strict_types=1);

/**
 * Stack Implementation (LIFO - Last In, First Out)
 *
 * Complete implementation of a stack data structure using PHP arrays.
 * Demonstrates push, pop, peek, and practical applications.
 *
 * Time Complexities:
 * - Push: O(1)
 * - Pop: O(1)
 * - Peek: O(1)
 * - Search: O(n)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Stack implementation using array
 */
class Stack
{
    private array $items = [];
    private int $maxSize;

    /**
     * Initialize stack with optional maximum size
     *
     * @param int $maxSize Maximum stack size (PHP_INT_MAX for unlimited)
     */
    public function __construct(int $maxSize = PHP_INT_MAX)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Push element onto stack - O(1)
     *
     * @param mixed $value The value to push
     * @return void
     * @throws OverflowException if stack is full
     */
    public function push(mixed $value): void
    {
        if ($this->isFull()) {
            throw new OverflowException("Stack overflow: Maximum size {$this->maxSize} reached");
        }

        $this->items[] = $value;
    }

    /**
     * Pop element from stack - O(1)
     *
     * @return mixed The popped value
     * @throws UnderflowException if stack is empty
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack underflow: Cannot pop from empty stack");
        }

        return array_pop($this->items);
    }

    /**
     * Peek at top element without removing - O(1)
     *
     * @return mixed The top value
     * @throws UnderflowException if stack is empty
     */
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot peek: Stack is empty");
        }

        return end($this->items);
    }

    /**
     * Check if stack is empty - O(1)
     *
     * @return bool True if empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Check if stack is full - O(1)
     *
     * @return bool True if full, false otherwise
     */
    public function isFull(): bool
    {
        return count($this->items) >= $this->maxSize;
    }

    /**
     * Get stack size - O(1)
     *
     * @return int Number of elements
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Clear the stack - O(1)
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Display stack contents (top to bottom)
     *
     * @return void
     */
    public function display(): void
    {
        if ($this->isEmpty()) {
            echo "Stack is empty\n";
            return;
        }

        echo "Stack (top to bottom): ";
        echo implode(' -> ', array_reverse($this->items)) . "\n";
    }

    /**
     * Convert to array - O(n)
     *
     * @return array Stack elements (bottom to top)
     */
    public function toArray(): array
    {
        return $this->items;
    }
}

/**
 * Practical applications of stacks
 */
class StackApplications
{
    /**
     * Check if parentheses/brackets are balanced - O(n)
     *
     * @param string $expression Expression to check
     * @return bool True if balanced, false otherwise
     */
    public static function isBalanced(string $expression): bool
    {
        $stack = new Stack();
        $pairs = [
            ')' => '(',
            '}' => '{',
            ']' => '['
        ];

        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];

            // Opening bracket - push to stack
            if (in_array($char, ['(', '{', '['])) {
                $stack->push($char);
            }
            // Closing bracket - check match
            elseif (isset($pairs[$char])) {
                if ($stack->isEmpty() || $stack->pop() !== $pairs[$char]) {
                    return false;
                }
            }
        }

        return $stack->isEmpty();
    }

    /**
     * Evaluate postfix (RPN) expression - O(n)
     *
     * @param string $expression Space-separated postfix expression
     * @return float The result
     * @throws InvalidArgumentException if expression is invalid
     */
    public static function evaluatePostfix(string $expression): float
    {
        $stack = new Stack();
        $tokens = explode(' ', trim($expression));

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $stack->push((float)$token);
            } else {
                if ($stack->size() < 2) {
                    throw new InvalidArgumentException("Invalid postfix expression");
                }

                $b = $stack->pop();
                $a = $stack->pop();

                $result = match($token) {
                    '+' => $a + $b,
                    '-' => $a - $b,
                    '*' => $a * $b,
                    '/' => $b != 0 ? $a / $b : throw new DivisionByZeroError("Division by zero"),
                    '^' => $a ** $b,
                    default => throw new InvalidArgumentException("Unknown operator: {$token}")
                };

                $stack->push($result);
            }
        }

        if ($stack->size() !== 1) {
            throw new InvalidArgumentException("Invalid postfix expression");
        }

        return $stack->pop();
    }

    /**
     * Convert infix to postfix notation - O(n)
     *
     * @param string $infix Infix expression
     * @return string Postfix expression
     */
    public static function infixToPostfix(string $infix): string
    {
        $stack = new Stack();
        $output = [];
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2, '^' => 3];

        $tokens = str_split(str_replace(' ', '', $infix));

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = $token;
            } elseif ($token === '(') {
                $stack->push($token);
            } elseif ($token === ')') {
                while (!$stack->isEmpty() && $stack->peek() !== '(') {
                    $output[] = $stack->pop();
                }
                if (!$stack->isEmpty()) {
                    $stack->pop(); // Remove '('
                }
            } elseif (isset($precedence[$token])) {
                while (!$stack->isEmpty() &&
                       $stack->peek() !== '(' &&
                       isset($precedence[$stack->peek()]) &&
                       $precedence[$stack->peek()] >= $precedence[$token]) {
                    $output[] = $stack->pop();
                }
                $stack->push($token);
            }
        }

        while (!$stack->isEmpty()) {
            $output[] = $stack->pop();
        }

        return implode(' ', $output);
    }

    /**
     * Reverse a string using stack - O(n)
     *
     * @param string $str String to reverse
     * @return string Reversed string
     */
    public static function reverseString(string $str): string
    {
        $stack = new Stack();

        for ($i = 0; $i < strlen($str); $i++) {
            $stack->push($str[$i]);
        }

        $reversed = '';
        while (!$stack->isEmpty()) {
            $reversed .= $stack->pop();
        }

        return $reversed;
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Stack Implementation Demo ===\n\n";

    // Basic operations
    echo "=== Basic Stack Operations ===\n";
    $stack = new Stack();

    echo "Pushing values: 10, 20, 30\n";
    $stack->push(10);
    $stack->push(20);
    $stack->push(30);
    $stack->display(); // 30 -> 20 -> 10

    echo "Peek: " . $stack->peek() . "\n"; // 30
    echo "Pop: " . $stack->pop() . "\n";   // 30
    $stack->display(); // 20 -> 10

    echo "Size: " . $stack->size() . "\n"; // 2
    echo "Is empty: " . ($stack->isEmpty() ? "yes" : "no") . "\n"; // no

    // Test stack overflow
    echo "\n=== Testing Stack Overflow ===\n";
    $limitedStack = new Stack(maxSize: 3);
    try {
        $limitedStack->push(1);
        $limitedStack->push(2);
        $limitedStack->push(3);
        echo "Stack full. Attempting to push 4...\n";
        $limitedStack->push(4); // This will throw
    } catch (OverflowException $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }

    // Test balanced parentheses
    echo "\n=== Balanced Parentheses Checker ===\n";
    $tests = [
        '({[]})' => true,
        '({[}])' => false,
        '((()))' => true,
        '((())' => false,
        '{[()]}' => true,
        '' => true,
    ];

    foreach ($tests as $expr => $expected) {
        $result = StackApplications::isBalanced($expr);
        $status = $result === $expected ? '✓' : '✗';
        echo sprintf("%s '%s': %s\n", $status, $expr, $result ? 'Balanced' : 'Not balanced');
    }

    // Test postfix evaluation
    echo "\n=== Postfix Expression Evaluation ===\n";
    $expressions = [
        '3 4 +' => 7,           // (3+4) = 7
        '3 4 + 2 *' => 14,      // (3+4)*2 = 14
        '5 1 2 + 4 * + 3 -' => 14, // 5+((1+2)*4)-3 = 14
        '2 3 ^' => 8,           // 2^3 = 8
    ];

    foreach ($expressions as $expr => $expected) {
        try {
            $result = StackApplications::evaluatePostfix($expr);
            $status = abs($result - $expected) < 0.0001 ? '✓' : '✗';
            echo sprintf("%s '%s' = %.2f (expected: %.2f)\n", $status, $expr, $result, $expected);
        } catch (Exception $e) {
            echo "Error evaluating '$expr': " . $e->getMessage() . "\n";
        }
    }

    // Test infix to postfix conversion
    echo "\n=== Infix to Postfix Conversion ===\n";
    $conversions = [
        '3+4' => '3 4 +',
        '3+4*2' => '3 4 2 * +',
        '(3+4)*2' => '3 4 + 2 *',
        '3+4*2/(1-5)' => '3 4 2 * 1 5 - / +',
    ];

    foreach ($conversions as $infix => $expected) {
        $postfix = StackApplications::infixToPostfix($infix);
        $status = $postfix === $expected ? '✓' : '✗';
        echo sprintf("%s '%s' -> '%s'\n", $status, $infix, $postfix);
    }

    // Test string reversal
    echo "\n=== String Reversal ===\n";
    $strings = ['Hello', 'Stack', 'Algorithm'];
    foreach ($strings as $str) {
        $reversed = StackApplications::reverseString($str);
        echo "'$str' reversed: '$reversed'\n";
    }

    // Edge cases
    echo "\n=== Testing Edge Cases ===\n";
    $emptyStack = new Stack();
    echo "Empty stack: ";
    $emptyStack->display();

    try {
        echo "Attempting to pop from empty stack...\n";
        $emptyStack->pop();
    } catch (UnderflowException $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }

    try {
        echo "Attempting to peek at empty stack...\n";
        $emptyStack->peek();
    } catch (UnderflowException $e) {
        echo "Caught: " . $e->getMessage() . "\n";
    }
}
