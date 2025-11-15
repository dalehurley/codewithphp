# Chapter 33: String Algorithms Deep Dive - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 33: String Algorithms Deep Dive from the PHP Algorithms series.

## Overview

These examples demonstrate advanced string matching and manipulation algorithms used in search engines, bioinformatics, plagiarism detection, and text processing.

## Code Samples

### 1. Aho-Corasick Algorithm (`01-aho-corasick.php`)

Multi-pattern string matching algorithm for finding multiple patterns simultaneously.

**Key Concepts:**
- Trie-based automaton
- Failure links for efficient transitions
- Multi-pattern matching in O(n + m + z) time
- Pattern replacement and counting

**Usage:**
```bash
php 01-aho-corasick.php
```

**Features:**
- Basic multi-pattern search
- Content filtering (profanity/spam detection)
- Pattern replacement
- DNA sequence matching
- Performance comparison with naive approach

**Time Complexity:**
- Build: O(m) where m is total pattern length
- Search: O(n + z) where n is text length, z is matches

**Use Cases:**
- Content filtering and moderation
- Virus/malware scanning
- Plagiarism detection
- Log analysis
- DNA sequence analysis

**Performance:**
- 100 patterns in 10KB text
- Aho-Corasick: ~5ms
- Naive (100 × strpos): ~50ms
- **10x speedup**

---

### 2. Suffix Array (`02-suffix-array.php`)

Sorted array of all suffixes enabling fast substring operations.

**Key Concepts:**
- Suffix array construction
- LCP (Longest Common Prefix) array
- Pattern matching in O(m log n)
- Longest repeated/common substring

**Usage:**
```bash
php 02-suffix-array.php
```

**Features:**
- Basic suffix array and LCP computation
- Pattern searching with binary search
- Longest repeated substring finder
- Longest common substring between two strings
- Document similarity calculator
- Distinct substring counter
- Palindrome detection

**Time Complexity:**
- Build: O(n log n) with comparison sort
- Search: O(m log n + occ) occurrences
- LRS/LCS: O(n) with suffix array

**Use Cases:**
- Text indexing for search engines
- Data compression (finding repeated patterns)
- Bioinformatics (genome analysis)
- Plagiarism detection
- Document similarity

**Applications:**
- Find `"banana"` → `"ana"` repeats at positions [1, 3]
- LCS of `"algorithm"` and `"logarithm"` → `"logar"`
- Count distinct substrings efficiently

---

### 3. Advanced String Matching (`03-advanced-string-matching.php`)

Collection of advanced pattern matching algorithms.

**Key Concepts:**
- Z-Algorithm for linear-time matching
- Rabin-Karp with rolling hash
- Manacher's algorithm for palindromes
- KMP (Knuth-Morris-Pratt) algorithm

**Usage:**
```bash
php 03-advanced-string-matching.php
```

**Features:**

#### Z-Algorithm
- Compute Z-array in O(n)
- Pattern matching with preprocessing
- Repeated pattern detection

#### Rabin-Karp
- Rolling hash for pattern matching
- Multi-pattern search
- O(n + m) average case

#### Manacher's Algorithm
- Find longest palindrome in O(n)
- Find all palindromic substrings
- Linear-time palindrome detection

#### KMP Algorithm
- Build prefix function
- Pattern matching without backtracking
- O(n + m) guaranteed

**Time Complexities:**
- Z-Algorithm: O(n + m)
- Rabin-Karp: O(n + m) average, O(nm) worst
- Manacher: O(n)
- KMP: O(n + m)

**Use Cases:**
- Pattern matching in large texts
- DNA sequence analysis
- Palindrome detection
- Repeated pattern finding
- Text search and replace

---

## Requirements

- PHP 8.0 or higher
- No external dependencies required!

All examples use pure PHP implementations with modern PHP 8.0+ syntax.

## Running All Examples

```bash
php 01-aho-corasick.php
php 02-suffix-array.php
php 03-advanced-string-matching.php
```

## Algorithm Comparison

| Algorithm | Time | Space | Best For |
|-----------|------|-------|----------|
| Aho-Corasick | O(n+m+z) | O(m×σ) | Multiple patterns |
| Suffix Array | O(n log n) | O(n) | Repeated queries |
| Z-Algorithm | O(n+m) | O(n+m) | Single pattern |
| Rabin-Karp | O(n+m)* | O(1) | Multiple patterns |
| Manacher | O(n) | O(n) | Palindromes |
| KMP | O(n+m) | O(m) | Single pattern |

*Average case; worst case is O(nm)

## Performance Comparisons

### Multi-Pattern Matching (100 patterns)
```
Sequential (100 × strpos): 50ms
Aho-Corasick: 5ms
Speedup: 10x
```

### Pattern Search (10KB text)
```
Native strpos: 0.05ms
Z-Algorithm: 0.08ms
Rabin-Karp: 0.10ms
KMP: 0.07ms
```

### Palindrome Detection
```
Naive O(n³): 1000ms
Manacher O(n): 5ms
Speedup: 200x
```

## Real-World Applications

### 1. Content Filtering (Aho-Corasick)
```php
$filter = new ContentFilter();
$filter->loadProfanityList(['bad', 'worse']);
$filter->loadSpamPatterns(['click here', 'buy now']);

$analysis = $filter->analyze($userContent);
// Returns: is_clean, violations, counts
```

### 2. Plagiarism Detection (Suffix Array)
```php
$doc1SA = new SuffixArray($document1);
$doc2SA = new SuffixArray($document2);

$lcs = LongestCommonSubstring::find($document1, $document2);
// Find common passages
```

### 3. DNA Analysis (Multiple Algorithms)
```php
$dna = new DNASequenceMatcher();
$dna->addSequence('ATCG', 'gene1');
$dna->addSequence('GCTA', 'gene2');

$matches = $dna->findSequences($strand);
// Find genetic markers
```

### 4. Document Similarity
```php
$similarity = DocumentSimilarity::calculate($doc1, $doc2);
// Returns: LCS, Jaccard similarity, overlap coefficient
```

## Key Concepts Explained

### Aho-Corasick Automaton
```
Patterns: ["he", "she", "his", "hers"]
Text: "she sells his hers"

Builds trie + failure links
Matches all patterns in one pass
```

### Suffix Array
```
Text: "banana"
Suffixes (sorted):
0: $
1: a$
2: ana$
3: anana$
4: banana$
5: na$
6: nana$

Binary search for pattern matching
```

### Z-Algorithm
```
Text: "aabcaabxaaz"
Z-array: [11, 1, 0, 0, 3, 1, 0, 0, 2, 1, 0]

Z[i] = length of longest substring starting at i
       that matches prefix
```

### Manacher's Algorithm
```
Text: "babad"
Transform: "^#b#a#b#a#d#$"
Find palindrome radii in O(n)
Result: "bab" or "aba"
```

## Common Use Cases

### Search Engines
- Aho-Corasick: Index keywords
- Suffix Array: Full-text search
- Fast substring queries

### Bioinformatics
- Aho-Corasick: Find DNA motifs
- Suffix Array: Genome assembly
- Pattern matching in sequences

### Text Editors
- KMP: Find/replace
- Suffix Array: Autocomplete
- Manacher: Palindrome highlighting

### Security
- Aho-Corasick: Virus signatures
- Content filtering: Block patterns
- Intrusion detection

## Best Practices

1. **Choose the Right Algorithm**
   - Single pattern → KMP or Z-Algorithm
   - Multiple patterns → Aho-Corasick
   - Repeated queries → Suffix Array

2. **Preprocessing vs Query Time**
   - Suffix Array: High preprocessing, fast queries
   - Aho-Corasick: Moderate preprocessing, fast multi-search
   - KMP: Low preprocessing, good for single searches

3. **Memory Considerations**
   - Suffix Array: O(n) space
   - Aho-Corasick: O(m × alphabet_size)
   - Consider space-time trade-offs

4. **Unicode Support**
   - Use `mb_*` functions for UTF-8
   - Consider grapheme clusters
   - Normalize input when needed

## Performance Tips

1. **For large texts**: Build suffix array once, query multiple times
2. **For multiple patterns**: Use Aho-Corasick
3. **For simple searches**: Native PHP functions are optimized
4. **For DNA sequences**: Use 4-character alphabet optimization
5. **For real-time**: Pre-build automata/suffix arrays

## Related Chapters

- **Chapter 17**: Advanced Sorting (Suffix sorting)
- **Chapter 23**: Dynamic Programming (Edit distance)
- **Chapter 26**: Approximate Algorithms (Fuzzy matching)
- **Chapter 32**: Probabilistic Algorithms (Bloom filters)

## Further Reading

- [Aho-Corasick Paper](https://dl.acm.org/doi/10.1145/360825.360855)
- [Suffix Arrays Tutorial](https://web.stanford.edu/class/cs97si/suffix-array.pdf)
- [Manacher's Algorithm](https://en.wikipedia.org/wiki/Longest_palindromic_substring)
- [String Matching Algorithms](https://www-igm.univ-mlv.fr/~lecroq/string/)

## Testing

Each file includes comprehensive test cases demonstrating:
- Basic functionality
- Edge cases
- Performance benchmarks
- Real-world applications
- Algorithm comparisons

Run any file directly to see demonstrations and benchmarks.
