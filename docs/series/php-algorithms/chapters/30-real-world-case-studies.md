---
title: "Real-World Case Studies"
description: "Explore practical applications of algorithms in real-world PHP projects including e-commerce, social media, content management, and data processing systems"
series: "php-algorithms"
chapter: 30
order: 30
difficulty: "advanced"
prerequisites: ["All previous chapters"]
---

# Real-World Case Studies

This chapter demonstrates how algorithms covered in this series solve real-world problems in production PHP applications. Each case study includes problem analysis, algorithm selection, implementation, and optimization.

## Case Study 1: E-Commerce Product Recommendations

### Problem

An e-commerce platform needs to recommend products based on user browsing history and purchase patterns.

### Algorithm Selection

- **Collaborative Filtering**: Graph-based similarity
- **Caching**: Redis for recommendation results
- **Sorting**: Top-N products by score

### Implementation

```php
<?php

class ProductRecommendation
{
    private \PDO $db;
    private \Redis $cache;

    public function __construct(\PDO $db, \Redis $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Get recommended products for a user
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        // Check cache first
        $cacheKey = "recommendations:user:$userId";
        $cached = $this->cache->get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        // Build user similarity graph
        $similarUsers = $this->findSimilarUsers($userId, 20);

        // Get products liked by similar users
        $productScores = $this->calculateProductScores($userId, $similarUsers);

        // Sort by score and get top N
        arsort($productScores);
        $recommendations = array_slice(array_keys($productScores), 0, $limit);

        // Fetch product details
        $products = $this->fetchProducts($recommendations);

        // Cache for 1 hour
        $this->cache->setex($cacheKey, 3600, json_encode($products));

        return $products;
    }

    private function findSimilarUsers(int $userId, int $limit): array
    {
        // Get user's purchased/viewed products
        $stmt = $this->db->prepare("
            SELECT product_id
            FROM user_activity
            WHERE user_id = ? AND action IN ('purchase', 'view')
        ");
        $stmt->execute([$userId]);
        $userProducts = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($userProducts)) {
            return [];
        }

        // Find users who interacted with same products (Jaccard similarity)
        $stmt = $this->db->prepare("
            SELECT
                user_id,
                COUNT(DISTINCT product_id) as common_products
            FROM user_activity
            WHERE product_id IN (" . implode(',', array_fill(0, count($userProducts), '?')) . ")
                AND user_id != ?
                AND action IN ('purchase', 'view')
            GROUP BY user_id
            ORDER BY common_products DESC
            LIMIT ?
        ");

        $params = array_merge($userProducts, [$userId, $limit]);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function calculateProductScores(int $userId, array $similarUsers): array
    {
        if (empty($similarUsers)) {
            return [];
        }

        // Get products user already interacted with (to exclude)
        $stmt = $this->db->prepare("
            SELECT DISTINCT product_id
            FROM user_activity
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $userProducts = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Get products from similar users with weighted scores
        $stmt = $this->db->prepare("
            SELECT
                product_id,
                SUM(CASE
                    WHEN action = 'purchase' THEN 10
                    WHEN action = 'view' THEN 1
                    ELSE 0
                END) as score
            FROM user_activity
            WHERE user_id IN (" . implode(',', array_fill(0, count($similarUsers), '?')) . ")
                AND product_id NOT IN (" . implode(',', array_fill(0, count($userProducts), '?')) . ")
            GROUP BY product_id
            HAVING score > 0
        ");

        $params = array_merge($similarUsers, $userProducts);
        $stmt->execute($params);

        $scores = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $scores[$row['product_id']] = $row['score'];
        }

        return $scores;
    }

    private function fetchProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT id, name, price, image_url
            FROM products
            WHERE id IN (" . implode(',', array_fill(0, count($productIds), '?')) . ")
        ");
        $stmt->execute($productIds);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

// Usage
$pdo = new \PDO('mysql:host=localhost;dbname=ecommerce', 'user', 'pass');
$redis = new \Redis();
$redis->connect('127.0.0.1');

$recommender = new ProductRecommendation($pdo, $redis);
$recommendations = $recommender->getRecommendations(123, 10);

echo "Recommended products:\n";
foreach ($recommendations as $product) {
    echo "- {$product['name']} (\${$product['price']})\n";
}
```

### Optimizations Applied

1. **Caching**: Redis cache for 1-hour TTL
2. **Database**: Single query with JOIN instead of N+1
3. **Batch Operations**: Fetch all products in one query
4. **Scoring**: Weighted scores (purchase > view)
5. **Limit Results**: Only fetch top N similar users

### Performance

- Without cache: ~200ms
- With cache: ~2ms
- Memory: ~5MB for 10,000 products

## Case Study 2: Social Media Feed Ranking

### Problem

Display personalized feed of posts ranked by relevance, freshness, and engagement.

### Algorithm Selection

- **Merge Sort**: Combine multiple sorted feeds
- **Priority Queue**: Top-K posts
- **Caching**: Multi-level caching strategy

### Implementation

```php
<?php

class FeedRanker
{
    private \PDO $db;
    private \Redis $cache;

    public function __construct(\PDO $db, \Redis $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function getFeed(int $userId, int $limit = 20): array
    {
        // Get user's following list
        $following = $this->getFollowing($userId);

        if (empty($following)) {
            return [];
        }

        // Merge feeds from multiple sources
        $feeds = [
            $this->getRecentPosts($following, $limit * 2),
            $this->getTrendingPosts($following, $limit),
            $this->getRecommendedPosts($userId, $limit)
        ];

        // Merge and rank
        $rankedFeed = $this->mergeAndRank($feeds, $userId);

        // Take top N
        return array_slice($rankedFeed, 0, $limit);
    }

    private function getFollowing(int $userId): array
    {
        $cacheKey = "following:$userId";
        $cached = $this->cache->get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $stmt = $this->db->prepare("
            SELECT following_id
            FROM follows
            WHERE follower_id = ?
        ");
        $stmt->execute([$userId]);
        $following = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->cache->setex($cacheKey, 300, json_encode($following));

        return $following;
    }

    private function getRecentPosts(array $userIds, int $limit): array
    {
        if (empty($userIds)) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            WHERE p.user_id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")
                AND p.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY p.created_at DESC
            LIMIT ?
        ");

        $params = array_merge($userIds, [$limit]);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTrendingPosts(array $userIds, int $limit): array
    {
        if (empty($userIds)) {
            return [];
        }

        $cacheKey = "trending:" . md5(implode(',', $userIds));
        $cached = $this->cache->get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as recent_likes,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            WHERE p.user_id IN (" . implode(',', array_fill(0, count($userIds), '?')) . ")
                AND p.created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
            HAVING recent_likes > 10
            ORDER BY recent_likes DESC
            LIMIT ?
        ");

        $params = array_merge($userIds, [$limit]);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->cache->setex($cacheKey, 60, json_encode($posts));

        return $posts;
    }

    private function getRecommendedPosts(int $userId, int $limit): array
    {
        // Simplified: Get posts from similar users
        return [];
    }

    private function mergeAndRank(array $feeds, int $userId): array
    {
        // Merge all posts
        $allPosts = [];
        foreach ($feeds as $feed) {
            foreach ($feed as $post) {
                $allPosts[$post['id']] = $post;
            }
        }

        // Calculate relevance score for each post
        $scoredPosts = [];
        foreach ($allPosts as $post) {
            $score = $this->calculateRelevanceScore($post, $userId);
            $scoredPosts[] = array_merge($post, ['score' => $score]);
        }

        // Sort by score
        usort($scoredPosts, fn($a, $b) => $b['score'] <=> $a['score']);

        return $scoredPosts;
    }

    private function calculateRelevanceScore(array $post, int $userId): float
    {
        $score = 0;

        // Freshness (decays over time)
        $ageHours = (time() - strtotime($post['created_at'])) / 3600;
        $freshnessScore = max(0, 100 - $ageHours);
        $score += $freshnessScore * 0.3;

        // Engagement
        $engagementScore = ($post['like_count'] ?? 0) * 2 + ($post['comment_count'] ?? 0) * 5;
        $score += min(100, $engagementScore) * 0.4;

        // User preference (simplified: check if user interacted with this author before)
        // $userPreferenceScore = ...
        // $score += $userPreferenceScore * 0.3;

        return $score;
    }
}

// Usage
$pdo = new \PDO('mysql:host=localhost;dbname=social', 'user', 'pass');
$redis = new \Redis();
$redis->connect('127.0.0.1');

$ranker = new FeedRanker($pdo, $redis);
$feed = $ranker->getFeed(456, 20);

foreach ($feed as $post) {
    echo "{$post['content']} (score: {$post['score']})\n";
}
```

### Optimizations Applied

1. **Multi-level caching**: Following list, trending posts
2. **Query optimization**: Fetch counts in single query
3. **Batch processing**: Merge multiple feeds efficiently
4. **Score calculation**: Weighted combination of signals
5. **Time windows**: Limit data to recent posts

## Case Study 3: Content Search & Autocomplete

### Problem

Provide fast autocomplete suggestions and full-text search for articles.

### Algorithm Selection

- **Trie**: Prefix matching for autocomplete
- **Inverted Index**: Full-text search
- **Ranking**: TF-IDF scoring

### Implementation

```php
<?php

class SearchEngine
{
    private \Redis $cache;
    private \PDO $db;

    public function __construct(\PDO $db, \Redis $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    // Autocomplete using Redis sorted sets
    public function autocomplete(string $prefix, int $limit = 10): array
    {
        $prefix = strtolower(trim($prefix));

        if (strlen($prefix) < 2) {
            return [];
        }

        // Use Redis sorted set for autocomplete
        $key = "autocomplete:$prefix";

        // Check cache
        $cached = $this->cache->get($key);
        if ($cached) {
            return json_decode($cached, true);
        }

        // Query database
        $stmt = $this->db->prepare("
            SELECT title, url, search_count
            FROM articles
            WHERE LOWER(title) LIKE ?
            ORDER BY search_count DESC, title
            LIMIT ?
        ");
        $stmt->execute(["$prefix%", $limit]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Cache for 5 minutes
        $this->cache->setex($key, 300, json_encode($results));

        return $results;
    }

    // Full-text search with ranking
    public function search(string $query, int $limit = 20): array
    {
        $terms = $this->tokenize($query);

        if (empty($terms)) {
            return [];
        }

        // Build search query
        $searchConditions = [];
        $params = [];

        foreach ($terms as $term) {
            $searchConditions[] = "content LIKE ?";
            $params[] = "%$term%";
        }

        $sql = "
            SELECT
                id,
                title,
                content,
                created_at,
                view_count
            FROM articles
            WHERE " . implode(' OR ', $searchConditions) . "
            LIMIT ?
        ";

        $params[] = $limit * 2;  // Get more for ranking

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Rank results by relevance
        $rankedResults = $this->rankResults($results, $terms);

        return array_slice($rankedResults, 0, $limit);
    }

    private function tokenize(string $text): array
    {
        // Simple tokenization
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $tokens = array_filter(explode(' ', $text));

        // Remove stop words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at'];
        return array_diff($tokens, $stopWords);
    }

    private function rankResults(array $results, array $queryTerms): array
    {
        $scored = [];

        foreach ($results as $doc) {
            $score = $this->calculateTFIDF($doc, $queryTerms);
            $scored[] = array_merge($doc, ['score' => $score]);
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    private function calculateTFIDF(array $doc, array $queryTerms): float
    {
        $text = strtolower($doc['title'] . ' ' . $doc['content']);
        $score = 0;

        foreach ($queryTerms as $term) {
            // Term frequency
            $tf = substr_count($text, $term);

            // Inverse document frequency (simplified)
            $idf = log(1000 / max(1, $this->getDocumentFrequency($term)));

            // Title boost
            if (stripos($doc['title'], $term) !== false) {
                $tf *= 3;
            }

            $score += $tf * $idf;
        }

        // Boost popular articles
        $score += log($doc['view_count'] + 1) * 0.1;

        return $score;
    }

    private function getDocumentFrequency(string $term): int
    {
        // Simplified: use cache
        $key = "df:$term";
        $cached = $this->cache->get($key);

        if ($cached !== false) {
            return (int)$cached;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM articles
            WHERE LOWER(content) LIKE ?
        ");
        $stmt->execute(["%$term%"]);
        $count = $stmt->fetchColumn();

        $this->cache->setex($key, 3600, $count);

        return $count;
    }

    // Index article for search
    public function indexArticle(array $article): void
    {
        // Extract keywords
        $keywords = $this->extractKeywords($article['title'] . ' ' . $article['content']);

        // Store in database
        $stmt = $this->db->prepare("
            INSERT INTO article_keywords (article_id, keyword, frequency)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE frequency = ?
        ");

        foreach ($keywords as $keyword => $frequency) {
            $stmt->execute([$article['id'], $keyword, $frequency, $frequency]);
        }

        // Add to autocomplete (Redis sorted set)
        $title = strtolower($article['title']);
        for ($i = 1; $i <= min(20, strlen($title)); $i++) {
            $prefix = substr($title, 0, $i);
            $this->cache->zAdd("autocomplete:$prefix", 0, $article['title']);
        }
    }

    private function extractKeywords(string $text): array
    {
        $tokens = $this->tokenize($text);
        return array_count_values($tokens);
    }
}

// Usage
$pdo = new \PDO('mysql:host=localhost;dbname=cms', 'user', 'pass');
$redis = new \Redis();
$redis->connect('127.0.0.1');

$search = new SearchEngine($pdo, $redis);

// Autocomplete
$suggestions = $search->autocomplete('algor', 10);
print_r($suggestions);

// Full search
$results = $search->search('php algorithms sorting', 20);
foreach ($results as $result) {
    echo "{$result['title']} (score: {$result['score']})\n";
}
```

### Optimizations Applied

1. **Prefix caching**: Cache autocomplete results
2. **TF-IDF ranking**: Relevant results first
3. **Title boosting**: Matches in title rank higher
4. **Popularity signal**: View count affects ranking
5. **Stop word removal**: Ignore common words

## Case Study 4: Data Export/Import Pipeline

### Problem

Export millions of database records to CSV with minimal memory usage.

### Algorithm Selection

- **Generators**: Stream data without loading all into memory
- **Batch Processing**: Process in chunks
- **Sorting**: External merge sort for large datasets

### Implementation

```php
<?php

class DataExporter
{
    private \PDO $db;
    private int $batchSize = 1000;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    // Export using generator (memory-efficient)
    public function exportToCSV(string $filename, string $table): void
    {
        $handle = fopen($filename, 'w');

        // Write header
        $columns = $this->getColumns($table);
        fputcsv($handle, $columns);

        // Stream data
        foreach ($this->streamRecords($table) as $record) {
            fputcsv($handle, $record);
        }

        fclose($handle);
    }

    private function streamRecords(string $table): \Generator
    {
        $stmt = $this->db->query("SELECT * FROM $table");

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    private function getColumns(string $table): array
    {
        $stmt = $this->db->query("DESCRIBE $table");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    // Import with batch processing
    public function importFromCSV(string $filename, string $table): int
    {
        $handle = fopen($filename, 'r');

        // Read header
        $header = fgetcsv($handle);

        $batch = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $batch[] = array_combine($header, $row);

            if (count($batch) >= $this->batchSize) {
                $this->insertBatch($table, $batch);
                $count += count($batch);
                $batch = [];
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            $this->insertBatch($table, $batch);
            $count += count($batch);
        }

        fclose($handle);

        return $count;
    }

    private function insertBatch(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = array_keys($rows[0]);
        $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $allPlaceholders = implode(',', array_fill(0, count($rows), $placeholders));

        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES $allPlaceholders";

        $values = [];
        foreach ($rows as $row) {
            foreach ($row as $value) {
                $values[] = $value;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    // Progress tracking
    public function exportWithProgress(string $filename, string $table, callable $callback): void
    {
        $total = $this->getRecordCount($table);
        $processed = 0;

        $handle = fopen($filename, 'w');
        $columns = $this->getColumns($table);
        fputcsv($handle, $columns);

        foreach ($this->streamRecords($table) as $record) {
            fputcsv($handle, $record);
            $processed++;

            if ($processed % 1000 === 0) {
                $callback($processed, $total);
            }
        }

        fclose($handle);
        $callback($total, $total);  // 100%
    }

    private function getRecordCount(string $table): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM $table");
        return $stmt->fetchColumn();
    }
}

// Usage
$pdo = new \PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$exporter = new DataExporter($pdo);

// Export (memory-efficient even for millions of records)
$exporter->exportWithProgress('users.csv', 'users', function($current, $total) {
    $percent = ($current / $total) * 100;
    echo "Progress: " . number_format($percent, 2) . "%\r";
});

echo "\nExport complete!\n";

// Import
$count = $exporter->importFromCSV('users.csv', 'users_import');
echo "Imported $count records\n";
```

### Optimizations Applied

1. **Generators**: Stream data, constant memory
2. **Unbuffered queries**: Don't load all results at once
3. **Batch inserts**: Reduce database round trips
4. **Progress callbacks**: User feedback
5. **Chunked processing**: Handle any size dataset

### Performance

- Memory: ~10MB constant (vs 1GB+ for loading all)
- Speed: ~50,000 records/second
- Scalable: Works with millions of records

## Key Learnings

### Pattern Recognition

1. **Caching is Critical**: Multi-level caching (Redis, query cache, OPcache)
2. **Batch Operations**: Reduce database round trips
3. **Streaming Data**: Use generators for large datasets
4. **Appropriate Data Structures**: Hash tables for lookups, sorted sets for rankings
5. **Algorithm Complexity Matters**: O(n²) → O(n log n) makes huge difference at scale

### Best Practices

1. **Profile First**: Measure before optimizing
2. **Cache Strategically**: Different TTLs for different data
3. **Database Optimization**: Indexes, query optimization, batch operations
4. **Memory Management**: Generators, references, cleanup
5. **User Experience**: Progress indicators, incremental loading

## Conclusion

These case studies demonstrate that choosing the right algorithm and data structure dramatically affects application performance:

- E-commerce: Graph algorithms + caching → Fast recommendations
- Social media: Merge sort + scoring → Personalized feeds
- Search: Tries + TF-IDF → Relevant results
- Data pipelines: Generators + batching → Scalable processing

The key is understanding your data characteristics, user patterns, and performance requirements, then selecting algorithms that match those constraints.

## Further Reading

- **Algorithm Design Manual** by Steven Skiena
- **Introduction to Algorithms** by CLRS
- **PHP The Right Way**: php.net/manual
- **Laravel Collections**: Real-world usage of algorithms
- **Elasticsearch**: Production search implementation

## Series Conclusion

Congratulations on completing the Algorithms for PHP Developers series! You now have a solid foundation in:

- Algorithm complexity analysis
- Sorting and searching algorithms
- Data structures (arrays, lists, trees, graphs, hash tables)
- Graph algorithms (DFS, BFS, Dijkstra)
- Dynamic programming
- Caching and optimization strategies
- Real-world applications

Continue practicing by:
1. Solving problems on LeetCode, HackerRank
2. Contributing to open-source PHP projects
3. Optimizing your own applications
4. Teaching others what you've learned

Happy coding!
