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

## PHP 8.1+ Performance Features

### Enums (More Efficient than Constants)

```php
// ❌ OLD: Class constants
class Status {
    const PENDING = 'pending';
    const ACTIVE = 'active';
    const COMPLETED = 'completed';
}

// ✅ NEW: Native enums (PHP 8.1+)
enum Status: string {
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}

// Benefits: Type safety, better performance, less memory
```

### Readonly Properties (Reduced Memory)

```php
// PHP 8.1+: readonly properties prevent accidental modification
class User {
    public function __construct(
        public readonly int $id,
        public readonly string $email,
    ) {}
}

// Benefits: Immutability, potential optimization by PHP engine
```

### First-class Callable Syntax

```php
// ❌ OLD: Slower closure creation
$mapper = function($x) { return $x * 2; };
array_map($mapper, $array);

// ✅ NEW: First-class callable (PHP 8.1+)
$mapper = $this->double(...);
array_map($mapper, $array);

// Benefits: Slightly faster, cleaner syntax
```

### Array Unpacking with String Keys (PHP 8.1+)

```php
// PHP 8.1+: More efficient array merging
$defaults = ['timeout' => 30, 'retries' => 3];
$custom = ['timeout' => 60];

// Efficient merge
$config = [...$defaults, ...$custom];
```

## PHP 8.2+ Performance Features

### Readonly Classes

```php
// PHP 8.2+: Entire class readonly
readonly class Configuration {
    public function __construct(
        public string $apiKey,
        public int $timeout,
        public bool $debug,
    ) {}
}

// Benefits: All properties automatically readonly, better optimization
```

### Standalone Null and False Types

```php
// PHP 8.2+: More precise type hints = better JIT optimization
function findUser(int $id): User|null {
    return $db->find($id);
}

function validateEmail(string $email): bool|string {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ?: 'Invalid';
}
```

### Disjunctive Normal Form (DNF) Types

```php
// PHP 8.2+: Complex type unions
function process((A&B)|null $value): void {
    // More precise types = better optimization
}
```

## PHP 8.3+ Performance Features

### Typed Class Constants

```php
// PHP 8.3+: Type-safe constants
class Math {
    public const float PI = 3.14159;
    public const int MAX_SIZE = 1000;
}

// Benefits: JIT can optimize better with known types
```

### Dynamic Class Constant Fetch

```php
// PHP 8.3+: Dynamic constant access
$constantName = 'MAX_SIZE';
$value = Math::{$constantName};

// More flexible without performance penalty
```

### `json_validate()` Function

```php
// PHP 8.3+: Fast JSON validation without decoding
// ❌ OLD: Decode to validate (slow, uses memory)
$isValid = json_decode($json) !== null;

// ✅ NEW: Validate without decoding
$isValid = json_validate($json);

// Much faster for large JSON strings!
```

### Override Attribute

```php
// PHP 8.3+: Catch method signature mistakes at compile time
class Child extends Parent {
    #[Override]
    public function process(): void {
        // Compile-time check = catch errors early
    }
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

## Framework-Specific Optimizations

### Laravel Performance Tips

**Config Caching**:
```bash
# Cache all config files into single file
php artisan config:cache

# Clear config cache
php artisan config:clear
```

**Route Caching**:
```bash
# Cache routes for faster lookup
php artisan route:cache

# Clear route cache
php artisan route:clear
```

**View Compilation**:
```bash
# Precompile all Blade templates
php artisan view:cache

# Clear view cache
php artisan view:clear
```

**Optimize Autoloader**:
```bash
# Production optimization
composer install --optimize-autoloader --no-dev
php artisan optimize
```

**Database Query Optimization**:
```php
// ❌ N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->bio;  // Query per user
}

// ✅ Eager loading
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->bio;  // No extra queries
}

// ✅ Lazy eager loading (when needed)
$users = User::all();
if ($needProfiles) {
    $users->load('profile');
}
```

**Queue Jobs for Heavy Operations**:
```php
// ❌ Slow: Process in web request
public function store(Request $request) {
    $this->processImages($request->files);
    $this->sendNotifications($request->users);
    return response()->json(['status' => 'success']);
}

// ✅ Fast: Queue heavy work
public function store(Request $request) {
    ProcessImages::dispatch($request->files);
    SendNotifications::dispatch($request->users);
    return response()->json(['status' => 'processing']);
}
```

**Use Chunk for Large Datasets**:
```php
// ❌ Memory intensive
$users = User::all();  // Loads all into memory

// ✅ Memory efficient
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        process($user);
    }
});

// ✅ Even better: lazy collections (Laravel 6+)
User::cursor()->each(function ($user) {
    process($user);
});
```

### Symfony Performance Tips

**Preload Classes** (PHP 7.4+):
```php
// config/preload.php
<?php
if (file_exists(__DIR__.'/../var/cache/prod/App_KernelProdContainer.preload.php')) {
    require __DIR__.'/../var/cache/prod/App_KernelProdContainer.preload.php';
}
```

**Optimize Autoloader**:
```bash
composer dump-autoload --optimize --classmap-authoritative
```

**Doctrine Optimization**:
```php
// ❌ N+1 queries
$articles = $articleRepository->findAll();
foreach ($articles as $article) {
    echo $article->getAuthor()->getName();
}

// ✅ JOIN fetch
$query = $em->createQuery('
    SELECT a, author
    FROM App\Entity\Article a
    JOIN a.author author
');
$articles = $query->getResult();
```

**Use APCu for Cache**:
```yaml
# config/packages/cache.yaml
framework:
    cache:
        app: cache.adapter.apcu
        default_redis_provider: redis://localhost
```

**Production Mode**:
```bash
# Set environment
export APP_ENV=prod

# Clear and warm cache
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod
```

## Monitoring and Observability

### Application Performance Monitoring (APM)

**New Relic Integration**:
```php
// Install extension
// sudo apt-get install newrelic-php5

// Manual instrumentation
if (extension_loaded('newrelic')) {
    newrelic_name_transaction('api/users/search');

    // Custom metrics
    newrelic_custom_metric('Custom/ProcessingTime', $duration);

    // Custom events
    newrelic_record_custom_event('UserActivity', [
        'user_id' => $userId,
        'action' => 'purchase',
        'amount' => $amount
    ]);
}
```

**Datadog APM**:
```php
// Install: composer require datadog/dd-trace

// Auto-instrumentation (via extension)
// Most PHP frameworks supported automatically

// Manual tracing
use DDTrace\GlobalTracer;

$tracer = GlobalTracer::get();
$span = $tracer->startActiveSpan('process.payment');

try {
    processPayment($data);
} finally {
    $span->getSpan()->setTag('user.id', $userId);
    $span->getSpan()->setTag('amount', $amount);
    $span->close();
}
```

**Blackfire Integration**:
```php
// Install probe + client

// Automatic profiling trigger
if (isset($_SERVER['HTTP_X_BLACKFIRE_QUERY'])) {
    // Blackfire is profiling this request
}

// Manual profiling in code
$probe = \BlackfireProbe::getMainInstance();
$probe->enable();

expensiveOperation();

$probe->close();

// CLI profiling
// blackfire run php script.php
```

**Tideways Integration**:
```php
// Install extension

// Manual profiling
\Tideways\Profiler::start([
    'api_key' => 'your-api-key',
]);

// Add custom metrics
\Tideways\Profiler::setCustomVariable('user_id', $userId);
\Tideways\Profiler::setCustomVariable('cache_hit', $cacheHit);

// Stop profiling
\Tideways\Profiler::stop();
```

### Custom Metrics and Logging

**Performance Metrics Collection**:
```php
class PerformanceMonitor {
    private array $metrics = [];

    public function startTimer(string $name): void {
        $this->metrics[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage()
        ];
    }

    public function endTimer(string $name): array {
        if (!isset($this->metrics[$name])) {
            return [];
        }

        $start = $this->metrics[$name];
        $result = [
            'duration' => microtime(true) - $start['start'],
            'memory' => memory_get_usage() - $start['memory_start'],
            'peak_memory' => memory_get_peak_usage()
        ];

        // Send to monitoring service
        $this->sendToMonitoring($name, $result);

        return $result;
    }

    private function sendToMonitoring(string $metric, array $data): void {
        // Send to StatsD, CloudWatch, Prometheus, etc.
        if (extension_loaded('statsd')) {
            statsd_gauge("app.{$metric}.duration", $data['duration']);
            statsd_gauge("app.{$metric}.memory", $data['memory']);
        }
    }
}

// Usage
$monitor = new PerformanceMonitor();
$monitor->startTimer('user.search');

$results = searchUsers($query);

$metrics = $monitor->endTimer('user.search');
```

**Structured Logging**:
```php
// Use Monolog with processors
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;

$log = new Logger('app');
$log->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
$log->pushProcessor(new MemoryUsageProcessor());
$log->pushProcessor(new WebProcessor());

// Log with context
$log->info('User search performed', [
    'query' => $query,
    'results' => count($results),
    'duration_ms' => $duration * 1000,
    'cache_hit' => $cacheHit
]);
```

**Health Check Endpoints**:
```php
// /health endpoint
class HealthCheckController {
    public function check(): JsonResponse {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'opcache' => $this->checkOpcache(),
            'memory' => $this->checkMemory(),
        ];

        $healthy = !in_array(false, $checks, true);

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => time()
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): bool {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkRedis(): bool {
        try {
            Redis::connection()->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkOpcache(): array {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }

        $status = opcache_get_status();
        return [
            'enabled' => true,
            'hit_rate' => round($status['opcache_statistics']['opcache_hit_rate'], 2),
            'memory_usage' => round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . 'MB'
        ];
    }

    private function checkMemory(): array {
        return [
            'current' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
            'limit' => ini_get('memory_limit')
        ];
    }
}
```

### Real-time Performance Monitoring

**Prometheus + Grafana**:
```php
// Install: composer require promphp/prometheus_client_php

use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;

$registry = new CollectorRegistry(new APC());

// Counter: Total requests
$counter = $registry->getOrRegisterCounter(
    'app',
    'requests_total',
    'Total number of requests',
    ['method', 'endpoint', 'status']
);
$counter->inc(['GET', '/api/users', '200']);

// Histogram: Response times
$histogram = $registry->getOrRegisterHistogram(
    'app',
    'request_duration_seconds',
    'Request duration in seconds',
    ['endpoint']
);
$histogram->observe($duration, ['/api/users']);

// Gauge: Current active connections
$gauge = $registry->getOrRegisterGauge(
    'app',
    'active_connections',
    'Current active connections'
);
$gauge->set(count($activeConnections));

// Metrics endpoint: /metrics
$renderer = new RenderTextFormat();
echo $renderer->render($registry->getMetricFamilySamples());
```

## Production Deployment Best Practices

### Pre-Deployment Checklist

```bash
# 1. Run tests
vendor/bin/phpunit

# 2. Static analysis
vendor/bin/phpstan analyse

# 3. Code style check
vendor/bin/php-cs-fixer fix --dry-run

# 4. Security audit
composer audit

# 5. Optimize autoloader
composer install --no-dev --optimize-autoloader --classmap-authoritative

# 6. Clear and warm caches (framework-specific)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Compile assets
npm run production

# 8. Run database migrations
php artisan migrate --force

# 9. Clear OPcache after deployment
php -r "opcache_reset();"
```

### Zero-Downtime Deployment

**Blue-Green Deployment**:
```nginx
# Use upstream switching
upstream backend {
    server backend-blue:9000;
    # server backend-green:9000;  # Switch when ready
}
```

**Graceful PHP-FPM Reload**:
```bash
# Reload PHP-FPM without dropping connections
kill -USR2 $(cat /var/run/php-fpm.pid)
```

### Docker Production Optimization

```dockerfile
# Multi-stage build
FROM composer:2 AS builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

FROM php:8.3-fpm-alpine
WORKDIR /app

# Install production extensions
RUN apk add --no-cache \
    opcache \
    && docker-php-ext-install opcache

# Copy optimized vendor
COPY --from=builder /app/vendor ./vendor
COPY . .

# Production PHP configuration
COPY php.ini-production /usr/local/etc/php/php.ini

# Precompile
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

CMD ["php-fpm"]
```

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

<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="appendix-b"
  label="PHP Performance Tips reviewed!"
/>

## Resources

- [PHP OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
- [PHP Performance Tips](https://www.php.net/manual/en/features.performance.php)
- [Blackfire Profiler](https://blackfire.io/)
- Chapter 29: Performance Optimization
- Appendix A: Complexity Cheat Sheet
