<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Custom Tools - Testing Tools\n\n";

class ToolTester
{
    public static function testTool(string $name, callable $handler, array $testCases): void
    {
        echo "Testing tool: {$name}\n";
        echo str_repeat("─", 40) . "\n";

        $passed = 0;
        $failed = 0;

        foreach ($testCases as $i => $testCase) {
            $input = $testCase['input'];
            $expected = $testCase['expected'];

            try {
                $result = $handler($input);

                if ($result == $expected) {
                    echo "  ✓ Test " . ($i + 1) . " passed\n";
                    $passed++;
                } else {
                    echo "  ✗ Test " . ($i + 1) . " failed\n";
                    echo "    Expected: " . json_encode($expected) . "\n";
                    echo "    Got: " . json_encode($result) . "\n";
                    $failed++;
                }
            } catch (\Exception $e) {
                echo "  ✗ Test " . ($i + 1) . " threw exception: {$e->getMessage()}\n";
                $failed++;
            }
        }

        echo "\nResults: {$passed} passed, {$failed} failed\n\n";
    }
}

// Test a calculator tool
$calculatorHandler = fn($input) => [
    'result' => match($input['op']) {
        'add' => $input['a'] + $input['b'],
        'subtract' => $input['a'] - $input['b'],
        default => throw new \Exception('Unknown operation')
    }
];

ToolTester::testTool('calculator', $calculatorHandler, [
    ['input' => ['op' => 'add', 'a' => 5, 'b' => 3], 'expected' => ['result' => 8]],
    ['input' => ['op' => 'add', 'a' => 10, 'b' => 20], 'expected' => ['result' => 30]],
    ['input' => ['op' => 'subtract', 'a' => 10, 'b' => 3], 'expected' => ['result' => 7]],
]);
