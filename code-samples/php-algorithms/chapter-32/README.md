# Chapter 32: Probabilistic Algorithms - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 32: Probabilistic Algorithms from the PHP Algorithms series.

## Overview

These examples demonstrate space-efficient probabilistic data structures that trade perfect accuracy for dramatic improvements in memory usage and performance.

## Code Samples

### 1. Bloom Filter (`01-bloom-filter.php`)

Space-efficient probabilistic data structure for set membership testing.

**Key Concepts:**
- Membership testing with controlled false positive rate
- Multiple hash functions
- Scalable bloom filters
- Memory vs accuracy trade-offs

**Usage:**
```bash
php 01-bloom-filter.php
```

**Features:**
- Basic bloom filter implementation
- Scalable bloom filter (auto-growing)
- Spam email filter example
- False positive rate analysis
- Memory efficiency demonstration

**Use Cases:**
- Cache filtering
- URL deduplication
- Spam detection
- Database query optimization

**Memory Savings:**
- Exact set: 1 billion items = ~8GB
- Bloom filter: 1 billion items = ~12MB (1% FPR)
- **650x memory reduction!**

---

### 2. HyperLogLog (`02-hyperloglog.php`)

Cardinality estimation algorithm with remarkable accuracy using minimal memory.

**Key Concepts:**
- Unique element counting
- Register-based estimation
- Merge operations for distributed counting
- Precision vs memory trade-offs

**Usage:**
```bash
php 02-hyperloglog.php
```

**Features:**
- Basic cardinality estimation
- Precision analysis (10, 12, 14, 16 bits)
- Merge operations for combining datasets
- Unique visitor counter with time windows
- Accuracy vs memory trade-off analysis

**Statistics:**
- Precision 14: 16KB memory, ±0.81% error
- Precision 16: 64KB memory, ±0.41% error
- Can count billions of unique items

**Use Cases:**
- Unique visitor counting
- Distinct element counting in streams
- Analytics systems
- Database query optimization

---

### 3. Count-Min Sketch (`03-count-min-sketch.php`)

Frequency estimation with controlled error bounds.

**Key Concepts:**
- Item frequency counting
- Heavy hitter detection
- Error bounds (εN with probability 1-δ)
- Merge operations

**Usage:**
```bash
php 03-count-min-sketch.php
```

**Features:**
- Basic frequency estimation
- Heavy hitters detector (top-K items)
- URL frequency tracker
- Merging multiple sketches
- Accuracy analysis with Zipf distribution

**Error Bounds:**
- ε = 0.001: 0.1% error margin
- δ = 0.01: 99% confidence
- Memory: width × depth × 8 bytes

**Use Cases:**
- Trending topics detection
- Frequency queries in streams
- Network traffic analysis
- Top-K item tracking

---

### 4. Reservoir Sampling (`04-reservoir-sampling.php`)

Maintains random samples from streams of unknown size.

**Key Concepts:**
- Uniform random sampling
- Weighted sampling
- Time-window sampling
- Distributed sampling

**Usage:**
```bash
php 04-reservoir-sampling.php
```

**Features:**
- Basic reservoir sampling (uniform)
- Weighted reservoir sampling
- Time-window based sampling
- Distributed reservoir sampling
- Uniform distribution verification

**Guarantees:**
- Each item has probability k/n of being in sample
- Works with streams of unknown size
- Space complexity: O(k)

**Use Cases:**
- Statistical sampling
- A/B testing
- Monitoring and alerting
- Data analysis

---

## Requirements

- PHP 8.0 or higher
- No external dependencies required!

All examples use pure PHP implementations.

## Running All Examples

```bash
php 01-bloom-filter.php
php 02-hyperloglog.php
php 03-count-min-sketch.php
php 04-reservoir-sampling.php
```

## Algorithm Comparison

| Algorithm | Use Case | Space | Accuracy | Operations |
|-----------|----------|-------|----------|------------|
| Bloom Filter | Membership test | O(m) | No false negatives | add, contains |
| HyperLogLog | Cardinality | O(2^p) | ±0.81% (p=14) | add, count, merge |
| Count-Min Sketch | Frequency | O(w×d) | ±εN | add, estimate |
| Reservoir | Sampling | O(k) | Exact distribution | add, getSample |

## Space-Accuracy Trade-offs

### Bloom Filter
- 10% FPR → 5.74 MB for 10M items
- 1% FPR → 11.42 MB for 10M items
- 0.1% FPR → 17.11 MB for 10M items
- **Lower FPR = More memory**

### HyperLogLog
- Precision 10 → 1 KB, ~3.2% error
- Precision 12 → 4 KB, ~1.6% error
- Precision 14 → 16 KB, ~0.81% error
- Precision 16 → 64 KB, ~0.41% error
- **Higher precision = Lower error**

### Count-Min Sketch
- ε = 0.01, δ = 0.01 → ~2.2 KB
- ε = 0.001, δ = 0.01 → ~22 KB
- ε = 0.0001, δ = 0.01 → ~220 KB
- **Lower ε = Better accuracy, More memory**

## When to Use Probabilistic Algorithms

✅ **Use when:**
- Dataset is too large for exact algorithms
- Approximate results are acceptable
- Real-time processing is required
- Memory/space is constrained

❌ **Don't use when:**
- Exact results are mandatory
- Dataset is small enough for exact methods
- False positives are unacceptable (use CMS/HLL)

## Performance Benefits

```
Exact Counting: O(n) space per unique item
HyperLogLog: O(2^14) = 16KB for billions of items

Exact Set: 1 billion items = 8GB RAM
Bloom Filter: 1 billion items = 12MB RAM (1% FPR)
```

## Real-World Applications

1. **Web Analytics** (HyperLogLog)
   - Count unique visitors across billions of page views
   - Memory: 16KB per metric

2. **Spam Detection** (Bloom Filter)
   - Check emails against known spam database
   - Memory: ~12MB for 1M spam signatures

3. **Trending Topics** (Count-Min Sketch)
   - Track hashtag frequencies in real-time
   - Memory: ~22KB for 0.1% accuracy

4. **Sample Analysis** (Reservoir Sampling)
   - Maintain representative samples from streams
   - Memory: Fixed at sample size

## Key Takeaways

1. **Trade accuracy for space**: 100-1000x memory reduction
2. **Controlled error bounds**: Know your accuracy guarantees
3. **Fast operations**: O(1) or O(log n) for most operations
4. **Mergeable**: Combine results from multiple sources
5. **Streaming-friendly**: Process data in one pass

## Related Chapters

- **Chapter 33**: String Algorithms Deep Dive
- **Chapter 36**: Stream Processing Algorithms
- **Chapter 26**: Approximate Algorithms

## Further Reading

- [Bloom Filters by Example](https://llimllib.github.io/bloomfilter-tutorial/)
- [HyperLogLog in Practice](https://research.neustar.biz/2012/10/25/sketch-of-the-day-hyperloglog-cornerstone-of-a-big-data-infrastructure/)
- [Count-Min Sketch](https://dimacs.rutgers.edu/~graham/pubs/papers/cm-full.pdf)
- [Reservoir Sampling](https://en.wikipedia.org/wiki/Reservoir_sampling)
