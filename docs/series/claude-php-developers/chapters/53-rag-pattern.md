---
title: "53: RAG Pattern"
description: "Build rag pattern with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 53
order: 53
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/52-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![53: RAG Pattern](/images/claude-php/chapter-53-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 53</span>
</div>

# Chapter 53: RAG Pattern

## Overview

This chapter is based on Tutorial 13 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 52** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement RAG pipelines for knowledge-grounded responses
- Build document retrieval systems
- Integrate external knowledge bases
- Chunk and embed documents effectively
- Combine retrieval with generation
- Handle citation and source attribution
- Optimize retrieval quality and performance

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



RAG (Retrieval-Augmented Generation) enhances AI agents with external knowledge by retrieving relevant information before generating responses. This grounds outputs in facts and extends agent capabilities beyond training data.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement RAG pipelines for knowledge-grounded responses
- Build document retrieval systems
- Integrate external knowledge bases
- Chunk and embed documents effectively
- Combine retrieval with generation
- Handle citation and source attribution
- Optimize retrieval quality and performance

### 🏗️ What We're Building

A RAG system with:

1. **Document Store** - Knowledge base of documents
2. **Chunking System** - Break documents into retrievable pieces
3. **Retriever** - Find relevant chunks for queries
4. **Context Builder** - Format retrieved content
5. **Generator** - Claude with enhanced context
6. **Citation System** - Track and attribute sources

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 12: Multi-Agent Debate](../12-multi-agent-debate/)
- Understanding of information retrieval concepts
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is RAG?

RAG combines retrieval and generation:

```
Without RAG:
Question → Claude → Answer (limited to training data)

With RAG:
Question → Retrieve Relevant Docs → Claude + Context → Grounded Answer
```

#### Why RAG?

**Benefits:**

- ✅ **Current Information** - Beyond training cutoff
- ✅ **Domain Expertise** - Use private documents
- ✅ **Factual Grounding** - Reduce hallucinations
- ✅ **Citations** - Traceable sources
- ✅ **Dynamic Updates** - Add knowledge without retraining

**Challenges:**

- ❌ **Retrieval Quality** - Finding right documents
- ❌ **Context Length** - Fitting retrieved docs
- ❌ **Latency** - Extra retrieval step
- ❌ **Cost** - More tokens from context

### 🔑 Key Concepts

#### 1. Document Chunking

Break documents into retrievable pieces:

```php
function chunkDocument($text, $chunkSize = 500, $overlap = 50) {
    $chunks = [];
    $words = explode(' ', $text);

    for ($i = 0; $i < count($words); $i += ($chunkSize - $overlap)) {
        $chunk = implode(' ', array_slice($words, $i, $chunkSize));
        if (!empty($chunk)) {
            $chunks[] = [
                'text' => $chunk,
                'start' => $i,
                'end' => min($i + $chunkSize, count($words))
            ];
        }
    }

    return $chunks;
}
```

#### 2. Similarity Search

Find relevant chunks (simplified keyword matching):

```php
function searchChunks($query, $chunks, $topK = 3) {
    $queryTerms = array_map('strtolower', explode(' ', $query));
    $scored = [];

    foreach ($chunks as $i => $chunk) {
        $chunkText = strtolower($chunk['text']);
        $score = 0;

        foreach ($queryTerms as $term) {
            $score += substr_count($chunkText, $term);
        }

        $scored[] = ['index' => $i, 'score' => $score, 'chunk' => $chunk];
    }

    // Sort by score descending
    usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

    return array_slice($scored, 0, $topK);
}
```

#### 3. Context Building

Format retrieved chunks for Claude:

```php
function buildContext($retrievedChunks) {
    $context = "Relevant information:\n\n";

    foreach ($retrievedChunks as $i => $item) {
        $source = $item['chunk']['source'] ?? 'Unknown';
        $text = $item['chunk']['text'];

        $context .= "[Source {$i}] {$source}:\n{$text}\n\n";
    }

    return $context;
}
```

#### 4. RAG Query

Complete retrieval + generation:

```php
function ragQuery($client, $query, $documents) {
    // 1. Retrieve relevant chunks
    $allChunks = [];
    foreach ($documents as $doc) {
        $chunks = chunkDocument($doc['content']);
        foreach ($chunks as $chunk) {
            $chunk['source'] = $doc['title'];
            $allChunks[] = $chunk;
        }
    }

    $retrieved = searchChunks($query, $allChunks, 3);

    // 2. Build context
    $context = buildContext($retrieved);

    // 3. Generate with context
    $prompt = "{$context}\n\nQuestion: {$query}\n\n" .
              "Answer based on the provided sources. " .
              "Cite sources using [Source N] notation.";

    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 2048,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ]);

    return extractTextContent($response);
}
```

### 💡 RAG Implementation Patterns

#### Basic RAG System

```php
class BasicRAG {
    private $client;
    private $documents = [];
    private $chunks = [];

    public function __construct($client) {
        $this->client = $client;
    }

    public function addDocument($title, $content) {
        $this->documents[] = ['title' => $title, 'content' => $content];

        // Chunk and store
        $chunks = $this->chunk($content);
        foreach ($chunks as $chunk) {
            $this->chunks[] = [
                'source' => $title,
                'text' => $chunk
            ];
        }
    }


## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 54](/series/claude-php-developers/chapters/54-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 13 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/13-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="53"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 54](/series/claude-php-developers/chapters/54-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 13 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/13-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/13-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
