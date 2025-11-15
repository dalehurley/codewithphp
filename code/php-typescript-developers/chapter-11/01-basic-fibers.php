<?php
declare(strict_types=1);

/**
 * Basic Fibers Example
 *
 * Demonstrates how PHP Fibers work for cooperative multitasking.
 * Similar to async/await in JavaScript but lower-level.
 */

echo "=== Basic Fiber Example ===\n\n";

// Create a fiber
$fiber = new Fiber(function(): void {
    echo "1: Fiber starts\n";
    Fiber::suspend(); // Pause execution here
    echo "3: Fiber resumes\n";
    Fiber::suspend(); // Pause again
    echo "5: Fiber resumes again\n";
});

echo "0: Before fiber\n";
$fiber->start(); // Start the fiber
echo "2: Fiber suspended\n";
$fiber->resume(); // Resume the fiber
echo "4: Fiber suspended again\n";
$fiber->resume(); // Resume again
echo "6: After fiber\n";

echo "\n=== Fiber with Return Value ===\n\n";

// Fiber that returns a value
$calculationFiber = new Fiber(function(): int {
    echo "Starting calculation...\n";
    Fiber::suspend();
    echo "Continuing calculation...\n";
    return 42;
});

$calculationFiber->start();
echo "Doing other work...\n";
$result = $calculationFiber->resume();
echo "Result: {$result}\n";

echo "\n=== Fiber with Parameters ===\n\n";

// Passing data to/from fibers
$greetFiber = new Fiber(function(): string {
    $name = Fiber::suspend('What is your name?');
    return "Hello, {$name}!";
});

$question = $greetFiber->start();
echo "{$question}\n";
$greeting = $greetFiber->resume('Alice');
echo "{$greeting}\n";
