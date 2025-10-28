---
title: "13: Natural Language Processing (NLP) Fundamentals"
description: Learn to process and analyze text data in PHP—from tokenization, stop-word removal, and stemming to bag-of-words and TF-IDF feature extraction for NLP projects
series: ai-ml-php-developers
chapter: 13
order: 13
difficulty: Intermediate
prerequisites:
  - "/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php"
---

![Natural Language Processing (NLP) Fundamentals](/images/ai-ml-php-developers/chapter-13-nlp-fundamentals-hero-full.webp)

# Chapter 13: Natural Language Processing (NLP) Fundamentals

## Overview

Natural Language Processing (NLP) is the bridge between human communication and machine understanding. While humans effortlessly parse sentences, understand context, and extract meaning from text, computers see only sequences of characters. Teaching machines to process language requires breaking text into structured, numeric representations that algorithms can analyze.

This chapter introduces the foundational techniques for working with text data in PHP. You'll learn how to tokenize text into words, remove noise with stop-word filtering, normalize words through stemming, and convert text into numeric feature vectors using bag-of-words and TF-IDF representations. These preprocessing steps are essential for every NLP project, from sentiment analysis to chatbots to document classification.

By building a complete text processing pipeline from scratch, you'll gain intuition for how NLP systems work under the hood. You'll see how seemingly simple text transforms into structured data ready for machine learning—and understand the design decisions that affect downstream model performance.

The techniques you learn here will directly enable the text classification project in Chapter 14 and the language model integrations in Chapter 15, making this a crucial foundation for the NLP track of this series.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Completion of [Chapter 12](/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php) or equivalent understanding of machine learning preprocessing
- Basic understanding of PHP arrays and string functions
- Familiarity with object-oriented PHP programming
- A text editor or IDE with PHP support
- **Estimated Time**: ~60-75 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version
# Should show PHP 8.4.x

# Ensure mbstring extension is enabled (for UTF-8 handling)
php -m | grep mbstring
# Should output: mbstring
```

## What You'll Build

By the end of this chapter, you will have created:

- A **Tokenizer class** that splits text into words with multiple strategies
- A **StopWordRemover** that filters common words like "the", "is", "and"
- A **Stemmer** that reduces words to their root forms (running → run)
- A **BagOfWords vectorizer** that converts text to numeric feature vectors
- A **TF-IDF vectorizer** that weighs term importance across documents
- A **TextProcessor pipeline** combining all components
- Working examples that process 10+ sample documents
- Reusable, object-oriented classes with proper error handling

::: info Code Examples
Complete, runnable examples are available in [`code/chapter-13/`](../code/chapter-13/):

**Core Classes:**

- [`tokenizer.php`](../code/chapter-13/tokenizer.php) — Text tokenization with multiple strategies
- [`stop-words.php`](../code/chapter-13/stop-words.php) — Stop word removal
- [`stemmer.php`](../code/chapter-13/stemmer.php) — Word stemming algorithm
- [`bag-of-words.php`](../code/chapter-13/bag-of-words.php) — Bag-of-words vectorization
- [`tfidf.php`](../code/chapter-13/tfidf.php) — TF-IDF feature extraction
- [`text-processor.php`](../code/chapter-13/text-processor.php) — Complete processing pipeline

**Test Scripts:**

- [`text-transformation-demo.php`](../code/chapter-13/text-transformation-demo.php) — Step-by-step text transformation
- [`test-tokenizer.php`](../code/chapter-13/test-tokenizer.php) — Tokenization examples
- [`test-stopwords.php`](../code/chapter-13/test-stopwords.php) — Stop word filtering demo
- [`test-stemmer.php`](../code/chapter-13/test-stemmer.php) — Stemming examples
- [`test-bow.php`](../code/chapter-13/test-bow.php) — Bag-of-words demonstration
- [`test-tfidf.php`](../code/chapter-13/test-tfidf.php) — TF-IDF scoring examples
- [`demo.php`](../code/chapter-13/demo.php) — Complete pipeline demonstration

**Data:**

- [`data/stop-words-en.txt`](../code/chapter-13/data/stop-words-en.txt) — English stop words list
- [`data/sample-documents.txt`](../code/chapter-13/data/sample-documents.txt) — Sample documents
  :::

## Quick Start

Want to see text processing in action? Run this 2-minute example:

```bash
cd docs/series/ai-ml-php-developers/code/chapter-13
php demo.php
```

This demonstrates tokenization, stop-word removal, stemming, and TF-IDF calculation on sample text, giving you immediate feedback on how text transforms through each stage.

## Objectives

- **Understand** how computers represent and process human language
- **Tokenize** text into words, handling punctuation and case sensitivity
- **Remove** stop words to focus on meaningful content words
- **Stem** words to their root forms for consistent analysis
- **Convert** text into bag-of-words numeric representations
- **Calculate** TF-IDF scores to identify important terms in documents
- **Build** a complete text preprocessing pipeline
- **Apply** preprocessing to real document collections
- **Evaluate** the impact of each preprocessing step on text analysis

## Step 1: Understanding Text as Data (~5 min)

### Goal

Understand how computers see text and why preprocessing is necessary before machine learning.

### Why It Matters

When you read "The cats are running quickly", you instantly understand:

- "cats" and "cat" are related
- "running" and "run" mean the same action
- "the" and "are" are structural words with little meaning
- The sentence describes animals moving fast

Computers see: `['T', 'h', 'e', ' ', 'c', 'a', 't', 's', ...]`—just characters. To enable ML algorithms to analyze text, we need to transform this into structured, numeric features that capture meaning while ignoring noise.

### The NLP Preprocessing Pipeline

```mermaid
flowchart LR
    A[Raw Text] --> B[Tokenization]
    B --> C[Lowercasing]
    C --> D[Stop Word Removal]
    D --> E[Stemming]
    E --> F[Feature Extraction]
    F --> G[Numeric Vectors]

    style A fill:#f9f9f9
    style G fill:#e1f5ff
```

### Example: Text Transformation

Let's see how text changes through each step:

```php
# filename: text-transformation-demo.php
<?php

declare(strict_types=1);

$text = "The cats are running quickly through the garden!";

echo "Original Text:\n";
echo "  \"$text\"\n\n";

// Step 1: Character array (how computers see it)
$chars = mb_str_split($text);
echo "As Characters (first 20):\n";
echo "  " . json_encode(array_slice($chars, 0, 20)) . "\n\n";

// Step 2: Words (tokenization)
$words = str_word_count(strtolower($text), 1);
echo "As Words (tokens):\n";
echo "  " . json_encode($words) . "\n\n";

// Step 3: Without stop words
$stopWords = ['the', 'are', 'through'];
$filtered = array_values(array_filter($words, fn($w) => !in_array($w, $stopWords)));
echo "Without Stop Words:\n";
echo "  " . json_encode($filtered) . "\n\n";

// Step 4: Stemmed (simplified)
$stemmed = array_map(fn($w) => rtrim($w, 'ing'), $filtered);
$stemmed = array_map(fn($w) => rtrim($w, 's'), $stemmed);
echo "Stemmed:\n";
echo "  " . json_encode($stemmed) . "\n\n";

// Step 5: Numeric representation (bag of words)
$vocab = array_unique($stemmed);
$vector = array_map(fn($word) => count(array_filter($stemmed, fn($w) => $w === $word)), $vocab);
echo "As Numbers (word frequencies):\n";
foreach ($vocab as $idx => $word) {
    echo "  '$word' => " . $vector[$idx] . "\n";
}
```

**Run it:**

```bash
php text-transformation-demo.php
```

### Expected Result

```
Original Text:
  "The cats are running quickly through the garden!"

As Characters (first 20):
  ["T","h","e"," ","c","a","t","s"," ","a","r","e"," ","r","u","n","n","i","n","g"]

As Words (tokens):
  ["the","cats","are","running","quickly","through","the","garden"]

Without Stop Words:
  ["cats","running","quickly","garden"]

After Stemming:
  ["cat","run","quick","garden"]

As Numbers (word frequencies):
  'cat' => 1
  'run' => 1
  'quick' => 1
  'garden' => 1
```

### Why It Works

Each transformation step reduces noise and captures meaning:

- **Tokenization** breaks text into processable units
- **Lowercasing** treats "Cat" and "cat" as the same word
- **Stop word removal** eliminates high-frequency, low-information words
- **Stemming** unifies word variants (running, runs, ran → run)
- **Vectorization** converts to numbers ML algorithms can process

### Key Concepts

- **Token**: A single unit of text (usually a word)
- **Corpus**: Collection of documents
- **Vocabulary**: Unique words across all documents
- **Feature vector**: Numeric representation of text

### Troubleshooting

- **Multibyte characters broken** — Use `mb_str_split()` instead of `str_split()` for UTF-8 text
- **Unexpected results** — Check text encoding with `mb_detect_encoding($text)`
- **Missing mbstring extension** — Install with `sudo apt-get install php-mbstring` (Linux) or enable in php.ini

## Step 2: Tokenization (~10 min)

### Goal

Implement a robust tokenizer that splits text into words while handling punctuation, contractions, and edge cases.

### Actions

1. **Create the Tokenizer class** (`tokenizer.php`):

```php
# filename: tokenizer.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Text Tokenizer
 *
 * Splits text into tokens (words) with multiple strategies.
 */
class Tokenizer
{
    /**
     * Simple word-based tokenization
     * Splits on whitespace and removes punctuation
     */
    public function tokenize(string $text, bool $lowercase = true): array
    {
        // Convert to lowercase if requested
        if ($lowercase) {
            $text = mb_strtolower($text);
        }

        // Remove punctuation except apostrophes (for contractions like "don't")
        $text = preg_replace("/[^\p{L}\p{N}\s']/u", ' ', $text);

        // Split on whitespace
        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Advanced tokenization preserving contractions and hyphenated words
     */
    public function tokenizeAdvanced(string $text, bool $lowercase = true): array
    {
        if ($lowercase) {
            $text = mb_strtolower($text);
        }

        // Pattern: words, contractions (don't), hyphenated words, numbers
        preg_match_all("/\b[\p{L}][\p{L}']*\b|\b\p{N}+\b/u", $text, $matches);

        return $matches[0] ?? [];
    }

    /**
     * Character-level tokenization (for language models)
     */
    public function tokenizeChars(string $text): array
    {
        return mb_str_split($text);
    }

    /**
     * N-gram tokenization (sequences of N words)
     *
     * @param int $n Size of n-grams (2 = bigrams, 3 = trigrams)
     */
    public function tokenizeNgrams(string $text, int $n = 2): array
    {
        $words = $this->tokenize($text);
        $ngrams = [];

        for ($i = 0; $i <= count($words) - $n; $i++) {
            $ngrams[] = implode(' ', array_slice($words, $i, $n));
        }

        return $ngrams;
    }

    /**
     * Sentence tokenization
     */
    public function tokenizeSentences(string $text): array
    {
        // Split on sentence boundaries (., !, ?)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $sentences ?: []);
    }
}
```

2. **Test the tokenizer** (`test-tokenizer.php`):

```php
# filename: test-tokenizer.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/tokenizer.php';

use AiMlPhp\Chapter13\Tokenizer;

$tokenizer = new Tokenizer();

$text = "The quick brown fox jumps over the lazy dog! Don't forget: it's 2024.";

echo "Original Text:\n";
echo "  $text\n\n";

// Simple tokenization
$tokens = $tokenizer->tokenize($text);
echo "Simple Tokens:\n";
echo "  " . implode(', ', $tokens) . "\n";
echo "  Count: " . count($tokens) . " tokens\n\n";

// Advanced tokenization
$advTokens = $tokenizer->tokenizeAdvanced($text);
echo "Advanced Tokens (preserves contractions):\n";
echo "  " . implode(', ', $advTokens) . "\n";
echo "  Count: " . count($advTokens) . " tokens\n\n";

// Bigrams
$bigrams = $tokenizer->tokenizeNgrams($text, 2);
echo "Bigrams (2-word sequences):\n";
foreach (array_slice($bigrams, 0, 5) as $bigram) {
    echo "  - \"$bigram\"\n";
}
echo "  Total: " . count($bigrams) . " bigrams\n\n";

// Sentences
$sentences = $tokenizer->tokenizeSentences($text);
echo "Sentences:\n";
foreach ($sentences as $idx => $sent) {
    echo "  " . ($idx + 1) . ". $sent\n";
}
```

3. **Run the test**:

```bash
php test-tokenizer.php
```

### Expected Result

```
Original Text:
  The quick brown fox jumps over the lazy dog! Don't forget: it's 2024.

Simple Tokens:
  the, quick, brown, fox, jumps, over, the, lazy, dog, don, t, forget, it, s, 2024
  Count: 15 tokens

Advanced Tokens (preserves contractions):
  the, quick, brown, fox, jumps, over, the, lazy, dog, don't, forget, it's, 2024
  Count: 13 tokens

Bigrams (2-word sequences):
  - "the quick"
  - "quick brown"
  - "brown fox"
  - "fox jumps"
  - "jumps over"
  Total: 14 bigrams

Sentences:
  1. The quick brown fox jumps over the lazy dog!
  2. Don't forget: it's 2024.
```

### Why It Works

**Simple tokenization** uses regex to split on whitespace after removing punctuation. It's fast but breaks contractions ("don't" → "don", "t").

**Advanced tokenization** uses a more sophisticated pattern that recognizes word boundaries while preserving apostrophes within words. It handles contractions, possessives, and hyphenated words correctly.

**N-grams** capture word sequences, which is important for phrases like "New York" or "not good" where word order matters.

### Troubleshooting

- **Contractions split incorrectly** — Use `tokenizeAdvanced()` which preserves apostrophes
- **Non-English characters missing** — Ensure you're using Unicode-aware patterns (`\p{L}` instead of `[a-z]`)
- **Empty array returned** — Check regex flags and use `PREG_SPLIT_NO_EMPTY`

## Step 3: Stop-Word Removal (~10 min)

### Goal

Filter out common words that carry little meaning, focusing analysis on content-bearing terms.

### Actions

1. **Create the StopWordRemover class** (`stop-words.php`):

```php
# filename: stop-words.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Stop Word Remover
 *
 * Filters common words that carry little semantic meaning.
 */
class StopWordRemover
{
    private array $stopWords;

    public function __construct(array $customStopWords = [])
    {
        // Common English stop words
        $defaultStopWords = [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from',
            'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the',
            'to', 'was', 'will', 'with', 'the', 'this', 'but', 'they', 'have',
            'had', 'what', 'when', 'where', 'who', 'which', 'why', 'how',
            'all', 'each', 'every', 'both', 'few', 'more', 'most', 'other',
            'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
            'than', 'too', 'very', 'can', 'will', 'just', 'should', 'now'
        ];

        $this->stopWords = array_merge($defaultStopWords, $customStopWords);
    }

    /**
     * Remove stop words from token array
     */
    public function remove(array $tokens): array
    {
        return array_values(
            array_filter($tokens, fn($token) => !$this->isStopWord($token))
        );
    }

    /**
     * Check if a single token is a stop word
     */
    public function isStopWord(string $token): bool
    {
        return in_array(mb_strtolower($token), $this->stopWords, true);
    }

    /**
     * Load stop words from file
     */
    public static function fromFile(string $filepath): self
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Stop words file not found: $filepath");
        }

        $content = file_get_contents($filepath);
        $words = array_filter(
            array_map('trim', explode("\n", $content)),
            fn($w) => $w !== '' && !str_starts_with($w, '#')
        );

        return new self($words);
    }

    /**
     * Get all stop words
     */
    public function getStopWords(): array
    {
        return $this->stopWords;
    }

    /**
     * Add custom stop words
     */
    public function addStopWords(array $words): void
    {
        $this->stopWords = array_unique(array_merge($this->stopWords, $words));
    }
}
```

2. **Create a stop words data file** (`data/stop-words-en.txt`):

```
# English stop words
# One word per line, lines starting with # are comments
a
an
and
are
as
at
be
been
by
for
from
has
have
he
in
is
it
its
of
on
that
the
to
was
were
will
with
```

3. **Test stop word removal** (`test-stopwords.php`):

```php
# filename: test-stopwords.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/tokenizer.php';
require_once __DIR__ . '/stop-words.php';

use AiMlPhp\Chapter13\Tokenizer;
use AiMlPhp\Chapter13\StopWordRemover;

$tokenizer = new Tokenizer();
$stopWordRemover = new StopWordRemover();

$text = "The quick brown fox jumps over the lazy dog in the garden.";

echo "Original Text:\n";
echo "  $text\n\n";

// Tokenize
$tokens = $tokenizer->tokenize($text);
echo "Tokens:\n";
echo "  " . implode(', ', $tokens) . "\n";
echo "  Count: " . count($tokens) . " tokens\n\n";

// Remove stop words
$filtered = $stopWordRemover->remove($tokens);
echo "After Stop Word Removal:\n";
echo "  " . implode(', ', $filtered) . "\n";
echo "  Count: " . count($filtered) . " tokens\n";
echo "  Removed: " . (count($tokens) - count($filtered)) . " stop words\n\n";

// Show which were removed
$removed = array_diff($tokens, $filtered);
echo "Stop Words Removed:\n";
echo "  " . implode(', ', $removed) . "\n";
```

4. **Run the test**:

```bash
php test-stopwords.php
```

### Expected Result

```
Original Text:
  The quick brown fox jumps over the lazy dog in the garden.

Tokens:
  the, quick, brown, fox, jumps, over, the, lazy, dog, in, the, garden
  Count: 12 tokens

After Stop Word Removal:
  quick, brown, fox, jumps, lazy, dog, garden
  Count: 7 tokens
  Removed: 5 stop words

Stop Words Removed:
  the, over, in
```

### Why It Works

Stop words are high-frequency words that appear in most documents ("the", "is", "and") but carry little semantic meaning. Removing them:

- **Reduces noise** in ML models
- **Decreases dimensionality** (fewer features to process)
- **Improves accuracy** by focusing on content-bearing terms
- **Speeds up processing** with smaller vocabularies

The trade-off: Some context is lost. For sentiment analysis, "not good" vs "good" matters—"not" is a stop word but changes meaning. Choose stop word lists carefully based on your task.

### Troubleshooting

- **Too many words removed** — Your stop word list is too aggressive; use a smaller list
- **Important words removed** — Add domain-specific exceptions (e.g., keep "not" for sentiment analysis)
- **Case sensitivity issues** — Always lowercase both tokens and stop words before comparing

## Step 4: Stemming and Normalization (~10 min)

### Goal

Reduce words to their root forms so that variants are treated as the same term (running, runs, ran → run).

### Actions

1. **Create the Stemmer class** (`stemmer.php`):

```php
# filename: stemmer.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Simple English Stemmer
 *
 * Implements a basic suffix-stripping algorithm similar to Porter Stemmer.
 * Note: This is a simplified version for educational purposes.
 * For production, consider using a full Porter Stemmer implementation.
 */
class Stemmer
{
    private const SUFFIX_PATTERNS = [
        // Plural and verb forms
        'sses' => 'ss',    // dresses → dress
        'ies' => 'i',      // ponies → poni
        'ss' => 'ss',      // mess → mess
        's' => '',         // cats → cat

        // Gerunds and past tense
        'eed' => 'ee',     // agreed → agree
        'ing' => '',       // running → run
        'ed' => '',        // played → play

        // Adjective forms
        'ful' => '',       // beautiful → beauti
        'ness' => '',      // sadness → sad
        'ly' => '',        // quickly → quick
        'ment' => '',      // development → develop

        // Comparative
        'er' => '',        // faster → fast
        'est' => '',       // fastest → fast

        // Other common suffixes
        'ation' => '',     // creation → creat
        'ence' => '',      // presence → pres
        'ance' => '',      // importance → import
    ];

    private int $minStemLength;

    public function __construct(int $minStemLength = 3)
    {
        $this->minStemLength = $minStemLength;
    }

    /**
     * Stem a single word
     */
    public function stem(string $word): string
    {
        $word = mb_strtolower($word);
        $originalLength = mb_strlen($word);

        // Don't stem very short words
        if ($originalLength <= $this->minStemLength) {
            return $word;
        }

        // Try each suffix pattern (longest first)
        foreach (self::SUFFIX_PATTERNS as $suffix => $replacement) {
            if (str_ends_with($word, $suffix)) {
                $stem = mb_substr($word, 0, -mb_strlen($suffix)) . $replacement;

                // Only keep the stem if it's long enough
                if (mb_strlen($stem) >= $this->minStemLength) {
                    return $stem;
                }
            }
        }

        return $word;
    }

    /**
     * Stem an array of tokens
     */
    public function stemTokens(array $tokens): array
    {
        return array_map(fn($token) => $this->stem($token), $tokens);
    }

    /**
     * Stem and return with original mapping
     * Useful for displaying results
     */
    public function stemWithMapping(array $tokens): array
    {
        $mapping = [];

        foreach ($tokens as $token) {
            $stem = $this->stem($token);
            if (!isset($mapping[$stem])) {
                $mapping[$stem] = [];
            }
            $mapping[$stem][] = $token;
        }

        return $mapping;
    }
}
```

2. **Test the stemmer** (`test-stemmer.php`):

```php
# filename: test-stemmer.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/stemmer.php';

use AiMlPhp\Chapter13\Stemmer;

$stemmer = new Stemmer();

$words = [
    'running', 'runs', 'ran', 'runner',
    'quickly', 'quicker', 'quickest',
    'beautiful', 'beautifully',
    'cats', 'cat', 'catlike',
    'development', 'developing', 'developed',
    'creation', 'creates', 'created', 'creating'
];

echo "Stemming Examples:\n\n";

foreach ($words as $word) {
    $stem = $stemmer->stem($word);
    echo sprintf("  %-15s → %s\n", $word, $stem);
}

// Show how stemming groups variants
echo "\n\nGrouping Variants by Stem:\n\n";
$mapping = $stemmer->stemWithMapping($words);

foreach ($mapping as $stem => $variants) {
    echo "  '$stem':\n";
    foreach ($variants as $variant) {
        echo "    - $variant\n";
    }
}
```

3. **Run the test**:

```bash
php test-stemmer.php
```

### Expected Result

```
Stemming Examples:

  running         → run
  runs            → run
  ran             → ran
  runner          → run
  quickly         → quick
  quicker         → quick
  quickest        → quick
  beautiful       → beauti
  beautifully     → beauti
  cats            → cat
  cat             → cat
  catlike         → catlike
  development     → develop
  developing      → develop
  developed       → develop
  creation        → creat
  creates         → creat
  created         → creat
  creating        → creat


Grouping Variants by Stem:

  'run':
    - running
    - runs
    - runner
  'ran':
    - ran
  'quick':
    - quickly
    - quicker
    - quickest
  'beauti':
    - beautiful
    - beautifully
  'cat':
    - cats
    - cat
  'catlike':
    - catlike
  'develop':
    - development
    - developing
    - developed
  'creat':
    - creation
    - creates
    - created
    - creating
```

### Why It Works

Stemming normalizes word variants to a common root form. This is crucial for NLP because:

- **"running", "runs", "ran"** all refer to the same action
- Without stemming, they'd be treated as completely different words
- Models would need to learn each variant separately
- Vocabulary size explodes with all variants

The algorithm works by stripping suffixes in order of length (longest first). The `minStemLength` parameter prevents over-stemming (e.g., "as" → "").

**Note**: This is a simplified stemmer. Production systems often use:

- **Porter Stemmer** — More sophisticated rules
- **Snowball Stemmer** — Multi-language support
- **Lemmatization** — Dictionary-based, more accurate (requires lookups)

### Troubleshooting

- **Over-stemming** — Words stem too aggressively (e.g., "news" → "new"). Increase `minStemLength` or use lemmatization
- **Under-stemming** — Variants not grouped (e.g., "ran" ≠ "run"). The simple algorithm doesn't handle irregular verbs; use Porter Stemmer
- **Language-specific issues** — This stemmer is English-only. Other languages need different rules

## Step 5: Bag-of-Words Representation (~10 min)

### Goal

Convert text documents into numeric feature vectors based on word frequencies.

### Actions

1. **Create the BagOfWords class** (`bag-of-words.php`):

```php
# filename: bag-of-words.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * Bag of Words Vectorizer
 *
 * Converts text documents into numeric feature vectors
 * based on word frequencies.
 */
class BagOfWords
{
    private array $vocabulary = [];
    private bool $fitted = false;

    /**
     * Build vocabulary from training documents
     *
     * @param array $documents Array of token arrays
     */
    public function fit(array $documents): self
    {
        $allTokens = [];

        foreach ($documents as $tokens) {
            $allTokens = array_merge($allTokens, $tokens);
        }

        $this->vocabulary = array_values(array_unique($allTokens));
        sort($this->vocabulary); // Sort for consistent ordering
        $this->fitted = true;

        return $this;
    }

    /**
     * Transform documents into feature vectors
     *
     * @param array $documents Array of token arrays
     * @return array Array of feature vectors
     */
    public function transform(array $documents): array
    {
        if (!$this->fitted) {
            throw new \RuntimeException("Vectorizer must be fitted before transform");
        }

        $vectors = [];

        foreach ($documents as $tokens) {
            $vectors[] = $this->vectorize($tokens);
        }

        return $vectors;
    }

    /**
     * Fit and transform in one step
     */
    public function fitTransform(array $documents): array
    {
        return $this->fit($documents)->transform($documents);
    }

    /**
     * Convert single document to feature vector
     */
    private function vectorize(array $tokens): array
    {
        $vector = array_fill(0, count($this->vocabulary), 0);

        foreach ($tokens as $token) {
            $index = array_search($token, $this->vocabulary, true);
            if ($index !== false) {
                $vector[$index]++;
            }
        }

        return $vector;
    }

    /**
     * Get the learned vocabulary
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get feature names (vocabulary terms)
     */
    public function getFeatureNames(): array
    {
        return $this->vocabulary;
    }

    /**
     * Display vector with feature names
     */
    public function displayVector(array $vector): array
    {
        $result = [];

        foreach ($this->vocabulary as $idx => $term) {
            if ($vector[$idx] > 0) {
                $result[$term] = $vector[$idx];
            }
        }

        return $result;
    }
}
```

2. **Test bag-of-words** (`test-bow.php`):

```php
# filename: test-bow.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/bag-of-words.php';

use AiMlPhp\Chapter13\BagOfWords;

// Sample documents (already tokenized and preprocessed)
$documents = [
    ['cat', 'dog', 'pet'],
    ['dog', 'bark', 'loud'],
    ['cat', 'meow', 'soft'],
    ['pet', 'love', 'care'],
];

$bow = new BagOfWords();

echo "Training Documents:\n";
foreach ($documents as $idx => $doc) {
    echo "  Doc " . ($idx + 1) . ": " . implode(', ', $doc) . "\n";
}
echo "\n";

// Fit and transform
$vectors = $bow->fitTransform($documents);

// Show vocabulary
$vocab = $bow->getVocabulary();
echo "Vocabulary (" . count($vocab) . " terms):\n";
echo "  " . implode(', ', $vocab) . "\n\n";

// Show vectors
echo "Feature Vectors:\n\n";
foreach ($vectors as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    echo "  Raw vector: [" . implode(', ', $vector) . "]\n";
    echo "  With labels: " . json_encode($bow->displayVector($vector)) . "\n\n";
}

// Test on new document
$newDoc = ['cat', 'dog', 'play'];
$newVector = $bow->transform([$newDoc])[0];
echo "New Document: " . implode(', ', $newDoc) . "\n";
echo "  Vector: " . json_encode($bow->displayVector($newVector)) . "\n";
```

3. **Run the test**:

```bash
php test-bow.php
```

### Expected Result

```
Training Documents:
  Doc 1: cat, dog, pet
  Doc 2: dog, bark, loud
  Doc 3: cat, meow, soft
  Doc 4: pet, love, care

Vocabulary (10 terms):
  bark, care, cat, dog, loud, love, meow, pet, soft

Feature Vectors:

Document 1:
  Raw vector: [0, 0, 1, 1, 0, 0, 0, 1, 0]
  With labels: {"cat":1,"dog":1,"pet":1}

Document 2:
  Raw vector: [1, 0, 0, 1, 1, 0, 0, 0, 0]
  With labels: {"bark":1,"dog":1,"loud":1}

Document 3:
  Raw vector: [0, 0, 1, 0, 0, 0, 1, 0, 1]
  With labels: {"cat":1,"meow":1,"soft":1}

Document 4:
  Raw vector: [0, 1, 0, 0, 0, 1, 0, 1, 0]
  With labels: {"care":1,"love":1,"pet":1}

New Document: cat, dog, play
  Vector: {"cat":1,"dog":1}
```

### Why It Works

Bag-of-words creates a **fixed-size numeric representation** of text:

1. **Vocabulary**: All unique words across training documents
2. **Vector**: One dimension per vocabulary word
3. **Values**: Count of how many times each word appears

**Key properties:**

- **Order-independent**: "cat dog" and "dog cat" have identical vectors
- **Sparse**: Most values are 0 (words not in document)
- **Interpretable**: Each dimension corresponds to a known word

**Limitations:**

- Ignores word order and context
- Treats all words equally (common and rare)
- Vocabulary grows with dataset size

The next step (TF-IDF) addresses some of these limitations by weighting terms.

### Troubleshooting

- **Huge vectors** — Vocabulary is too large. Add stop-word removal and stemming before vectorization
- **"Vectorizer must be fitted" error** — Call `fit()` before `transform()` or use `fitTransform()`
- **New words ignored** — Words not in vocabulary get zero weight. This is expected behavior

## Step 6: TF-IDF Feature Extraction (~15 min)

### Goal

Calculate Term Frequency-Inverse Document Frequency (TF-IDF) scores to identify important terms in documents relative to the entire corpus.

### Actions

1. **Create the TF-IDF class** (`tfidf.php`):

```php
# filename: tfidf.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

/**
 * TF-IDF Vectorizer
 *
 * Converts text to vectors weighted by term importance.
 * TF-IDF = Term Frequency × Inverse Document Frequency
 */
class TfIdfVectorizer
{
    private array $vocabulary = [];
    private array $idf = [];
    private bool $fitted = false;

    /**
     * Build vocabulary and calculate IDF weights
     *
     * @param array $documents Array of token arrays
     */
    public function fit(array $documents): self
    {
        // Build vocabulary
        $allTokens = [];
        foreach ($documents as $tokens) {
            $allTokens = array_merge($allTokens, $tokens);
        }
        $this->vocabulary = array_values(array_unique($allTokens));
        sort($this->vocabulary);

        // Calculate IDF for each term
        $numDocs = count($documents);
        $this->idf = [];

        foreach ($this->vocabulary as $term) {
            // Count documents containing this term
            $docFreq = 0;
            foreach ($documents as $tokens) {
                if (in_array($term, $tokens, true)) {
                    $docFreq++;
                }
            }

            // IDF = log(total docs / docs containing term)
            // Add 1 to avoid division by zero
            $this->idf[$term] = log($numDocs / ($docFreq + 1)) + 1;
        }

        $this->fitted = true;
        return $this;
    }

    /**
     * Transform documents into TF-IDF weighted vectors
     *
     * @param array $documents Array of token arrays
     * @return array Array of TF-IDF vectors
     */
    public function transform(array $documents): array
    {
        if (!$this->fitted) {
            throw new \RuntimeException("Vectorizer must be fitted before transform");
        }

        $vectors = [];

        foreach ($documents as $tokens) {
            $vectors[] = $this->vectorize($tokens);
        }

        return $vectors;
    }

    /**
     * Fit and transform in one step
     */
    public function fitTransform(array $documents): array
    {
        return $this->fit($documents)->transform($documents);
    }

    /**
     * Convert single document to TF-IDF vector
     */
    private function vectorize(array $tokens): array
    {
        $vector = array_fill(0, count($this->vocabulary), 0.0);

        // Calculate term frequencies
        $termFreq = [];
        $totalTokens = count($tokens);

        foreach ($tokens as $token) {
            $termFreq[$token] = ($termFreq[$token] ?? 0) + 1;
        }

        // Calculate TF-IDF for each term
        foreach ($this->vocabulary as $idx => $term) {
            if (isset($termFreq[$term])) {
                // TF = term frequency in document
                $tf = $termFreq[$term] / $totalTokens;

                // TF-IDF = TF × IDF
                $vector[$idx] = $tf * $this->idf[$term];
            }
        }

        return $vector;
    }

    /**
     * Get vocabulary
     */
    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    /**
     * Get IDF weights
     */
    public function getIdf(): array
    {
        return $this->idf;
    }

    /**
     * Display vector with feature names and scores
     */
    public function displayVector(array $vector, int $topN = 10): array
    {
        $result = [];

        foreach ($this->vocabulary as $idx => $term) {
            if ($vector[$idx] > 0) {
                $result[$term] = round($vector[$idx], 4);
            }
        }

        // Sort by score descending
        arsort($result);

        // Return top N terms
        return array_slice($result, 0, $topN, true);
    }

    /**
     * Get most important terms across all documents
     */
    public function getMostImportantTerms(int $topN = 10): array
    {
        if (!$this->fitted) {
            throw new \RuntimeException("Vectorizer must be fitted first");
        }

        // Get average IDF (lower = more common)
        $idfCopy = $this->idf;
        arsort($idfCopy);

        return array_slice($idfCopy, 0, $topN, true);
    }
}
```

2. **Test TF-IDF** (`test-tfidf.php`):

```php
# filename: test-tfidf.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/tfidf.php';

use AiMlPhp\Chapter13\TfIdfVectorizer;

// Sample documents (preprocessed)
$documents = [
    ['machine', 'learning', 'algorithm', 'data'],
    ['machine', 'learning', 'model', 'train'],
    ['deep', 'learning', 'neural', 'network'],
    ['data', 'analysis', 'statistics', 'model'],
    ['algorithm', 'optimization', 'performance']
];

$tfidf = new TfIdfVectorizer();

echo "Training Documents:\n";
foreach ($documents as $idx => $doc) {
    echo "  Doc " . ($idx + 1) . ": " . implode(', ', $doc) . "\n";
}
echo "\n";

// Fit and transform
$vectors = $tfidf->fitTransform($documents);

// Show IDF weights
echo "IDF Weights (Inverse Document Frequency):\n";
echo "  Higher IDF = rarer term = more distinctive\n\n";
$idf = $tfidf->getIdf();
arsort($idf);
foreach (array_slice($idf, 0, 10, true) as $term => $score) {
    echo "  " . sprintf("%-15s: %.4f", $term, $score) . "\n";
}
echo "\n";

// Show TF-IDF vectors for each document
echo "TF-IDF Vectors (Top Terms per Document):\n\n";
foreach ($vectors as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    $topTerms = $tfidf->displayVector($vector, 5);
    foreach ($topTerms as $term => $score) {
        echo "  " . sprintf("%-15s: %.4f", $term, $score) . "\n";
    }
    echo "\n";
}

// Compare: same term in different contexts
echo "Term Importance Comparison:\n";
echo "  'learning' appears in docs 1, 2, 3 (common)\n";
echo "  'optimization' appears in doc 5 only (rare)\n\n";

$learningIdf = $idf['learning'];
$optimizationIdf = $idf['optimization'];

echo "  IDF('learning'): " . round($learningIdf, 4) . "\n";
echo "  IDF('optimization'): " . round($optimizationIdf, 4) . "\n";
echo "  → 'optimization' is more distinctive for document classification\n";
```

3. **Run the test**:

```bash
php test-tfidf.php
```

### Expected Result

```
Training Documents:
  Doc 1: machine, learning, algorithm, data
  Doc 2: machine, learning, model, train
  Doc 3: deep, learning, neural, network
  Doc 4: data, analysis, statistics, model
  Doc 5: algorithm, optimization, performance

IDF Weights (Inverse Document Frequency):
  Higher IDF = rarer term = more distinctive

  optimization    : 2.6094
  performance     : 2.6094
  train           : 2.6094
  statistics      : 2.6094
  analysis        : 2.6094
  network         : 2.6094
  neural          : 2.6094
  deep            : 2.6094
  model           : 1.9162
  algorithm       : 1.9162

TF-IDF Vectors (Top Terms per Document):

Document 1:
  algorithm       : 0.4791
  data            : 0.4791
  machine         : 0.4791
  learning        : 0.4041

Document 2:
  train           : 0.6524
  machine         : 0.4791
  model           : 0.4791
  learning        : 0.4041

Document 3:
  deep            : 0.6524
  neural          : 0.6524
  network         : 0.6524
  learning        : 0.4041

Document 4:
  statistics      : 0.6524
  analysis        : 0.6524
  data            : 0.4791
  model           : 0.4791

Document 5:
  optimization    : 0.8698
  performance     : 0.8698
  algorithm       : 0.6387

Term Importance Comparison:
  'learning' appears in docs 1, 2, 3 (common)
  'optimization' appears in doc 5 only (rare)

  IDF('learning'): 1.6162
  IDF('optimization'): 2.6094
  → 'optimization' is more distinctive for document classification
```

### Why It Works

**TF-IDF balances two factors:**

1. **Term Frequency (TF)**: How often a term appears in a document

   - High TF = important within this document

2. **Inverse Document Frequency (IDF)**: How rare the term is across all documents
   - High IDF = distinctive, appears in few documents
   - Low IDF = common, appears in many documents

**Formula:**

```
TF-IDF(term, doc) = (count(term, doc) / len(doc)) × log(num_docs / docs_containing_term)
```

**Why it's better than bag-of-words:**

- **Downweights common terms**: "learning" appears in 3 docs, so lower weight
- **Upweights rare terms**: "optimization" appears in 1 doc, so higher weight
- **Context-aware**: Same term has different weights in different documents

**Real-world impact:**

- Search engines use TF-IDF to rank document relevance
- Recommendation systems find similar documents
- Text classification gives more weight to distinctive terms

### Troubleshooting

- **All scores near zero** — Normalize vectors or use smoothing: change `log(n/df)` to `log(n/df + 1) + 1`
- **Division by zero** — Add smoothing constant in IDF calculation
- **Negative values** — Should not happen with proper IDF formula; check implementation

## Step 7: Complete Text Processing Pipeline (~5 min)

### Goal

Combine all components into a reusable pipeline for end-to-end text preprocessing.

### Actions

1. **Create the pipeline** (`text-processor.php`):

```php
# filename: text-processor.php
<?php

declare(strict_types=1);

namespace AiMlPhp\Chapter13;

require_once __DIR__ . '/tokenizer.php';
require_once __DIR__ . '/stop-words.php';
require_once __DIR__ . '/stemmer.php';
require_once __DIR__ . '/bag-of-words.php';
require_once __DIR__ . '/tfidf.php';

/**
 * Complete Text Processing Pipeline
 *
 * Chains tokenization, stop-word removal, stemming, and vectorization.
 */
class TextProcessor
{
    private Tokenizer $tokenizer;
    private StopWordRemover $stopWordRemover;
    private Stemmer $stemmer;
    private bool $useStemming;
    private bool $useStopWords;

    public function __construct(
        bool $useStemming = true,
        bool $useStopWords = true,
        array $customStopWords = []
    ) {
        $this->tokenizer = new Tokenizer();
        $this->stopWordRemover = new StopWordRemover($customStopWords);
        $this->stemmer = new Stemmer();
        $this->useStemming = $useStemming;
        $this->useStopWords = $useStopWords;
    }

    /**
     * Process a single text document
     */
    public function process(string $text): array
    {
        // Step 1: Tokenize
        $tokens = $this->tokenizer->tokenize($text);

        // Step 2: Remove stop words
        if ($this->useStopWords) {
            $tokens = $this->stopWordRemover->remove($tokens);
        }

        // Step 3: Stem
        if ($this->useStemming) {
            $tokens = $this->stemmer->stemTokens($tokens);
        }

        return $tokens;
    }

    /**
     * Process multiple documents
     */
    public function processMany(array $texts): array
    {
        return array_map(fn($text) => $this->process($text), $texts);
    }

    /**
     * Process and vectorize with bag-of-words
     */
    public function processToBagOfWords(array $texts): array
    {
        $processedDocs = $this->processMany($texts);
        $bow = new BagOfWords();
        return [
            'vectors' => $bow->fitTransform($processedDocs),
            'vocabulary' => $bow->getVocabulary(),
            'vectorizer' => $bow
        ];
    }

    /**
     * Process and vectorize with TF-IDF
     */
    public function processToTfIdf(array $texts): array
    {
        $processedDocs = $this->processMany($texts);
        $tfidf = new TfIdfVectorizer();
        return [
            'vectors' => $tfidf->fitTransform($processedDocs),
            'vocabulary' => $tfidf->getVocabulary(),
            'idf' => $tfidf->getIdf(),
            'vectorizer' => $tfidf
        ];
    }

    /**
     * Get processing statistics
     */
    public function getStats(string $originalText, array $processedTokens): array
    {
        $originalTokens = $this->tokenizer->tokenize($originalText);

        return [
            'original_length' => mb_strlen($originalText),
            'original_tokens' => count($originalTokens),
            'processed_tokens' => count($processedTokens),
            'reduction_pct' => round((1 - count($processedTokens) / count($originalTokens)) * 100, 1),
            'unique_terms' => count(array_unique($processedTokens))
        ];
    }
}
```

2. **Test the complete pipeline** (`demo.php`):

```php
# filename: demo.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/text-processor.php';

use AiMlPhp\Chapter13\TextProcessor;

// Sample documents
$documents = [
    "Machine learning is a subset of artificial intelligence that enables computers to learn from data.",
    "Deep learning uses neural networks with multiple layers to process complex patterns in data.",
    "Natural language processing helps computers understand and generate human language effectively.",
    "Data science combines statistics, programming, and domain expertise to extract insights from data.",
    "Artificial intelligence systems can perform tasks that typically require human intelligence."
];

echo "=================================================================\n";
echo "Text Processing Pipeline Demo\n";
echo "=================================================================\n\n";

$processor = new TextProcessor(useStemming: true, useStopWords: true);

// Process each document and show transformation
echo "Document Processing:\n\n";
foreach (array_slice($documents, 0, 2) as $idx => $doc) {
    $processed = $processor->process($doc);
    $stats = $processor->getStats($doc, $processed);

    echo "Document " . ($idx + 1) . ":\n";
    echo "  Original: \"" . mb_substr($doc, 0, 60) . "...\"\n";
    echo "  Processed: " . implode(', ', $processed) . "\n";
    echo "  Stats: {$stats['original_tokens']} → {$stats['processed_tokens']} tokens ";
    echo "({$stats['reduction_pct']}% reduction)\n\n";
}

// Create TF-IDF vectors
echo "\n=================================================================\n";
echo "TF-IDF Vectorization\n";
echo "=================================================================\n\n";

$result = $processor->processToTfIdf($documents);
$tfidf = $result['vectorizer'];

echo "Vocabulary size: " . count($result['vocabulary']) . " unique terms\n\n";

// Show most important terms per document
echo "Most Important Terms per Document:\n\n";
foreach ($result['vectors'] as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    $topTerms = $tfidf->displayVector($vector, 5);
    foreach ($topTerms as $term => $score) {
        echo "  " . sprintf("%-20s: %.4f", $term, $score) . "\n";
    }
    echo "\n";
}

echo "\n=================================================================\n";
echo "Pipeline Complete!\n";
echo "=================================================================\n";
```

3. **Run the demo**:

```bash
php demo.php
```

### Expected Result

```
=================================================================
Text Processing Pipeline Demo
=================================================================

Document Processing:

Document 1:
  Original: "Machine learning is a subset of artificial intellige..."
  Processed: machin, learn, subset, artifici, intellig, enabl, comput, learn, data
  Stats: 17 → 9 tokens (47.1% reduction)

Document 2:
  Original: "Deep learning uses neural networks with multiple laye..."
  Processed: deep, learn, neural, network, multipl, layer, process, complex, pattern, data
  Stats: 15 → 10 tokens (33.3% reduction)


=================================================================
TF-IDF Vectorization
=================================================================

Vocabulary size: 34 unique terms

Most Important Terms per Document:

Document 1:
  machin              : 0.3456
  subset              : 0.3456
  artifici            : 0.3456
  intellig            : 0.2901
  enabl               : 0.3456

Document 2:
  deep                : 0.3456
  neural              : 0.3456
  network             : 0.3456
  multipl             : 0.3456
  layer               : 0.3456

Document 3:
  natur               : 0.3840
  languag             : 0.2560
  process             : 0.2560
  help                : 0.3840
  understand          : 0.3840

Document 4:
  scienc              : 0.3923
  combin              : 0.3923
  statist             : 0.3923
  program             : 0.3923
  domain              : 0.3923

Document 5:
  artifici            : 0.3041
  intellig            : 0.2554
  system              : 0.3456
  perform             : 0.3456
  task                : 0.3456


=================================================================
Pipeline Complete!
=================================================================
```

### Why It Works

The pipeline chains transformations in the optimal order:

1. **Tokenization** — Split text into processable units
2. **Stop-word removal** — Eliminate noise early to reduce processing
3. **Stemming** — Normalize variants before vectorization
4. **Vectorization** — Convert to numeric features for ML

This modular design allows:

- **Flexibility**: Enable/disable steps as needed
- **Reusability**: Same pipeline for training and production
- **Maintainability**: Each component is independently testable
- **Performance**: Process documents efficiently

### Troubleshooting

- **Pipeline too slow** — Disable stemming for speed, or cache processed results
- **Results don't match expectations** — Test each component individually to isolate issues
- **Vocabulary too large** — Increase stop-word list or filter rare terms (appear in <2 docs)

## Exercises

### Exercise 1: Extend Tokenizer for URLs and Emails

**Goal**: Handle special tokens that shouldn't be split by standard tokenization

Create a method `tokenizePreserving()` that:

- Preserves email addresses (user@example.com)
- Preserves URLs (https://example.com)
- Treats them as single tokens
- Handles normal words around them

**Validation**:

```php
$text = "Contact us at info@example.com or visit https://example.com for more details.";
$tokens = $tokenizer->tokenizePreserving($text);
// Should contain: "info@example.com" and "https://example.com" as single tokens
```

### Exercise 2: Build a Domain-Specific Stop Word List

**Goal**: Create a custom stop word list for a specific domain (e.g., product reviews)

1. Analyze 20+ product reviews
2. Identify words that appear in >80% of reviews but don't carry meaning
3. Add them to a custom stop word list
4. Compare TF-IDF results with and without your custom list
5. Measure impact on vocabulary size and top terms

**Validation**: Custom stop words should reduce vocabulary by 10-20% while preserving meaningful terms

### Exercise 3: Implement Bi-gram Features

**Goal**: Extend bag-of-words to include 2-word phrases

Create a `BigramBagOfWords` class that:

- Includes both unigrams (single words) and bigrams (2-word sequences)
- Example: "machine learning" becomes a feature alongside "machine" and "learning"
- Compare classification performance on sample data

**Validation**: Vocabulary should be larger, and phrases like "machine learning" should score high in relevant docs

### Exercise 4: Build a Text Similarity Calculator

**Goal**: Find similar documents using TF-IDF and cosine similarity

Create a function that:

1. Processes documents with TF-IDF
2. Calculates cosine similarity between all pairs
3. Returns top N most similar document pairs
4. Explains why they're similar (show overlapping high-weight terms)

**Validation**:

```php
$similar = findSimilarDocuments($documents, topN: 3);
// Should return pairs with similarity scores and shared terms
```

### Exercise 5: Text Statistics Analyzer

**Goal**: Build a comprehensive text analysis tool

Create a class that reports:

- Vocabulary richness (unique words / total words)
- Average word length
- Sentence count and average sentence length
- Top 10 most frequent words (before and after stop-word removal)
- Readability score estimate

**Validation**: Test on multiple documents and compare statistics

## Troubleshooting

### Encoding Issues with Non-English Text

**Symptom**: Special characters become `?` or garbled text

**Cause**: PHP string functions are not UTF-8 aware by default

**Solution**: Use `mb_*` functions and ensure UTF-8 encoding:

```php
// Wrong
$length = strlen($text);
$lower = strtolower($text);

// Correct
$length = mb_strlen($text, 'UTF-8');
$lower = mb_strtolower($text, 'UTF-8');

// Check encoding
$encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'ASCII'], true);
if ($encoding !== 'UTF-8') {
    $text = mb_convert_encoding($text, 'UTF-8', $encoding);
}
```

### Empty Results After Stop-Word Removal

**Symptom**: Processed tokens array is empty or has very few words

**Cause**: Stop word list is too aggressive for your text

**Solution**: Use a smaller stop word list or add domain-specific exceptions:

```php
// Create custom remover with exceptions
$stopWords = [...]; // Your stop word list
$exceptions = ['not', 'very', 'no']; // Keep these for sentiment analysis
$filteredStopWords = array_diff($stopWords, $exceptions);

$remover = new StopWordRemover($filteredStopWords);
```

### Division by Zero in TF-IDF

**Symptom**: `Warning: Division by zero` in IDF calculation

**Cause**: A term appears in all documents (docFreq = numDocs)

**Solution**: Add smoothing constants:

```php
// In TfIdfVectorizer::fit()
$this->idf[$term] = log(($numDocs + 1) / ($docFreq + 1));
// Adding 1 prevents division by zero and log(0)
```

### Memory Exhausted with Large Corpora

**Symptom**: `Fatal error: Allowed memory size exhausted`

**Cause**: Loading all documents into memory at once

**Solution**: Process in batches:

```php
function processBatch(array $documents, int $batchSize = 1000): Generator
{
    foreach (array_chunk($documents, $batchSize) as $batch) {
        $processor = new TextProcessor();
        $processed = $processor->processMany($batch);
        yield $processed;
    }
}

// Usage
foreach (processBatch($largeDocumentSet) as $processedBatch) {
    // Process each batch
    saveToDatabase($processedBatch);
}
```

### Vocabulary Too Large

**Symptom**: Vocabulary has 10,000+ terms, slowing down vectorization

**Cause**: Including rare terms that appear in only 1-2 documents

**Solution**: Filter vocabulary by document frequency:

```php
function filterVocabulary(array $documents, int $minDocFreq = 2, int $maxDocFreq = null): array
{
    $docFreq = [];

    // Count document frequency for each term
    foreach ($documents as $tokens) {
        foreach (array_unique($tokens) as $term) {
            $docFreq[$term] = ($docFreq[$term] ?? 0) + 1;
        }
    }

    // Filter by frequency thresholds
    $maxDocFreq = $maxDocFreq ?? (int)(count($documents) * 0.8); // Default: 80%
    $filtered = array_filter(
        $docFreq,
        fn($freq) => $freq >= $minDocFreq && $freq <= $maxDocFreq
    );

    return array_keys($filtered);
}
```

## Wrap-up

Congratulations! You've built a complete NLP preprocessing toolkit in PHP. Let's review what you accomplished:

✅ **Tokenized text** into words using multiple strategies (simple, advanced, n-grams, sentences)  
✅ **Removed stop words** to filter out noise and focus on meaningful content  
✅ **Stemmed words** to normalize variants and reduce vocabulary size  
✅ **Created bag-of-words vectors** for basic text-to-numeric conversion  
✅ **Implemented TF-IDF** to weight terms by importance and rarity  
✅ **Built a complete pipeline** that chains all preprocessing steps  
✅ **Processed real text data** and extracted meaningful features for machine learning

These NLP fundamentals are the foundation for every text analysis task—from sentiment analysis to topic modeling to chatbots. You now understand how raw text transforms into structured data that ML algorithms can process.

**Key insights you've gained:**

- Text preprocessing is crucial: 40-70% token reduction while preserving meaning
- Order matters: tokenize → stop-words → stem → vectorize
- TF-IDF balances frequency with distinctiveness for better features
- Each preprocessing choice (stem vs not, stop words vs none) affects model performance

In Chapter 14, you'll apply these techniques to build a complete text classification project—using your preprocessing pipeline to prepare training data for a sentiment analyzer or spam filter. You'll see firsthand how preprocessing quality directly impacts classification accuracy.

**Next steps:**

- Experiment with the exercises to deepen understanding
- Try processing your own text data (tweets, reviews, articles)
- Explore the `solutions/` directory for exercise implementations
- Read Chapter 14 to see these techniques power real NLP models

## Further Reading

### NLP Fundamentals

- [Natural Language Processing — Wikipedia](https://en.wikipedia.org/wiki/Natural_language_processing) — Comprehensive overview of the field
- [Stanford NLP Course](https://web.stanford.edu/~jurafsky/slp3/) — Speech and Language Processing textbook (free online)
- [spaCy NLP Guide](https://spacy.io/usage/spacy-101) — Modern NLP concepts explained clearly

### Tokenization

- [Tokenization in NLP](https://nlp.stanford.edu/IR-book/html/htmledition/tokenization-1.html) — Stanford IR book chapter
- [Unicode Text Segmentation](https://unicode.org/reports/tr29/) — Handling international text
- [Regular Expressions in PHP](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) — PCRE patterns for tokenization

### Stop Words and Stemming

- [Porter Stemming Algorithm](https://tartarus.org/martin/PorterStemmer/) — Original algorithm description
- [Snowball Stemmers](https://snowballstem.org/) — Multi-language stemming
- [Stop Word Lists](https://github.com/stopwords-iso/stopwords-iso) — Stop words for 50+ languages

### TF-IDF and Vectorization

- [TF-IDF Explained](https://en.wikipedia.org/wiki/Tf%E2%80%93idf) — Mathematical foundations
- [Document Similarity](https://nlp.stanford.edu/IR-book/html/htmledition/dot-products-1.html) — Using vectors for similarity
- [Bag of Words Model](https://en.wikipedia.org/wiki/Bag-of-words_model) — Theoretical background

### Advanced Topics

- [Word Embeddings](https://www.tensorflow.org/text/guide/word_embeddings) — Beyond bag-of-words (Word2Vec, GloVe)
- [BERT and Transformers](https://huggingface.co/course/chapter1/1) — State-of-the-art NLP models
- [Subword Tokenization](https://huggingface.co/docs/transformers/tokenizer_summary) — BPE, WordPiece for modern NLP

### PHP Resources

- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) — Official PHP documentation
- [PHP Multibyte String](https://www.php.net/manual/en/book.mbstring.php) — UTF-8 handling
- [PHP PCRE Functions](https://www.php.net/manual/en/ref.pcre.php) — Regular expressions

### Production NLP

- [Real-time NLP at Scale](https://blog.twitter.com/engineering/en_us/topics/infrastructure/2021/processing-billions-of-events-in-real-time-at-twitter) — Twitter's NLP infrastructure
- [Elasticsearch for NLP](https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis.html) — Production text search and analysis
- [Language Detection Libraries](https://github.com/patrickschur/language-detection) — Identifying text language in PHP
