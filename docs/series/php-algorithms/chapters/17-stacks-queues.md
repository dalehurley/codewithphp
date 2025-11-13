---
title: "17: Stacks & Queues"
description: "Implement LIFO and FIFO data structures. Build an expression evaluator and task scheduler."
series: "php-algorithms"
chapter: 17
order: 17
difficulty: "Intermediate"
prerequisites:
  - "Understanding of arrays"
  - "Familiarity with linked lists (Chapter 16)"
  - "Completion of Chapter 15"
---

# Stacks & Queues

Stacks and queues are fundamental linear data structures with restricted access patterns. While they may seem simple, they're incredibly powerful and used everywhere—from function call management to task scheduling. In this chapter, we'll implement both structures and build practical applications.

## Stack: Last-In, First-Out (LIFO)

A **stack** is like a stack of plates: you add and remove from the top only.

**Operations:**
- **Push**: Add element to top
- **Pop**: Remove and return top element
- **Peek**: View top element without removing
- **isEmpty**: Check if stack is empty

### Array-Based Stack

```php
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

## Queue: First-In, First-Out (FIFO)

A **queue** is like a line at a store: first person in line is served first.

**Operations:**
- **Enqueue**: Add element to rear
- **Dequeue**: Remove and return front element
- **Peek**: View front element without removing
- **isEmpty**: Check if queue is empty

### Array-Based Queue

```php
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

## Comparison: Stack vs Queue vs Deque

| Feature | Stack | Queue | Deque |
|---------|-------|-------|-------|
| **Insert** | Top only | Rear only | Both ends |
| **Remove** | Top only | Front only | Both ends |
| **Order** | LIFO | FIFO | Flexible |
| **Use Case** | Undo, recursion | Scheduling, BFS | Sliding window |

## Practice Exercises

### Exercise 1: Min Stack

Implement a stack that supports push, pop, and getMin in O(1):

```php
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
class QueueWithStacks
{
    // Use two Stack objects
    // Your implementation here
}
```

### Exercise 3: Sliding Window Maximum

Find maximum in each window of size k:

```php
function slidingWindowMax(array $nums, int $k): array
{
    // Use deque
    // Your code here
}

print_r(slidingWindowMax([1,3,-1,-3,5,3,6,7], 3));
// [3, 3, 5, 5, 6, 7]
```

## Key Takeaways

- **Stacks** are LIFO - perfect for undo, recursion, expression evaluation
- **Queues** are FIFO - ideal for scheduling, BFS, buffering
- **Array implementation** is simple but dequeue can be O(n)
- **Linked list implementation** makes all operations O(1)
- **Circular queue** is space-efficient
- **Deque** is versatile - can act as stack or queue
- **Priority queue** processes by priority, not order

## What's Next

In the next chapter, we'll explore **Trees & Binary Search Trees**, learning hierarchical data structures and their operations.

---

Continue to [Chapter 18: Trees & Binary Search Trees](/series/php-algorithms/chapters/18-trees-binary-search-trees).
