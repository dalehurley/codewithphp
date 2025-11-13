<?php

declare(strict_types=1);

require_once __DIR__ . '/examples/Stack.php';
require_once __DIR__ . '/examples/Queue.php';
require_once __DIR__ . '/examples/CircularQueue.php';
require_once __DIR__ . '/examples/BalancedParentheses.php';

use ComputerScience\Chapter03\Stack;
use ComputerScience\Chapter03\Queue;
use ComputerScience\Chapter03\CircularQueue;
use ComputerScience\Chapter03\BalancedParentheses;

/**
 * Interactive demonstration of Stacks and Queues
 */

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 03: Stacks and Queues - Interactive Demo        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// =============================================================================
// STACK DEMO (LIFO)
// =============================================================================

echo "📚 STACK DEMONSTRATION (LIFO: Last In, First Out)\n";
echo str_repeat("─", 60) . "\n\n";

$stack = new Stack();

echo "1. Creating empty stack...\n";
echo "   isEmpty: " . ($stack->isEmpty() ? 'true' : 'false') . "\n";
echo "   size: " . $stack->size() . "\n\n";

echo "2. Pushing items: 10, 20, 30\n";
$stack->push(10);
echo "   Pushed 10 → Stack: " . implode(', ', $stack->toArray()) . "\n";
$stack->push(20);
echo "   Pushed 20 → Stack: " . implode(', ', $stack->toArray()) . "\n";
$stack->push(30);
echo "   Pushed 30 → Stack: " . implode(', ', $stack->toArray()) . "\n";
echo "   size: " . $stack->size() . "\n\n";

echo "3. Peeking at top (without removing)...\n";
echo "   peek: " . $stack->peek() . " (still in stack)\n";
echo "   size: " . $stack->size() . " (unchanged)\n\n";

echo "4. Popping items (LIFO order)...\n";
echo "   pop: " . $stack->pop() . " → Stack: " . implode(', ', $stack->toArray()) . "\n";
echo "   pop: " . $stack->pop() . " → Stack: " . implode(', ', $stack->toArray()) . "\n";
echo "   pop: " . $stack->pop() . " → Stack: " . (empty($stack->toArray()) ? 'empty' : implode(', ', $stack->toArray())) . "\n";
echo "   isEmpty: " . ($stack->isEmpty() ? 'true' : 'false') . "\n\n";

echo "💡 Use Case: Undo functionality\n";
$actions = new Stack();
$actions->push("Type 'Hello'");
$actions->push("Type ' World'");
$actions->push("Type '!'");
echo "   Actions performed: " . implode(' → ', $actions->toArray()) . "\n";
echo "   Undo (pop): " . $actions->pop() . "\n";
echo "   Remaining: " . implode(' → ', $actions->toArray()) . "\n\n";

// =============================================================================
// QUEUE DEMO (FIFO)
// =============================================================================

echo "\n📬 QUEUE DEMONSTRATION (FIFO: First In, First Out)\n";
echo str_repeat("─", 60) . "\n\n";

$queue = new Queue();

echo "1. Creating empty queue...\n";
echo "   isEmpty: " . ($queue->isEmpty() ? 'true' : 'false') . "\n";
echo "   size: " . $queue->size() . "\n\n";

echo "2. Enqueueing items: 10, 20, 30\n";
$queue->enqueue(10);
echo "   Enqueued 10 → Queue: " . implode(', ', $queue->toArray()) . "\n";
$queue->enqueue(20);
echo "   Enqueued 20 → Queue: " . implode(', ', $queue->toArray()) . "\n";
$queue->enqueue(30);
echo "   Enqueued 30 → Queue: " . implode(', ', $queue->toArray()) . "\n";
echo "   size: " . $queue->size() . "\n\n";

echo "3. Checking front (without removing)...\n";
echo "   front: " . $queue->front() . " (still in queue)\n";
echo "   size: " . $queue->size() . " (unchanged)\n\n";

echo "4. Dequeueing items (FIFO order)...\n";
echo "   dequeue: " . $queue->dequeue() . " → Queue: " . implode(', ', $queue->toArray()) . "\n";
echo "   dequeue: " . $queue->dequeue() . " → Queue: " . implode(', ', $queue->toArray()) . "\n";
echo "   dequeue: " . $queue->dequeue() . " → Queue: " . (empty($queue->toArray()) ? 'empty' : implode(', ', $queue->toArray())) . "\n";
echo "   isEmpty: " . ($queue->isEmpty() ? 'true' : 'false') . "\n\n";

echo "💡 Use Case: Print queue\n";
$printQueue = new Queue();
$printQueue->enqueue("Document1.pdf");
$printQueue->enqueue("Report.docx");
$printQueue->enqueue("Image.jpg");
echo "   Print jobs: " . implode(' → ', $printQueue->toArray()) . "\n";
echo "   Printing: " . $printQueue->dequeue() . "\n";
echo "   Remaining: " . implode(' → ', $printQueue->toArray()) . "\n\n";

// =============================================================================
// CIRCULAR QUEUE DEMO
// =============================================================================

echo "\n🔄 CIRCULAR QUEUE DEMONSTRATION (Efficient O(1) Queue)\n";
echo str_repeat("─", 60) . "\n\n";

$circularQueue = new CircularQueue(5);

echo "1. Creating circular queue with capacity 5...\n";
echo "   isEmpty: " . ($circularQueue->isEmpty() ? 'true' : 'false') . "\n";
echo "   isFull: " . ($circularQueue->isFull() ? 'true' : 'false') . "\n\n";

echo "2. Filling the queue...\n";
for ($i = 1; $i <= 5; $i++) {
    $circularQueue->enqueue($i * 10);
    echo "   Enqueued " . ($i * 10) . " → Queue: " . implode(', ', $circularQueue->toArray()) . " (size: " . $circularQueue->size() . ")\n";
}
echo "   isFull: " . ($circularQueue->isFull() ? 'true' : 'false') . "\n\n";

echo "3. Dequeue 2 items to make room...\n";
echo "   dequeue: " . $circularQueue->dequeue() . " → Queue: " . implode(', ', $circularQueue->toArray()) . "\n";
echo "   dequeue: " . $circularQueue->dequeue() . " → Queue: " . implode(', ', $circularQueue->toArray()) . "\n\n";

echo "4. Enqueue 2 more items (circular wraparound)...\n";
$circularQueue->enqueue(60);
echo "   Enqueued 60 → Queue: " . implode(', ', $circularQueue->toArray()) . "\n";
$circularQueue->enqueue(70);
echo "   Enqueued 70 → Queue: " . implode(', ', $circularQueue->toArray()) . "\n";
echo "   isFull: " . ($circularQueue->isFull() ? 'true' : 'false') . "\n\n";

echo "💡 Performance: CircularQueue dequeue is O(1) vs Queue's O(n)\n";
echo "   For 100K elements: CircularQueue is ~350x faster!\n\n";

// =============================================================================
// BALANCED PARENTHESES DEMO
// =============================================================================

echo "\n⚖️  BALANCED PARENTHESES CHECKER (Stack Application)\n";
echo str_repeat("─", 60) . "\n\n";

$testCases = [
    '({[]})' => true,
    '({[})'  => false,
    '((()))'  => true,
    '((())'   => false,
    '(a + b) * [c - d]' => true,
    '([)]'    => false,
    ''        => true,
];

foreach ($testCases as $expression => $expected) {
    $result = BalancedParentheses::isBalanced($expression);
    $icon = $result ? '✓' : '✗';
    $status = $result === $expected ? 'PASS' : 'FAIL';

    echo "   $icon  \"$expression\" → " . ($result ? 'balanced' : 'NOT balanced') . " [$status]\n";
}

echo "\n💡 Detailed Analysis Example:\n";
$analysis = BalancedParentheses::analyze('({[})');
echo "   Expression: '({[})'\n";
echo "   Balanced: " . ($analysis['balanced'] ? 'true' : 'false') . "\n";
echo "   Error: " . $analysis['error'] . "\n";
echo "   Position: " . $analysis['position'] . "\n\n";

echo "💡 Visual Representation:\n";
echo BalancedParentheses::visualize('({[]})');
echo "\n";

// =============================================================================
// PERFORMANCE COMPARISON
// =============================================================================

echo "\n⚡ PERFORMANCE COMPARISON\n";
echo str_repeat("─", 60) . "\n\n";

$iterations = 10000;

// Stack performance
$start = microtime(true);
$stack = new Stack();
for ($i = 0; $i < $iterations; $i++) {
    $stack->push($i);
}
for ($i = 0; $i < $iterations; $i++) {
    $stack->pop();
}
$stackTime = microtime(true) - $start;

// Queue performance
$start = microtime(true);
$queue = new Queue();
for ($i = 0; $i < $iterations; $i++) {
    $queue->enqueue($i);
}
for ($i = 0; $i < $iterations; $i++) {
    $queue->dequeue();
}
$queueTime = microtime(true) - $start;

// CircularQueue performance
$start = microtime(true);
$circularQueue = new CircularQueue($iterations);
for ($i = 0; $i < $iterations; $i++) {
    $circularQueue->enqueue($i);
}
for ($i = 0; $i < $iterations; $i++) {
    $circularQueue->dequeue();
}
$circularTime = microtime(true) - $start;

echo "Operations performed: " . number_format($iterations) . " push/pop (or enqueue/dequeue)\n\n";
echo "Stack:          " . number_format($stackTime, 4) . "s\n";
echo "Queue:          " . number_format($queueTime, 4) . "s (slower due to array reindexing)\n";
echo "CircularQueue:  " . number_format($circularTime, 4) . "s\n\n";

if ($queueTime > 0 && $circularTime > 0) {
    $speedup = $queueTime / $circularTime;
    echo "CircularQueue is " . number_format($speedup, 1) . "x faster than Queue!\n\n";
}

// =============================================================================
// SUMMARY
// =============================================================================

echo "\n📖 KEY TAKEAWAYS\n";
echo str_repeat("─", 60) . "\n\n";
echo "✓ Stack: LIFO (Last In, First Out) - O(1) operations\n";
echo "  Use for: undo/redo, backtracking, DFS, expression evaluation\n\n";
echo "✓ Queue: FIFO (First In, First Out)\n";
echo "  Array-based: O(n) dequeue ⚠️\n";
echo "  Use for: small datasets, simple use cases\n\n";
echo "✓ CircularQueue: FIFO with O(1) operations ✓\n";
echo "  Use for: high-performance, producer-consumer, BFS\n\n";
echo "✓ Real-world applications:\n";
echo "  - Balanced parentheses checking (compilers, editors)\n";
echo "  - Task scheduling (OS, web servers)\n";
echo "  - Navigation history (browsers)\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  Demo Complete! Check README.md for more details.         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
