# Chapter 07: Long-Term Memory with Datastores — Code Examples

This directory contains complete, runnable examples for Chapter 07.

## Prerequisites

- PHP 8.4+
- `claude-php/agent` package installed
- SQLite or MySQL database
- Anthropic API key in environment (`ANTHROPIC_API_KEY`)

## Examples

### 1. Memory Schema Design
**File:** `memory-schema-design.php`

Creates database tables for long-term memory storage.

```bash
php memory-schema-design.php
```

### 2. Basic Memory Storage
**File:** `basic-memory-storage.php`

Demonstrates storing and retrieving memories with tags and metadata.

```bash
php basic-memory-storage.php
```

### 3. Embedding and Semantic Search
**File:** `embedding-semantic-search.php`

Shows semantic search using embeddings for meaning-based retrieval.

```bash
php embedding-semantic-search.php
```

### 4. Relevance Scoring
**File:** `relevance-scoring.php`

Implements multi-signal relevance scoring (semantic + recency + frequency).

```bash
php relevance-scoring.php
```

### 5. Entity Memory Tracking
**File:** `entity-memory-tracking.php`

Extracts and tracks entities (people, places, organizations) from conversations.

```bash
php entity-memory-tracking.php
```

### 6. Knowledge Graph Memory
**File:** `knowledge-graph-memory.php`

Builds a knowledge graph of entities and relationships.

```bash
php knowledge-graph-memory.php
```

### 7. Memory Lifecycle Management
**File:** `memory-lifecycle-management.php`

Handles memory pruning, consolidation, and maintenance.

```bash
php memory-lifecycle-management.php
```

### 8. Production Memory System
**File:** `production-memory-system.php`

Complete production-ready memory system combining all concepts.

```bash
php production-memory-system.php
```

## Running All Examples

```bash
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Database Setup

Most examples create SQLite databases automatically. For MySQL:

```php
$dsn = 'mysql:host=localhost;dbname=agent_memory;charset=utf8mb4';
$username = 'your_username';
$password = 'your_password';
```

## Notes

- **Mock Embeddings:** Development examples use mock embeddings. For production, integrate Voyage AI, OpenAI, or Cohere.
- **Performance:** For large-scale deployments, consider Pinecone, Weaviate, or Qdrant for vector storage.
- **Entity Extraction:** Uses Claude for entity extraction. This requires API calls.

## Related Chapters

- **Chapter 06:** Short-term memory and conversation state
- **Chapter 08:** RAG pipelines with document retrieval
- **Chapter 09:** Planning with memory-augmented agents
