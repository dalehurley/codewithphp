# Chapter 13: Natural Language Processing (NLP) Fundamentals

This directory contains complete, runnable code examples for Chapter 13.

## Contents

### Core Classes

- **`tokenizer.php`** — Text tokenization with multiple strategies
- **`stop-words.php`** — Stop word removal for filtering common words
- **`stemmer.php`** — Word stemming to reduce to root forms
- **`bag-of-words.php`** — Bag-of-words vectorization
- **`tfidf.php`** — TF-IDF feature extraction
- **`text-processor.php`** — Complete processing pipeline

### Test Scripts

- **`test-tokenizer.php`** — Demonstrates tokenization methods
- **`test-stopwords.php`** — Shows stop word filtering
- **`test-stemmer.php`** — Examples of stemming variants
- **`test-bow.php`** — Bag-of-words vectorization demo
- **`test-tfidf.php`** — TF-IDF scoring examples
- **`demo.php`** — Complete pipeline demonstration

### Data Files

- **`data/stop-words-en.txt`** — English stop words list
- **`data/sample-documents.txt`** — Sample text documents for testing

### Solutions

- **`solutions/`** — Exercise solutions (to be added)

## Quick Start

Run the complete demo to see all components working together:

```bash
cd /Users/dalehurley/Code/PHP-From-Scratch/docs/series/ai-ml-php-developers/code/chapter-13
php demo.php
```

## Testing Individual Components

### Tokenization

```bash
php test-tokenizer.php
```

Shows different tokenization strategies:

- Simple word tokenization
- Advanced tokenization (preserves contractions)
- N-gram generation (bigrams, trigrams)
- Sentence splitting

### Stop Word Removal

```bash
php test-stopwords.php
```

Demonstrates filtering common words like "the", "is", "and" to focus on meaningful content.

### Stemming

```bash
php test-stemmer.php
```

Shows how word variants are reduced to root forms:

- running, runs, ran → run
- quickly, quicker, quickest → quick

### Bag of Words

```bash
php test-bow.php
```

Converts text documents to numeric feature vectors based on word counts.

### TF-IDF

```bash
php test-tfidf.php
```

Demonstrates term weighting based on importance and rarity across documents.

## Using the Pipeline

### Basic Usage

```php
<?php
require_once 'text-processor.php';

use AiMlPhp\Chapter13\TextProcessor;

$processor = new TextProcessor(
    useStemming: true,
    useStopWords: true
);

$text = "Natural language processing helps computers understand human language.";
$tokens = $processor->process($text);

print_r($tokens);
// Output: ['natur', 'languag', 'process', 'help', 'comput', 'understand', 'human', 'languag']
```

### Processing Multiple Documents

```php
$documents = [
    "Machine learning enables computers to learn from data.",
    "Deep learning uses neural networks for complex patterns.",
    "Natural language processing helps understand text."
];

$result = $processor->processToTfIdf($documents);

// Access components
$vectors = $result['vectors'];        // TF-IDF vectors
$vocabulary = $result['vocabulary'];  // Unique terms
$vectorizer = $result['vectorizer'];  // Fitted vectorizer

// Display top terms for first document
$topTerms = $vectorizer->displayVector($vectors[0], 5);
print_r($topTerms);
```

### Custom Configuration

```php
// Disable stemming for exact matching
$processor = new TextProcessor(
    useStemming: false,
    useStopWords: true
);

// Add domain-specific stop words
$processor = new TextProcessor(
    useStemming: true,
    useStopWords: true,
    customStopWords: ['please', 'thanks', 'regards']
);
```

## Requirements

- PHP 8.4 or higher
- mbstring extension (for UTF-8 support)

Check your PHP version:

```bash
php --version
```

Verify mbstring is enabled:

```bash
php -m | grep mbstring
```

If mbstring is not installed:

```bash
# Ubuntu/Debian
sudo apt-get install php-mbstring

# macOS (Homebrew)
brew install php@8.4

# Or enable in php.ini
extension=mbstring
```

## Architecture

The pipeline follows this flow:

```
Raw Text
   ↓
Tokenization (split into words)
   ↓
Stop Word Removal (filter common words)
   ↓
Stemming (reduce to root forms)
   ↓
Vectorization (convert to numbers)
   ↓
Numeric Features for ML
```

Each component is:

- **Independent** — Can be used standalone
- **Reusable** — Apply same pipeline to new data
- **Testable** — Each class has focused responsibility
- **Extensible** — Easy to add new strategies

## Common Issues

### Encoding Errors

**Problem**: Special characters display incorrectly

**Solution**: Ensure UTF-8 encoding

```php
// Check encoding
$encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1'], true);

// Convert if needed
if ($encoding !== 'UTF-8') {
    $text = mb_convert_encoding($text, 'UTF-8', $encoding);
}
```

### Empty Results

**Problem**: Processed tokens array is empty

**Solution**: Stop word list may be too aggressive

```php
// Use smaller stop word list
$processor = new TextProcessor(
    useStemming: true,
    useStopWords: false  // Disable stop word removal
);
```

### Memory Issues

**Problem**: Out of memory with large document collections

**Solution**: Process in batches

```php
function processBatch(array $documents, int $batchSize = 1000) {
    foreach (array_chunk($documents, $batchSize) as $batch) {
        $processor = new TextProcessor();
        yield $processor->processMany($batch);
    }
}
```

## Performance Tips

1. **Disable stemming** if speed is critical (50% faster)
2. **Filter vocabulary** to remove rare terms (appear in <2 documents)
3. **Cache processed results** for repeated analysis
4. **Use batch processing** for large corpora

## Next Steps

After mastering these fundamentals:

1. **Chapter 14** — Apply to text classification (sentiment analysis, spam detection)
2. **Chapter 15** — Integrate with language models (GPT, BERT)
3. **Exercises** — Extend tokenizer, build similarity calculator, create custom analyzers

## Further Reading

- [Natural Language Toolkit (NLTK) Book](https://www.nltk.org/book/) — Python-based but concepts apply
- [spaCy NLP](https://spacy.io/) — State-of-the-art NLP library
- [Stanford NLP Course](https://web.stanford.edu/~jurafsky/slp3/) — Comprehensive NLP textbook
- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) — PHP documentation

## License

These code examples are part of the "AI/ML for PHP Developers" series and are provided for educational purposes.
