<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

use ClaudeAgents\RAG\RAGPipeline;
use ClaudePhp\ClaudePhp;

/**
 * Basic RAG Pipeline Example
 * 
 * Demonstrates simple document indexing and retrieval-augmented generation
 * using the claude-php-agent framework.
 */

// Setup
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create RAG pipeline
$rag = RAGPipeline::create($client);

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          Basic RAG Pipeline - Document Q&A System             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Add documents to the knowledge base
echo "=== Indexing Documents ===\n\n";

$rag->addDocument(
    title: 'Product Refund Policy',
    content: 'ProductX offers a 30-day money-back guarantee. Returns must be in original condition. 
              Refunds are processed within 5-7 business days. Shipping costs are non-refundable 
              unless the item is defective. Customer must provide proof of purchase.',
    metadata: ['category' => 'policy', 'type' => 'refund', 'version' => '2024']
);

$rag->addDocument(
    title: 'Product Warranty',
    content: 'ProductX includes a 1-year manufacturer warranty covering defects in materials and 
              workmanship. Extended warranty available for purchase up to 3 years. Warranty does not 
              cover accidental damage, normal wear and tear, or unauthorized modifications. 
              Register product within 30 days of purchase.',
    metadata: ['category' => 'policy', 'type' => 'warranty', 'version' => '2024']
);

$rag->addDocument(
    title: 'Shipping Information',
    content: 'ProductX ships within 1-2 business days via standard shipping. Free shipping on orders 
              over $50 USD. Express shipping available for $15 (1-2 day delivery). International 
              shipping available to select countries with customs fees paid by customer. 
              Tracking number provided for all orders.',
    metadata: ['category' => 'logistics', 'type' => 'shipping', 'version' => '2024']
);

$rag->addDocument(
    title: 'Product Support',
    content: 'ProductX support available 24/7 via email and phone. Live chat support during business 
              hours (9 AM - 5 PM EST). Average response time under 2 hours. Premium support plans 
              available with priority handling and dedicated account manager.',
    metadata: ['category' => 'support', 'type' => 'contact', 'version' => '2024']
);

echo "✓ Indexed {$rag->getDocumentCount()} documents\n";
echo "✓ Created {$rag->getChunkCount()} text chunks\n\n";

// Query the knowledge base
echo "=== Querying Knowledge Base ===\n\n";

$questions = [
    'What is the refund policy?',
    'Does ProductX have a warranty?',
    'How long does shipping take?',
    'How can I contact support?',
    'Can I return a defective product?',
];

foreach ($questions as $i => $question) {
    echo "Q{" . ($i + 1) . "}: {$question}\n";
    
    // Retrieve top 2 most relevant chunks and generate answer
    $result = $rag->query($question, topK: 2);
    
    // Display answer with word wrapping
    $answer = wordwrap($result['answer'], 70);
    echo "A: {$answer}\n";
    
    // Show sources
    if (!empty($result['sources'])) {
        echo "\n📚 Sources:\n";
        foreach ($result['sources'] as $source) {
            echo "   • {$source['source']}\n";
        }
    }
    
    // Show citations
    if (!empty($result['citations'])) {
        echo "\n🔗 Citations: ";
        echo implode(', ', array_map(fn($i) => "[Source {$i}]", $result['citations']));
        echo "\n";
    }
    
    // Show token usage
    if (isset($result['tokens'])) {
        $total = ($result['tokens']['input'] ?? 0) + ($result['tokens']['output'] ?? 0);
        echo "\n📊 Tokens: {$result['tokens']['input']} input + {$result['tokens']['output']} output = {$total} total\n";
    }
    
    echo "\n" . str_repeat('─', 70) . "\n\n";
}

// Demonstrate filtering by metadata
echo "=== Filtered Retrieval (Policy Documents Only) ===\n\n";

$policyQuestion = 'What policies should I know about?';
echo "Q: {$policyQuestion}\n";

// Retrieve only from policy documents
$result = $rag->query($policyQuestion, topK: 3, filters: ['category' => 'policy']);

$answer = wordwrap($result['answer'], 70);
echo "A: {$answer}\n\n";

echo "📚 Policy Sources:\n";
foreach ($result['sources'] as $source) {
    echo "   • {$source['source']} (category: {$source['metadata']['category']})\n";
}

echo "\n" . str_repeat('─', 70) . "\n\n";

// Show statistics
echo "=== RAG Pipeline Statistics ===\n\n";
echo "Total Documents: {$rag->getDocumentCount()}\n";
echo "Total Chunks: {$rag->getChunkCount()}\n";
echo "Average Chunks per Document: " . round($rag->getChunkCount() / max(1, $rag->getDocumentCount()), 1) . "\n";

echo "\n✅ Basic RAG pipeline demonstration complete!\n";
echo "\n💡 Key Takeaways:\n";
echo "   1. RAG grounds answers in factual sources\n";
echo "   2. Citations allow verification of claims\n";
echo "   3. Metadata filtering narrows search scope\n";
echo "   4. Token usage scales with retrieved context\n";
