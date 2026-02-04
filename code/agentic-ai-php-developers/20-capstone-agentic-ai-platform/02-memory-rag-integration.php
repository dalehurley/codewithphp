<?php

declare(strict_types=1);

/**
 * 02 - Memory + RAG Integration
 * 
 * Combined short-term conversation memory and long-term knowledge storage
 * with semantic retrieval for grounded agent responses.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


use ClaudePhp\ClaudePhp;

/**
 * Simple in-memory conversation storage
 */
class ConversationMemory
{
    private array $sessions = [];

    public function add(string $sessionId, array $turn): void
    {
        if (!isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId] = [];
        }
        
        $this->sessions[$sessionId][] = $turn;
    }

    public function get(string $sessionId, int $limit = 10): array
    {
        if (!isset($this->sessions[$sessionId])) {
            return [];
        }
        
        return array_slice($this->sessions[$sessionId], -$limit);
    }

    public function clear(string $sessionId): void
    {
        unset($this->sessions[$sessionId]);
    }

    public function countSessions(): int
    {
        return count($this->sessions);
    }
}

/**
 * Simple vector memory with cosine similarity
 */
class VectorMemory
{
    private array $items = [];

    public function store(string $content, array $metadata = []): void
    {
        $this->items[] = [
            'content' => $content,
            'metadata' => $metadata,
            'timestamp' => time(),
        ];
    }

    public function search(string $query, int $limit = 5): array
    {
        // Simple keyword-based search (in production, use embeddings)
        $queryWords = str_word_count(strtolower($query), 1);
        
        $scored = [];
        foreach ($this->items as $item) {
            $contentWords = str_word_count(strtolower($item['content']), 1);
            $matches = count(array_intersect($queryWords, $contentWords));
            
            if ($matches > 0) {
                $scored[] = [
                    'item' => $item,
                    'score' => $matches / count($queryWords),
                ];
            }
        }
        
        // Sort by score
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice(
            array_column($scored, 'item'),
            0,
            $limit
        );
    }

    public function count(): int
    {
        return count($this->items);
    }
}

/**
 * Simple vector store (in production, use a real vector DB)
 */
class VectorStore
{
    private array $vectors = [];

    public function store(array $data): void
    {
        $this->vectors[] = $data;
    }

    public function search(array $embedding, int $limit = 5): array
    {
        // In production, compute actual cosine similarity
        // For demo, return most recent items
        return array_slice(array_reverse($this->vectors), 0, $limit);
    }

    public function count(): int
    {
        return count($this->vectors);
    }
}

/**
 * Simple embedding service (mock)
 */
class EmbeddingService
{
    public function embed(string $text): array
    {
        // Mock embedding - in production, use real embeddings
        // Return a simple hash-based pseudo-embedding
        $hash = md5($text);
        $embedding = [];
        
        for ($i = 0; $i < 64; $i++) {
            $embedding[] = hexdec($hash[$i % 32]) / 15.0;
        }
        
        return $embedding;
    }
}

/**
 * Integrated Memory + RAG System
 */
class MemoryRAGSystem
{
    private ConversationMemory $conversationMemory;
    private VectorMemory $longTermMemory;
    private EmbeddingService $embeddings;
    private VectorStore $vectorStore;

    public function __construct()
    {
        $this->conversationMemory = new ConversationMemory();
        $this->longTermMemory = new VectorMemory();
        $this->embeddings = new EmbeddingService();
        $this->vectorStore = new VectorStore();
    }

    /**
     * Store conversation turn in short-term memory
     */
    public function storeConversation(
        string $sessionId,
        string $role,
        string $content
    ): void {
        $this->conversationMemory->add($sessionId, [
            'role' => $role,
            'content' => $content,
            'timestamp' => time(),
        ]);
    }

    /**
     * Store knowledge in long-term memory with embeddings
     */
    public function storeKnowledge(
        string $content,
        array $metadata = []
    ): void {
        $embedding = $this->embeddings->embed($content);
        
        $this->vectorStore->store([
            'content' => $content,
            'embedding' => $embedding,
            'metadata' => array_merge($metadata, [
                'stored_at' => time(),
            ]),
        ]);
        
        $this->longTermMemory->store($content, $metadata);
    }

    /**
     * Retrieve relevant context for a query
     */
    public function retrieveContext(
        string $query,
        string $sessionId,
        int $maxResults = 5
    ): array {
        $context = [];
        
        // Get recent conversation history
        $conversationHistory = $this->conversationMemory->get(
            $sessionId,
            limit: 10
        );
        
        if (!empty($conversationHistory)) {
            $context['conversation'] = [
                'source' => 'short_term_memory',
                'items' => $conversationHistory,
            ];
        }
        
        // Retrieve relevant long-term knowledge
        $relevant = $this->longTermMemory->search($query, $maxResults);
        
        if (!empty($relevant)) {
            $context['knowledge'] = [
                'source' => 'long_term_memory',
                'items' => $relevant,
            ];
        }
        
        return $context;
    }

    /**
     * Consolidate session into long-term knowledge
     */
    public function consolidateSession(string $sessionId): void
    {
        $history = $this->conversationMemory->get($sessionId, limit: 100);
        
        if (empty($history)) {
            return;
        }
        
        // Extract key facts
        foreach ($history as $turn) {
            if ($turn['role'] === 'assistant' && strlen($turn['content']) > 100) {
                $this->storeKnowledge($turn['content'], [
                    'session_id' => $sessionId,
                    'type' => 'consolidated_fact',
                ]);
            }
        }
    }

    public function clearSession(string $sessionId): void
    {
        $this->conversationMemory->clear($sessionId);
    }

    public function getStats(): array
    {
        return [
            'conversation_sessions' => $this->conversationMemory->countSessions(),
            'long_term_items' => $this->longTermMemory->count(),
            'vector_store_size' => $this->vectorStore->count(),
        ];
    }
}

// Example usage
if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Please set ANTHROPIC_API_KEY environment variable\n");
}

echo "=== Memory + RAG Integration ===\n\n";

// Initialize system
$memorySystem = new MemoryRAGSystem();
$sessionId = 'user_' . uniqid();

echo "📝 Session ID: {$sessionId}\n\n";

// Simulate a conversation
echo "💬 Simulating conversation...\n";

$conversation = [
    ['role' => 'user', 'content' => 'What is PHP?'],
    ['role' => 'assistant', 'content' => 'PHP is a popular server-side scripting language designed for web development. It stands for "PHP: Hypertext Preprocessor" and is widely used for building dynamic websites and web applications.'],
    ['role' => 'user', 'content' => 'What are some popular frameworks?'],
    ['role' => 'assistant', 'content' => 'The most popular PHP frameworks include Laravel (known for elegant syntax and developer experience), Symfony (enterprise-grade with reusable components), CodeIgniter (lightweight and fast), and Yii (high-performance framework).'],
    ['role' => 'user', 'content' => 'Tell me about Laravel'],
    ['role' => 'assistant', 'content' => 'Laravel is the most popular PHP framework, created by Taylor Otwell in 2011. It features an elegant syntax, built-in authentication, Eloquent ORM for database operations, Blade templating engine, and Artisan CLI for code generation. Laravel follows the MVC pattern and includes tools for routing, caching, queuing, and testing.'],
];

foreach ($conversation as $turn) {
    $memorySystem->storeConversation($sessionId, $turn['role'], $turn['content']);
    echo "  {$turn['role']}: " . substr($turn['content'], 0, 60) . "...\n";
}

echo "\n";

// Store some long-term knowledge
echo "🧠 Storing long-term knowledge...\n";

$knowledge = [
    "PHP 8.4 was released in November 2024 with property hooks, asymmetric visibility, and new array functions.",
    "Composer is the dependency manager for PHP, used to install and manage libraries and packages.",
    "PSR standards define coding styles and interfaces for PHP interoperability between frameworks.",
    "The claude-php/claude-php-agent framework provides tools for building AI agents in PHP.",
    "PHPUnit is the most widely used testing framework for PHP applications.",
];

foreach ($knowledge as $fact) {
    $memorySystem->storeKnowledge($fact, ['type' => 'fact', 'domain' => 'php']);
    echo "  • " . substr($fact, 0, 60) . "...\n";
}

echo "\n";

// Test retrieval
echo "🔍 Testing context retrieval...\n\n";

$queries = [
    "What did we discuss about Laravel?",
    "Tell me about PHP 8.4",
    "What is Composer?",
];

foreach ($queries as $query) {
    echo "Query: \"{$query}\"\n";
    
    $context = $memorySystem->retrieveContext($query, $sessionId, 3);
    
    if (isset($context['conversation'])) {
        echo "  📝 From recent conversation:\n";
        foreach ($context['conversation']['items'] as $turn) {
            $preview = substr($turn['content'], 0, 50) . '...';
            echo "     - [{$turn['role']}] {$preview}\n";
        }
    }
    
    if (isset($context['knowledge'])) {
        echo "  🧠 From knowledge base:\n";
        foreach ($context['knowledge']['items'] as $item) {
            $preview = substr($item['content'], 0, 60) . '...';
            echo "     - {$preview}\n";
        }
    }
    
    echo "\n";
}

// Consolidate session
echo "💾 Consolidating session into long-term memory...\n";
$beforeCount = $memorySystem->getStats()['long_term_items'];
$memorySystem->consolidateSession($sessionId);
$afterCount = $memorySystem->getStats()['long_term_items'];
echo "  Added " . ($afterCount - $beforeCount) . " items to long-term memory\n\n";

// Display statistics
echo "📊 Memory System Statistics:\n";
$stats = $memorySystem->getStats();
echo "  • Active conversation sessions: {$stats['conversation_sessions']}\n";
echo "  • Long-term memory items: {$stats['long_term_items']}\n";
echo "  • Vector store size: {$stats['vector_store_size']}\n";

echo "\n✅ Memory + RAG Integration demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Short-term conversation memory\n";
echo "   • Long-term knowledge storage\n";
echo "   • Semantic retrieval (RAG)\n";
echo "   • Context-aware responses\n";
echo "   • Session consolidation\n";
echo "\n💡 Production enhancements:\n";
echo "   • Use real vector database (Pinecone, Weaviate, pgvector)\n";
echo "   • Implement actual embeddings (OpenAI, Cohere)\n";
echo "   • Add relevance scoring and reranking\n";
echo "   • Implement memory pruning strategies\n";
echo "   • Add entity extraction and tracking\n";
