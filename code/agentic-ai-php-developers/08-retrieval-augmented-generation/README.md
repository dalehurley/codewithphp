# Chapter 08 Code Examples: Retrieval-Augmented Generation (RAG)

This directory contains complete, runnable examples for Chapter 08 on Retrieval-Augmented Generation.

## Examples Overview

1. **[basic-rag-pipeline.php](./basic-rag-pipeline.php)** — Simple RAG pipeline with keyword retrieval
2. **[document-chunking-strategies.php](./document-chunking-strategies.php)** — Different chunking approaches
3. **[semantic-vector-search.php](./semantic-vector-search.php)** — Embedding-based semantic retrieval
4. **[citation-generation.php](./citation-generation.php)** — Citation-style response generation
5. **[query-transformation.php](./query-transformation.php)** — Query transformation techniques
6. **[reranking-results.php](./reranking-results.php)** — Reranking for better relevance
7. **[document-loaders.php](./document-loaders.php)** — Loading documents from various sources
8. **[production-rag-system.php](./production-rag-system.php)** — Complete production RAG system

## Prerequisites

```bash
# Install dependencies (from project root)
composer install

# Set API key
export ANTHROPIC_API_KEY='your-api-key-here'
```

## Running Examples

```bash
# Run any example
php basic-rag-pipeline.php
php document-chunking-strategies.php
php semantic-vector-search.php
php citation-generation.php
php query-transformation.php
php reranking-results.php
php document-loaders.php
php production-rag-system.php
```

## What Each Example Teaches

### 1. Basic RAG Pipeline

**Concepts:**
- Document indexing
- Chunk-based retrieval
- Citation generation
- Metadata filtering

**Key Learnings:**
- How RAG reduces hallucinations
- Importance of source attribution
- Token usage in RAG queries

### 2. Document Chunking Strategies

**Concepts:**
- Sentence-based chunking
- Recursive character splitting
- Token-aware splitting
- Markdown/code-aware splitting

**Key Learnings:**
- Chunk size vs context trade-offs
- Overlap for preventing information loss
- Domain-specific chunking strategies

### 3. Semantic Vector Search

**Concepts:**
- Embedding-based retrieval
- Cosine similarity
- Semantic vs keyword matching
- Vector stores

**Key Learnings:**
- Embeddings capture meaning
- Works across synonyms
- Production embedding services

### 4. Citation Generation

**Concepts:**
- Source attribution
- Citation formats
- Verification support
- Trust signals

**Key Learnings:**
- Citations ground responses
- Metadata builds credibility
- Multiple citation styles

### 5. Query Transformation

**Concepts:**
- Multi-query generation
- HyDE (Hypothetical Document Embeddings)
- Query decomposition
- Improved recall

**Key Learnings:**
- Transform queries to improve retrieval
- Multiple perspectives increase coverage
- Complex queries benefit from decomposition

### 6. Reranking Results

**Concepts:**
- Score-based reranking
- LLM semantic reranking
- Hybrid pipelines
- Relevance scoring

**Key Learnings:**
- Reranking improves precision
- Balance cost vs quality
- Combine multiple signals

### 7. Document Loaders

**Concepts:**
- Text file loading
- JSON/CSV loading
- Directory traversal
- Web content loading

**Key Learnings:**
- Choose loader by source type
- Metadata enrichment
- Batch processing patterns

### 8. Production RAG System

**Concepts:**
- Complete RAG implementation
- RAGAgent usage
- Health checks
- Monitoring and metrics

**Key Learnings:**
- Production architecture
- Error handling and logging
- Performance optimization
- System reliability

## Common Patterns

### Initializing RAG Pipeline

```php
use ClaudeAgents\RAG\RAGPipeline;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$rag = RAGPipeline::create($client);
```

### Adding Documents

```php
$rag->addDocument(
    title: 'Document Title',
    content: 'Document content...',
    metadata: ['category' => 'docs', 'version' => '2024']
);
```

### Querying with Filters

```php
$result = $rag->query(
    question: 'What is the refund policy?',
    topK: 3,
    filters: ['category' => 'policy']
);
```

### Using RAGAgent

```php
use ClaudeAgents\Factory\AgentFactory;

$factory = new AgentFactory($client);
$agent = $factory->createRAGAgent([
    'name' => 'my_rag_agent',
    'top_k' => 5,
]);

$agent->addDocument('title', 'content', []);
$result = $agent->run('question');

echo $result->getAnswer();
```

## Testing

All examples are self-contained and can be run independently:

```bash
# Test all examples
for file in *.php; do
    echo "Testing $file..."
    php "$file" > /dev/null
    if [ $? -eq 0 ]; then
        echo "✓ $file passed"
    else
        echo "✗ $file failed"
    fi
done
```

## Troubleshooting

### API Key Issues

```bash
# Verify key is set
echo $ANTHROPIC_API_KEY

# Set if needed
export ANTHROPIC_API_KEY='sk-ant-...'
```

### Memory Issues

For large document collections, increase PHP memory:

```bash
php -d memory_limit=512M production-rag-system.php
```

### Missing Dependencies

```bash
# Reinstall dependencies
composer install --no-cache
```

## Next Steps

1. **Experiment:** Modify examples with your own data
2. **Integrate:** Use patterns in your applications
3. **Optimize:** Tune chunk sizes and retrieval parameters
4. **Deploy:** Apply production patterns from example 8

## Related Chapters

- **Chapter 07:** Long-Term Memory with Datastores
- **Chapter 09:** Planning: From Tasks to Steps
- **Chapter 17:** Evaluation Harnesses and QA

## Resources

- [claude-php-agent RAG Documentation](https://github.com/claude-php/claude-php-agent/tree/master/src/RAG)
- [RAG Paper (Lewis et al., 2020)](https://arxiv.org/abs/2005.11401)
- [Voyage AI Embeddings](https://www.voyageai.com/)
- [Advanced RAG Techniques](https://www.pinecone.io/learn/advanced-rag-techniques/)

## License

Examples provided for educational purposes as part of the "Agentic AI for PHP Developers" tutorial series.
