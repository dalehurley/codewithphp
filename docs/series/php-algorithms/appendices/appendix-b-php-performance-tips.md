# Appendix B: PHP Performance Tips

A comprehensive guide to optimizing PHP code and algorithms for production environments.

## General Performance Principles

### 1. Choose the Right Data Structure

```php
// ❌ BAD: Using in_array() in loops (O(n²))
$items = [1, 2, 3, 4, 5];
foreach ($data as $value) {
    if (in_array($value, $items)) {  // O(n) each iteration
        // ...
    }
}

// ✅ GOOD: Use associative array (O(n))
$items = [1 => true, 2 => true, 3 => true, 4 => true, 5 => true];
foreach ($data as $value) {
    if (isset($items[$value])) {  // O(1) each iteration
        // ...
    }
}
```

### 2. Avoid Repeated Function Calls

```php
// ❌ BAD: Calling count() in loop condition
for ($i = 0; $i < count($array); $i++) {
    // ...
}

// ✅ GOOD: Cache the count
$length = count($array);
for ($i = 0; $i < $length; $i++) {
    // ...
}

// ✅ BETTER: Use foreach when possible
foreach ($array as $item) {
    // ...
}
```

### 3. Use Native Functions

```php
// ❌ SLOW: Manual implementation
function arraySum(array $arr): int {
    $sum = 0;
    foreach ($arr as $value) {
        $sum += $value;
    }
    return $sum;
}

// ✅ FAST: Native function (optimized in C)
$sum = array_sum($arr);
```

## Array Operations

### Array Access Patterns

```php
// Fast: Direct key access
$value = $array[$key];  // O(1)

// Fast: isset() for checking existence
if (isset($array[$key])) { }  // O(1)

// Slow: in_array() for large arrays
if (in_array($value, $array)) { }  // O(n)

// Fast alternative: Flip array for membership testing
$flipped = array_flip($original);
if (isset($flipped[$value])) { }  // O(1)
```

### Array Building

```php
// ❌ SLOW: String concatenation in loop
$result = '';
foreach ($items as $item) {
    $result .= $item;  // Creates new string each time
}

// ✅ FAST: Use array + implode
$parts = [];
foreach ($items as $item) {
    $parts[] = $item;
}
$result = implode('', $parts);

// ✅ FASTEST: array_map + implode
$result = implode('', array_map($callback, $items));
```

### Array Filtering

```php
// Multiple array operations
$filtered = array_filter($array, $callback);
$mapped = array_map($transform, $filtered);
$result = array_values($mapped);

// ✅ BETTER: Single pass with foreach
$result = [];
foreach ($array as $item) {
    if ($callback($item)) {
        $result[] = $transform($item);
    }
}
```

## String Operations

### String Concatenation

```php
// ❌ SLOW: Repeated concatenation
$html = '';
$html .= '<div>';
$html .= '<p>' . $content . '</p>';
$html .= '</div>';

// ✅ FAST: Single concatenation
$html = '<div><p>' . $content . '</p></div>';

// ✅ ALTERNATIVE: Array + implode for many pieces
$parts = ['<div>', '<p>', $content, '</p>', '</div>'];
$html = implode('', $parts);
```

### String Searching

```php
// Fast: strpos() for substring check
if (strpos($haystack, $needle) !== false) { }

// Fast: str_starts_with() (PHP 8.0+)
if (str_starts_with($string, $prefix)) { }

// Fast: str_ends_with() (PHP 8.0+)
if (str_ends_with($string, $suffix)) { }

// Slower: preg_match() (only use when needed)
if (preg_match('/pattern/', $string)) { }
```

### String Replacement

```php
// Fast: str_replace() for simple replacements
$result = str_replace('old', 'new', $string);

// Multiple replacements
$result = str_replace(['old1', 'old2'], ['new1', 'new2'], $string);

// Only use regex when necessary
$result = preg_replace('/pattern/', 'replacement', $string);
```

## Loop Optimization

### Loop Types

```php
// Fastest: foreach with value
foreach ($array as $value) {
    // Direct access
}

// Fast: foreach with key and value
foreach ($array as $key => $value) {
    // ...
}

// Slower: for loop with count caching
$count = count($array);
for ($i = 0; $i < $count; $i++) {
    $value = $array[$i];
}

// Slowest: for loop without caching
for ($i = 0; $i < count($array); $i++) {  // count() called each iteration
    $value = $array[$i];
}
```

### Early Exit

```php
// ✅ GOOD: Break early
foreach ($items as $item) {
    if ($item === $target) {
        return $item;  // Exit immediately
    }
}

// ✅ GOOD: Continue to skip
foreach ($items as $item) {
    if ($item < 0) {
        continue;  // Skip to next iteration
    }
    process($item);
}
```

## Function Call Overhead

### Reduce Function Calls

```php
// ❌ SLOW: Multiple calls
$x = abs($value);
$y = abs($value);  // Called twice

// ✅ FAST: Cache result
$absolute = abs($value);
$x = $absolute;
$y = $absolute;
```

### Inline Simple Operations

```php
// Overhead: Function call
function isEven($n) {
    return $n % 2 === 0;
}
if (isEven($num)) { }

// ✅ FASTER: Inline for simple operations
if ($num % 2 === 0) { }
```

### Use Static Methods for Utilities

```php
// Slightly slower: Instance method
class Math {
    public function add($a, $b) {
        return $a + $b;
    }
}
$math = new Math();
$result = $math->add(1, 2);

// Slightly faster: Static method (no object creation)
class Math {
    public static function add($a, $b) {
        return $a + $b;
    }
}
$result = Math::add(1, 2);
```

## Memory Management

### Unset Large Variables

```php
function processLargeData() {
    $largeArray = fetchBigDataset();  // Uses lots of memory

    $result = processData($largeArray);

    unset($largeArray);  // Free memory immediately

    return $result;
}
```

### Use Generators for Large Datasets

```php
// ❌ MEMORY INTENSIVE: Load all at once
function getAllRecords(): array {
    $records = [];
    $result = mysql_query("SELECT * FROM large_table");
    while ($row = mysql_fetch_assoc($result)) {
        $records[] = $row;
    }
    return $records;  // All in memory
}

// ✅ MEMORY EFFICIENT: Use generator
function getAllRecords(): Generator {
    $result = mysql_query("SELECT * FROM large_table");
    while ($row = mysql_fetch_assoc($result)) {
        yield $row;  // One at a time
    }
}

// Usage remains similar
foreach (getAllRecords() as $record) {
    process($record);
}
```

### Reference vs Copy

```php
// Copy: Uses more memory
function processArray(array $data) {
    // Entire array copied
    return array_map($callback, $data);
}

// Reference: More memory efficient
function processArray(array &$data) {
    // No copy, modifies original
    array_walk($data, $callback);
}

// For read-only, copy is usually fine
// For large arrays that will be modified, consider references
```

## Database Optimization

### Query Optimization

```php
// ❌ BAD: N+1 queries
$users = $db->query("SELECT * FROM users");
foreach ($users as $user) {
    $posts = $db->query("SELECT * FROM posts WHERE user_id = {$user['id']}");
}

// ✅ GOOD: Single query with JOIN
$result = $db->query("
    SELECT users.*, posts.*
    FROM users
    LEFT JOIN posts ON users.id = posts.user_id
");
```

### Prepared Statements

```php
// ❌ SLOW: Prepare each time
foreach ($users as $user) {
    $stmt = $db->prepare("INSERT INTO log (user_id, action) VALUES (?, ?)");
    $stmt->execute([$user['id'], 'login']);
}

// ✅ FAST: Prepare once, execute multiple times
$stmt = $db->prepare("INSERT INTO log (user_id, action) VALUES (?, ?)");
foreach ($users as $user) {
    $stmt->execute([$user['id'], 'login']);
}
```

### Batch Operations

```php
// ❌ SLOW: Individual inserts
foreach ($records as $record) {
    $db->exec("INSERT INTO table (col1, col2) VALUES ('{$record[0]}', '{$record[1]}')");
}

// ✅ FAST: Batch insert
$values = [];
foreach ($records as $record) {
    $values[] = "('{$record[0]}', '{$record[1]}')";
}
$db->exec("INSERT INTO table (col1, col2) VALUES " . implode(', ', $values));
```

## OPcache Configuration

### Recommended php.ini Settings

```ini
; Enable OPcache
opcache.enable=1

; Memory allocation
opcache.memory_consumption=256  ; MB (adjust based on app size)
opcache.interned_strings_buffer=16  ; MB

; Performance
opcache.max_accelerated_files=20000  ; Adjust to number of PHP files
opcache.validate_timestamps=0  ; Production: disable file checking
opcache.revalidate_freq=0  ; Only relevant if validate_timestamps=1

; Optimization
opcache.optimization_level=0x7FFFBFFF  ; All optimizations
opcache.enable_file_override=1  ; Cache file_exists(), is_file()

; Development vs Production
opcache.validate_timestamps=1  ; Development: check file changes
opcache.validate_timestamps=0  ; Production: maximum performance
```

### Cache Invalidation

```bash
# Clear OPcache (production deployment)
php -r "opcache_reset();"

# Or via web endpoint (secure it!)
<?php
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    opcache_reset();
    echo "OPcache cleared";
}
```

### Monitor OPcache

```php
// Check OPcache status
$status = opcache_get_status();

echo "Memory usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
echo "Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
echo "Cached files: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";

// Warnings
if ($status['opcache_statistics']['opcache_hit_rate'] < 95) {
    echo "WARNING: Low hit rate - consider increasing memory\n";
}

if ($status['memory_usage']['current_wasted_percentage'] > 10) {
    echo "WARNING: High wasted memory - consider opcache_reset()\n";
}
```

## Autoloading

### Use Composer's Optimized Autoloader

```bash
# Development
composer dump-autoload

# Production: Optimized with class map
composer dump-autoload --optimize --no-dev

# Production: APCu for even faster lookup
composer dump-autoload --optimize --apcu --no-dev
```

### Class Loading Strategy

```php
// ❌ SLOW: Multiple require_once
require_once 'class1.php';
require_once 'class2.php';
require_once 'class3.php';

// ✅ FAST: Autoloading (only load when needed)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
```

## Caching Strategies

### Object Caching

```php
class Cache {
    private static array $cache = [];

    public static function remember(string $key, callable $callback, int $ttl = 3600) {
        if (isset(self::$cache[$key])) {
            if (self::$cache[$key]['expires'] > time()) {
                return self::$cache[$key]['value'];
            }
        }

        $value = $callback();
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return $value;
    }
}

// Usage
$expensiveResult = Cache::remember('key', function() {
    return expensiveOperation();
}, 3600);
```

### APCu for Persistent Cache

```php
// Check if APCu is available
if (function_exists('apcu_fetch')) {
    // Try to fetch from cache
    $data = apcu_fetch('my_key', $success);

    if (!$success) {
        // Not in cache, compute and store
        $data = expensiveOperation();
        apcu_store('my_key', $data, 3600);  // TTL: 3600 seconds
    }
} else {
    $data = expensiveOperation();
}
```

### Precompute and Cache

```php
// ❌ SLOW: Compute on every request
function getPopularPosts() {
    return $db->query("
        SELECT posts.*, COUNT(likes.id) as like_count
        FROM posts
        LEFT JOIN likes ON posts.id = likes.post_id
        GROUP BY posts.id
        ORDER BY like_count DESC
        LIMIT 10
    ");
}

// ✅ FAST: Precompute and cache
function getPopularPosts() {
    $cached = apcu_fetch('popular_posts', $success);
    if ($success) {
        return $cached;
    }

    $posts = $db->query("...");  // Expensive query
    apcu_store('popular_posts', $posts, 300);  // Cache 5 minutes

    return $posts;
}
```

## JIT Compiler (PHP 8.0+)

### Enable JIT

```ini
; php.ini
opcache.enable=1
opcache.jit_buffer_size=100M  ; Allocate memory for JIT
opcache.jit=1255  ; JIT mode (recommended for most apps)
```

### JIT Modes

| Mode | Description | Use Case |
|------|-------------|----------|
| 0 | Disabled | Default |
| 1201 | Tracing JIT (minimal) | CPU-intensive, few functions |
| 1255 | Tracing JIT (recommended) | General applications |
| 1275 | Tracing JIT (aggressive) | Maximum optimization |

### When JIT Helps

```php
// ✅ JIT BENEFITS: CPU-intensive calculations
function fibonacci($n) {
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2);
}

// ❌ JIT NO BENEFIT: I/O-bound operations
function fetchFromDatabase() {
    return $db->query("SELECT * FROM users");  // I/O is bottleneck
}
```

## Profiling and Monitoring

### Measure Execution Time

```php
// Simple timing
$start = microtime(true);
expensiveOperation();
$duration = microtime(true) - $start;
echo "Duration: " . number_format($duration * 1000, 2) . " ms\n";
```

### Memory Usage

```php
$before = memory_get_usage();
expensiveOperation();
$after = memory_get_usage();

echo "Memory used: " . number_format(($after - $before) / 1024, 2) . " KB\n";
echo "Peak memory: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
```

### Use Profiling Tools

**Xdebug (Development)**:
```ini
; php.ini
xdebug.mode=profile
xdebug.output_dir=/tmp/xdebug
```

**Blackfire (Production)**:
```bash
# Install Blackfire probe and CLI
blackfire run php script.php
```

**Tideways (Production)**:
- Lightweight profiling
- Minimal overhead
- Production-safe

## Production Best Practices

### 1. Disable Development Features

```ini
; php.ini (Production)
display_errors=Off
display_startup_errors=Off
error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors=On
error_log=/var/log/php_errors.log
```

### 2. Use Persistent Connections

```php
// Database: Use persistent connections
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_PERSISTENT => true
]);

// Redis: Persistent connection
$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);
```

### 3. Optimize Session Storage

```php
// ❌ SLOW: File-based sessions
ini_set('session.save_handler', 'files');

// ✅ FAST: Redis sessions
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://127.0.0.1:6379');

// ✅ ALTERNATIVE: Memcached
ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', '127.0.0.1:11211');
```

### 4. Minimize File I/O

```php
// ❌ SLOW: Read config on every request
$config = json_decode(file_get_contents('config.json'), true);

// ✅ FAST: Cache in memory
static $config = null;
if ($config === null) {
    $config = json_decode(file_get_contents('config.json'), true);
}

// ✅ BETTER: Use OPcache
// Put config in PHP file (gets opcached)
return [
    'database' => [...],
    'cache' => [...]
];
```

### 5. HTTP/2 and Asset Optimization

```apache
# Enable HTTP/2
Protocols h2 h2c http/1.1

# Enable compression
AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript

# Browser caching
<FilesMatch "\.(jpg|jpeg|png|gif|css|js)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
```

## Quick Wins Checklist

- [ ] Enable OPcache with `validate_timestamps=0` in production
- [ ] Use Composer's optimized autoloader (`--optimize --apcu`)
- [ ] Replace `in_array()` with `isset()` on associative arrays
- [ ] Cache `count()` results outside loops
- [ ] Use generators for large datasets
- [ ] Enable JIT (PHP 8.0+) for CPU-intensive code
- [ ] Use Redis/Memcached for sessions
- [ ] Batch database operations
- [ ] Use prepared statements
- [ ] Profile with Blackfire/Tideways
- [ ] Minimize file I/O
- [ ] Use persistent database connections
- [ ] Implement caching strategy (APCu, Redis)
- [ ] Disable display_errors in production
- [ ] Monitor OPcache hit rate

## Performance Testing

```php
// Benchmark template
function benchmark(callable $fn, int $iterations = 1000): array {
    $times = [];
    $memoryUsages = [];

    for ($i = 0; $i < $iterations; $i++) {
        $startTime = microtime(true);
        $startMem = memory_get_usage();

        $fn();

        $times[] = microtime(true) - $startTime;
        $memoryUsages[] = memory_get_usage() - $startMem;
    }

    return [
        'avg_time' => array_sum($times) / count($times),
        'min_time' => min($times),
        'max_time' => max($times),
        'avg_memory' => array_sum($memoryUsages) / count($memoryUsages),
        'peak_memory' => max($memoryUsages)
    ];
}

// Usage
$results = benchmark(function() {
    myAlgorithm($data);
}, 100);

print_r($results);
```

## Resources

- [PHP OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
- [PHP Performance Tips](https://www.php.net/manual/en/features.performance.php)
- [Blackfire Profiler](https://blackfire.io/)
- Chapter 29: Performance Optimization
- Appendix A: Complexity Cheat Sheet
