<?php

declare(strict_types=1);

namespace App;

use Anthropic\Anthropic;
use Anthropic\Resources\Messages;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * RAG Pipeline
 *
 * Complete Retrieval Augmented Generation workflow.
 */
class RAGPipeline
{
    private Messages $messages;
    private string $model;

    public function __construct(
        private Anthropic $client,
        private ChunkingService $chunker,
        private EmbeddingService $embedder,
        private RetrievalEngine $retriever,
        private LoggerInterface $logger = new NullLogger()
    ) {
        $this->messages = $this->client->messages();
        $this->model = 'claude-3-5-sonnet-20241022';
    }

    /**
     * Index documents for RAG
     */
    public function indexDocuments(string $content, array $metadata = []): void
    {
        $this->logger->info('Starting document indexing');

        // Chunk the document
        $chunks = $this->chunker->chunkByParagraph($content);
        $chunks = $this->chunker->enrichChunks($chunks, $metadata);

        // Index in retrieval engine
        $this->retriever->indexDocuments($chunks);

        $this->logger->info('Document indexing complete', [
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Query with RAG
     */
    public function query(
        string $question,
        array $options = []
    ): RAGResult {
        $startTime = microtime(true);

        $this->logger->info('Processing RAG query', [
            'question' => substr($question, 0, 100)
        ]);

        // Retrieve relevant context
        $topK = $options['top_k'] ?? 5;
        $retrieved = $this->retriever->retrieve($question, $topK);

        if (empty($retrieved)) {
            $this->logger->warning('No relevant documents found');
        }

        // Build context from retrieved documents
        $context = $this->buildContext($retrieved);

        // Generate response with Claude
        $response = $this->generateResponse($question, $context, $options);

        $processingTime = microtime(true) - $startTime;

        return new RAGResult(
            question: $question,
            answer: $response,
            context: $retrieved,
            processingTime: $processingTime,
            metadata: [
                'documents_retrieved' => count($retrieved),
                'context_length' => strlen($context),
                'model' => $this->model
            ]
        );
    }

    /**
     * Build context from retrieved documents
     */
    private function buildContext(array $retrieved): string
    {
        if (empty($retrieved)) {
            return '';
        }

        $context = "# Relevant Context\n\n";

        foreach ($retrieved as $item) {
            $doc = $item['document'];
            $score = round($item['score'], 3);

            $heading = $doc['heading'] ?? "Document {$item['rank']}";
            $context .= "## {$heading} (Relevance: {$score})\n\n";
            $context .= $doc['text'] . "\n\n";
        }

        return $context;
    }

    /**
     * Generate response using Claude
     */
    private function generateResponse(
        string $question,
        string $context,
        array $options = []
    ): string {
        $systemPrompt = $options['system_prompt'] ?? <<<PROMPT
You are a helpful assistant that answers questions based on the provided context.
Use only the information from the context to answer the question.
If the context doesn't contain enough information, say so clearly.
Cite relevant parts of the context in your answer.
PROMPT;

        $userPrompt = empty($context)
            ? $question
            : <<<PROMPT
Context:
{$context}

Question: {$question}

Please provide a detailed answer based on the context above.
PROMPT;

        try {
            $response = $this->messages->create([
                'model' => $this->model,
                'max_tokens' => $options['max_tokens'] ?? 2048,
                'temperature' => $options['temperature'] ?? 0.7,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt
                    ]
                ],
                'system' => $systemPrompt
            ]);

            return $response->content[0]->text;

        } catch (\Throwable $e) {
            $this->logger->error('Response generation failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Batch query processing
     */
    public function batchQuery(array $questions, array $options = []): array
    {
        $results = [];

        foreach ($questions as $question) {
            try {
                $results[] = $this->query($question, $options);
            } catch (\Throwable $e) {
                $this->logger->error('Batch query failed', [
                    'question' => $question,
                    'error' => $e->getMessage()
                ]);

                $results[] = new RAGResult(
                    question: $question,
                    answer: "Error: {$e->getMessage()}",
                    context: [],
                    processingTime: 0,
                    metadata: ['error' => $e->getMessage()]
                );
            }
        }

        return $results;
    }

    /**
     * Get pipeline statistics
     */
    public function getStats(): array
    {
        return $this->retriever->getStats();
    }
}

/**
 * RAG Result container
 */
class RAGResult
{
    public function __construct(
        public readonly string $question,
        public readonly string $answer,
        public readonly array $context,
        public readonly float $processingTime,
        public readonly array $metadata
    ) {}

    public function toArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => $this->answer,
            'sources' => array_map(fn($item) => [
                'text' => substr($item['document']['text'], 0, 200) . '...',
                'score' => $item['score'],
                'rank' => $item['rank']
            ], $this->context),
            'processing_time' => $this->processingTime,
            'metadata' => $this->metadata
        ];
    }
}
