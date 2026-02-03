<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\QueryTransformation\MultiQueryGenerator;
use ClaudeAgents\RAG\QueryTransformation\HyDEGenerator;
use ClaudeAgents\RAG\QueryTransformation\QueryDecomposer;
use ClaudePhp\ClaudePhp;

/**
 * Query Transformation Techniques
 * 
 * Demonstrates advanced query transformation methods to improve retrieval:
 * - Multi-Query: Generate variations to improve recall
 * - HyDE: Generate hypothetical documents for better matching
 * - Decomposition: Break complex queries into sub-queries
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         Query Transformation - Advanced Retrieval             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// 1. Multi-Query Generation
echo "=== 1. Multi-Query Generation ===\n";
echo "Goal: Generate multiple query variations to improve recall\n\n";

$multiQueryGen = new MultiQueryGenerator($client);

$originalQuery = 'How do I deploy a PHP application to production?';

echo "Original Query:\n";
echo "  {$originalQuery}\n\n";

// Generate variations
echo "Generating query variations...\n";
$variations = $multiQueryGen->generate($originalQuery, numQueries: 4);

echo "\nGenerated Variations:\n";
foreach ($variations as $i => $variation) {
    echo "  " . ($i + 1) . ". {$variation}\n";
}

echo "\n💡 Benefits:\n";
echo "   • Retrieve from multiple perspectives\n";
echo "   • Capture different phrasings of same intent\n";
echo "   • Improve recall by casting wider net\n";
echo "   • Combine results from all variations\n\n";

echo "Use Case: User query might miss relevant docs due to wording\n";
echo "Solution: Rephrase query in multiple ways, retrieve all, merge\n\n";

echo str_repeat('─', 70) . "\n\n";

// 2. HyDE (Hypothetical Document Embeddings)
echo "=== 2. HyDE (Hypothetical Document Embeddings) ===\n";
echo "Goal: Generate hypothetical answer, search for similar documents\n\n";

$hydeGen = new HyDEGenerator($client);

$query = 'What are the benefits of property hooks in PHP 8.4?';

echo "Query:\n";
echo "  {$query}\n\n";

echo "Generating hypothetical document...\n";
$hypotheticalDoc = $hydeGen->generate($query);

echo "\nHypothetical Document:\n";
echo wordwrap($hypotheticalDoc, 70, "\n  ") . "\n\n";

// Augmented query
$augmentedQuery = $hydeGen->augmentQuery($query);

echo "Augmented Query (for retrieval):\n";
echo wordwrap($augmentedQuery, 70, "\n  ") . "\n\n";

echo "💡 Benefits:\n";
echo "   • Search for document that would answer question\n";
echo "   • More effective than searching for question itself\n";
echo "   • Matches document style and structure\n";
echo "   • Especially good for technical/factual queries\n\n";

echo "Why It Works:\n";
echo "  Question: 'What are X benefits?'\n";
echo "  → Embedding of question differs from embedding of answer\n";
echo "  → HyDE generates answer-like text to match answer documents\n\n";

echo str_repeat('─', 70) . "\n\n";

// 3. Query Decomposition
echo "=== 3. Query Decomposition ===\n";
echo "Goal: Break complex multi-part questions into simpler sub-queries\n\n";

$decomposer = new QueryDecomposer($client);

$complexQueries = [
    'Compare the performance, developer experience, and deployment options of Laravel vs Symfony',
    'What are the security implications, best practices, and common pitfalls when implementing JWT authentication in PHP?',
    'How do I migrate from PHP 7.4 to 8.4 while maintaining backwards compatibility and minimizing downtime?',
];

foreach ($complexQueries as $idx => $complexQuery) {
    echo "Complex Query " . ($idx + 1) . ":\n";
    echo "  " . wordwrap($complexQuery, 68, "\n  ") . "\n\n";
    
    echo "Decomposing...\n";
    $subQueries = $decomposer->decompose($complexQuery);
    
    echo "\nSub-Queries:\n";
    foreach ($subQueries as $i => $subQuery) {
        echo "  " . ($i + 1) . ". {$subQuery}\n";
    }
    
    echo "\n💡 Strategy:\n";
    echo "   1. Answer each sub-query independently\n";
    echo "   2. Retrieve relevant sources for each\n";
    echo "   3. Synthesize comprehensive final answer\n";
    echo "   4. Cite sources from all sub-queries\n\n";
    
    if ($idx < count($complexQueries) - 1) {
        echo str_repeat('·', 70) . "\n\n";
    }
}

echo str_repeat('─', 70) . "\n\n";

// Comparison of techniques
echo "=== Technique Comparison ===\n\n";

echo "┌─────────────────────┬────────────────────┬────────────────────┬──────────┐\n";
echo "│ Technique           │ Best For           │ Improves           │ Cost     │\n";
echo "├─────────────────────┼────────────────────┼────────────────────┼──────────┤\n";
echo "│ Multi-Query         │ Simple queries     │ Recall             │ Low      │\n";
echo "│ HyDE                │ Factual Q&A        │ Precision          │ Medium   │\n";
echo "│ Decomposition       │ Complex queries    │ Comprehensiveness  │ High     │\n";
echo "└─────────────────────┴────────────────────┴────────────────────┴──────────┘\n\n";

// Integration example
echo "=== Integration with RAG Pipeline ===\n\n";

echo "Example: Using query transformation in production RAG\n\n";

echo "```php\n";
echo "// Multi-Query approach\n";
echo "\$variations = \$multiQueryGen->generate(\$userQuery, numQueries: 3);\n";
echo "\$allResults = [];\n";
echo "\n";
echo "foreach (\$variations as \$variation) {\n";
echo "    \$results = \$rag->query(\$variation, topK: 3);\n";
echo "    \$allResults = array_merge(\$allResults, \$results['sources']);\n";
echo "}\n";
echo "\n";
echo "// Deduplicate and rerank\n";
echo "\$uniqueResults = \$this->deduplicateSources(\$allResults);\n";
echo "\$rerankedResults = \$reranker->rerank(\$uniqueResults, \$userQuery);\n";
echo "\n";
echo "// Generate final answer with top sources\n";
echo "\$answer = \$rag->generateAnswer(\$userQuery, array_slice(\$rerankedResults, 0, 5));\n";
echo "```\n\n";

echo str_repeat('─', 70) . "\n\n";

// Best practices
echo "=== Best Practices ===\n\n";

echo "1. **Choose Based on Query Type**\n";
echo "   • Simple queries → Multi-Query (fast, broad)\n";
echo "   • Factual queries → HyDE (precise matching)\n";
echo "   • Complex queries → Decomposition (comprehensive)\n\n";

echo "2. **Combine Techniques**\n";
echo "   • Multi-Query + HyDE for maximum coverage\n";
echo "   • Decomposition + Multi-Query per sub-query\n";
echo "   • Experiment with combinations for your domain\n\n";

echo "3. **Balance Cost vs Quality**\n";
echo "   • More queries = higher cost + better recall\n";
echo "   • Cache generated variations for common queries\n";
echo "   • Monitor cost/quality trade-offs in production\n\n";

echo "4. **Merge and Deduplicate Results**\n";
echo "   • Multiple queries return overlapping results\n";
echo "   • Deduplicate by document ID or content hash\n";
echo "   • Rerank merged results for final top-K\n\n";

echo "5. **User Experience Considerations**\n";
echo "   • Show single coherent answer (not multiple)\n";
echo "   • Indicate when multiple sources were used\n";
echo "   • Provide confidence scores if available\n\n";

echo str_repeat('─', 70) . "\n\n";

// Real-world example
echo "=== Real-World Example: Technical Documentation Search ===\n\n";

$technicalQuery = 'How do I implement caching in my PHP API?';

echo "User Query: {$technicalQuery}\n\n";

// Apply transformations
echo "1. Applying Multi-Query:\n";
$mqVariations = $multiQueryGen->generate($technicalQuery, numQueries: 2);
foreach ($mqVariations as $i => $var) {
    echo "   • Variation " . ($i + 1) . ": {$var}\n";
}

echo "\n2. Applying HyDE:\n";
$hydeDoc = $hydeGen->generate($technicalQuery);
echo "   • Generated doc: " . substr($hydeDoc, 0, 80) . "...\n";

echo "\n3. Retrieval Strategy:\n";
echo "   a. Retrieve with original query\n";
echo "   b. Retrieve with each multi-query variation\n";
echo "   c. Retrieve with HyDE-augmented query\n";
echo "   d. Merge all results (6-15 total sources)\n";
echo "   e. Deduplicate by document ID\n";
echo "   f. Rerank by relevance to original query\n";
echo "   g. Generate answer using top 5 sources\n\n";

echo "Expected Outcome:\n";
echo "   ✓ Higher recall (found more relevant docs)\n";
echo "   ✓ Better precision (most relevant docs ranked higher)\n";
echo "   ✓ Comprehensive answer (covered multiple aspects)\n\n";

echo "✅ Query transformation demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Multi-Query improves recall with variations\n";
echo "   2. HyDE matches answer-style documents\n";
echo "   3. Decomposition handles complex queries\n";
echo "   4. Combine techniques for best results\n";
echo "   5. Balance cost vs quality in production\n";
