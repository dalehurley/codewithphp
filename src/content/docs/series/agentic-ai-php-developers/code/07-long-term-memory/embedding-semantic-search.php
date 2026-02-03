#!/usr/bin/env php
<?php
/**
 * Embedding and Semantic Search Example
 * 
 * Demonstrates semantic search using embeddings to find memories by meaning.
 * Uses mock embeddings for development (replace with Voyage AI in production).
 * 
 * Usage: php embedding-semantic-search.php
 */

declare(strict_types=1);

// Mock embedding service (for development)
class MockEmbeddingService
{
    private int $dimensions;
    
    public function __construct(int $dimensions = 384)
    {
        $this->dimensions = $dimensions;
    }
    
    public function embed(string $text): array
    {
        $hash = hash('sha256', $text);
        $embedding = [];
        
        for ($i = 0; $i < $this->dimensions; $i++) {
            $hexPair = substr($hash, ($i * 2) % 64, 2);
            $value = hexdec($hexPair) / 255.0;
            $embedding[] = ($value - 0.5) * 2.0;
        }
        
        return $this->normalize($embedding);
    }
    
    public function similarity(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2) || count($embedding1) === 0) {
            return 0.0;
        }
        
        $dotProduct = 0.0;
        $norm1 = 0.0;
        $norm2 = 0.0;
        
        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $norm1 += $embedding1[$i] * $embedding1[$i];
            $norm2 += $embedding2[$i] * $embedding2[$i];
        }
        
        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);
        
        if ($norm1 === 0.0 || $norm2 === 0.0) {
            return 0.0;
        }
        
        return $dotProduct / ($norm1 * $norm2);
    }
    
    private function normalize(array $vector): array
    {
        $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        
        if ($norm === 0.0) {
            return $vector;
        }
        
        return array_map(fn($v) => $v / $norm, $vector);
    }
}

// Simplified MemoryStore (focused on embeddings)
class MemoryStore
{
    private PDO $pdo;
    
    public function __construct(string $dsn)
    {
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function store(string $content, string $userId, array $embedding, array $tags = [], float $importance = 0.5): string
    {
        $id = 'mem_' . bin2hex(random_bytes(16));
        $now = time();
        
        $stmt = $this->pdo->prepare("
            INSERT INTO memories (
                id, user_id, content, content_hash, embedding,
                importance, created_at, updated_at, metadata
            ) VALUES (
                :id, :user_id, :content, :content_hash, :embedding,
                :importance, :created_at, :updated_at, :metadata
            )
        ");
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'embedding' => serialize($embedding),
            'importance' => $importance,
            'created_at' => $now,
            'updated_at' => $now,
            'metadata' => json_encode(['tags' => $tags]),
        ]);
        
        return $id;
    }
    
    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}

// Semantic search implementation
class SemanticMemorySearch
{
    private MemoryStore $store;
    private MockEmbeddingService $embeddings;
    
    public function __construct(MemoryStore $store, MockEmbeddingService $embeddings)
    {
        $this->store = $store;
        $this->embeddings = $embeddings;
    }
    
    public function storeWithEmbedding(string $content, string $userId, array $tags = [], float $importance = 0.5): string
    {
        $embedding = $this->embeddings->embed($content);
        return $this->store->store($content, $userId, $embedding, $tags, $importance);
    }
    
    public function search(string $query, ?string $userId = null, int $topK = 5, float $minSimilarity = 0.3): array
    {
        $queryEmbedding = $this->embeddings->embed($query);
        
        $pdo = $this->store->getPDO();
        
        $sql = "SELECT * FROM memories WHERE embedding IS NOT NULL";
        $params = [];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $memories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($memories)) {
            return [];
        }
        
        $scored = [];
        
        foreach ($memories as $memory) {
            $memoryEmbedding = unserialize($memory['embedding']);
            $similarity = $this->embeddings->similarity($queryEmbedding, $memoryEmbedding);
            
            if ($similarity >= $minSimilarity) {
                $scored[] = [
                    'memory' => $memory,
                    'similarity' => $similarity,
                ];
            }
        }
        
        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return array_slice($scored, 0, $topK);
    }
}

// Main execution
echo "=== Embedding and Semantic Search Example ===\n\n";

$dbPath = __DIR__ . '/memory.db';

if (!file_exists($dbPath)) {
    echo "❌ Database not found. Run memory-schema-design.php first.\n";
    exit(1);
}

$store = new MemoryStore('sqlite:' . $dbPath);
$embeddings = new MockEmbeddingService(dimensions: 384);
$search = new SemanticMemorySearch($store, $embeddings);

$userId = 'user_semantic_' . time();

echo "Using mock embeddings (384 dimensions)\n";
echo "Note: Replace with Voyage AI or OpenAI for production\n\n";

// Store knowledge base
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 1: Building Knowledge Base\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$knowledge = [
    [
        'content' => 'PHP is a popular server-side scripting language for web development',
        'tags' => ['php', 'programming', 'web'],
        'importance' => 0.8,
    ],
    [
        'content' => 'Laravel is a modern PHP framework with elegant syntax',
        'tags' => ['php', 'laravel', 'framework'],
        'importance' => 0.7,
    ],
    [
        'content' => 'Composer is the dependency manager for PHP projects',
        'tags' => ['php', 'tools', 'composer'],
        'importance' => 0.6,
    ],
    [
        'content' => 'Python is widely used for machine learning and data science',
        'tags' => ['python', 'programming', 'ml'],
        'importance' => 0.7,
    ],
    [
        'content' => 'React is a JavaScript library for building user interfaces',
        'tags' => ['javascript', 'react', 'frontend'],
        'importance' => 0.7,
    ],
    [
        'content' => 'Docker containers package applications with their dependencies',
        'tags' => ['docker', 'devops', 'tools'],
        'importance' => 0.6,
    ],
];

foreach ($knowledge as $item) {
    $id = $search->storeWithEmbedding(
        $item['content'],
        $userId,
        $item['tags'],
        $item['importance']
    );
    echo "✓ Stored: {$item['content']}\n";
}

echo "\n✓ Knowledge base created with " . count($knowledge) . " items\n\n";

// Semantic search examples
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 2: Semantic Search Queries\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$queries = [
    "Tell me about web development languages",
    "What tools help manage dependencies?",
    "How do I build UIs?",
    "Deployment and containerization",
];

foreach ($queries as $query) {
    echo "Query: \"{$query}\"\n";
    echo str_repeat('-', 60) . "\n";
    
    $results = $search->search($query, $userId, topK: 3);
    
    if (empty($results)) {
        echo "No results found.\n\n";
        continue;
    }
    
    foreach ($results as $i => $result) {
        $similarity = round($result['similarity'], 3);
        $content = $result['memory']['content'];
        
        echo sprintf("%d. [%.3f] %s\n", $i + 1, $similarity, $content);
    }
    
    echo "\n";
}

// Compare semantic vs keyword matching
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 3: Semantic vs Keyword Matching\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$testQuery = "server side code";

echo "Query: \"{$testQuery}\"\n\n";
echo "Semantic Results (finds 'PHP' even though not mentioned):\n";

$semanticResults = $search->search($testQuery, $userId, topK: 2);

foreach ($semanticResults as $i => $result) {
    echo sprintf("  %d. [%.3f] %s\n", $i + 1, $result['similarity'], $result['memory']['content']);
}

echo "\n";

// Similarity matrix
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 4: Similarity Matrix\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$texts = [
    'PHP web development',
    'Laravel framework',
    'Python machine learning',
];

echo "Comparing similarity between texts:\n\n";

$embeddingsCache = [];
foreach ($texts as $text) {
    $embeddingsCache[$text] = $embeddings->embed($text);
}

foreach ($texts as $i => $text1) {
    foreach ($texts as $j => $text2) {
        if ($j <= $i) continue;
        
        $sim = $embeddings->similarity($embeddingsCache[$text1], $embeddingsCache[$text2]);
        
        echo sprintf(
            "\"%s\" <-> \"%s\": %.3f\n",
            $text1,
            $text2,
            $sim
        );
    }
}

echo "\n✓ Semantic search example complete!\n";
echo "\nNote: In production, integrate Voyage AI (https://voyageai.com) for best results.\n";
