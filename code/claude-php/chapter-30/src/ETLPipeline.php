<?php

declare(strict_types=1);

namespace App;

use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * ETL Pipeline for Data Extraction
 *
 * Orchestrates the complete extraction, transformation, and loading process
 * for unstructured data using Claude AI.
 */
class ETLPipeline
{
    private ClaudePhp $client;
    private string $model;
    private int $maxTokens;

    public function __construct(
        ClaudePhp $client,
        private DocumentParser $parser,
        private DataValidator $validator,
        private AnalyticsEngine $analytics,
        private LoggerInterface $logger = new NullLogger(),
        array $config = []
    ) {
        $this->client = $client;
        $this->model = $config['model'] ?? 'claude-sonnet-4-5';
        $this->maxTokens = $config['max_tokens'] ?? 4096;
    }

    /**
     * Process document through complete ETL pipeline
     */
    public function process(
        string $source,
        string $sourceType,
        array $schema,
        array $options = []
    ): ExtractionResult {
        $startTime = microtime(true);
        $this->logger->info('Starting ETL pipeline', [
            'source_type' => $sourceType,
            'schema' => array_keys($schema)
        ]);

        try {
            // Step 1: Parse source document
            $parsed = $this->parser->parse($source, $sourceType);
            $this->logger->debug('Document parsed', ['length' => strlen($parsed)]);

            // Step 2: Extract structured data
            $extracted = $this->extractData($parsed, $schema);
            $this->logger->info('Data extracted', ['fields' => count($extracted)]);

            // Step 3: Validate data quality
            $validationResult = $this->validator->validate($extracted, $schema);

            if (!$validationResult->isValid() && ($options['strict'] ?? true)) {
                throw new \RuntimeException(
                    'Validation failed: ' . implode(', ', $validationResult->getErrors())
                );
            }

            // Step 4: Transform data
            $transformed = $this->transformData(
                $extracted,
                $options['output_format'] ?? 'json'
            );

            // Step 5: Generate analytics (if requested)
            $analyticsData = null;
            if ($options['generate_analytics'] ?? false) {
                $analyticsData = $this->analytics->analyze($transformed);
                $this->logger->info('Analytics generated', [
                    'insights' => count($analyticsData['insights'] ?? [])
                ]);
            }

            $processingTime = microtime(true) - $startTime;

            return new ExtractionResult(
                data: $transformed,
                validation: $validationResult,
                analytics: $analyticsData,
                metadata: [
                    'source_type' => $sourceType,
                    'processing_time' => $processingTime,
                    'timestamp' => date('c'),
                    'model' => $this->model
                ]
            );
        } catch (\Throwable $e) {
            $this->logger->error('ETL pipeline failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Extract structured data using Claude
     */
    private function extractData(string $content, array $schema): array
    {
        $schemaDescription = $this->buildSchemaDescription($schema);

        $prompt = <<<PROMPT
Extract structured data from the following content according to this schema:

{$schemaDescription}

Content to extract from:
{$content}

Return a JSON object with all the requested fields. Ensure accuracy and completeness.
If a field cannot be determined from the content, use null.
PROMPT;

        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'system' => 'You are a data extraction expert. Extract information accurately and return valid JSON.'
        ]);

        $text = $response->content[0]->text;

        // Extract JSON from response
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $text = $matches[1];
        }

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse extracted data: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Build human-readable schema description
     */
    private function buildSchemaDescription(array $schema): string
    {
        $lines = [];
        foreach ($schema as $field => $config) {
            $type = $config['type'] ?? 'string';
            $required = ($config['required'] ?? false) ? 'required' : 'optional';
            $description = $config['description'] ?? '';

            $lines[] = "- {$field} ({$type}, {$required}): {$description}";
        }

        return implode("\n", $lines);
    }

    /**
     * Transform data to requested format
     */
    private function transformData(array $data, string $format): mixed
    {
        return match ($format) {
            'json' => $data,
            'array' => $data,
            'csv' => $this->convertToCsv($data),
            'xml' => $this->convertToXml($data),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Convert data to CSV format
     */
    private function convertToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, array_keys($data));

        // Write data
        fputcsv($output, array_values($data));

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Convert data to XML format
     */
    private function convertToXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<data/>');

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                foreach ($value as $k => $v) {
                    $child->addChild((string)$k, htmlspecialchars((string)$v));
                }
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }

        return $xml->asXML();
    }

    /**
     * Batch process multiple documents
     */
    public function batchProcess(
        array $sources,
        string $sourceType,
        array $schema,
        array $options = []
    ): array {
        $results = [];
        $batchSize = $options['batch_size'] ?? 10;
        $batches = array_chunk($sources, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            $this->logger->info("Processing batch {$batchIndex}", [
                'size' => count($batch)
            ]);

            foreach ($batch as $index => $source) {
                try {
                    $results[] = $this->process($source, $sourceType, $schema, $options);
                } catch (\Throwable $e) {
                    $this->logger->error("Failed to process item {$index}", [
                        'error' => $e->getMessage()
                    ]);

                    if ($options['stop_on_error'] ?? false) {
                        throw $e;
                    }

                    $results[] = new ExtractionResult(
                        data: [],
                        validation: new ValidationResult(false, [$e->getMessage()]),
                        analytics: null,
                        metadata: ['error' => $e->getMessage()]
                    );
                }
            }
        }

        return $results;
    }
}

/**
 * Extraction result container
 */
class ExtractionResult
{
    public function __construct(
        public readonly array $data,
        public readonly ValidationResult $validation,
        public readonly ?array $analytics,
        public readonly array $metadata
    ) {}

    public function isSuccess(): bool
    {
        return $this->validation->isValid();
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'validation' => [
                'valid' => $this->validation->isValid(),
                'errors' => $this->validation->getErrors(),
                'confidence' => $this->validation->getConfidence()
            ],
            'analytics' => $this->analytics,
            'metadata' => $this->metadata
        ];
    }
}
