# Chapter 10: PHP Built-in Sorting Functions

Complete guide to PHP's built-in sorting functions with practical examples.

## Code Files

### 01-basic-sorting-functions.php
Demonstrates sort(), rsort(), asort(), arsort(), ksort(), krsort(), and natsort().

**Run:** `php 01-basic-sorting-functions.php`

### 02-custom-sorting-usort.php
Custom comparators with usort(), uasort(), uksort() and best practices.

**Run:** `php 02-custom-sorting-usort.php`

## Quick Reference

| Function | Sorts By | Maintains Keys | Order |
|----------|----------|----------------|-------|
| `sort()` | Value | No | Ascending |
| `rsort()` | Value | No | Descending |
| `asort()` | Value | Yes | Ascending |
| `arsort()` | Value | Yes | Descending |
| `ksort()` | Key | Yes | Ascending |
| `krsort()` | Key | Yes | Descending |
| `usort()` | Value | No | Custom |
| `uasort()` | Value | Yes | Custom |
| `natsort()` | Value | Yes | Natural |

## Key Tips

- **Use built-in functions** whenever possible (highly optimized)
- **Pre-calculate expensive values** before custom sorting
- **Spaceship operator (<=>)** simplifies comparisons
- **Sort modifies in place** - returns bool, not sorted array

## Quick Start
```bash
cd /home/user/codewithphp/code-samples/php-algorithms/chapter-10
php 01-basic-sorting-functions.php
php 02-custom-sorting-usort.php
```
