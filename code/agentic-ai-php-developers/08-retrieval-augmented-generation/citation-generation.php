<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudePhp\ClaudePhp;

/**
 * Citation Generation Example
 * 
 * Demonstrates how to generate answers with proper citations that allow
 * users to verify information and trace claims back to source documents.
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         Citation Generation - Grounded Responses              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$rag = RAGPipeline::create($client);

echo "=== Building Academic Knowledge Base ===\n\n";

// Add research papers and studies
$rag->addDocument(
    title: 'Smith et al. (2023) - RAG Performance Study',
    content: 'Our comprehensive study evaluated Retrieval-Augmented Generation across 1,000 factual 
              questions spanning multiple domains. Results demonstrate that RAG reduces hallucinations 
              by 67% compared to pure LLM generation without retrieval. We measured accuracy using 
              human evaluation and automated fact-checking. The study was conducted over 6 months 
              with 50 domain experts as evaluators.',
    metadata: [
        'year' => 2023,
        'authors' => 'Smith, J., Johnson, M., & Williams, K.',
        'venue' => 'NeurIPS 2023',
        'type' => 'empirical_study',
        'citations' => 127,
    ]
);

$rag->addDocument(
    title: 'Johnson (2024) - Optimal Chunking Strategies',
    content: 'This paper presents a comprehensive analysis of document chunking strategies for RAG 
              systems. For question-answering tasks, optimal chunk size ranges from 300-500 tokens 
              with 50-100 token overlap. For summarization tasks, larger chunks of 800-1200 tokens 
              perform better. We tested on 5 different domains with 200 test queries each. 
              Results show chunk size significantly impacts both retrieval quality and generation accuracy.',
    metadata: [
        'year' => 2024,
        'authors' => 'Johnson, A.',
        'venue' => 'EMNLP 2024',
        'type' => 'empirical_study',
        'citations' => 43,
    ]
);

$rag->addDocument(
    title: 'Chen & Park (2024) - Embedding Models Comparison',
    content: 'We compared 8 different embedding models for retrieval tasks: OpenAI ada-002, 
              text-embedding-3-small, Cohere embed-v3, Voyage-code-2, and others. Voyage-code-2 
              performs best on technical documentation (0.89 MRR) while Cohere excels at multilingual 
              retrieval (0.91 MRR). Model choice depends on domain and language requirements. 
              Computational cost varies by 10x between models.',
    metadata: [
        'year' => 2024,
        'authors' => 'Chen, L. & Park, S.',
        'venue' => 'ACL 2024',
        'type' => 'empirical_study',
        'citations' => 89,
    ]
);

$rag->addDocument(
    title: 'Rodriguez et al. (2023) - Citation Generation Methods',
    content: 'Citation generation in RAG systems can follow multiple approaches: inline citations 
              like [1], footnote style, or source attribution. Our user study with 200 participants 
              found inline citations increase trust by 34% and improve verification rates by 52%. 
              Users prefer numbered citations over author-date format for technical Q&A. 
              Clear source attribution is critical for high-stakes applications.',
    metadata: [
        'year' => 2023,
        'authors' => 'Rodriguez, M., Kim, H., & Thompson, B.',
        'venue' => 'CHI 2023',
        'type' => 'user_study',
        'citations' => 76,
    ]
);

echo "✓ Indexed {$rag->getDocumentCount()} research papers\n\n";

echo str_repeat('─', 70) . "\n\n";

// Query with citation analysis
echo "=== Research Questions with Citations ===\n\n";

$questions = [
    'How much does RAG reduce hallucinations?',
    'What is the optimal chunk size for Q&A systems?',
    'Which embedding model works best for technical documentation?',
    'Do citations improve user trust?',
];

foreach ($questions as $i => $question) {
    echo "Q" . ($i + 1) . ": {$question}\n";
    echo str_repeat('─', 70) . "\n\n";
    
    // Retrieve with citations
    $result = $rag->query($question, topK: 3);
    
    // Display answer
    echo "ANSWER:\n";
    echo wordwrap($result['answer'], 70) . "\n\n";
    
    // Analyze citations
    if (!empty($result['citations'])) {
        echo "CITATIONS USED:\n";
        foreach ($result['citations'] as $citationIndex) {
            if (isset($result['sources'][$citationIndex])) {
                $source = $result['sources'][$citationIndex];
                echo "  [Source {$citationIndex}] {$source['source']}\n";
                
                if (!empty($source['metadata'])) {
                    $meta = $source['metadata'];
                    if (isset($meta['authors'])) {
                        echo "    Authors: {$meta['authors']}\n";
                    }
                    if (isset($meta['venue'])) {
                        echo "    Published: {$meta['venue']}, {$meta['year']}\n";
                    }
                    if (isset($meta['citations'])) {
                        echo "    Citations: {$meta['citations']}\n";
                    }
                }
                echo "\n";
            }
        }
    } else {
        echo "⚠️  No citations provided (answer may not be grounded)\n\n";
    }
    
    // Show source previews
    echo "SOURCE PREVIEWS:\n";
    foreach ($result['sources'] as $source) {
        echo "  • {$source['source']}\n";
        echo "    " . substr($source['text_preview'], 0, 80) . "...\n\n";
    }
    
    // Verification guidance
    echo "VERIFICATION:\n";
    echo "  ✓ Claims can be verified by checking cited sources above\n";
    echo "  ✓ Each [Source N] reference links to specific document\n";
    echo "  ✓ Original papers available in knowledge base\n\n";
    
    echo str_repeat('═', 70) . "\n\n";
}

// Citation statistics
echo "=== Citation Statistics ===\n\n";

$totalQueries = count($questions);
$queriesWithCitations = 0;
$totalCitations = 0;
$averageCitationsPerQuery = 0;

// Rerun queries to collect stats
foreach ($questions as $question) {
    $result = $rag->query($question, topK: 3);
    if (!empty($result['citations'])) {
        $queriesWithCitations++;
        $totalCitations += count($result['citations']);
    }
}

if ($queriesWithCitations > 0) {
    $averageCitationsPerQuery = $totalCitations / $queriesWithCitations;
}

echo "Total Queries: {$totalQueries}\n";
echo "Queries with Citations: {$queriesWithCitations} (" . round(($queriesWithCitations / $totalQueries) * 100, 1) . "%)\n";
echo "Total Citations: {$totalCitations}\n";
echo "Average Citations per Query: " . round($averageCitationsPerQuery, 1) . "\n\n";

echo str_repeat('─', 70) . "\n\n";

// Citation best practices
echo "=== Citation Best Practices ===\n\n";

echo "1. **Always Include Source Attribution**\n";
echo "   ✓ Use [Source N] notation for inline citations\n";
echo "   ✓ Provide full source details for verification\n";
echo "   ✓ Link citations to specific source documents\n\n";

echo "2. **Granular Citations**\n";
echo "   ✓ Cite specific claims, not whole answers\n";
echo "   ✓ Multiple citations per answer when appropriate\n";
echo "   ✓ Clear mapping between claim and source\n\n";

echo "3. **Citation Metadata**\n";
echo "   ✓ Include: author, year, venue, page numbers\n";
echo "   ✓ Add: DOI/URL for digital access\n";
echo "   ✓ Track: number of citations for authority\n\n";

echo "4. **Verification Support**\n";
echo "   ✓ Provide source previews/snippets\n";
echo "   ✓ Enable drill-down to full source\n";
echo "   ✓ Show confidence scores when available\n\n";

echo "5. **User Trust Signals**\n";
echo "   ✓ Inline citations (immediate context)\n";
echo "   ✓ Source ranking (most relevant first)\n";
echo "   ✓ Metadata signals (year, citations, venue)\n\n";

echo str_repeat('─', 70) . "\n\n";

// Example of structured citation output
echo "=== Structured Citation Format ===\n\n";

$structuredQuery = 'What chunk size should I use for Q&A?';
$result = $rag->query($structuredQuery, topK: 2);

echo "Query: {$structuredQuery}\n\n";

echo "ANSWER WITH STRUCTURED CITATIONS:\n";
echo wordwrap($result['answer'], 70) . "\n\n";

if (!empty($result['sources'])) {
    echo "REFERENCES:\n\n";
    foreach ($result['sources'] as $i => $source) {
        echo "[{$i}] {$source['source']}\n";
        
        if (!empty($source['metadata'])) {
            $m = $source['metadata'];
            $citation = "    ";
            
            if (isset($m['authors'])) {
                $citation .= "{$m['authors']}. ";
            }
            if (isset($m['year'])) {
                $citation .= "({$m['year']}). ";
            }
            if (isset($m['venue'])) {
                $citation .= "{$m['venue']}.";
            }
            
            echo "{$citation}\n";
            
            if (isset($m['type'])) {
                echo "    Type: {$m['type']}\n";
            }
            if (isset($m['citations'])) {
                echo "    Cited by: {$m['citations']} papers\n";
            }
        }
        
        echo "    Preview: {$source['text_preview']}\n\n";
    }
}

echo "✅ Citation generation demonstration complete!\n\n";

echo "💡 Key Takeaways:\n";
echo "   1. Citations ground responses in verifiable sources\n";
echo "   2. Inline [Source N] format works best for Q&A\n";
echo "   3. Metadata (authors, year, venue) builds trust\n";
echo "   4. Source previews enable quick verification\n";
echo "   5. Multiple citations per answer improve accuracy\n";
