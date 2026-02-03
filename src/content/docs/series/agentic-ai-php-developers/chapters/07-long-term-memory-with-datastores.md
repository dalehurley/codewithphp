---
title: "07: Long-Term Memory with Datastores"
description: "Design long-term memory tables, embeddings, and relevance scoring. Decide what to store and when to retrieve."
series: "agentic-ai-php-developers"
chapter: 7
difficulty: "Advanced"
keywords: ["Long-Term Memory PHP", "Vector Database", "Embeddings", "Semantic Search", "Knowledge Storage", "Relevance Scoring"]
teaches: ["Long-term memory architecture", "Database schema design for AI memory", "Embedding-based semantic search", "Relevance scoring algorithms", "Memory lifecycle management", "Vector store implementation"]
estimatedTime: "PT120M"
---

# Chapter 07: Long-Term Memory with Datastores

## Overview

In Chapter 06, you built agents with short-term memory that could maintain coherent conversations within a session. But what happens when the session ends? What if you need to remember facts across days, weeks, or months? What if you want your agent to recall specific details about thousands of users, products, or documents?

This is where **long-term memory** comes in: the ability to store and retrieve information persistently, semantically, and efficiently. Unlike short-term memory (which keeps recent conversation context), long-term memory is about **knowledge retention**, **semantic recall**, and **cross-session persistence**.

The [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) framework provides comprehensive long-term memory capabilities through `EntityMemory`, `ConversationKGMemory`, `VectorStore`, and `MemoryManagerAgent`. In this chapter, you'll learn to design memory systems that scale, retrieve the right information at the right time, and build agents that truly remember.

In this chapter you'll:

- Understand **long-term vs short-term memory** and when to use each
- Design **memory database schemas** for facts, entities, and embeddings
- Implement **embedding-based semantic search** with vector stores
- Build **relevance scoring algorithms** to retrieve the most useful memories
- Create **memory lifecycle management** (what to store, when to prune)
- Use the framework's **MemoryManagerAgent** for production memory systems
- Add **entity tracking** and **knowledge graphs** for structured memory

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use the framework's memory components and RAG systems extensively.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`memory-schema-design.php`](../../code/agentic-ai-php-developers/07-long-term-memory/memory-schema-design.php) — Database schema for long-term memory
- [`basic-memory-storage.php`](../../code/agentic-ai-php-developers/07-long-term-memory/basic-memory-storage.php) — Storing and retrieving memories
- [`embedding-semantic-search.php`](../../code/agentic-ai-php-developers/07-long-term-memory/embedding-semantic-search.php) — Semantic search with embeddings
- [`relevance-scoring.php`](../../code/agentic-ai-php-developers/07-long-term-memory/relevance-scoring.php) — Relevance scoring algorithms
- [`entity-memory-tracking.php`](../../code/agentic-ai-php-developers/07-long-term-memory/entity-memory-tracking.php) — Entity extraction and tracking
- [`knowledge-graph-memory.php`](../../code/agentic-ai-php-developers/07-long-term-memory/knowledge-graph-memory.php) — Knowledge graph for relationships
- [`memory-lifecycle-management.php`](../../code/agentic-ai-php-developers/07-long-term-memory/memory-lifecycle-management.php) — Memory pruning and maintenance
- [`production-memory-system.php`](../../code/agentic-ai-php-developers/07-long-term-memory/production-memory-system.php) — Complete production memory system

All files are in [`code/agentic-ai-php-developers/07-long-term-memory/`](../../code/agentic-ai-php-developers/07-long-term-memory/README.md).
:::

---

## Understanding Long-Term Memory

Before diving into implementation, let's understand what long-term memory means for AI agents.

### Short-Term vs Long-Term Memory

**Short-Term Memory (from Chapter 06):**
- **Scope:** Current conversation session
- **Duration:** Minutes to hours
- **Size:** Last 5-20 turns
- **Storage:** In-memory or session files
- **Purpose:** Maintain conversation coherence

**Long-Term Memory (this chapter):**
- **Scope:** Cross-session, persistent knowledge
- **Duration:** Days, weeks, months, years
- **Size:** Thousands to millions of facts
- **Storage:** Databases with embeddings
- **Purpose:** Knowledge retention and recall

### When to Use Long-Term Memory

Use long-term memory when you need to:

1. **Remember facts across sessions** — User preferences, past conversations, learned information
2. **Scale to large knowledge bases** — Product catalogs, documentation, customer history
3. **Semantic retrieval** — Find relevant information based on meaning, not keywords
4. **Entity tracking** — Track people, places, organizations across time
5. **Relationship modeling** — Understand connections between entities

### Memory Architecture Overview

```text
┌─────────────────────────────────────────────────────────────────┐
│                    LONG-TERM MEMORY ARCHITECTURE                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────┐     ┌──────────────┐     ┌─────────────┐     │
│  │   Storage   │     │  Embeddings  │     │  Retrieval  │     │
│  │   Layer     │────▶│   & Index    │────▶│   & Rank    │     │
│  └─────────────┘     └──────────────┘     └─────────────┘     │
│       │                     │                      │            │
│       ├─ Facts DB          ├─ Vector Store        ├─ Scoring   │
│       ├─ Entities DB       ├─ Embeddings         ├─ Filtering │
│       └─ Relations DB      └─ Similarity         └─ Ranking   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                   Lifecycle Management                   │   │
│  │  ├─ Pruning (delete old/irrelevant memories)           │   │
│  │  ├─ Consolidation (merge similar memories)              │   │
│  │  └─ Archival (move to cold storage)                     │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Memory Database Schema Design

Let's design a database schema for long-term memory.

### Core Tables

**1. Memories Table (Facts)**

```sql
CREATE TABLE memories (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(255),
    content TEXT NOT NULL,
    content_hash VARCHAR(64),
    embedding BLOB,  -- Serialized float array
    source VARCHAR(100),
    confidence FLOAT DEFAULT 1.0,
    importance FLOAT DEFAULT 0.5,
    access_count INT DEFAULT 0,
    last_accessed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    metadata JSON,
    
    INDEX idx_user_id (user_id),
    INDEX idx_source (source),
    INDEX idx_importance (importance),
    INDEX idx_created_at (created_at),
    INDEX idx_access_count (access_count)
);
```

**2. Memory Tags**

```sql
CREATE TABLE memory_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memory_id VARCHAR(36) NOT NULL,
    tag VARCHAR(100) NOT NULL,
    
    INDEX idx_memory_id (memory_id),
    INDEX idx_tag (tag),
    FOREIGN KEY (memory_id) REFERENCES memories(id) ON DELETE CASCADE
);
```

**3. Entities Table**

```sql
CREATE TABLE entities (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,  -- person, place, organization, etc.
    mentions INT DEFAULT 1,
    first_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    attributes JSON,
    
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_mentions (mentions)
);
```

**4. Entity Relationships (Knowledge Graph)**

```sql
CREATE TABLE entity_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id VARCHAR(36) NOT NULL,
    predicate VARCHAR(100) NOT NULL,
    object_id VARCHAR(36) NOT NULL,
    confidence FLOAT DEFAULT 1.0,
    source VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_subject (subject_id),
    INDEX idx_object (object_id),
    INDEX idx_predicate (predicate),
    FOREIGN KEY (subject_id) REFERENCES entities(id) ON DELETE CASCADE,
    FOREIGN KEY (object_id) REFERENCES entities(id) ON DELETE CASCADE
);
```

**5. Memory Access Log**

```sql
CREATE TABLE memory_access_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    memory_id VARCHAR(36) NOT NULL,
    query_text TEXT,
    relevance_score FLOAT,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_memory_id (memory_id),
    INDEX idx_accessed_at (accessed_at),
    FOREIGN KEY (memory_id) REFERENCES memories(id) ON DELETE CASCADE
);
```

---

## Basic Memory Storage and Retrieval

Let's implement basic memory operations using PDO.

### Memory Store Implementation

```php
<?php

declare(strict_types=1);

class MemoryStore
{
    private PDO $pdo;
    
    public function __construct(string $dsn, string $username = '', string $password = '')
    {
        $this->pdo = new PDO($dsn, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * Store a new memory.
     */
    public function store(
        string $content,
        string $userId,
        ?array $embedding = null,
        array $tags = [],
        array $metadata = [],
        string $source = 'user_input',
        float $importance = 0.5
    ): string {
        $id = $this->generateId();
        $contentHash = hash('sha256', $content);
        
        // Check for duplicate content
        $existing = $this->findByContentHash($contentHash, $userId);
        if ($existing) {
            // Update existing memory instead of creating duplicate
            $this->updateAccessCount($existing['id']);
            return $existing['id'];
        }
        
        // Insert memory
        $stmt = $this->pdo->prepare("
            INSERT INTO memories (
                id, user_id, content, content_hash, embedding,
                source, importance, metadata
            ) VALUES (
                :id, :user_id, :content, :content_hash, :embedding,
                :source, :importance, :metadata
            )
        ");
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'content' => $content,
            'content_hash' => $contentHash,
            'embedding' => $embedding ? serialize($embedding) : null,
            'source' => $source,
            'importance' => $importance,
            'metadata' => json_encode($metadata),
        ]);
        
        // Add tags
        if (!empty($tags)) {
            $this->addTags($id, $tags);
        }
        
        return $id;
    }
    
    /**
     * Retrieve a memory by ID.
     */
    public function retrieve(string $memoryId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM memories WHERE id = :id
        ");
        $stmt->execute(['id' => $memoryId]);
        
        $memory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$memory) {
            return null;
        }
        
        // Update access tracking
        $this->updateAccessCount($memoryId);
        
        // Deserialize data
        if ($memory['embedding']) {
            $memory['embedding'] = unserialize($memory['embedding']);
        }
        if ($memory['metadata']) {
            $memory['metadata'] = json_decode($memory['metadata'], true);
        }
        
        // Get tags
        $memory['tags'] = $this->getTags($memoryId);
        
        return $memory;
    }
    
    /**
     * Find memories by tag.
     */
    public function findByTag(string $tag, ?string $userId = null, int $limit = 10): array
    {
        $sql = "
            SELECT DISTINCT m.*
            FROM memories m
            INNER JOIN memory_tags mt ON m.id = mt.memory_id
            WHERE mt.tag = :tag
        ";
        
        $params = ['tag' => $tag];
        
        if ($userId) {
            $sql .= " AND m.user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $sql .= " ORDER BY m.importance DESC, m.created_at DESC LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->processResults($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    /**
     * Get all memories for a user.
     */
    public function getUserMemories(
        string $userId,
        int $limit = 100,
        int $offset = 0
    ): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM memories
            WHERE user_id = :user_id
            ORDER BY importance DESC, last_accessed_at DESC, created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->processResults($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    /**
     * Update memory importance.
     */
    public function updateImportance(string $memoryId, float $importance): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE memories
            SET importance = :importance, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        $stmt->execute([
            'id' => $memoryId,
            'importance' => max(0.0, min(1.0, $importance)),
        ]);
    }
    
    /**
     * Delete a memory.
     */
    public function delete(string $memoryId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM memories WHERE id = :id");
        $stmt->execute(['id' => $memoryId]);
    }
    
    /**
     * Count memories for a user.
     */
    public function count(?string $userId = null): int
    {
        if ($userId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM memories");
        }
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Add tags to a memory.
     */
    private function addTags(string $memoryId, array $tags): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO memory_tags (memory_id, tag) VALUES (:memory_id, :tag)
        ");
        
        foreach ($tags as $tag) {
            $stmt->execute([
                'memory_id' => $memoryId,
                'tag' => strtolower(trim($tag)),
            ]);
        }
    }
    
    /**
     * Get tags for a memory.
     */
    private function getTags(string $memoryId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT tag FROM memory_tags WHERE memory_id = :memory_id
        ");
        $stmt->execute(['memory_id' => $memoryId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Find memory by content hash.
     */
    private function findByContentHash(string $hash, string $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM memories
            WHERE content_hash = :hash AND user_id = :user_id
        ");
        $stmt->execute(['hash' => $hash, 'user_id' => $userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Update access count and timestamp.
     */
    private function updateAccessCount(string $memoryId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE memories
            SET access_count = access_count + 1,
                last_accessed_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute(['id' => $memoryId]);
    }
    
    /**
     * Process database results.
     */
    private function processResults(array $results): array
    {
        foreach ($results as &$result) {
            if (isset($result['embedding']) && $result['embedding']) {
                $result['embedding'] = unserialize($result['embedding']);
            }
            if (isset($result['metadata']) && $result['metadata']) {
                $result['metadata'] = json_decode($result['metadata'], true);
            }
            
            $result['tags'] = $this->getTags($result['id']);
        }
        
        return $results;
    }
    
    /**
     * Generate unique memory ID.
     */
    private function generateId(): string
    {
        return 'mem_' . bin2hex(random_bytes(16));
    }
    
    /**
     * Get PDO connection for advanced operations.
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
```

---

## Embeddings and Semantic Search

Embeddings enable semantic search—finding memories by meaning, not just keywords.

### What Are Embeddings?

**Embeddings** are vector representations of text that capture semantic meaning:

- **Vector:** Array of floats (e.g., 1536 dimensions for OpenAI's embeddings)
- **Similarity:** Similar meanings → similar vectors → high cosine similarity
- **Example:** "PHP programming" and "coding in PHP" have similar embeddings

### Generating Embeddings

For production, use embedding APIs:

- **Voyage AI:** [voyageai.com](https://www.voyageai.com/) — Best for code and technical content
- **OpenAI:** text-embedding-3-small / text-embedding-3-large
- **Cohere:** embed-english-v3.0 / embed-multilingual-v3.0

**Mock Embedding Service (for development):**

```php
<?php

declare(strict_types=1);

/**
 * Mock embedding service for development.
 * In production, replace with Voyage AI, OpenAI, or Cohere.
 */
class MockEmbeddingService
{
    private int $dimensions;
    
    public function __construct(int $dimensions = 384)
    {
        $this->dimensions = $dimensions;
    }
    
    /**
     * Generate embedding for text.
     * 
     * @return float[]
     */
    public function embed(string $text): array
    {
        // Simple mock: use hash to generate deterministic vector
        $hash = hash('sha256', $text);
        $embedding = [];
        
        for ($i = 0; $i < $this->dimensions; $i++) {
            $hexPair = substr($hash, ($i * 2) % 64, 2);
            $value = hexdec($hexPair) / 255.0; // Normalize to [0, 1]
            $embedding[] = ($value - 0.5) * 2.0; // Scale to [-1, 1]
        }
        
        // Normalize to unit vector
        return $this->normalize($embedding);
    }
    
    /**
     * Generate embeddings for multiple texts.
     * 
     * @param string[] $texts
     * @return float[][]
     */
    public function embedBatch(array $texts): array
    {
        return array_map(fn($text) => $this->embed($text), $texts);
    }
    
    /**
     * Calculate cosine similarity between two embeddings.
     */
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
    
    /**
     * Normalize vector to unit length.
     * 
     * @param float[] $vector
     * @return float[]
     */
    private function normalize(array $vector): array
    {
        $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        
        if ($norm === 0.0) {
            return $vector;
        }
        
        return array_map(fn($v) => $v / $norm, $vector);
    }
}
```

### Semantic Search Implementation

```php
<?php

declare(strict_types=1);

class SemanticMemorySearch
{
    private MemoryStore $store;
    private MockEmbeddingService $embeddings;
    
    public function __construct(MemoryStore $store, MockEmbeddingService $embeddings)
    {
        $this->store = $store;
        $this->embeddings = $embeddings;
    }
    
    /**
     * Store memory with embedding.
     */
    public function storeWithEmbedding(
        string $content,
        string $userId,
        array $tags = [],
        array $metadata = [],
        float $importance = 0.5
    ): string {
        // Generate embedding
        $embedding = $this->embeddings->embed($content);
        
        return $this->store->store(
            content: $content,
            userId: $userId,
            embedding: $embedding,
            tags: $tags,
            metadata: $metadata,
            importance: $importance
        );
    }
    
    /**
     * Search memories by semantic similarity.
     */
    public function search(
        string $query,
        ?string $userId = null,
        int $topK = 5,
        float $minSimilarity = 0.5
    ): array {
        // Generate query embedding
        $queryEmbedding = $this->embeddings->embed($query);
        
        // Get all memories (with embeddings)
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
        
        // Calculate similarities
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
        
        // Sort by similarity descending
        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        // Return top K
        $results = array_slice($scored, 0, $topK);
        
        // Process results
        foreach ($results as &$result) {
            $memory = $result['memory'];
            
            if ($memory['metadata']) {
                $memory['metadata'] = json_decode($memory['metadata'], true);
            }
            
            $memory['tags'] = $this->getMemoryTags($memory['id']);
            unset($memory['embedding']); // Don't return raw embedding
            
            $result['memory'] = $memory;
        }
        
        // Log search access
        $this->logSearch($query, $results);
        
        return $results;
    }
    
    /**
     * Find similar memories to a given memory.
     */
    public function findSimilar(string $memoryId, int $topK = 5): array
    {
        $memory = $this->store->retrieve($memoryId);
        
        if (!$memory || !$memory['embedding']) {
            return [];
        }
        
        return $this->searchByEmbedding(
            embedding: $memory['embedding'],
            userId: $memory['user_id'],
            topK: $topK + 1, // +1 to exclude self
            excludeId: $memoryId
        );
    }
    
    /**
     * Search by embedding vector.
     */
    private function searchByEmbedding(
        array $embedding,
        ?string $userId = null,
        int $topK = 5,
        ?string $excludeId = null
    ): array {
        $pdo = $this->store->getPDO();
        
        $sql = "SELECT * FROM memories WHERE embedding IS NOT NULL";
        $params = [];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
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
            $similarity = $this->embeddings->similarity($embedding, $memoryEmbedding);
            
            $scored[] = [
                'memory' => $memory,
                'similarity' => $similarity,
            ];
        }
        
        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return array_slice($scored, 0, $topK);
    }
    
    /**
     * Get memory tags.
     */
    private function getMemoryTags(string $memoryId): array
    {
        $pdo = $this->store->getPDO();
        $stmt = $pdo->prepare("SELECT tag FROM memory_tags WHERE memory_id = :id");
        $stmt->execute(['id' => $memoryId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Log search for analytics.
     */
    private function logSearch(string $query, array $results): void
    {
        $pdo = $this->store->getPDO();
        $stmt = $pdo->prepare("
            INSERT INTO memory_access_log (memory_id, query_text, relevance_score)
            VALUES (:memory_id, :query_text, :relevance_score)
        ");
        
        foreach ($results as $result) {
            $stmt->execute([
                'memory_id' => $result['memory']['id'],
                'query_text' => $query,
                'relevance_score' => $result['similarity'],
            ]);
        }
    }
}
```

---

## Relevance Scoring

Combine multiple signals to rank memories by relevance.

### Multi-Signal Scoring

```php
<?php

declare(strict_types=1);

class RelevanceScorer
{
    private array $weights;
    
    public function __construct(array $weights = [])
    {
        $this->weights = array_merge([
            'semantic' => 0.5,      // Embedding similarity
            'recency' => 0.2,       // How recent
            'access_frequency' => 0.15, // How often accessed
            'importance' => 0.15,   // Stored importance
        ], $weights);
    }
    
    /**
     * Score a memory for relevance.
     */
    public function score(array $memory, float $semanticSimilarity, float $queryTime): float
    {
        $scores = [
            'semantic' => $semanticSimilarity,
            'recency' => $this->recencyScore($memory, $queryTime),
            'access_frequency' => $this->accessFrequencyScore($memory),
            'importance' => $memory['importance'] ?? 0.5,
        ];
        
        $totalScore = 0.0;
        
        foreach ($scores as $signal => $score) {
            $totalScore += $score * $this->weights[$signal];
        }
        
        return $totalScore;
    }
    
    /**
     * Rank memories by relevance.
     */
    public function rank(array $memories, string $query, float $queryTime): array
    {
        foreach ($memories as &$item) {
            $item['relevance_score'] = $this->score(
                memory: $item['memory'],
                semanticSimilarity: $item['similarity'],
                queryTime: $queryTime
            );
        }
        
        // Sort by relevance descending
        usort($memories, fn($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);
        
        return $memories;
    }
    
    /**
     * Calculate recency score (decay over time).
     */
    private function recencyScore(array $memory, float $queryTime): float
    {
        $createdAt = strtotime($memory['created_at']);
        $lastAccessed = $memory['last_accessed_at']
            ? strtotime($memory['last_accessed_at'])
            : $createdAt;
        
        // Use more recent of created or last accessed
        $referenceTime = max($createdAt, $lastAccessed);
        
        // Time difference in days
        $daysSince = max(0, ($queryTime - $referenceTime) / 86400);
        
        // Exponential decay (half-life of 30 days)
        $halfLife = 30.0;
        $decayRate = log(2) / $halfLife;
        
        return exp(-$decayRate * $daysSince);
    }
    
    /**
     * Calculate access frequency score.
     */
    private function accessFrequencyScore(array $memory): float
    {
        $accessCount = $memory['access_count'] ?? 0;
        
        // Logarithmic scaling (diminishing returns)
        if ($accessCount === 0) {
            return 0.0;
        }
        
        return min(1.0, log($accessCount + 1) / log(100)); // Max at 100 accesses
    }
}
```

---

## Entity Memory and Knowledge Graphs

Track entities and their relationships using the framework's components.

### Entity Tracking

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Memory\EntityMemory;
use ClaudeAgents\Memory\Entities\EntityExtractor;
use ClaudeAgents\Memory\Entities\EntityStore;
use ClaudePhp\ClaudePhp;

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create entity extractor and store
$extractor = new EntityExtractor($client);
$entityStore = new EntityStore();

// Create entity memory
$entityMemory = new EntityMemory(
    extractor: $extractor,
    store: $entityStore,
    extractionInterval: 3 // Extract every 3 messages
);

// Add conversation messages
$entityMemory->add([
    'role' => 'user',
    'content' => 'I work at Acme Corporation in San Francisco.',
]);

$entityMemory->add([
    'role' => 'assistant',
    'content' => 'That\'s great! San Francisco is a wonderful city for tech companies.',
]);

$entityMemory->add([
    'role' => 'user',
    'content' => 'Yes, I report to Sarah Johnson, our CTO.',
]);

// Extract entities
$entityMemory->extractEntities();

// Get all entities
$entities = $entityMemory->getEntityStore()->all();

echo "Extracted Entities:\n";
foreach ($entities as $name => $entity) {
    echo "\n";
    echo "Name: {$name}\n";
    echo "Type: {$entity['type']}\n";
    echo "Mentions: {$entity['mentions']}\n";
    
    if (!empty($entity['attributes'])) {
        echo "Attributes:\n";
        foreach ($entity['attributes'] as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    }
}

// Search entities
$orgs = $entityMemory->getEntitiesByType('organization');
echo "\nOrganizations:\n";
foreach ($orgs as $name => $entity) {
    echo "  - {$name}\n";
}

// Get entity info
$acmeInfo = $entityMemory->getEntity('Acme Corporation');
if ($acmeInfo) {
    echo "\nAcme Corporation Details:\n";
    print_r($acmeInfo);
}
```

### Knowledge Graph Memory

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Memory\ConversationKGMemory;
use ClaudeAgents\Memory\Entities\EntityExtractor;
use ClaudePhp\ClaudePhp;

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$extractor = new EntityExtractor($client);

// Create knowledge graph memory
$kgMemory = new ConversationKGMemory(
    client: $client,
    extractor: $extractor,
    options: [
        'extraction_interval' => 2,
        'model' => 'claude-haiku-4-5', // Fast model for extraction
    ]
);

// Add conversation
$kgMemory->add([
    'role' => 'user',
    'content' => 'Alice works at Google in Mountain View.',
]);

$kgMemory->add([
    'role' => 'user',
    'content' => 'She is the manager of the ML team.',
]);

$kgMemory->add([
    'role' => 'user',
    'content' => 'Bob reports to Alice and works on TensorFlow.',
]);

// Extract knowledge
$kgMemory->extractKnowledge();

// Get entities and relationships
$entities = $kgMemory->getEntities();
$relationships = $kgMemory->getRelationships();

echo "Entities:\n";
foreach ($entities as $name => $entity) {
    echo "  - {$name} ({$entity['type']})\n";
}

echo "\nRelationships:\n";
foreach ($relationships as $rel) {
    echo "  - {$rel['subject']} {$rel['predicate']} {$rel['object']}\n";
}

// Query relationships
echo "\nAlice's Relationships:\n";
$aliceRels = $kgMemory->getEntityRelationships('Alice');
foreach ($aliceRels as $rel) {
    echo "  - {$rel['subject']} {$rel['predicate']} {$rel['object']}\n";
}

// Query specific predicate
$worksAtRels = $kgMemory->query(null, 'works_at', null);
echo "\n'works_at' Relationships:\n";
foreach ($worksAtRels as $rel) {
    echo "  - {$rel['subject']} works at {$rel['object']}\n";
}

// Get context for LLM
$context = $kgMemory->getContext();
echo "\nContext for LLM (" . count($context) . " messages):\n";
echo $context[0]['content'] . "\n";
```

---

## Memory Lifecycle Management

Decide what to keep, what to prune, and when to archive.

### Memory Pruning Strategies

```php
<?php

declare(strict_types=1);

class MemoryLifecycleManager
{
    private MemoryStore $store;
    private RelevanceScorer $scorer;
    
    public function __construct(MemoryStore $store, RelevanceScorer $scorer)
    {
        $this->store = $store;
        $this->scorer = $scorer;
    }
    
    /**
     * Prune old, low-value memories.
     */
    public function pruneMemories(
        string $userId,
        int $maxMemories = 1000,
        float $minImportance = 0.1
    ): array {
        $pdo = $this->store->getPDO();
        
        // Get memory count
        $count = $this->store->count($userId);
        
        if ($count <= $maxMemories) {
            return ['deleted' => 0, 'reason' => 'under_limit'];
        }
        
        // Calculate how many to prune
        $toPrune = $count - $maxMemories;
        
        // Get candidates for deletion (low importance, old, rarely accessed)
        $stmt = $pdo->prepare("
            SELECT id, importance, access_count, UNIX_TIMESTAMP(created_at) as created_timestamp
            FROM memories
            WHERE user_id = :user_id
            ORDER BY importance ASC, access_count ASC, created_at ASC
            LIMIT :limit
        ");
        
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('limit', $toPrune * 2, PDO::PARAM_INT); // Get 2x for safety
        $stmt->execute();
        
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Score candidates
        $now = time();
        $scored = [];
        
        foreach ($candidates as $memory) {
            $score = $this->scorer->score(
                memory: $memory,
                semanticSimilarity: 0.0, // Not relevant to query
                queryTime: $now
            );
            
            if ($memory['importance'] < $minImportance || $score < 0.2) {
                $scored[] = [
                    'id' => $memory['id'],
                    'score' => $score,
                ];
            }
        }
        
        // Sort by score ascending (worst first)
        usort($scored, fn($a, $b) => $a['score'] <=> $b['score']);
        
        // Delete lowest scoring memories
        $toDelete = array_slice($scored, 0, $toPrune);
        $deleted = 0;
        
        foreach ($toDelete as $item) {
            $this->store->delete($item['id']);
            $deleted++;
        }
        
        return [
            'deleted' => $deleted,
            'reason' => 'pruning',
            'remaining' => $count - $deleted,
        ];
    }
    
    /**
     * Delete expired memories.
     */
    public function deleteExpired(): int
    {
        $pdo = $this->store->getPDO();
        
        $stmt = $pdo->prepare("
            DELETE FROM memories
            WHERE expires_at IS NOT NULL AND expires_at < CURRENT_TIMESTAMP
        ");
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Consolidate duplicate memories.
     */
    public function consolidateDuplicates(string $userId): int
    {
        $pdo = $this->store->getPDO();
        
        // Find duplicates by content hash
        $stmt = $pdo->prepare("
            SELECT content_hash, GROUP_CONCAT(id) as ids, COUNT(*) as count
            FROM memories
            WHERE user_id = :user_id
            GROUP BY content_hash
            HAVING count > 1
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $consolidated = 0;
        
        foreach ($duplicates as $group) {
            $ids = explode(',', $group['ids']);
            
            // Keep first (oldest), delete others
            $keepId = $ids[0];
            $deleteIds = array_slice($ids, 1);
            
            // Update access count on kept memory
            $stmt = $pdo->prepare("
                UPDATE memories
                SET access_count = access_count + :additional
                WHERE id = :id
            ");
            $stmt->execute(['id' => $keepId, 'additional' => count($deleteIds)]);
            
            // Delete duplicates
            foreach ($deleteIds as $deleteId) {
                $this->store->delete($deleteId);
                $consolidated++;
            }
        }
        
        return $consolidated;
    }
    
    /**
     * Boost importance of frequently accessed memories.
     */
    public function promoteImportantMemories(string $userId, int $threshold = 10): int
    {
        $pdo = $this->store->getPDO();
        
        $stmt = $pdo->prepare("
            UPDATE memories
            SET importance = LEAST(1.0, importance + 0.1)
            WHERE user_id = :user_id
              AND access_count >= :threshold
              AND importance < 0.9
        ");
        
        $stmt->execute(['user_id' => $userId, 'threshold' => $threshold]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get memory statistics.
     */
    public function getStats(string $userId): array
    {
        $pdo = $this->store->getPDO();
        
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total,
                AVG(importance) as avg_importance,
                AVG(access_count) as avg_access_count,
                MAX(access_count) as max_access_count,
                COUNT(CASE WHEN expires_at IS NOT NULL THEN 1 END) as with_expiry
            FROM memories
            WHERE user_id = :user_id
        ");
        
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

---

## Production Memory System

Putting it all together with the framework's `MemoryManagerAgent`.

### Complete Implementation

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agents\MemoryManagerAgent;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ProductionMemorySystem
{
    private MemoryManagerAgent $memoryAgent;
    private MemoryStore $store;
    private SemanticMemorySearch $search;
    private MemoryLifecycleManager $lifecycle;
    private Logger $logger;
    
    public function __construct(string $apiKey, string $dbDsn)
    {
        // Setup logger
        $this->logger = new Logger('memory-system');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        // Setup Claude client
        $client = new ClaudePhp(apiKey: $apiKey);
        
        // Setup memory components
        $this->store = new MemoryStore($dbDsn);
        $embeddings = new MockEmbeddingService(); // Replace with Voyage/OpenAI in production
        $this->search = new SemanticMemorySearch($this->store, $embeddings);
        
        $scorer = new RelevanceScorer();
        $this->lifecycle = new MemoryLifecycleManager($this->store, $scorer);
        
        // Setup memory manager agent
        $this->memoryAgent = new MemoryManagerAgent($client, [
            'name' => 'production_memory_manager',
            'max_memories' => 10000,
            'logger' => $this->logger,
        ]);
        
        $this->logger->info('Production memory system initialized');
    }
    
    /**
     * Store a memory with semantic indexing.
     */
    public function remember(
        string $content,
        string $userId,
        array $tags = [],
        array $metadata = [],
        float $importance = 0.5
    ): string {
        $memoryId = $this->search->storeWithEmbedding(
            content: $content,
            userId: $userId,
            tags: $tags,
            metadata: $metadata,
            importance: $importance
        );
        
        $this->logger->info('Stored memory', [
            'memory_id' => $memoryId,
            'user_id' => $userId,
            'importance' => $importance,
        ]);
        
        return $memoryId;
    }
    
    /**
     * Recall relevant memories.
     */
    public function recall(
        string $query,
        string $userId,
        int $topK = 5,
        float $minRelevance = 0.5
    ): array {
        $results = $this->search->search(
            query: $query,
            userId: $userId,
            topK: $topK,
            minSimilarity: $minRelevance
        );
        
        // Apply relevance scoring
        $scorer = new RelevanceScorer();
        $rankedResults = $scorer->rank($results, $query, time());
        
        $this->logger->info('Recalled memories', [
            'query' => $query,
            'user_id' => $userId,
            'results' => count($rankedResults),
        ]);
        
        return $rankedResults;
    }
    
    /**
     * Get memory by ID.
     */
    public function retrieve(string $memoryId): ?array
    {
        return $this->store->retrieve($memoryId);
    }
    
    /**
     * Update memory importance.
     */
    public function setImportance(string $memoryId, float $importance): void
    {
        $this->store->updateImportance($memoryId, $importance);
        
        $this->logger->info('Updated memory importance', [
            'memory_id' => $memoryId,
            'importance' => $importance,
        ]);
    }
    
    /**
     * Forget a memory.
     */
    public function forget(string $memoryId): void
    {
        $this->store->delete($memoryId);
        
        $this->logger->info('Deleted memory', ['memory_id' => $memoryId]);
    }
    
    /**
     * Run maintenance tasks.
     */
    public function runMaintenance(string $userId): array
    {
        $this->logger->info('Running memory maintenance', ['user_id' => $userId]);
        
        $results = [];
        
        // Delete expired
        $expired = $this->lifecycle->deleteExpired();
        $results['expired_deleted'] = $expired;
        $this->logger->info("Deleted {$expired} expired memories");
        
        // Consolidate duplicates
        $consolidated = $this->lifecycle->consolidateDuplicates($userId);
        $results['duplicates_consolidated'] = $consolidated;
        $this->logger->info("Consolidated {$consolidated} duplicate memories");
        
        // Promote important
        $promoted = $this->lifecycle->promoteImportantMemories($userId);
        $results['memories_promoted'] = $promoted;
        $this->logger->info("Promoted {$promoted} important memories");
        
        // Prune if needed
        $pruned = $this->lifecycle->pruneMemories($userId, maxMemories: 1000);
        $results['pruning'] = $pruned;
        $this->logger->info("Pruned {$pruned['deleted']} low-value memories");
        
        return $results;
    }
    
    /**
     * Get user memory statistics.
     */
    public function getStats(string $userId): array
    {
        return $this->lifecycle->getStats($userId);
    }
    
    /**
     * Use natural language with MemoryManagerAgent.
     */
    public function naturalLanguageCommand(string $command, string $userId): string
    {
        $result = $this->memoryAgent->run($command);
        
        if ($result->isSuccess()) {
            return $result->getAnswer();
        }
        
        return "Error: {$result->getError()}";
    }
}

// Usage example
$memorySystem = new ProductionMemorySystem(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    dbDsn: 'sqlite:' . __DIR__ . '/memory.db'
);

// Store memories
echo "=== Storing Memories ===\n\n";

$id1 = $memorySystem->remember(
    content: 'User prefers dark mode for all applications',
    userId: 'user_123',
    tags: ['preferences', 'ui'],
    metadata: ['category' => 'settings'],
    importance: 0.8
);
echo "Stored: {$id1}\n";

$id2 = $memorySystem->remember(
    content: 'User\'s favorite programming language is PHP',
    userId: 'user_123',
    tags: ['preferences', 'programming'],
    metadata: ['category' => 'profile'],
    importance: 0.7
);
echo "Stored: {$id2}\n";

$id3 = $memorySystem->remember(
    content: 'User asked about implementing authentication in Laravel',
    userId: 'user_123',
    tags: ['history', 'technical'],
    metadata: ['category' => 'conversation'],
    importance: 0.5
);
echo "Stored: {$id3}\n\n";

// Recall memories
echo "=== Recalling Memories ===\n\n";

$results = $memorySystem->recall(
    query: 'What are the user\'s programming preferences?',
    userId: 'user_123',
    topK: 3
);

echo "Found " . count($results) . " relevant memories:\n\n";

foreach ($results as $result) {
    echo "Content: {$result['memory']['content']}\n";
    echo "Similarity: " . round($result['similarity'], 3) . "\n";
    echo "Relevance: " . round($result['relevance_score'], 3) . "\n";
    echo "Tags: " . implode(', ', $result['memory']['tags']) . "\n";
    echo "\n";
}

// Get statistics
echo "=== Memory Statistics ===\n\n";

$stats = $memorySystem->getStats('user_123');
echo "Total memories: {$stats['total']}\n";
echo "Average importance: " . round($stats['avg_importance'], 2) . "\n";
echo "Average access count: " . round($stats['avg_access_count'], 1) . "\n\n";

// Run maintenance
echo "=== Running Maintenance ===\n\n";

$maintenance = $memorySystem->runMaintenance('user_123');
print_r($maintenance);
```

---

## Summary

In this chapter, you learned how to build long-term memory systems for AI agents:

✅ **Memory architecture** — Understanding storage, retrieval, and lifecycle  
✅ **Database schema** — Designing tables for memories, entities, relationships  
✅ **Embeddings** — Semantic search with vector representations  
✅ **Relevance scoring** — Multi-signal ranking for memory retrieval  
✅ **Entity tracking** — Extract and track entities with `EntityMemory`  
✅ **Knowledge graphs** — Model relationships with `ConversationKGMemory`  
✅ **Lifecycle management** — Prune, consolidate, and maintain memories  
✅ **Production patterns** — Complete memory system with the framework

With long-term memory, your agents can now remember facts across sessions, retrieve relevant information semantically, and build rich knowledge bases over time.

---

## Practice Exercises

### Exercise 1: Implement Voyage AI Embeddings

Replace `MockEmbeddingService` with real Voyage AI embeddings:

- Sign up at [voyageai.com](https://www.voyageai.com/)
- Use their PHP SDK or HTTP API
- Compare results with mock embeddings

### Exercise 2: Add Memory Clustering

Implement clustering to group similar memories:

- Use k-means clustering on embeddings
- Create memory clusters by topic
- Provide cluster-based retrieval

### Exercise 3: Build Memory Analytics Dashboard

Create a dashboard showing:

- Memory growth over time
- Most accessed memories
- Entity relationship graphs
- Embedding similarity heatmaps

### Exercise 4: Implement Cold Storage

Add archival tier for old memories:

- Move rarely accessed memories to S3/cold storage
- Keep metadata in primary database
- Load from archive only when needed

---

## Next Steps

Now that you have long-term memory, you're ready to combine it with document retrieval. In **Chapter 08: Retrieval-Augmented Generation (RAG) for Agents**, you'll build complete RAG pipelines with chunking, indexing, and citation-style responses to ground agent answers in factual sources.

Continue to [Chapter 08](/series/agentic-ai-php-developers/chapters/08-retrieval-augmented-generation-for-agents) →

---

## Further Reading

- [`claude-php/agent` Memory Components](https://github.com/claude-php/claude-php-agent/tree/master/src/Memory)
- [`claude-php/agent` RAG System](https://github.com/claude-php/claude-php-agent/tree/master/src/RAG)
- [Vector Databases for AI](https://www.pinecone.io/learn/vector-database/)
- [Embeddings for Semantic Search](https://www.voyageai.com/blog/embeddings-for-semantic-search)
- [Knowledge Graphs in AI Systems](https://neo4j.com/developer/graph-data-science/)
