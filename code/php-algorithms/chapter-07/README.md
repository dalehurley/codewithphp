# Chapter 07: Quick Sort & Pivot Strategies

Comprehensive code samples for quick sort algorithm with different pivot strategies and optimizations.

## Code Files

### 01-quick-sort-basic.php
Basic quick sort implementation with visualization and performance analysis on different data patterns.

**Run:** `php 01-quick-sort-basic.php`

### 02-pivot-strategies.php
Comparison of pivot selection strategies: last element, random, and median-of-three.

**Run:** `php 02-pivot-strategies.php`

### 03-quick-sort-optimized.php
Optimized quick sort with 3-way partitioning (Dutch National Flag) and hybrid approach.

**Run:** `php 03-quick-sort-optimized.php`

### 04-quick-select.php
QuickSelect algorithm for finding kth smallest element in O(n) average time.

**Run:** `php 04-quick-select.php`

## Key Concepts

- **Time Complexity:** O(n log n) average, O(n²) worst case
- **Pivot Selection Critical:** Random or median-of-three prevents worst case
- **3-Way Partitioning:** Excellent for arrays with duplicates
- **QuickSelect:** Find kth element without full sort

## Quick Start
```bash
cd /home/user/codewithphp/code-samples/php-algorithms/chapter-07
php 01-quick-sort-basic.php
php 02-pivot-strategies.php
```
