---
title: "17: Stacks & Queues"
description: "Implement LIFO and FIFO data structures. Build an expression evaluator and task scheduler."
sidebar:
  label: "17: Stacks & Queues"
  order: 17
  badge:
    text: Intermediate
    variant: caution
---
![17: Stacks & Queues](/images/php-algorithms/chapter-17-stacks-queues-hero-full.webp)


# Stacks & Queues <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## What You'll Learn

- Master the Stack (LIFO) data structure for last-in-first-out operations
- Implement the Queue (FIFO) data structure for first-in-first-out operations
- Build practical applications: expression evaluator, undo/redo, task scheduler
- Understand when to use stacks vs. queues in real-world scenarios
- Implement both structures using arrays and linked lists

**Estimated Time**: ~45 minutes

## Prerequisites

Before starting this chapter, you should have:

- ✓ Understanding of arrays and their operations
- ✓ Familiarity with linked lists from Chapter 16
- ✓ Completion of Chapter 15 (Arrays & Dynamic Arrays)
- ✓ Basic knowledge of recursion concepts

Stacks and queues are fundamental linear data structures with restricted access patterns. While they may seem simple, they're incredibly powerful and used everywhere - from function call management to task scheduling, browser history to print spoolers. In this chapter, we'll implement both structures and build practical applications that demonstrate their real-world utility.

## What You'll Build

By the end of this chapter, you will have created:

- Complete stack implementation using arrays and linked lists
- Queue implementations with multiple strategies (array, circular, linked list)
- Practical applications: expression evaluator, undo/redo system, task scheduler
- Advanced variants: double-ended queue (deque) and priority queue
- Performance benchmarking tools to compare implementations
- Framework integration examples for Laravel and Symfony
- Security-hardened versions with overflow protection and input validation

## Visual Step-by-Step: Stack Operations

Let's visualize how a stack works:

### Push Operations

```
Initial state:
Stack: [empty]

After push(10):
TOP → [10]

After push(20):
TOP → [20]
      [10]

After push(30):
TOP → [30]
      [20]
      [10]

After push(40):
TOP → [40]
      [30]
      [20]
      [10]
```

### Pop Operation

```
Current state:
TOP → [40]
      [30]
      [20]
      [10]

pop() returns 40:
TOP → [30]
      [20]
      [10]

pop() returns 30:
TOP → [20]
      [10]
```

### Peek Operation

```
Current state:
TOP → [20]
      [10]

peek() returns 20 (no change to stack):
TOP → [20]
      [10]
```

## Stack: Last-In, First-Out (LIFO)

A **stack** is like a stack of plates: you add and remove from the top only.

**Operations:**
- **Push**: Add element to top
- **Pop**: Remove and return top element
- **Peek**: View top element without removing
- **isEmpty**: Check if stack is empty

### Array-Based Stack

```php
# filename: stack.php
<?php

declare(strict_types=1);

class Stack
{
    private array $items = [];

    // Push element - O(1)
    public function push(mixed $value): void
    {
        $this->items[] = $value;
    }

    // Pop element - O(1)
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        return array_pop($this->items);
    }

    // Peek at top - O(1)
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        return end($this->items);
    }

    // Check if empty - O(1)
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    // Get size - O(1)
    public function size(): int
    {
        return count($this->items);
    }

    // Clear stack - O(1)
    public function clear(): void
    {
        $this->items = [];
    }

    // Display stack
    public function display(): void
    {
        echo "Stack (top to bottom): ";
        echo implode(' -> ', array_reverse($this->items)) . "\n";
    }
}

// Usage
$stack = new Stack();
$stack->push(10);
$stack->push(20);
$stack->push(30);
$stack->display(); // 30 -> 20 -> 10

echo $stack->pop() . "\n";  // 30
echo $stack->peek() . "\n"; // 20
```

### Linked List-Based Stack

```php
# filename: linked-stack.php
<?php

declare(strict_types=1);

class Node
{
    public function __construct(
        public mixed $data,
        public ?Node $next = null
    ) {}
}

class LinkedStack
{
    private ?Node $top = null;
    private int $size = 0;

    public function push(mixed $value): void
    {
        $newNode = new Node($value);
        $newNode->next = $this->top;
        $this->top = $newNode;
        $this->size++;
    }

    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        $value = $this->top->data;
        $this->top = $this->top->next;
        $this->size--;

        return $value;
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        return $this->top->data;
    }

    public function isEmpty(): bool
    {
        return $this->top === null;
    }

    public function size(): int
    {
        return $this->size;
    }
}
```

## Stack Applications

### 1. Balanced Parentheses Checker

```php
# filename: balanced-parentheses.php
<?php

declare(strict_types=1);

function isBalanced(string $expression): bool
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

echo isBalanced("({[]})") ? "Balanced" : "Not balanced";     // Balanced
echo isBalanced("({[}])") ? "Balanced" : "Not balanced";     // Not balanced
echo isBalanced("((())") ? "Balanced" : "Not balanced";      // Not balanced
```

### 2. Expression Evaluator (Postfix/RPN)

```php
# filename: postfix-evaluator.php
<?php

declare(strict_types=1);

function evaluatePostfix(string $expression): float
{
    $stack = new Stack();
    $tokens = explode(' ', $expression);

    foreach ($tokens as $token) {
        if (is_numeric($token)) {
            $stack->push((float)$token);
        } else {
            $b = $stack->pop();
            $a = $stack->pop();

            switch ($token) {
                case '+':
                    $stack->push($a + $b);
                    break;
                case '-':
                    $stack->push($a - $b);
                    break;
                case '*':
                    $stack->push($a * $b);
                    break;
                case '/':
                    $stack->push($a / $b);
                    break;
            }
        }
    }

    return $stack->pop();
}

echo evaluatePostfix("3 4 + 2 *"); // (3+4)*2 = 14
echo evaluatePostfix("5 1 2 + 4 * + 3 -"); // 5+((1+2)*4)-3 = 14
```

### 3. Infix to Postfix Conversion

```php
# filename: infix-to-postfix.php
<?php

declare(strict_types=1);

function infixToPostfix(string $infix): string
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
            $stack->pop(); // Remove '('
        } elseif (isset($precedence[$token])) {
            while (!$stack->isEmpty() &&
                   $stack->peek() !== '(' &&
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

echo infixToPostfix("3+4*2/(1-5)"); // 3 4 2 * 1 5 - / +
```

### 4. Undo/Redo Functionality

```php
# filename: undo-redo.php
<?php

declare(strict_types=1);

class TextEditor
{
    private string $text = '';
    private Stack $undoStack;
    private Stack $redoStack;

    public function __construct()
    {
        $this->undoStack = new Stack();
        $this->redoStack = new Stack();
    }

    public function type(string $newText): void
    {
        $this->undoStack->push($this->text);
        $this->text .= $newText;
        $this->redoStack->clear(); // Clear redo on new action
    }

    public function undo(): void
    {
        if (!$this->undoStack->isEmpty()) {
            $this->redoStack->push($this->text);
            $this->text = $this->undoStack->pop();
        }
    }

    public function redo(): void
    {
        if (!$this->redoStack->isEmpty()) {
            $this->undoStack->push($this->text);
            $this->text = $this->redoStack->pop();
        }
    }

    public function getText(): string
    {
        return $this->text;
    }
}

$editor = new TextEditor();
$editor->type("Hello");
$editor->type(" World");
echo $editor->getText() . "\n"; // Hello World

$editor->undo();
echo $editor->getText() . "\n"; // Hello

$editor->redo();
echo $editor->getText() . "\n"; // Hello World
```

### 5. Function Call Stack Simulation

```php
# filename: call-stack-simulation.php
<?php

declare(strict_types=1);

function factorial(int $n, Stack $callStack = null): int
{
    if ($callStack === null) {
        $callStack = new Stack();
    }

    $callStack->push("factorial($n)");
    echo "Call: factorial($n)\n";

    if ($n <= 1) {
        echo "Return: 1\n";
        $callStack->pop();
        return 1;
    }

    $result = $n * factorial($n - 1, $callStack);
    echo "Return: $result from factorial($n)\n";
    $callStack->pop();

    return $result;
}

factorial(5);
```

## Visual Step-by-Step: Queue Operations

Let's visualize how a queue works:

### Enqueue Operations

```
Initial state:
Queue: [empty]

After enqueue(10):
FRONT → [10] ← REAR

After enqueue(20):
FRONT → [10] [20] ← REAR

After enqueue(30):
FRONT → [10] [20] [30] ← REAR

After enqueue(40):
FRONT → [10] [20] [30] [40] ← REAR
```

### Dequeue Operation

```
Current state:
FRONT → [10] [20] [30] [40] ← REAR

dequeue() returns 10:
FRONT → [20] [30] [40] ← REAR

dequeue() returns 20:
FRONT → [30] [40] ← REAR
```

### Combined Operations

```
Start:
FRONT → [30] [40] ← REAR

enqueue(50):
FRONT → [30] [40] [50] ← REAR

dequeue() returns 30:
FRONT → [40] [50] ← REAR

enqueue(60):
FRONT → [40] [50] [60] ← REAR
```

## Queue: First-In, First-Out (FIFO)

A **queue** is like a line at a store: first person in line is served first.

**Operations:**
- **Enqueue**: Add element to rear
- **Dequeue**: Remove and return front element
- **Peek**: View front element without removing
- **isEmpty**: Check if queue is empty

### Array-Based Queue

```php
# filename: queue.php
<?php

declare(strict_types=1);

class Queue
{
    private array $items = [];

    // Enqueue - O(1)
    public function enqueue(mixed $value): void
    {
        $this->items[] = $value;
    }

    // Dequeue - O(n) due to array_shift
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return array_shift($this->items);
    }

    // Peek front - O(1)
    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->items[0];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function size(): int
    {
        return count($this->items);
    }

    public function display(): void
    {
        echo "Queue (front to rear): ";
        echo implode(' <- ', $this->items) . "\n";
    }
}

$queue = new Queue();
$queue->enqueue(10);
$queue->enqueue(20);
$queue->enqueue(30);
$queue->display(); // 10 <- 20 <- 30

echo $queue->dequeue() . "\n"; // 10
echo $queue->peek() . "\n";    // 20
```

### Efficient Circular Queue

```php
# filename: circular-queue.php
<?php

declare(strict_types=1);

class CircularQueue
{
    private array $items;
    private int $front = 0;
    private int $rear = -1;
    private int $size = 0;
    private int $capacity;

    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
        $this->items = array_fill(0, $capacity, null);
    }

    public function enqueue(mixed $value): void
    {
        if ($this->isFull()) {
            throw new OverflowException("Queue is full");
        }

        $this->rear = ($this->rear + 1) % $this->capacity;
        $this->items[$this->rear] = $value;
        $this->size++;
    }

    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        $value = $this->items[$this->front];
        $this->items[$this->front] = null;
        $this->front = ($this->front + 1) % $this->capacity;
        $this->size--;

        return $value;
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->items[$this->front];
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    public function isFull(): bool
    {
        return $this->size === $this->capacity;
    }

    public function size(): int
    {
        return $this->size;
    }
}
```

### Linked List-Based Queue

```php
# filename: linked-queue.php
<?php

declare(strict_types=1);

class LinkedQueue
{
    private ?Node $front = null;
    private ?Node $rear = null;
    private int $size = 0;

    public function enqueue(mixed $value): void
    {
        $newNode = new Node($value);

        if ($this->isEmpty()) {
            $this->front = $this->rear = $newNode;
        } else {
            $this->rear->next = $newNode;
            $this->rear = $newNode;
        }

        $this->size++;
    }

    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        $value = $this->front->data;
        $this->front = $this->front->next;

        if ($this->front === null) {
            $this->rear = null;
        }

        $this->size--;
        return $value;
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->front->data;
    }

    public function isEmpty(): bool
    {
        return $this->front === null;
    }

    public function size(): int
    {
        return $this->size;
    }
}
```

## Queue Applications

### 1. BFS (Breadth-First Search)

```php
# filename: bfs.php
<?php

declare(strict_types=1);

function bfs(array $graph, int $start): array
{
    $visited = [];
    $queue = new Queue();

    $queue->enqueue($start);
    $visited[$start] = true;

    while (!$queue->isEmpty()) {
        $node = $queue->dequeue();
        echo "$node ";

        foreach ($graph[$node] as $neighbor) {
            if (!isset($visited[$neighbor])) {
                $queue->enqueue($neighbor);
                $visited[$neighbor] = true;
            }
        }
    }

    return array_keys($visited);
}

$graph = [
    0 => [1, 2],
    1 => [0, 3, 4],
    2 => [0, 4],
    3 => [1],
    4 => [1, 2]
];

bfs($graph, 0); // 0 1 2 3 4
```

### 2. Task Scheduler

```php
# filename: task-scheduler.php
<?php

declare(strict_types=1);

class Task
{
    public function __construct(
        public string $name,
        public int $priority,
        public callable $action
    ) {}
}

class TaskScheduler
{
    private Queue $queue;

    public function __construct()
    {
        $this->queue = new Queue();
    }

    public function addTask(Task $task): void
    {
        $this->queue->enqueue($task);
        echo "Scheduled: {$task->name}\n";
    }

    public function processNext(): void
    {
        if ($this->queue->isEmpty()) {
            echo "No tasks to process\n";
            return;
        }

        $task = $this->queue->dequeue();
        echo "Processing: {$task->name}\n";
        ($task->action)();
    }

    public function processAll(): void
    {
        while (!$this->queue->isEmpty()) {
            $this->processNext();
        }
    }
}

$scheduler = new TaskScheduler();
$scheduler->addTask(new Task("Email", 1, fn() => print("Sending email...\n")));
$scheduler->addTask(new Task("Backup", 2, fn() => print("Running backup...\n")));
$scheduler->processAll();
```

### 3. Print Queue

```php
# filename: print-queue.php
<?php

declare(strict_types=1);

class PrintJob
{
    public function __construct(
        public string $document,
        public int $pages
    ) {}
}

class PrintQueue
{
    private Queue $queue;

    public function __construct()
    {
        $this->queue = new Queue();
    }

    public function addJob(PrintJob $job): void
    {
        $this->queue->enqueue($job);
        echo "Added to print queue: {$job->document} ({$job->pages} pages)\n";
    }

    public function printNext(): void
    {
        if ($this->queue->isEmpty()) {
            echo "Print queue is empty\n";
            return;
        }

        $job = $this->queue->dequeue();
        echo "Printing: {$job->document}";

        for ($i = 0; $i < $job->pages; $i++) {
            echo ".";
            usleep(100000); // Simulate printing
        }

        echo " Done!\n";
    }

    public function getQueueSize(): int
    {
        return $this->queue->size();
    }
}
```

## Double-Ended Queue (Deque)

A **deque** allows insertion and deletion at both ends.

```php
# filename: deque.php
<?php

declare(strict_types=1);

class Deque
{
    private array $items = [];

    public function pushFront(mixed $value): void
    {
        array_unshift($this->items, $value);
    }

    public function pushBack(mixed $value): void
    {
        $this->items[] = $value;
    }

    public function popFront(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Deque is empty");
        }
        return array_shift($this->items);
    }

    public function popBack(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Deque is empty");
        }
        return array_pop($this->items);
    }

    public function peekFront(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Deque is empty");
        }
        return $this->items[0];
    }

    public function peekBack(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Deque is empty");
        }
        return end($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function size(): int
    {
        return count($this->items);
    }
}

// Can be used as stack or queue!
$deque = new Deque();
$deque->pushBack(1);
$deque->pushFront(2);
$deque->pushBack(3);
// Deque: 2 <- 1 <- 3
```

## Priority Queue

Elements are dequeued based on priority, not insertion order.

```php
# filename: priority-queue.php
<?php

declare(strict_types=1);

class PriorityQueue
{
    private array $items = [];

    public function enqueue(mixed $value, int $priority): void
    {
        $this->items[] = ['value' => $value, 'priority' => $priority];

        // Sort by priority (higher priority first)
        usort($this->items, fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Priority queue is empty");
        }

        $item = array_shift($this->items);
        return $item['value'];
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Priority queue is empty");
        }

        return $this->items[0]['value'];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

$pq = new PriorityQueue();
$pq->enqueue("Low priority task", 1);
$pq->enqueue("High priority task", 10);
$pq->enqueue("Medium priority task", 5);

echo $pq->dequeue(); // High priority task
echo $pq->dequeue(); // Medium priority task
```

## PHP SPL: Built-in Stack and Queue

PHP's Standard PHP Library provides optimized implementations of stacks and queues.

### SplStack

```php
# filename: spl-stack-example.php
<?php

declare(strict_types=1);

$stack = new SplStack();

// Push elements
$stack->push(10);
$stack->push(20);
$stack->push(30);

// Access elements
echo $stack->top() . "\n";     // 30 (peek at top)
echo $stack->pop() . "\n";     // 30 (remove and return)
echo $stack->count() . "\n";   // 2

// Iterate (LIFO order)
foreach ($stack as $item) {
    echo "$item "; // 20 10
}

// Check if empty
if (!$stack->isEmpty()) {
    echo "Stack has elements\n";
}
```

### SplQueue

```php
# filename: spl-queue-example.php
<?php

declare(strict_types=1);

$queue = new SplQueue();

// Enqueue elements
$queue->enqueue(10);
$queue->enqueue(20);
$queue->enqueue(30);

// Dequeue
echo $queue->dequeue() . "\n"; // 10 (FIFO)

// Array access
echo $queue[0] . "\n";         // 20 (front of queue)

// Iterate (FIFO order)
foreach ($queue as $item) {
    echo "$item "; // 20 30
}
```

### SplPriorityQueue

```php
# filename: spl-priority-queue-example.php
<?php

declare(strict_types=1);

$pq = new SplPriorityQueue();

// Insert with priority
$pq->insert('Low priority', 1);
$pq->insert('High priority', 10);
$pq->insert('Medium priority', 5);

// Extract in priority order
echo $pq->extract() . "\n"; // High priority
echo $pq->extract() . "\n"; // Medium priority
echo $pq->extract() . "\n"; // Low priority

// Set extraction mode
$pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
$pq->insert('Task A', 5);
$result = $pq->extract();
print_r($result); // ['data' => 'Task A', 'priority' => 5]
```

### SplDoublyLinkedList (Base for Stack/Queue)

```php
# filename: spl-doubly-linked-list-example.php
<?php

declare(strict_types=1);

$list = new SplDoublyLinkedList();

// Can be used as stack
$list->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
$list->push(1);
$list->push(2);
$list->push(3);

foreach ($list as $item) {
    echo "$item "; // 3 2 1 (LIFO)
}

// Or as queue
$list->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
foreach ($list as $item) {
    echo "$item "; // 1 2 3 (FIFO)
}
```

## Performance Benchmarks

Let's measure real-world performance of different implementations:

```php
# filename: benchmark-stacks-queues.php
<?php

declare(strict_types=1);

function benchmarkStackImplementations(): void
{
    $operations = 10000;

    // Array-based Stack
    $start = microtime(true);
    $stack = new Stack();
    for ($i = 0; $i < $operations; $i++) {
        $stack->push($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $stack->pop();
    }
    $arrayTime = microtime(true) - $start;

    // SPL Stack
    $start = microtime(true);
    $splStack = new SplStack();
    for ($i = 0; $i < $operations; $i++) {
        $splStack->push($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $splStack->pop();
    }
    $splTime = microtime(true) - $start;

    // Linked List Stack
    $start = microtime(true);
    $linkedStack = new LinkedStack();
    for ($i = 0; $i < $operations; $i++) {
        $linkedStack->push($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $linkedStack->pop();
    }
    $linkedTime = microtime(true) - $start;

    echo "=== Stack Performance ({$operations} push + {$operations} pop) ===\n";
    echo sprintf("Array-based:  %.4f seconds\n", $arrayTime);
    echo sprintf("SPL Stack:    %.4f seconds (%.2fx)\n", $splTime, $arrayTime / $splTime);
    echo sprintf("Linked List:  %.4f seconds (%.2fx)\n", $linkedTime, $arrayTime / $linkedTime);
}

function benchmarkQueueImplementations(): void
{
    $operations = 10000;

    // Array-based Queue (with array_shift - slow)
    $start = microtime(true);
    $queue = new Queue();
    for ($i = 0; $i < $operations; $i++) {
        $queue->enqueue($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $queue->dequeue();
    }
    $arrayTime = microtime(true) - $start;

    // SPL Queue
    $start = microtime(true);
    $splQueue = new SplQueue();
    for ($i = 0; $i < $operations; $i++) {
        $splQueue->enqueue($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $splQueue->dequeue();
    }
    $splTime = microtime(true) - $start;

    // Linked Queue
    $start = microtime(true);
    $linkedQueue = new LinkedQueue();
    for ($i = 0; $i < $operations; $i++) {
        $linkedQueue->enqueue($i);
    }
    for ($i = 0; $i < $operations; $i++) {
        $linkedQueue->dequeue();
    }
    $linkedTime = microtime(true) - $start;

    echo "\n=== Queue Performance ({$operations} enqueue + {$operations} dequeue) ===\n";
    echo sprintf("Array-based:  %.4f seconds\n", $arrayTime);
    echo sprintf("SPL Queue:    %.4f seconds (%.2fx faster)\n", $splTime, $arrayTime / $splTime);
    echo sprintf("Linked List:  %.4f seconds (%.2fx faster)\n", $linkedTime, $arrayTime / $linkedTime);
}

// Sample output:
// === Stack Performance (10000 push + 10000 pop) ===
// Array-based:  0.0089 seconds
// SPL Stack:    0.0034 seconds (2.62x)
// Linked List:  0.0156 seconds (0.57x)
//
// === Queue Performance (10000 enqueue + 10000 dequeue) ===
// Array-based:  1.2345 seconds
// SPL Queue:    0.0045 seconds (274.33x faster)
// Linked List:  0.0198 seconds (62.35x faster)
```

### Memory Usage Analysis

```php
# filename: memory-analysis.php
<?php

declare(strict_types=1);

function analyzeMemoryUsage(): void
{
    $elements = 1000;

    // Stack memory
    $before = memory_get_usage();
    $stack = new Stack();
    for ($i = 0; $i < $elements; $i++) {
        $stack->push($i);
    }
    $stackMemory = memory_get_usage() - $before;

    // SPL Stack memory
    $before = memory_get_usage();
    $splStack = new SplStack();
    for ($i = 0; $i < $elements; $i++) {
        $splStack->push($i);
    }
    $splMemory = memory_get_usage() - $before;

    // Queue memory
    $before = memory_get_usage();
    $queue = new Queue();
    for ($i = 0; $i < $elements; $i++) {
        $queue->enqueue($i);
    }
    $queueMemory = memory_get_usage() - $before;

    echo "Memory usage for {$elements} elements:\n";
    echo sprintf("Stack (array):    %s KB\n", number_format($stackMemory / 1024, 2));
    echo sprintf("SPL Stack:        %s KB\n", number_format($splMemory / 1024, 2));
    echo sprintf("Queue (array):    %s KB\n", number_format($queueMemory / 1024, 2));
}

// Sample output:
// Memory usage for 1000 elements:
// Stack (array):    32.45 KB
// SPL Stack:        98.23 KB
// Queue (array):    32.67 KB
```

**Performance Insights:**
- **SPL implementations are fastest** (written in C)
- **Array-based queues with array_shift are very slow** (O(n) dequeue)
- **Linked list queues are much faster** for dequeue operations
- **For stacks, arrays are competitive** since array_pop is O(1)
- **SPL uses more memory** but provides better performance

## Edge Cases and Error Handling

Robust implementations must handle edge cases:

```php
# filename: robust-stack.php
<?php

declare(strict_types=1);

class RobustStack
{
    private array $items = [];
    private int $maxSize;

    public function __construct(int $maxSize = PHP_INT_MAX)
    {
        if ($maxSize <= 0) {
            throw new InvalidArgumentException("Max size must be positive");
        }
        $this->maxSize = $maxSize;
    }

    public function push(mixed $value): void
    {
        // Edge case: Stack overflow
        if ($this->size() >= $this->maxSize) {
            throw new OverflowException(
                "Stack overflow: cannot push to full stack (max: {$this->maxSize})"
            );
        }

        // Edge case: Prevent null values if desired
        if ($value === null) {
            throw new InvalidArgumentException("Cannot push null value");
        }

        $this->items[] = $value;
    }

    public function pop(): mixed
    {
        // Edge case: Stack underflow
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack underflow: cannot pop from empty stack");
        }

        return array_pop($this->items);
    }

    public function peek(): mixed
    {
        // Edge case: Peek on empty stack
        if ($this->isEmpty()) {
            throw new UnderflowException("Cannot peek: stack is empty");
        }

        return end($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isFull(): bool
    {
        return $this->size() >= $this->maxSize;
    }

    public function size(): int
    {
        return count($this->items);
    }

    public function clear(): void
    {
        $this->items = [];
    }
}

class RobustQueue
{
    private array $items = [];
    private int $maxSize;

    public function __construct(int $maxSize = PHP_INT_MAX)
    {
        if ($maxSize <= 0) {
            throw new InvalidArgumentException("Max size must be positive");
        }
        $this->maxSize = $maxSize;
    }

    public function enqueue(mixed $value): void
    {
        // Edge case: Queue overflow
        if ($this->size() >= $this->maxSize) {
            throw new OverflowException("Queue is full");
        }

        $this->items[] = $value;
    }

    public function dequeue(): mixed
    {
        // Edge case: Queue underflow
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        // Note: array_shift is O(n) - use LinkedQueue for better performance
        return array_shift($this->items);
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->items[0];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isFull(): bool
    {
        return $this->size() >= $this->maxSize;
    }

    public function size(): int
    {
        return count($this->items);
    }
}
```

### Common Edge Cases Checklist

```php
# filename: edge-cases-test.php
<?php

declare(strict_types=1);

// 1. Operations on empty structures
$stack = new RobustStack();
try {
    $stack->pop(); // Should throw UnderflowException
} catch (UnderflowException $e) {
    echo "Caught: {$e->getMessage()}\n";
}

// 2. Operations on full structures
$stack = new RobustStack(maxSize: 3);
$stack->push(1);
$stack->push(2);
$stack->push(3);
try {
    $stack->push(4); // Should throw OverflowException
} catch (OverflowException $e) {
    echo "Caught: {$e->getMessage()}\n";
}

// 3. Single element
$stack = new RobustStack();
$stack->push(10);
echo $stack->peek(); // Should work
$stack->pop();       // Should work
echo $stack->isEmpty() ? "Empty" : "Not empty"; // Empty

// 4. Alternating push/pop
$stack = new RobustStack();
$stack->push(1);
$stack->pop();
$stack->push(2);
$stack->pop();
echo $stack->isEmpty() ? "Empty" : "Not empty"; // Empty

// 5. Type safety
$stack = new RobustStack();
$stack->push(42);           // int
$stack->push("hello");      // string
$stack->push([1, 2, 3]);    // array
$stack->push(new stdClass); // object
```

## Framework Integration

### Laravel: Queue Service with Dependency Injection

```php
# filename: app/Services/QueueService.php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use SplQueue;

class QueueService
{
    private SplQueue $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function enqueue(mixed $item): void
    {
        $this->queue->enqueue($item);
        \Log::info('Item enqueued', ['item' => $item]);
    }

    public function dequeue(): mixed
    {
        if ($this->queue->isEmpty()) {
            throw new \RuntimeException('Queue is empty');
        }

        $item = $this->queue->dequeue();
        \Log::info('Item dequeued', ['item' => $item]);

        return $item;
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    public function size(): int
    {
        return $this->queue->count();
    }
}

// Usage in Controller
use App\Services\QueueService;

class TaskController extends Controller
{
    public function __construct(
        private QueueService $queueService
    ) {}

    public function enqueue(Request $request)
    {
        $task = $request->input('task');
        $this->queueService->enqueue($task);

        return response()->json(['message' => 'Task queued']);
    }

    public function process()
    {
        if ($this->queueService->isEmpty()) {
            return response()->json(['message' => 'No tasks to process']);
        }

        $task = $this->queueService->dequeue();

        // Process task
        ProcessTaskJob::dispatch($task);

        return response()->json(['message' => 'Task processing started']);
    }
}
```

### Symfony: Stack-based Undo System

```php
# filename: src/Service/UndoService.php
<?php

declare(strict_types=1);

namespace App\Service;

use SplStack;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(shared: false)] // New instance per request
class UndoService
{
    private SplStack $undoStack;
    private SplStack $redoStack;

    public function __construct()
    {
        $this->undoStack = new SplStack();
        $this->redoStack = new SplStack();
    }

    public function executeCommand(CommandInterface $command): void
    {
        $command->execute();
        $this->undoStack->push($command);

        // Clear redo stack on new action
        $this->redoStack = new SplStack();
    }

    public function undo(): bool
    {
        if ($this->undoStack->isEmpty()) {
            return false;
        }

        $command = $this->undoStack->pop();
        $command->undo();
        $this->redoStack->push($command);

        return true;
    }

    public function redo(): bool
    {
        if ($this->redoStack->isEmpty()) {
            return false;
        }

        $command = $this->redoStack->pop();
        $command->execute();
        $this->undoStack->push($command);

        return true;
    }

    public function canUndo(): bool
    {
        return !$this->undoStack->isEmpty();
    }

    public function canRedo(): bool
    {
        return !$this->redoStack->isEmpty();
    }
}

// Command Interface
interface CommandInterface
{
    public function execute(): void;
    public function undo(): void;
}

// Example Command
class UpdateTextCommand implements CommandInterface
{
    public function __construct(
        private Document $document,
        private string $newText,
        private ?string $oldText = null
    ) {
        $this->oldText = $document->getText();
    }

    public function execute(): void
    {
        $this->document->setText($this->newText);
    }

    public function undo(): void
    {
        $this->document->setText($this->oldText);
    }
}
```

### Laravel: Rate Limiting with Queue

```php
# filename: app/Services/RateLimiter.php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use SplQueue;

class RateLimiter
{
    private int $maxRequests;
    private int $timeWindow; // in seconds

    public function __construct(int $maxRequests = 100, int $timeWindow = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    public function allow(string $key): bool
    {
        $cacheKey = "rate_limit:{$key}";
        $queue = Cache::get($cacheKey);

        if (!$queue) {
            $queue = new SplQueue();
        }

        $now = time();

        // Remove old timestamps outside the time window
        while (!$queue->isEmpty() && $queue->bottom() < $now - $this->timeWindow) {
            $queue->dequeue();
        }

        // Check if limit exceeded
        if ($queue->count() >= $this->maxRequests) {
            return false;
        }

        // Add current timestamp
        $queue->enqueue($now);

        // Store back in cache
        Cache::put($cacheKey, $queue, $this->timeWindow);

        return true;
    }

    public function remaining(string $key): int
    {
        $cacheKey = "rate_limit:{$key}";
        $queue = Cache::get($cacheKey, new SplQueue());

        $now = time();

        // Remove old timestamps
        while (!$queue->isEmpty() && $queue->bottom() < $now - $this->timeWindow) {
            $queue->dequeue();
        }

        return max(0, $this->maxRequests - $queue->count());
    }
}
```

## Security Considerations

### 1. Prevent Stack Overflow Attacks

```php
# filename: secure-stack.php
<?php

declare(strict_types=1);

class SecureStack
{
    private array $items = [];
    private int $maxSize = 1000; // Reasonable limit

    public function push(mixed $value): void
    {
        // Prevent unbounded growth
        if (count($this->items) >= $this->maxSize) {
            throw new OverflowException("Stack size limit exceeded");
        }

        // Validate input size (prevent memory exhaustion)
        if (is_string($value) && strlen($value) > 1000000) {
            throw new InvalidArgumentException("Value too large");
        }

        $this->items[] = $value;
    }
}
```

### 2. Sanitize Queue Data

```php
# filename: secure-queue.php
<?php

declare(strict_types=1);

class SecureQueue
{
    private SplQueue $queue;

    public function enqueue(mixed $value): void
    {
        // Sanitize strings
        if (is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        // Prevent serialized object injection
        if (is_string($value) && $this->isSerialized($value)) {
            throw new InvalidArgumentException("Serialized data not allowed");
        }

        $this->queue->enqueue($value);
    }

    private function isSerialized(string $data): bool
    {
        return @unserialize($data) !== false || $data === 'b:0;';
    }
}
```

### 3. Rate Limiting to Prevent DoS

```php
# filename: throttled-stack.php
<?php

declare(strict_types=1);

class ThrottledStack
{
    private Stack $stack;
    private int $operations = 0;
    private int $maxOperationsPerSecond = 1000;
    private float $lastReset;

    public function __construct()
    {
        $this->stack = new Stack();
        $this->lastReset = microtime(true);
    }

    private function checkThrottle(): void
    {
        $now = microtime(true);

        if ($now - $this->lastReset >= 1.0) {
            $this->operations = 0;
            $this->lastReset = $now;
        }

        if ($this->operations >= $this->maxOperationsPerSecond) {
            throw new RuntimeException("Rate limit exceeded");
        }

        $this->operations++;
    }

    public function push(mixed $value): void
    {
        $this->checkThrottle();
        $this->stack->push($value);
    }

    public function pop(): mixed
    {
        $this->checkThrottle();
        return $this->stack->pop();
    }
}
```

## Common Pitfalls and Solutions

### Pitfall 1: Using array_shift for Queue (O(n) Performance)

```php
# filename: pitfalls-example.php
<?php

declare(strict_types=1);

// BAD: Very slow for large queues
class SlowQueue
{
    private array $items = [];

    public function dequeue(): mixed
    {
        return array_shift($this->items); // O(n) - reindexes entire array!
    }
}

// GOOD: Use SplQueue or linked list
class FastQueue
{
    private SplQueue $queue;

    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    public function dequeue(): mixed
    {
        return $this->queue->dequeue(); // O(1)
    }
}

// OR: Use circular queue with fixed size
class FastCircularQueue
{
    private array $items;
    private int $front = 0;
    private int $rear = -1;
    private int $size = 0;
    private int $capacity;

    public function dequeue(): mixed
    {
        // ... (O(1) with modulo arithmetic)
    }
}
```

### Pitfall 2: Not Checking Empty Before Pop/Dequeue

```php
# filename: empty-check-example.php
<?php

declare(strict_types=1);

// BAD: Can cause errors
$stack = new Stack();
$value = $stack->pop(); // Fatal error or unexpected behavior

// GOOD: Always check
$stack = new Stack();
if (!$stack->isEmpty()) {
    $value = $stack->pop();
} else {
    // Handle empty case
}

// BETTER: Use exceptions
try {
    $value = $stack->pop();
} catch (UnderflowException $e) {
    // Handle gracefully
    error_log("Attempted to pop from empty stack");
}
```

### Pitfall 3: Modifying Queue During Iteration

```php
# filename: iteration-pitfall.php
<?php

declare(strict_types=1);

// BAD: Unexpected behavior
$queue = new SplQueue();
$queue->enqueue(1);
$queue->enqueue(2);
$queue->enqueue(3);

foreach ($queue as $item) {
    $queue->dequeue(); // Don't modify while iterating!
}

// GOOD: Use while loop
while (!$queue->isEmpty()) {
    $item = $queue->dequeue();
    // Process item
}
```

### Pitfall 4: Not Clearing Redo Stack on New Action

```php
# filename: undo-redo-pitfall.php
<?php

declare(strict_types=1);

// BAD: Redo stack has invalid states
class BadUndoRedo
{
    private Stack $undoStack;
    private Stack $redoStack;

    public function execute($command): void
    {
        $command->execute();
        $this->undoStack->push($command);
        // FORGOT: $this->redoStack->clear();
    }
}

// GOOD: Clear redo on new action
class GoodUndoRedo
{
    private Stack $undoStack;
    private Stack $redoStack;

    public function execute($command): void
    {
        $command->execute();
        $this->undoStack->push($command);
        $this->redoStack->clear(); // Clear redo history!
    }
}
```

### Pitfall 5: Memory Leaks with Large Objects

```php
# filename: memory-leak-example.php
<?php

declare(strict_types=1);

// BAD: Keeps references to large objects
class LeakyStack
{
    private array $items = [];

    public function push($value): void
    {
        $this->items[] = $value; // Large objects never released
    }
}

// GOOD: Implement proper cleanup
class CleanStack
{
    private array $items = [];
    private int $maxSize = 100;

    public function push($value): void
    {
        // Limit size to prevent unbounded growth
        if (count($this->items) >= $this->maxSize) {
            array_shift($this->items); // Remove oldest
        }

        $this->items[] = $value;
    }

    public function clear(): void
    {
        // Explicitly clear to help garbage collection
        $this->items = [];
    }

    public function __destruct()
    {
        $this->clear();
    }
}
```

## Comparison: Stack vs Queue vs Deque

| Feature | Stack | Queue | Deque |
|---------|-------|-------|-------|
| **Insert** | Top only | Rear only | Both ends |
| **Remove** | Top only | Front only | Both ends |
| **Order** | LIFO | FIFO | Flexible |
| **Use Case** | Undo, recursion | Scheduling, BFS | Sliding window |

## Advanced Stack Techniques: Monotonic Stack

A **monotonic stack** maintains elements in either strictly increasing or strictly decreasing order. This powerful technique solves "next greater/smaller element" problems efficiently.

### How Monotonic Stack Works

The stack stores indices (or values) and maintains a monotonic property:
- **Increasing monotonic stack**: Elements increase from bottom to top
- **Decreasing monotonic stack**: Elements decrease from bottom to top

When processing a new element, we pop elements that violate the monotonic property.

### 1. Next Greater Element

Find the next greater element for each element in an array:

```php
# filename: next-greater-element.php
<?php

declare(strict_types=1);

function nextGreaterElements(array $nums): array
{
    $n = count($nums);
    $result = array_fill(0, $n, -1);
    $stack = []; // Stores indices

    for ($i = 0; $i < $n; $i++) {
        // While stack not empty and current is greater than stack top
        while (!empty($stack) && $nums[$i] > $nums[end($stack)]) {
            $idx = array_pop($stack);
            $result[$idx] = $nums[$i];
        }

        $stack[] = $i;
    }

    return $result;
}

$nums = [4, 2, 1, 5, 3];
print_r(nextGreaterElements($nums));
// [5, 5, 5, -1, -1]
// Explanation: 4→5, 2→5, 1→5, 5→none, 3→none
```

**Time Complexity**: O(n) - each element pushed and popped at most once  
**Space Complexity**: O(n)

### 2. Next Smaller Element

Find the next smaller element for each element:

```php
# filename: next-smaller-element.php
<?php

declare(strict_types=1);

function nextSmallerElements(array $nums): array
{
    $n = count($nums);
    $result = array_fill(0, $n, -1);
    $stack = []; // Stores indices

    for ($i = 0; $i < $n; $i++) {
        // While stack not empty and current is smaller than stack top
        while (!empty($stack) && $nums[$i] < $nums[end($stack)]) {
            $idx = array_pop($stack);
            $result[$idx] = $nums[$i];
        }

        $stack[] = $i;
    }

    return $result;
}

$nums = [4, 2, 1, 5, 3];
print_r(nextSmallerElements($nums));
// [2, 1, -1, 3, -1]
```

### 3. Largest Rectangle in Histogram

Find the largest rectangle area in a histogram:

```php
# filename: largest-rectangle-histogram.php
<?php

declare(strict_types=1);

function largestRectangleArea(array $heights): int
{
    $stack = [];
    $maxArea = 0;
    $n = count($heights);

    for ($i = 0; $i <= $n; $i++) {
        $height = ($i === $n) ? 0 : $heights[$i];

        while (!empty($stack) && $height < $heights[end($stack)]) {
            $h = $heights[array_pop($stack)];
            $width = empty($stack) ? $i : $i - end($stack) - 1;
            $maxArea = max($maxArea, $h * $width);
        }

        $stack[] = $i;
    }

    return $maxArea;
}

$heights = [2, 1, 5, 6, 2, 3];
echo largestRectangleArea($heights); // 10
// Explanation: Rectangle of height 5 and width 2 (indices 2-3)
```

**Why It Works**: We maintain an increasing stack. When we find a bar shorter than the top, we calculate the area of rectangles ending at the popped bar.

### 4. Stock Span Problem

Calculate the span of stock prices (days until a higher price):

```php
# filename: stock-span.php
<?php

declare(strict_types=1);

function stockSpan(array $prices): array
{
    $n = count($prices);
    $span = array_fill(0, $n, 1);
    $stack = []; // Stores indices

    for ($i = 0; $i < $n; $i++) {
        // Pop indices with prices <= current price
        while (!empty($stack) && $prices[end($stack)] <= $prices[$i]) {
            array_pop($stack);
        }

        // Span is distance from last higher price
        $span[$i] = empty($stack) ? $i + 1 : $i - end($stack);
        $stack[] = $i;
    }

    return $span;
}

$prices = [100, 80, 60, 70, 60, 75, 85];
print_r(stockSpan($prices));
// [1, 1, 1, 2, 1, 4, 6]
```

### 5. Daily Temperatures

Find how many days until a warmer temperature:

```php
# filename: daily-temperatures.php
<?php

declare(strict_types=1);

function dailyTemperatures(array $temperatures): array
{
    $n = count($temperatures);
    $result = array_fill(0, $n, 0);
    $stack = []; // Stores indices

    for ($i = 0; $i < $n; $i++) {
        // While current temperature is warmer than stack top
        while (!empty($stack) && $temperatures[$i] > $temperatures[end($stack)]) {
            $idx = array_pop($stack);
            $result[$idx] = $i - $idx;
        }

        $stack[] = $i;
    }

    return $result;
}

$temps = [73, 74, 75, 71, 69, 72, 76, 73];
print_r(dailyTemperatures($temps));
// [1, 1, 4, 2, 1, 1, 0, 0]
```

## Monotonic Deque for Sliding Window

A **monotonic deque** maintains elements in sorted order and efficiently finds min/max in a sliding window. This is covered in detail in [Chapter 36: Stream Processing Algorithms](/series/php-algorithms/chapters/36-stream-processing-algorithms), but here's a brief introduction:

```php
# filename: monotonic-deque-intro.php
<?php

declare(strict_types=1);

class MonotonicDeque
{
    private SplDoublyLinkedList $deque;

    public function __construct()
    {
        $this->deque = new SplDoublyLinkedList();
    }

    // Maintain decreasing order for max
    public function pushForMax(int $value): void
    {
        while (!$this->deque->isEmpty() && $this->deque->top() < $value) {
            $this->deque->pop();
        }
        $this->deque->push($value);
    }

    // Maintain increasing order for min
    public function pushForMin(int $value): void
    {
        while (!$this->deque->isEmpty() && $this->deque->top() > $value) {
            $this->deque->pop();
        }
        $this->deque->push($value);
    }

    public function getMax(): ?int
    {
        return $this->deque->isEmpty() ? null : $this->deque->bottom();
    }

    public function getMin(): ?int
    {
        return $this->deque->isEmpty() ? null : $this->deque->bottom();
    }

    public function remove(int $value): void
    {
        if (!$this->deque->isEmpty() && $this->deque->bottom() === $value) {
            $this->deque->shift();
        }
    }
}
```

**Key Insight**: By maintaining monotonic order, the front always contains the min/max, giving O(1) access. See Chapter 36 for complete sliding window implementations.

## Practice Exercises

### Exercise 1: Min Stack

Implement a stack that supports push, pop, and getMin in O(1):

```php
# filename: exercise-min-stack.php
<?php

declare(strict_types=1);

class MinStack
{
    // Your implementation here

    public function push(int $value): void {}
    public function pop(): int {}
    public function top(): int {}
    public function getMin(): int {}  // O(1)!
}

$stack = new MinStack();
$stack->push(3);
$stack->push(5);
$stack->push(2);
echo $stack->getMin(); // 2
```

### Exercise 2: Queue Using Two Stacks

Implement a queue using only two stacks:

```php
# filename: exercise-queue-with-stacks.php
<?php

declare(strict_types=1);

class QueueWithStacks
{
    private Stack $stack1; // For enqueue
    private Stack $stack2; // For dequeue

    public function __construct()
    {
        $this->stack1 = new Stack();
        $this->stack2 = new Stack();
    }

    public function enqueue(mixed $value): void
    {
        $this->stack1->push($value);
    }

    public function dequeue(): mixed
    {
        if ($this->stack2->isEmpty()) {
            // Move all elements from stack1 to stack2
            while (!$this->stack1->isEmpty()) {
                $this->stack2->push($this->stack1->pop());
            }
        }

        if ($this->stack2->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->stack2->pop();
    }

    public function peek(): mixed
    {
        if ($this->stack2->isEmpty()) {
            while (!$this->stack1->isEmpty()) {
                $this->stack2->push($this->stack1->pop());
            }
        }

        if ($this->stack2->isEmpty()) {
            throw new UnderflowException("Queue is empty");
        }

        return $this->stack2->peek();
    }

    public function isEmpty(): bool
    {
        return $this->stack1->isEmpty() && $this->stack2->isEmpty();
    }
}

// Usage
$queue = new QueueWithStacks();
$queue->enqueue(1);
$queue->enqueue(2);
$queue->enqueue(3);

echo $queue->dequeue() . "\n"; // 1 (FIFO)
echo $queue->peek() . "\n";    // 2
```

**How It Works**: 
- **Enqueue**: Push to stack1 (O(1))
- **Dequeue**: If stack2 is empty, transfer all elements from stack1 to stack2 (reverses order), then pop from stack2
- **Amortized Complexity**: O(1) per operation (each element moved at most once)

### Exercise 3: Sliding Window Maximum

Find maximum in each window of size k:

```php
# filename: exercise-sliding-window.php
<?php

declare(strict_types=1);

function slidingWindowMax(array $nums, int $k): array
{
    $n = count($nums);
    $result = [];
    $deque = new SplDoublyLinkedList(); // Stores indices

    for ($i = 0; $i < $n; $i++) {
        // Remove indices outside current window
        while (!$deque->isEmpty() && $deque->bottom() <= $i - $k) {
            $deque->shift();
        }

        // Remove indices with values smaller than current
        while (!$deque->isEmpty() && $nums[$deque->top()] <= $nums[$i]) {
            $deque->pop();
        }

        $deque->push($i);

        // Add max to result when window is complete
        if ($i >= $k - 1) {
            $result[] = $nums[$deque->bottom()];
        }
    }

    return $result;
}

print_r(slidingWindowMax([1,3,-1,-3,5,3,6,7], 3));
// [3, 3, 5, 5, 6, 7]
```

**How It Works**: Maintain a decreasing deque. The front always contains the index of the maximum in the current window. Remove indices outside the window and those with smaller values.

### Exercise 4: Stack Using Two Queues

Implement a stack using only two queues:

```php
# filename: exercise-stack-with-queues.php
<?php

declare(strict_types=1);

class StackWithQueues
{
    private Queue $queue1;
    private Queue $queue2;

    public function __construct()
    {
        $this->queue1 = new Queue();
        $this->queue2 = new Queue();
    }

    public function push(mixed $value): void
    {
        // Always push to non-empty queue (or queue1 if both empty)
        if (!$this->queue1->isEmpty()) {
            $this->queue1->enqueue($value);
        } else {
            $this->queue2->enqueue($value);
        }
    }

    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        // Find non-empty queue
        $fromQueue = $this->queue1->isEmpty() ? $this->queue2 : $this->queue1;
        $toQueue = $this->queue1->isEmpty() ? $this->queue1 : $this->queue2;

        // Move all but last element to other queue
        while ($fromQueue->size() > 1) {
            $toQueue->enqueue($fromQueue->dequeue());
        }

        // Last element is the top of stack
        return $fromQueue->dequeue();
    }

    public function peek(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException("Stack is empty");
        }

        $fromQueue = $this->queue1->isEmpty() ? $this->queue2 : $this->queue1;
        $toQueue = $this->queue1->isEmpty() ? $this->queue1 : $this->queue2;

        $top = null;
        while (!$fromQueue->isEmpty()) {
            $top = $fromQueue->dequeue();
            $toQueue->enqueue($top);
        }

        // Swap queues
        $temp = $this->queue1;
        $this->queue1 = $this->queue2;
        $this->queue2 = $temp;

        return $top;
    }

    public function isEmpty(): bool
    {
        return $this->queue1->isEmpty() && $this->queue2->isEmpty();
    }
}

// Usage
$stack = new StackWithQueues();
$stack->push(1);
$stack->push(2);
$stack->push(3);

echo $stack->pop() . "\n"; // 3 (LIFO)
echo $stack->peek() . "\n"; // 2
```

**How It Works**: 
- **Push**: Add to the non-empty queue (or queue1 if both empty)
- **Pop**: Move all elements except the last from one queue to the other, then dequeue the last element
- **Complexity**: O(n) for pop/peek, O(1) for push

## Wrap-up

Congratulations! You've completed this chapter on stacks and queues. Here's what you've accomplished:

- ✓ Implemented stack (LIFO) data structure using arrays and linked lists
- ✓ Built queue (FIFO) data structure with multiple implementation strategies
- ✓ Created practical applications: expression evaluator, undo/redo system, task scheduler
- ✓ Understood performance trade-offs between array and linked list implementations
- ✓ Explored advanced variants: circular queues, deques, and priority queues
- ✓ Learned PHP SPL implementations (SplStack, SplQueue, SplPriorityQueue)
- ✓ Analyzed real-world performance benchmarks and memory usage
- ✓ Applied stacks and queues to solve classic problems (balanced parentheses, BFS, rate limiting)
- ✓ Learned monotonic stack technique for next greater/smaller element problems
- ✓ Solved advanced problems: largest rectangle, stock span, daily temperatures
- ✓ Understood security considerations and common pitfalls

Stacks and queues are fundamental building blocks for many algorithms and data structures. The concepts you've learned here will be essential for understanding tree traversals, graph algorithms, and many other advanced topics in the chapters ahead.

## Key Takeaways

- **Stacks** are LIFO - perfect for undo, recursion, expression evaluation
- **Queues** are FIFO - ideal for scheduling, BFS, buffering
- **Array implementation** is simple but dequeue can be O(n)
- **Linked list implementation** makes all operations O(1)
- **Circular queue** is space-efficient
- **Deque** is versatile - can act as stack or queue
- **Priority queue** processes by priority, not order
- **SPL implementations** are optimized C code - use them in production
- **Choose implementation** based on your use case: arrays for simplicity, linked lists for performance
- **Monotonic stack** solves next greater/smaller element problems efficiently
- **Monotonic deque** enables O(1) min/max queries in sliding windows

## Further Reading

- [Stack Data Structure on GeeksforGeeks](https://www.geeksforgeeks.org/stack-data-structure/) - Comprehensive guide to stack operations and applications
- [Queue Data Structure on GeeksforGeeks](https://www.geeksforgeeks.org/queue-data-structure/) - Detailed explanation of queue implementations
- [PHP SPL Documentation](https://www.php.net/manual/en/book.spl.php) - Official PHP Standard PHP Library documentation
- [Expression Evaluation Algorithms](https://en.wikipedia.org/wiki/Shunting_yard_algorithm) - Shunting yard algorithm for infix to postfix conversion
- [Breadth-First Search](https://en.wikipedia.org/wiki/Breadth-first_search) - How queues enable BFS graph traversal
- [Monotonic Stack Problems](https://leetcode.com/tag/monotonic-stack/) - LeetCode problems using monotonic stack technique
- [Chapter 4: Problem-Solving Strategies](/series/php-algorithms/chapters/04-problem-solving-strategies) - Additional monotonic stack examples
- [Chapter 16: Linked Lists](/series/php-algorithms/chapters/16-linked-lists) - Review linked list fundamentals used in stack/queue implementations
- [Chapter 18: Trees & Binary Search Trees](/series/php-algorithms/chapters/18-trees-binary-search-trees) - See how stacks enable tree traversals
- [Chapter 36: Stream Processing Algorithms](/series/php-algorithms/chapters/36-stream-processing-algorithms) - Advanced monotonic deque applications for sliding windows


## What's Next

In the next chapter, we'll explore **Trees & Binary Search Trees**, learning hierarchical data structures and their operations.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 17 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-17)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-17
php 01-*.php
```

---

Continue to [Chapter 18: Trees & Binary Search Trees](/series/php-algorithms/chapters/18-trees-binary-search-trees).
