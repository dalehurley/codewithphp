# Chapter 6: Handling Large Datasets in PHP Without Running Out of Memory

This directory contains all code examples from Chapter 6 of the **Data Science for PHP Developers** series.

## 📚 What's Included

### Core Classes

- **`src/Memory/MemoryMonitor.php`**  
  Monitor and profile memory usage during data processing

- **`src/Streaming/StreamingCSVReader.php`**  
  Read CSV files of any size using generators without loading into memory

- **`src/Database/ChunkedProcessor.php`**  
  Process millions of database rows efficiently using pagination and cursors

- **`src/Pipeline/LazyPipeline.php`**  
  Build composable data processing pipelines with lazy evaluation

### Example Scripts

- **`examples/check-memory.php`**  
  Check current PHP memory configuration

- **`examples/memory-problem-demo.php`**  
  Demonstrate why naive approaches fail with large datasets

- **`examples/generate-large-csv.php`**  
  Generate sample large CSV file for testing (100k rows)

- **`examples/streaming-csv.php`**  
  Process large CSV files with constant memory usage

- **`examples/chunked-database.php`**  
  Process database results in memory-efficient chunks

- **`examples/lazy-pipeline.php`**  
  Build complex data processing pipelines with minimal memory

## 🚀 Getting Started

### Prerequisites

- PHP 8.4 or higher
- Composer
- At least 128MB memory limit (default)

### Installation

```bash
# Navigate to this directory
cd code/data-science-php-developers/chapter-06

# Install dependencies
composer install
```

### Running Examples

**1. Check Memory Configuration**

```bash
php examples/check-memory.php
```

**2. Generate Sample Data**

```bash
php examples/generate-large-csv.php
```

This creates a 100,000-row CSV file (~5-10MB) for testing.

**3. Streaming CSV Processing**

```bash
php examples/streaming-csv.php
```

**Expected Output:**
- Counts 100k rows with ~2MB memory
- Filters high earners with constant memory
- Calculates statistics while streaming
- Processes in chunks efficiently

**4. Database Chunking**

```bash
php examples/chunked-database.php
```

**Expected Output:**
- Inserts 50k records
- Processes in 1000-row chunks
- Aggregates with cursors
- All with minimal memory usage

**5. Lazy Pipelines**

```bash
php examples/lazy-pipeline.php
```

**Expected Output:**
- Chains filter, map, reduce operations
- Processes data lazily
- Calculates complex aggregations
- Memory stays constant

## 📖 Key Concepts

### Generators

PHP generators use `yield` to produce values one at a time:

```php
function largeDataset(): Generator {
    for ($i = 0; $i < 1000000; $i++) {
        yield $i; // Memory stays constant!
    }
}
```

**Benefits:**
- Constant memory usage
- Lazy evaluation
- Infinite sequences possible

### Streaming

Read files line-by-line instead of loading entirely:

```php
// ❌ BAD: Loads entire file
$lines = file('huge.csv');

// ✅ GOOD: Streams one line at a time
$handle = fopen('huge.csv', 'r');
while (($line = fgetcsv($handle)) !== false) {
    processLine($line);
}
fclose($handle);
```

### Chunking

Process data in batches:

```php
// Process 1000 rows at a time
$offset = 0;
$chunkSize = 1000;

while (true) {
    $rows = $pdo->query("SELECT * FROM users LIMIT {$chunkSize} OFFSET {$offset}")->fetchAll();
    
    if (empty($rows)) {
        break;
    }
    
    processChunk($rows);
    $offset += $chunkSize;
    unset($rows); // Free memory
}
```

### Lazy Evaluation

Chain operations without executing until needed:

```php
$result = LazyPipeline::from($dataSource)
    ->filter(fn($x) => $x > 100)
    ->map(fn($x) => $x * 2)
    ->take(10)
    ->toArray(); // Only now does execution happen
```

## 🎯 Performance Comparison

| Approach | Memory Usage | Speed | Scalability |
|----------|--------------|-------|-------------|
| Load All | High (grows with data) | Fast | ❌ Limited by RAM |
| Generators | Constant (~2MB) | Medium | ✅ Unlimited |
| Chunking | Low (chunk size) | Medium | ✅ Unlimited |
| Streaming | Constant (~2MB) | Fast | ✅ Unlimited |

## 🔧 Usage in Your Projects

### Stream Any CSV File

```php
use DataScience\Streaming\StreamingCSVReader;

$reader = new StreamingCSVReader();

// Count rows (any file size)
$count = $reader->count('huge-file.csv');

// Filter while streaming
foreach ($reader->filter('data.csv', fn($row) => $row['active'] === '1') as $row) {
    echo $row['name'] . "\n";
}

// Calculate statistics
$stats = $reader->calculateStats('sales.csv', 'revenue');
echo "Average: {$stats['mean']}\n";
```

### Process Database in Chunks

```php
use DataScience\Database\ChunkedProcessor;

$processor = new ChunkedProcessor($pdo);

// Process 1000 rows at a time
foreach ($processor->processInChunks('users', chunkSize: 1000) as $chunk) {
    foreach ($chunk as $user) {
        processUser($user);
    }
}

// Or use cursor for one-at-a-time
foreach ($processor->processWithCursor('SELECT * FROM orders') as $order) {
    processOrder($order);
}
```

### Build Data Pipelines

```php
use DataScience\Pipeline\LazyPipeline;

$result = LazyPipeline::from($dataSource)
    ->filter(fn($user) => $user['age'] > 18)
    ->map(fn($user) => [
        'name' => $user['name'],
        'email' => $user['email'],
    ])
    ->take(100)
    ->toArray();
```

## 🐛 Troubleshooting

### Error: "Allowed memory size exhausted"

**Cause:** Loading too much data into memory at once.

**Solution:** Use generators or chunking:

```php
// ❌ BAD
$data = file('huge.csv');

// ✅ GOOD
foreach ($reader->readFile('huge.csv') as $row) {
    // Process one row at a time
}
```

### Problem: Slow performance with large offsets

**Cause:** `OFFSET` requires scanning all previous rows.

**Solution:** Use keyset pagination:

```php
// ❌ SLOW
SELECT * FROM users ORDER BY id LIMIT 1000 OFFSET 1000000;

// ✅ FAST
SELECT * FROM users WHERE id > 1000000 ORDER BY id LIMIT 1000;
```

### Problem: Generator is slower than array

**Cause:** Generators have slight overhead.

**Solution:** This is expected—you're trading speed for memory. For small datasets (<10k rows), arrays are fine. For large datasets, generators are necessary.

## 📚 Related Chapters

- **Chapter 3**: Data Collection (get the data)
- **Chapter 4**: Data Cleaning (prepare the data)
- **Chapter 5**: EDA (understand the data)
- **Chapter 6**: Large Datasets (scale the processing) ← You are here
- **Chapter 7**: Statistics (analyze the data)

## 🔗 Additional Resources

- [PHP Generators Documentation](https://www.php.net/manual/en/language.generators.php)
- [League CSV Documentation](https://csv.thephpleague.com/)
- [Memory Management in PHP](https://www.php.net/manual/en/features.gc.php)
- [External Sorting Algorithms](https://en.wikipedia.org/wiki/External_sorting)

## 📝 License

This code is part of the **Code with PHP** tutorial series and is provided for educational purposes.

---

**Need help?** Refer to the full chapter at [codewithphp.com/series/data-science-php-developers/chapters/06-handling-large-datasets-in-php-without-running-out-of-memory](https://codewithphp.com/series/data-science-php-developers/chapters/06-handling-large-datasets-in-php-without-running-out-of-memory)


