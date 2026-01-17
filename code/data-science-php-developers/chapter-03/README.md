# Chapter 03: Data Collection - Code Examples

This directory contains the production-ready collector classes used in Chapter 03: Collecting Data (Databases, APIs, and Web Scraping).

## Directory Structure

```
chapter-03/
├── README.md (this file)
└── src/
    └── Collectors/
        ├── DatabaseCollector.php
        ├── ApiCollector.php
        └── WebScraper.php
```

## Classes

### DatabaseCollector

**Purpose:** Collect data from SQL databases with pagination and memory-efficient streaming.

**Features:**
- PDO-based with prepared statements
- Automatic pagination for large result sets
- Generator-based streaming for minimal memory usage
- Aggregate statistics without loading data
- Configurable chunk sizes

**Usage Example:**

```php
use DataScience\Collectors\DatabaseCollector;

$collector = new DatabaseCollector(
    'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
    'username',
    'password',
    1000  // Chunk size
);

// Collect all data (with automatic pagination)
$data = $collector->collect("SELECT * FROM orders WHERE status = :status", [
    'status' => 'completed'
]);

// Or use generator for large datasets
foreach ($collector->collectGenerator("SELECT * FROM orders") as $order) {
    // Process one order at a time
    processOrder($order);
}

// Get statistics without loading data
$stats = $collector->getStats('orders', 'total');
echo "Average order value: $" . number_format($stats['avg'], 2);
```

**Key Methods:**
- `collect(string $query, array $params, ?callable $callback): array` - Collect with pagination
- `collectGenerator(string $query, array $params): Generator` - Memory-efficient streaming
- `getStats(string $table, string $column): array` - Aggregate statistics
- `execute(string $query, array $params): int` - Execute INSERT/UPDATE/DELETE

### ApiCollector

**Purpose:** Consume REST APIs with authentication, rate limiting, and automatic retries.

**Features:**
- Guzzle-based HTTP client
- Automatic rate limiting between requests
- Exponential backoff retry logic
- Support for paginated endpoints
- Bearer token and custom header authentication
- Request/response logging

**Usage Example:**

```php
use DataScience\Collectors\ApiCollector;

$collector = new ApiCollector(
    'https://api.example.com',
    ['Authorization' => 'Bearer YOUR_TOKEN'],
    1000,  // 1 second delay between requests
    3      // Max 3 retries
);

// GET request
$data = $collector->get('/users', ['page' => 1, 'limit' => 100]);

// POST request
$created = $collector->post('/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Collect paginated data automatically
$allPosts = $collector->collectPaginated(
    '/posts',
    'page',      // Page parameter name
    'data',      // Data key in response
    100          // Max pages
);
```

**Key Methods:**
- `get(string $endpoint, array $params): array` - GET request
- `post(string $endpoint, array $data): array` - POST request
- `put(string $endpoint, array $data): array` - PUT request
- `delete(string $endpoint): array` - DELETE request
- `collectPaginated(...)` - Automatic pagination handling

**Error Handling:**
- Retries on 429, 500, 502, 503, 504 status codes
- Exponential backoff: 1s, 2s, 4s, 8s (capped at 10s)
- Throws `RuntimeException` on final failure

### WebScraper

**Purpose:** Ethically scrape web pages with proper delays and robots.txt compliance.

**Features:**
- Symfony DomCrawler for HTML parsing
- CSS selector-based extraction
- Automatic robots.txt checking
- Configurable delays between requests
- Meta tag, link, and image extraction
- List and table parsing support

**Usage Example:**

```php
use DataScience\Collectors\WebScraper;

$scraper = new WebScraper(
    2000,  // 2 second delay between requests
    'Mozilla/5.0 (compatible; MyBot/1.0)',
    true   // Respect robots.txt
);

// Scrape a page
$crawler = $scraper->scrape('https://example.com/products');

// Extract single values
$data = $scraper->extract($crawler, [
    'title' => 'h1.page-title',
    'price' => '.product-price',
    'description' => '.product-description',
]);

// Extract list of items
$products = $scraper->extractList(
    $crawler,
    '.product',  // Item selector
    [
        'name' => '.product-name',
        'price' => '.product-price',
        'rating' => '.product-rating',
    ]
);

// Extract all links
$links = $scraper->extractLinks($crawler);
```

**Key Methods:**
- `scrape(string $url): Crawler` - Fetch and parse page
- `extract(Crawler $crawler, array $selectors): array` - Extract data
- `extractList(Crawler $crawler, string $itemSelector, array $fieldSelectors): array` - Extract multiple items
- `extractLinks(Crawler $crawler, ?string $filter): array` - Extract links
- `extractImages(Crawler $crawler): array` - Extract images
- `extractMetaTags(Crawler $crawler): array` - Extract meta tags

## Installation

These classes require:

```bash
composer require guzzlehttp/guzzle:^7.8
composer require symfony/dom-crawler:^7.0
composer require symfony/css-selector:^7.0
```

## Integration

### In Your Projects

1. **Copy the classes to your project:**

```bash
cp -r code/data-science-php-developers/chapter-03/src /your-project/src
```

2. **Update composer.json autoload:**

```json
{
    "autoload": {
        "psr-4": {
            "DataScience\\Collectors\\": "src/Collectors/"
        }
    }
}
```

3. **Regenerate autoload:**

```bash
composer dump-autoload
```

4. **Use in your code:**

```php
require 'vendor/autoload.php';

use DataScience\Collectors\DatabaseCollector;
use DataScience\Collectors\ApiCollector;
use DataScience\Collectors\WebScraper;

// Your code here...
```

## Testing

Complete, runnable examples are available in:

```
/testing/data-science-php-developers/chapter-03/
```

See the testing directory README for setup instructions and example usage.

## Production Considerations

### Database Collection

1. **Connection Pooling**: Reuse PDO instances across collections
2. **Timeouts**: Set appropriate query timeout limits
3. **Indexes**: Ensure queries use proper database indexes
4. **Monitoring**: Log slow queries and collection metrics

### API Collection

1. **Rate Limits**: Configure delays based on API documentation
2. **Caching**: Cache responses when appropriate
3. **Monitoring**: Track API errors and retry rates
4. **Credentials**: Use environment variables, never hardcode

### Web Scraping

1. **robots.txt**: Always check and respect
2. **Rate Limiting**: Be polite, use delays of 2+ seconds
3. **User-Agent**: Use descriptive, identifiable user-agent
4. **Monitoring**: Watch for HTML structure changes
5. **Legal**: Review terms of service, respect copyright

## Environment Variables

For production use, configure via environment variables:

```php
// Database
$collector = new DatabaseCollector(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE']
    ),
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

// API
$collector = new ApiCollector(
    $_ENV['API_BASE_URL'],
    ['Authorization' => 'Bearer ' . $_ENV['API_TOKEN']],
    (int)$_ENV['API_RATE_LIMIT_MS'],
    (int)$_ENV['API_MAX_RETRIES']
);

// Scraper
$scraper = new WebScraper(
    (int)$_ENV['SCRAPE_DELAY_MS'],
    $_ENV['USER_AGENT']
);
```

## Common Patterns

### Combine Multiple Sources

```php
// Collect from database
$dbCollector = new DatabaseCollector(...);
$orders = $dbCollector->collect("SELECT * FROM orders WHERE status = 'pending'");

// Enrich with API data
$apiCollector = new ApiCollector(...);
foreach ($orders as &$order) {
    $order['payment_status'] = $apiCollector->get("/payments/{$order['payment_id']}");
}

// Store enriched data
// ...
```

### Error Recovery

```php
$collector = new ApiCollector(...);
$results = [];
$failures = [];

foreach ($endpoints as $endpoint) {
    try {
        $results[$endpoint] = $collector->get($endpoint);
    } catch (RuntimeException $e) {
        $failures[$endpoint] = $e->getMessage();
        // Log error, continue with next endpoint
    }
}
```

### Batch Processing

```php
$collector = new DatabaseCollector(...);

$processedCount = 0;
foreach ($collector->collectGenerator("SELECT * FROM large_table") as $row) {
    processRow($row);
    $processedCount++;
    
    if ($processedCount % 1000 === 0) {
        echo "Processed {$processedCount} records...\n";
    }
}
```

## Performance Tuning

### Database

- Use `collectGenerator()` for datasets > 10,000 records
- Adjust chunk size based on row size and available memory
- Add indexes for frequently queried columns
- Use `getStats()` instead of collecting all data for aggregations

### API

- Use parallel requests for independent endpoints
- Implement response caching (Redis, Memcached)
- Monitor rate limit headers and adjust delays
- Batch API calls when supported by the API

### Scraping

- Cache scraped pages to avoid re-fetching
- Use headless browsers only when JavaScript is required
- Parse HTML efficiently (avoid XPath when CSS selectors work)
- Implement backoff when receiving 429/503 errors

## Further Reading

- [Chapter 03 Tutorial](../../docs/series/data-science-php-developers/chapters/03-collecting-data-databases-apis-scraping.md)
- [PHP PDO Manual](https://www.php.net/manual/en/book.pdo.php)
- [Guzzle Documentation](http://docs.guzzlephp.org/)
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Web Scraping Best Practices](https://www.scrapehero.com/web-scraping-best-practices/)

## License

MIT License - See LICENSE file in project root.

## Support

For questions or issues:
1. Check the testing examples in `/testing/data-science-php-developers/chapter-03/`
2. Review the chapter tutorial
3. Check documentation for dependencies (Guzzle, Symfony DomCrawler)
4. Open an issue on GitHub: https://github.com/dalehurley/codewithphp
