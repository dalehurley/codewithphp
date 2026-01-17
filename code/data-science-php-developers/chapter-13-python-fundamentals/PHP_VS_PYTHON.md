# Python vs PHP: Quick Reference for Data Science

## Core Syntax

| Feature | PHP 8.4 | Python 3.10+ |
|---------|---------|--------------|
| **Variables** | `$count = 10;` | `count = 10` |
| **Constants** | `define('MAX', 100);` | `MAX = 100` (convention) |
| **Null** | `null` | `None` |
| **Boolean** | `true`/`false` | `True`/`False` |
| **String concat** | `"Hi " . $name` | `f"Hi {name}"` |
| **Arrays** | `$arr = [1, 2, 3];` | `arr = [1, 2, 3]` |
| **Assoc arrays** | `['key' => 'val']` | `{'key': 'val'}` |
| **Functions** | `function add($a, $b) {}` | `def add(a, b):` |
| **Classes** | `class User {}` | `class User:` |
| **Type hints** | `function add(int $a): int` | `def add(a: int) -> int:` |

## Data Operations

### PHP Approach
```php
$data = [10, 20, 30, 40, 50];

// Map: multiply by 2
$doubled = array_map(fn($x) => $x * 2, $data);

// Filter: get evens
$evens = array_filter($data, fn($x) => $x % 2 === 0);

// Reduce: sum
$sum = array_reduce($data, fn($acc, $x) => $acc + $x, 0);

// Mean calculation
$mean = $sum / count($data);
```

### Python (Standard) Approach
```python
data = [10, 20, 30, 40, 50]

# Map: multiply by 2
doubled = [x * 2 for x in data]

# Filter: get evens
evens = [x for x in data if x % 2 == 0]

# Reduce: sum
sum_val = sum(data)

# Mean calculation
mean = sum(data) / len(data)
```

### Python (NumPy) Approach - **10-100x Faster!**
```python
import numpy as np

data = np.array([10, 20, 30, 40, 50])

# Map: multiply by 2 (vectorized!)
doubled = data * 2

# Filter: get evens (boolean masking!)
evens = data[data % 2 == 0]

# Sum and mean (built-in!)
sum_val = data.sum()
mean = data.mean()
```

## Why Use Python for Data Science?

### When PHP is Sufficient

✅ **Use PHP when:**
- Dataset < 10,000 rows
- Simple statistics (mean, median, mode)
- Web-based CRUD operations
- String processing and regex
- Basic data visualization for web
- Existing PHP codebase integration

### When Python Excels

🚀 **Use Python when:**
- Dataset > 100,000 rows
- Complex statistical analysis
- Machine learning / AI
- Scientific computing
- Multi-dimensional arrays
- Image/audio/NLP processing
- Research and experimentation

## Performance Comparison

| Operation | PHP (foreach loop) | Python (loop) | NumPy (vectorized) |
|-----------|-------------------|---------------|-------------------|
| Sum 1M numbers | ~80ms | ~60ms | **1.5ms** |
| Matrix multiply (1000x1000) | Minutes | Minutes | **50ms** |
| String cleaning (1M rows) | ~200ms | ~150ms (loop) | **15ms** (pandas) |

## Integration Patterns

### Pattern 1: PHP as Orchestrator

```php
// PHP: Business logic and routing
class ReportGenerator {
    public function generate(array $userData): Report {
        // 1. PHP validates and prepares data
        $validated = $this->validate($userData);
        
        // 2. Python does heavy math
        $stats = $this->callPythonAnalysis($validated);
        
        // 3. PHP formats and presents
        return $this->formatReport($stats);
    }
    
    private function callPythonAnalysis(array $data): array {
        $process = proc_open('python3 analyze.py', [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
        ], $pipes);
        
        fwrite($pipes[0], json_encode($data));
        fclose($pipes[0]);
        
        $result = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);
        
        return json_decode($result, true);
    }
}
```

### Pattern 2: Microservice Architecture

```
┌─────────┐    HTTP/JSON    ┌──────────────┐
│   PHP   │───────────────>│   Python     │
│   Web   │                 │   ML API     │
│   App   │<───────────────│  (Flask)     │
└─────────┘                 └──────────────┘
```

### Pattern 3: Async Processing

```
┌─────────┐   Queue Job   ┌────────────┐
│   PHP   │─────────────>│   Redis    │
│  Worker │               │   Queue    │
└─────────┘               └────────────┘
                                ↓
                          ┌────────────┐
                          │  Python    │
                          │  Worker    │
                          └────────────┘
```

## Cost-Benefit Analysis

### Development Cost

| Task | PHP | Python | Winner |
|------|-----|--------|--------|
| CRUD operations | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | PHP |
| Statistics | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Python |
| ML models | ⭐ | ⭐⭐⭐⭐⭐ | Python |
| Web forms | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | PHP |

### Maintenance Cost

**PHP:** Lower barrier to entry for web developers

**Python:** Requires understanding of data science concepts

**Hybrid:** Best of both worlds, but requires infrastructure

## When to Adopt Python for Data Science

### Signals it's Time to Add Python

1. ⚠️ Your PHP scripts are running out of memory
2. ⚠️ Data processing taking > 30 seconds
3. ⚠️ Need to use pre-trained ML models
4. ⚠️ Manual analysis in Excel before coding
5. ⚠️ Competitor using AI/ML features

### Red Flags (Don't Rush to Python)

1. ❌ Dataset fits comfortably in RAM
2. ❌ Simple aggregations (COUNT, SUM, AVG)
3. ❌ Team has zero Python experience
4. ❌ "Because everyone is doing AI"
5. ❌ Problem solvable with better SQL

## Learning Path

For PHP developers adding Python data science:

1. **Week 1-2:** Python syntax basics (this chapter)
2. **Week 3-4:** NumPy and pandas fundamentals (Chapter 14)
3. **Week 5-6:** Statistics and visualization (Chapters 15, 18)
4. **Month 2:** Machine learning basics (Chapter 16)
5. **Month 3+:** Deep learning and production (Chapters 17, 20)

## Common PHP Developer Mistakes in Python

### 1. Variable Names
```python
# ❌ BAD (PHP habit)
$user_name = "John"

# ✅ GOOD
user_name = "John"
```

### 2. Null Checks
```python
# ❌ BAD (won't work as expected)
if value == None:
    
# ✅ GOOD (idiomatic Python)
if value is None:
```

### 3. Array Access
```python
# ❌ BAD (0-based indexing!)
first = arr[1]  # Second element!

# ✅ GOOD
first = arr[0]
```

### 4. String Concatenation
```python
# ❌ BAD (slow for many strings)
result = ""
for item in items:
    result = result + item + ", "

# ✅ GOOD (much faster)
result = ", ".join(items)
```

### 5. Type Coercion
```python
# ❌ BAD (Python won't auto-convert)
print("Age: " + 30)  # TypeError!

# ✅ GOOD
print(f"Age: {30}")
# or
print("Age: " + str(30))
```

## Summary: The Best of Both Worlds

**PHP Strengths:**
- Mature web ecosystem (Laravel, Symfony)
- Excellent ORM and database tools
- Built-in web server
- Deployment simplicity
- Large talent pool

**Python Strengths:**
- NumPy/pandas speed
- Massive ML/AI libraries
- Scientific computing
- Research community
- Jupyter notebooks

**Ideal Architecture:**
```
[PHP Laravel App]
    ↓ HTTP
[Python Flask ML Service]
    ↓ Model inference
[TensorFlow/PyTorch]
```

**Result:** Professional web applications powered by cutting-edge AI, using the best tool for each job.
