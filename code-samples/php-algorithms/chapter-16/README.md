# Chapter 16: Linked Lists - Code Samples

This directory contains comprehensive, runnable PHP code samples for Chapter 16: Linked Lists.

## Files Overview

### 1. `01-singly-linked-list.php`
**Singly Linked List Implementation**

Complete implementation of a singly linked list with tail pointer for O(1) append operations.

**Features:**
- Prepend (add to beginning) - O(1)
- Append (add to end) - O(1)
- Delete by value - O(n)
- Search - O(n)
- Insert at position - O(n)
- Get by index - O(n)
- Size tracking and isEmpty check

**Key Concepts:**
- Each node points only to the next node
- Head and tail pointers for efficient operations
- Proper edge case handling (empty list, single element, etc.)

**Run:**
```bash
php 01-singly-linked-list.php
```

### 2. `02-doubly-linked-list.php`
**Doubly Linked List Implementation**

Implementation of a doubly linked list where each node has both next and previous pointers, enabling bidirectional traversal.

**Features:**
- Prepend/Append - O(1)
- Delete by value - O(n)
- Delete by node reference - O(1)
- Bidirectional traversal
- Insert before/after specific node - O(1)

**Key Concepts:**
- Each node has prev and next pointers
- Easier deletion when node reference is known
- Useful for implementing undo/redo, browser history

**Run:**
```bash
php 02-doubly-linked-list.php
```

### 3. `03-linked-list-algorithms.php`
**Common Linked List Algorithms**

Collection of essential linked list algorithms demonstrating problem-solving techniques.

**Algorithms Included:**
- **Reverse List** - Iterative reversal in O(n) time, O(1) space
- **Find Middle** - Fast/slow pointer technique (Floyd's tortoise and hare)
- **Detect Cycle** - Floyd's cycle detection algorithm
- **Remove Nth from End** - Two-pointer technique
- **Merge Sorted Lists** - Merge two sorted lists maintaining order
- **Check Palindrome** - Verify if list reads same forwards and backwards
- **Remove Duplicates** - Remove duplicate values from unsorted list
- **Find Intersection** - Find where two lists intersect

**Run:**
```bash
php 03-linked-list-algorithms.php
```

### 4. `04-browser-history.php`
**Real-World Application: Browser History**

Practical implementation of browser navigation history using doubly linked lists.

**Features:**
- Visit new URLs (clears forward history)
- Navigate back/forward - O(1)
- View history
- Check navigation capability
- Maximum history size with automatic trimming

**Use Cases:**
- Web browsers
- Document editors (undo/redo)
- Media players (playlist navigation)

**Run:**
```bash
php 04-browser-history.php
```

## Key Takeaways

### When to Use Linked Lists
✅ **Use when:**
- Frequent insertions/deletions at beginning or end
- Size changes frequently
- Don't need random access by index
- Memory fragmentation is a concern

❌ **Don't use when:**
- Need random access (use arrays instead)
- Mostly reading, few modifications
- Size is relatively stable
- Cache locality matters (arrays are more cache-friendly)

### Time Complexity Comparison

| Operation | Singly Linked List | Doubly Linked List | Array |
|-----------|-------------------|-------------------|-------|
| Access by index | O(n) | O(n) | O(1) |
| Search | O(n) | O(n) | O(n) |
| Insert at beginning | O(1) | O(1) | O(n) |
| Insert at end | O(1)* | O(1) | O(1) amortized |
| Delete at beginning | O(1) | O(1) | O(n) |
| Delete at end | O(n) or O(1)* | O(1) | O(1) |
| Delete by reference | O(n) | O(1) | O(n) |

*With tail pointer

### Memory Usage

- **Singly Linked List**: 1 pointer per node (next)
- **Doubly Linked List**: 2 pointers per node (next, prev)
- **Array**: Contiguous memory, lower overhead

Linked lists use approximately 3-5x more memory than arrays due to pointer overhead, but offer O(1) insertions/deletions at specific positions when you have a node reference.

## Common Pitfalls and Solutions

### 1. Forgetting to Update Tail Pointer
```php
// BAD: Tail becomes invalid
public function delete(mixed $data): void {
    // ... delete logic
    // FORGOT: Update tail if deleting last node
}

// GOOD: Always update tail
public function delete(mixed $data): void {
    // ... delete logic
    if ($deletedNode === $this->tail) {
        $this->tail = $previousNode;
    }
}
```

### 2. Not Handling Edge Cases
Always test:
- Empty list operations
- Single element list
- Operations at boundaries (first/last element)
- Invalid indices

### 3. Memory Leaks with Circular References
Break references when deleting to help garbage collection:
```php
public function clear(): void {
    $current = $this->head;
    while ($current !== null) {
        $next = $current->next;
        $current->next = null; // Break reference
        $current = $next;
    }
    $this->head = $this->tail = null;
}
```

## Performance Tips

1. **Use tail pointer** for singly linked lists if appending frequently
2. **Choose doubly linked** when you need O(1) deletion with node reference
3. **Consider SPL classes** (SplDoublyLinkedList) for production - they're implemented in C and faster
4. **Avoid sequential insertion** of sorted data into BST (creates linked list)

## Testing the Code

All files include comprehensive examples and edge case testing. Run each file individually to see demonstrations of:
- Normal operations
- Edge cases
- Error handling
- Performance characteristics

## Further Reading

- Chapter 16: Linked Lists (full theoretical coverage)
- Chapter 17: Stacks & Queues (built on linked lists)
- Chapter 18: Trees & Binary Search Trees

## Requirements

- PHP 8.0 or higher
- No external dependencies required

All code samples are self-contained and runnable with standard PHP installation.
