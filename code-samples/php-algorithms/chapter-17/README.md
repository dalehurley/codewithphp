# Chapter 17: Stacks & Queues - Code Samples

Comprehensive PHP implementations of LIFO (Stack) and FIFO (Queue) data structures with practical applications.

## Files Overview

### 1. `01-stack-implementation.php`
**Stack (LIFO) Implementation & Applications**

Complete stack implementation with real-world applications including:
- Basic operations (push, pop, peek)
- Balanced parentheses checker
- Postfix expression evaluator (RPN calculator)
- Infix to postfix conversion
- String reversal

**Run:** `php 01-stack-implementation.php`

### 2. `02-queue-implementation.php`
**Queue (FIFO) Implementation & Variants**

Multiple queue implementations:
- Basic Queue using SplQueue
- Circular Queue for O(1) operations
- Priority Queue

**Run:** `php 02-queue-implementation.php`

## Key Concepts

### Stack (LIFO)
- **Last In, First Out** principle
- Operations at one end only (top)
- Use cases: Undo/redo, expression evaluation, backtracking

### Queue (FIFO)
- **First In, First Out** principle
- Enqueue at rear, dequeue from front
- Use cases: Task scheduling, BFS, print queues

## Time Complexity

| Operation | Stack | Queue (Array) | Circular Queue |
|-----------|-------|---------------|----------------|
| Push/Enqueue | O(1) | O(1) | O(1) |
| Pop/Dequeue | O(1) | O(n)* | O(1) |
| Peek | O(1) | O(1) | O(1) |
| Size | O(1) | O(1) | O(1) |

*array_shift() is O(n); use SplQueue or circular queue for O(1)

## Requirements

- PHP 8.0+
- SPL extension (included by default)
