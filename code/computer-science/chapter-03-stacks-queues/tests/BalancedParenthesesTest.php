<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03\Tests;

use ComputerScience\Chapter03\BalancedParentheses;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for BalancedParentheses checker
 */
class BalancedParenthesesTest extends TestCase
{
    public function testEmptyStringIsBalanced(): void
    {
        $this->assertTrue(BalancedParentheses::isBalanced(''));
    }

    public function testSimpleBalancedParentheses(): void
    {
        $this->assertTrue(BalancedParentheses::isBalanced('()'));
        $this->assertTrue(BalancedParentheses::isBalanced('[]'));
        $this->assertTrue(BalancedParentheses::isBalanced('{}'));
    }

    public function testNestedBalancedBrackets(): void
    {
        $this->assertTrue(BalancedParentheses::isBalanced('({[]})'));
        $this->assertTrue(BalancedParentheses::isBalanced('([])'));
        $this->assertTrue(BalancedParentheses::isBalanced('{[()]}'));
    }

    public function testMultipleBalancedGroups(): void
    {
        $this->assertTrue(BalancedParentheses::isBalanced('()[]{}'));
        $this->assertTrue(BalancedParentheses::isBalanced('()()'));
        $this->assertTrue(BalancedParentheses::isBalanced('[][]'));
    }

    public function testBalancedWithOtherCharacters(): void
    {
        $this->assertTrue(BalancedParentheses::isBalanced('(a + b) * [c - d]'));
        $this->assertTrue(BalancedParentheses::isBalanced('function foo() { return [1, 2, 3]; }'));
        $this->assertTrue(BalancedParentheses::isBalanced('if (x > 0) { print("positive"); }'));
    }

    public function testUnbalancedOpeningBrackets(): void
    {
        $this->assertFalse(BalancedParentheses::isBalanced('('));
        $this->assertFalse(BalancedParentheses::isBalanced('['));
        $this->assertFalse(BalancedParentheses::isBalanced('{'));
        $this->assertFalse(BalancedParentheses::isBalanced('((())'));
    }

    public function testUnbalancedClosingBrackets(): void
    {
        $this->assertFalse(BalancedParentheses::isBalanced(')'));
        $this->assertFalse(BalancedParentheses::isBalanced(']'));
        $this->assertFalse(BalancedParentheses::isBalanced('}'));
        $this->assertFalse(BalancedParentheses::isBalanced('())'));
    }

    public function testMismatchedBrackets(): void
    {
        $this->assertFalse(BalancedParentheses::isBalanced('(]'));
        $this->assertFalse(BalancedParentheses::isBalanced('({[})'));
        $this->assertFalse(BalancedParentheses::isBalanced('[{]}'));
        $this->assertFalse(BalancedParentheses::isBalanced('[(])'));
    }

    public function testAnalyzeBalancedExpression(): void
    {
        $result = BalancedParentheses::analyze('({[]})');

        $this->assertTrue($result['balanced']);
        $this->assertNull($result['error']);
        $this->assertNull($result['position']);
    }

    public function testAnalyzeUnexpectedClosing(): void
    {
        $result = BalancedParentheses::analyze('())');

        $this->assertFalse($result['balanced']);
        $this->assertStringContainsString('Unexpected closing bracket', $result['error']);
        $this->assertEquals(2, $result['position']);
    }

    public function testAnalyzeMismatchedBrackets(): void
    {
        $result = BalancedParentheses::analyze('({[})');

        $this->assertFalse($result['balanced']);
        $this->assertStringContainsString('Mismatched brackets', $result['error']);
        $this->assertEquals(4, $result['position']);
    }

    public function testAnalyzeUnclosedBracket(): void
    {
        $result = BalancedParentheses::analyze('({[');

        $this->assertFalse($result['balanced']);
        $this->assertStringContainsString('Unclosed bracket', $result['error']);
        $this->assertIsInt($result['position']);
    }

    public function testVisualizationReturnsString(): void
    {
        $visualization = BalancedParentheses::visualize('({[]})');

        $this->assertIsString($visualization);
        $this->assertStringContainsString('({[]})', $visualization);
    }

    public function testComplexBalancedExpression(): void
    {
        $expression = '((a + b) * [c - d]) / {e + f}';
        $this->assertTrue(BalancedParentheses::isBalanced($expression));
    }

    public function testComplexUnbalancedExpression(): void
    {
        $expression = '((a + b) * [c - d] / {e + f}';
        $this->assertFalse(BalancedParentheses::isBalanced($expression));
    }

    public function testRealCodeSnippet(): void
    {
        $code = <<<'PHP'
function calculate($x) {
    if ($x > 0) {
        return [$x * 2, $x * 3];
    }
    return null;
}
PHP;
        $this->assertTrue(BalancedParentheses::isBalanced($code));
    }

    public function testUnbalancedRealCodeSnippet(): void
    {
        $code = <<<'PHP'
function broken($x) {
    if ($x > 0) {
        return [$x * 2, $x * 3;
    }
    return null;
}
PHP;
        $this->assertFalse(BalancedParentheses::isBalanced($code));
    }
}
