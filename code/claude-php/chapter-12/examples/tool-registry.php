<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\CustomTools\ToolRegistry;

echo "Custom Tools - Tool Registry\n\n";

$registry = new ToolRegistry();

// Register math tools
$registry->register(
    'add',
    'Add two numbers',
    ['type' => 'object', 'properties' => [
        'a' => ['type' => 'number'],
        'b' => ['type' => 'number'],
    ], 'required' => ['a', 'b']],
    fn($input) => ['result' => $input['a'] + $input['b']],
    'math'
);

$registry->register(
    'multiply',
    'Multiply two numbers',
    ['type' => 'object', 'properties' => [
        'a' => ['type' => 'number'],
        'b' => ['type' => 'number'],
    ], 'required' => ['a', 'b']],
    fn($input) => ['result' => $input['a'] * $input['b']],
    'math'
);

// Register string tools
$registry->register(
    'uppercase',
    'Convert string to uppercase',
    ['type' => 'object', 'properties' => [
        'text' => ['type' => 'string'],
    ], 'required' => ['text']],
    fn($input) => ['result' => strtoupper($input['text'])],
    'string'
);

echo "Registered Tools:\n";
foreach ($registry->getTools() as $tool) {
    echo "  - {$tool['name']}: {$tool['description']}\n";
}

echo "\nMath Tools:\n";
foreach ($registry->getByCategory('math') as $toolName) {
    echo "  - {$toolName}\n";
}

echo "\nExecuting tools:\n";
echo "  add(5, 3) = " . json_encode($registry->execute('add', ['a' => 5, 'b' => 3])) . "\n";
echo "  multiply(4, 7) = " . json_encode($registry->execute('multiply', ['a' => 4, 'b' => 7])) . "\n";
echo "  uppercase('hello') = " . json_encode($registry->execute('uppercase', ['text' => 'hello'])) . "\n";
