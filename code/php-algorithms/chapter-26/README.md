# Chapter 26: Advanced Dynamic Programming

This directory contains comprehensive, runnable PHP code samples demonstrating advanced dynamic programming techniques.

## Code Samples

### 1. matrix-chain-multiplication.php
**Matrix Chain Multiplication - Interval DP**

Finds the optimal order to multiply a chain of matrices to minimize scalar multiplications.

- **Time Complexity:** O(n³)
- **Space Complexity:** O(n²)
- **Key Concepts:** Interval DP, optimal parenthesization, cost minimization

**Features:**
- Calculate minimum multiplications needed
- Get optimal parenthesization order
- Detailed solution breakdown
- Performance benchmarking

**Example Usage:**
```bash
php matrix-chain-multiplication.php
```

**Sample Output:**
```
Matrices: A1(10×20), A2(20×30), A3(30×40), A4(40×30)
Minimum multiplications: 30,000
Optimal order: ((M1 × M2) × (M3 × M4))
```

---

### 2. traveling-salesman-bitmask.php
**Traveling Salesman Problem using Bitmask DP**

Solves TSP using bitmask dynamic programming to represent visited cities efficiently.

- **Time Complexity:** O(2^n × n²)
- **Space Complexity:** O(2^n × n)
- **Key Concepts:** Bitmask DP, state compression, Hamiltonian path

**Features:**
- Calculate minimum tour cost
- Find actual tour path
- Multiple starting cities optimization
- Works well for n ≤ 20 cities

**Example Usage:**
```bash
php traveling-salesman-bitmask.php
```

**Sample Output:**
```
Four Cities Distance Matrix:
City 0:  0, 10, 15, 20
City 1: 10,  0, 35, 25
City 2: 15, 35,  0, 30
City 3: 20, 25, 30,  0

Minimum tour cost: 80
Tour: 0 → 1 → 3 → 2 → 0
```

---

### 3. edit-distance.php
**Edit Distance (Levenshtein Distance)**

Calculates minimum edit operations to transform one string into another.

- **Time Complexity:** O(m × n)
- **Space Complexity:** O(m × n) standard, O(min(m,n)) optimized
- **Key Concepts:** Multi-dimensional DP, sequence alignment

**Features:**
- Calculate minimum edit distance
- Space-optimized version
- Get transformation steps
- String similarity calculation
- Spell checker implementation

**Example Usage:**
```bash
php edit-distance.php
```

**Sample Output:**
```
'horse' → 'ros': 3 operations, 40.0% similar
'kitten' → 'sitting': 3 operations, 57.1% similar

Transform 'algorithm' → 'altruistic':
  1. Replace 'g' with 't' at position 4
  2. Replace 'o' with 'u' at position 5
  3. Insert 's' at position 7
  Total operations: 6
```

---

### 4. digit-dp-examples.php
**Digit Dynamic Programming Examples**

Counts numbers with specific digit properties using digit DP technique.

- **Time Complexity:** O(d × s × 2^k) where d=digits, s=sum, k=flags
- **Space Complexity:** O(d × s × 2^k)
- **Key Concepts:** Digit DP, tight bound, state compression

**Features:**
- Count numbers with target digit sum
- Numbers without consecutive repeating digits
- Numbers with at most K different digits
- Range queries
- Lottery probability calculator

**Example Usage:**
```bash
php digit-dp-examples.php
```

**Sample Output:**
```
Numbers 1-100 with digit sum = 10: 8
  Examples: 19, 28, 37, 46, 55...

Numbers without consecutive repeating digits:
1-100: 90 numbers (90.0%)
1-1000: 738 numbers (73.8%)
```

---

## Running the Examples

All files are standalone and can be run directly:

```bash
# Run individual examples
php matrix-chain-multiplication.php
php traveling-salesman-bitmask.php
php edit-distance.php
php digit-dp-examples.php

# Run all examples
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Key Concepts Covered

### Interval DP
- Process ranges/intervals of elements
- Optimal substructure on subranges
- Examples: Matrix chain, palindrome partitioning

### Bitmask DP
- Use bitmasks to represent subsets
- Efficient for problems with small n (≤ 20)
- Examples: TSP, assignment problem

### Multi-Dimensional DP
- Multiple parameters define state
- Examples: Edit distance, egg drop

### Digit DP
- Count numbers with digit constraints
- Process digits left-to-right with tight bound
- Handles very large ranges efficiently

## Performance Characteristics

| Problem | States | Time | Space | Max Input |
|---------|--------|------|-------|-----------|
| Matrix Chain | O(n²) | O(n³) | O(n²) | n ≈ 100 |
| TSP Bitmask | O(2^n × n) | O(2^n × n²) | O(2^n × n) | n ≤ 20 |
| Edit Distance | O(m × n) | O(m × n) | O(min(m,n)) | m,n ≈ 10,000 |
| Digit DP | O(d × s) | O(d × s × 10) | O(d × s) | n ≈ 10^18 |

## Requirements

- PHP 8.0 or higher
- No external dependencies
- All examples include error handling

## Learning Path

1. **Start with:** `edit-distance.php` - Classic multi-dimensional DP
2. **Then try:** `matrix-chain-multiplication.php` - Interval DP pattern
3. **Advanced:** `traveling-salesman-bitmask.php` - Bitmask DP technique
4. **Expert:** `digit-dp-examples.php` - Complex state management

## Additional Resources

- [Chapter 26 Documentation](../../../docs/series/php-algorithms/chapters/26-advanced-dynamic-programming.md)
- [Chapter 25: DP Fundamentals](../chapter-25/)
- [LeetCode DP Problems](https://leetcode.com/tag/dynamic-programming/)

## Common Pitfalls

1. **State Definition:** Ensure state captures all necessary information
2. **Base Cases:** Handle empty/single element cases correctly
3. **Memory Limits:** Use space optimization for large inputs
4. **Integer Overflow:** Use proper data types for large values

## Performance Tips

1. **Memoization:** Clear cache between independent problems
2. **Bottom-up vs Top-down:** Bottom-up often faster, top-down easier to code
3. **Space Optimization:** Use rolling arrays when only previous row needed
4. **Early Termination:** Return immediately when answer found

## Testing

Each file includes:
- Multiple test cases with expected outputs
- Edge case handling
- Performance benchmarks
- Memory usage analysis

Verify all examples work correctly:
```bash
php matrix-chain-multiplication.php > /tmp/out1.txt && echo "✓ Matrix Chain OK"
php traveling-salesman-bitmask.php > /tmp/out2.txt && echo "✓ TSP OK"
php edit-distance.php > /tmp/out3.txt && echo "✓ Edit Distance OK"
php digit-dp-examples.php > /tmp/out4.txt && echo "✓ Digit DP OK"
```

## Contributing

Found an issue or want to add more examples? Feel free to contribute!

---

**Next Chapter:** [Chapter 27: Caching & Memoization Strategies](../chapter-27/)
