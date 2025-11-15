# Chapter 31: Retrieval Augmented Generation (RAG)

Build intelligent RAG systems that enhance Claude's responses with relevant context from your documents and knowledge base.

## Overview

This chapter demonstrates how to implement RAG (Retrieval Augmented Generation) systems that:
- Chunk documents into semantic segments
- Generate embeddings for efficient similarity search
- Retrieve relevant context for queries
- Enhance Claude responses with retrieved information

## Installation

```bash
composer install
cp .env.example .env
# Edit .env with your API keys
```

## Structure

```
chapter-31/
├── composer.json
├── .env.example
├── README.md
├── src/
│   ├── ChunkingService.php       # Document chunking
│   ├── EmbeddingService.php      # Embedding generation
│   ├── RetrievalEngine.php       # Context retrieval
│   └── RAGPipeline.php           # Complete RAG workflow
└── examples/
    ├── chunking.php              # Chunking strategies
    ├── embedding-service.php     # Embedding examples
    ├── retrieval-engine.php      # Retrieval demonstrations
    └── rag-pipeline.php          # Full RAG implementation
```

## Examples

### 1. Document Chunking
Split documents into semantic chunks:
```bash
php examples/chunking.php
```

### 2. Embedding Generation
Generate embeddings for search:
```bash
php examples/embedding-service.php
```

### 3. Context Retrieval
Retrieve relevant document sections:
```bash
php examples/retrieval-engine.php
```

### 4. Complete RAG Pipeline
End-to-end RAG implementation:
```bash
php examples/rag-pipeline.php
```

## Features

- **Intelligent Chunking**: Semantic-aware document segmentation
- **Embedding Support**: Compatible with various embedding providers
- **Similarity Search**: Efficient context retrieval
- **Hybrid Retrieval**: Combine semantic and keyword search
- **Context Ranking**: Relevance scoring and re-ranking
- **Caching**: Performance optimization
- **Multi-document Support**: Query across multiple sources

## Use Cases

- Technical documentation Q&A
- Customer support knowledge bases
- Legal document analysis
- Research paper summaries
- Product information retrieval
- Internal wiki search
- Code documentation lookup

## Requirements

- PHP 8.2+
- Anthropic API key
- Embedding service (OpenAI, Cohere, or custom)

## Learn More

Full documentation: [Chapter 31 - RAG](https://codewithphp.com/series/claude-php-developers/chapters/31-retrieval-augmented-generation)
