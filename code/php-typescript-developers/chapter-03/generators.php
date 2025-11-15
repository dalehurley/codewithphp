<?php
declare(strict_types=1);

/**
 * Generators in PHP
 * Similar to JavaScript generators - lazy evaluation with yield
 */

echo "=== Basic Generator ===" . PHP_EOL . PHP_EOL;

function generateNumbers(int $max): Generator {
    echo "  [Generator started]" . PHP_EOL;
    for ($i = 0; $i < $max; $i++) {
        echo "  [Yielding {$i}]" . PHP_EOL;
        yield $i;
    }
    echo "  [Generator finished]" . PHP_EOL;
}

echo "Iterating through generator:" . PHP_EOL;
foreach (generateNumbers(5) as $num) {
    echo "Received: {$num}" . PHP_EOL;
}

// Generator with keys
echo PHP_EOL . "=== Generator with Keys ===" . PHP_EOL . PHP_EOL;

function generateKeyValue(): Generator {
    yield 'name' => 'Alice';
    yield 'age' => 30;
    yield 'email' => 'alice@example.com';
}

foreach (generateKeyValue() as $key => $value) {
    echo "{$key}: {$value}" . PHP_EOL;
}

// Lazy evaluation - memory efficient
echo PHP_EOL . "=== Memory Efficiency ===" . PHP_EOL . PHP_EOL;

function generateRange(int $start, int $end): Generator {
    for ($i = $start; $i <= $end; $i++) {
        yield $i;
    }
}

// This creates all values in memory
function arrayRange(int $start, int $end): array {
    $result = [];
    for ($i = $start; $i <= $end; $i++) {
        $result[] = $i;
    }
    return $result;
}

// Generator: Memory usage stays constant
echo "Generator (lazy):" . PHP_EOL;
$memBefore = memory_get_usage();
$generator = generateRange(1, 100000);
$memAfter = memory_get_usage();
echo "  Memory used: " . ($memAfter - $memBefore) . " bytes" . PHP_EOL;

// Array: Memory usage grows with size
echo "Array (eager):" . PHP_EOL;
$memBefore = memory_get_usage();
$array = arrayRange(1, 100000);
$memAfter = memory_get_usage();
echo "  Memory used: " . ($memAfter - $memBefore) . " bytes" . PHP_EOL;

// Practical: Reading large files
echo PHP_EOL . "=== Practical: File Reading ===" . PHP_EOL . PHP_EOL;

function readLines(string $filename): Generator {
    $handle = fopen($filename, 'r');
    if ($handle === false) {
        throw new RuntimeException("Cannot open file: {$filename}");
    }

    try {
        while (($line = fgets($handle)) !== false) {
            yield trim($line);
        }
    } finally {
        fclose($handle);
    }
}

// Create a sample file
$filename = '/tmp/sample.txt';
file_put_contents($filename, "Line 1\nLine 2\nLine 3\nLine 4\nLine 5\n");

echo "Reading file line by line:" . PHP_EOL;
foreach (readLines($filename) as $lineNumber => $line) {
    echo "  Line " . ($lineNumber + 1) . ": {$line}" . PHP_EOL;
}

unlink($filename); // Clean up

// Generator delegation (yield from)
echo PHP_EOL . "=== Generator Delegation (yield from) ===" . PHP_EOL . PHP_EOL;

function generateLetters(): Generator {
    yield 'A';
    yield 'B';
    yield 'C';
}

function generateNumbers2(): Generator {
    yield 1;
    yield 2;
    yield 3;
}

function generateCombined(): Generator {
    yield from generateLetters();
    yield from generateNumbers2();
}

echo "Combined generator:" . PHP_EOL;
foreach (generateCombined() as $value) {
    echo "  {$value}" . PHP_EOL;
}

// Sending values to generators
echo PHP_EOL . "=== Sending Values to Generators ===" . PHP_EOL . PHP_EOL;

function echoGenerator(): Generator {
    while (true) {
        $value = yield;
        if ($value === null) {
            break;
        }
        echo "Received: {$value}" . PHP_EOL;
    }
}

$echo = echoGenerator();
$echo->current(); // Start generator
$echo->send("Hello");
$echo->send("World");
$echo->send(123);
$echo->send(null); // Stop

// Practical: Fibonacci sequence
echo PHP_EOL . "=== Practical: Fibonacci Sequence ===" . PHP_EOL . PHP_EOL;

function fibonacci(): Generator {
    $a = 0;
    $b = 1;

    yield $a;
    yield $b;

    while (true) {
        $next = $a + $b;
        yield $next;
        $a = $b;
        $b = $next;
    }
}

$fib = fibonacci();
echo "First 10 Fibonacci numbers:" . PHP_EOL;
for ($i = 0; $i < 10; $i++) {
    echo $fib->current() . " ";
    $fib->next();
}
echo PHP_EOL;

// Practical: Pagination
echo PHP_EOL . "=== Practical: Pagination ===" . PHP_EOL . PHP_EOL;

function paginateResults(array $items, int $pageSize): Generator {
    $totalPages = (int)ceil(count($items) / $pageSize);

    for ($page = 1; $page <= $totalPages; $page++) {
        $offset = ($page - 1) * $pageSize;
        $pageItems = array_slice($items, $offset, $pageSize);

        yield $page => [
            'page' => $page,
            'total_pages' => $totalPages,
            'items' => $pageItems
        ];
    }
}

$allItems = range(1, 25);

foreach (paginateResults($allItems, 10) as $page => $data) {
    echo "Page {$page}/{$data['total_pages']}: ";
    echo implode(', ', $data['items']) . PHP_EOL;
}

// Practical: Infinite sequence with limit
echo PHP_EOL . "=== Practical: Infinite Sequence ===" . PHP_EOL . PHP_EOL;

function infiniteCounter(int $start = 0, int $step = 1): Generator {
    $current = $start;
    while (true) {
        yield $current;
        $current += $step;
    }
}

$counter = infiniteCounter(10, 5);
echo "First 5 values from infinite counter:" . PHP_EOL;
for ($i = 0; $i < 5; $i++) {
    echo $counter->current() . " ";
    $counter->next();
}
echo PHP_EOL;

// Practical: Data processing pipeline
echo PHP_EOL . "=== Practical: Data Processing Pipeline ===" . PHP_EOL . PHP_EOL;

function dataSource(): Generator {
    $data = [10, 20, 30, 40, 50];
    foreach ($data as $value) {
        yield $value;
    }
}

function filterEven(Generator $generator): Generator {
    foreach ($generator as $value) {
        if ($value % 2 === 0) {
            yield $value;
        }
    }
}

function multiplyBy(Generator $generator, int $factor): Generator {
    foreach ($generator as $value) {
        yield $value * $factor;
    }
}

echo "Processing pipeline: source → filter even → multiply by 3" . PHP_EOL;
$result = multiplyBy(filterEven(dataSource()), 3);

foreach ($result as $value) {
    echo "  Result: {$value}" . PHP_EOL;
}
