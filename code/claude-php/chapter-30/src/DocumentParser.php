<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Document Parser
 *
 * Parses various document formats into plain text for extraction.
 */
class DocumentParser
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * Parse document based on source type
     */
    public function parse(string $source, string $type): string
    {
        $this->logger->debug("Parsing document of type: {$type}");

        return match($type) {
            'text', 'txt' => $this->parseText($source),
            'html' => $this->parseHtml($source),
            'json' => $this->parseJson($source),
            'csv' => $this->parseCsv($source),
            'xml' => $this->parseXml($source),
            'pdf' => $this->parsePdf($source),
            'file' => $this->parseFile($source),
            'url' => $this->parseUrl($source),
            default => throw new \InvalidArgumentException("Unsupported type: {$type}")
        };
    }

    /**
     * Parse plain text
     */
    private function parseText(string $text): string
    {
        return trim($text);
    }

    /**
     * Parse HTML content
     */
    private function parseHtml(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Strip all HTML tags
        $text = strip_tags($html);

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n+/', "\n", $text);

        return trim($text);
    }

    /**
     * Parse JSON content
     */
    private function parseJson(string $json): string
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        return $this->arrayToText($data);
    }

    /**
     * Parse CSV content
     */
    private function parseCsv(string $csv): string
    {
        $lines = str_getcsv($csv, "\n");
        $data = array_map('str_getcsv', $lines);

        if (empty($data)) {
            return '';
        }

        // First row is headers
        $headers = array_shift($data);
        $output = [];

        foreach ($data as $row) {
            $rowData = array_combine($headers, $row);
            $output[] = $this->arrayToText($rowData);
        }

        return implode("\n\n", $output);
    }

    /**
     * Parse XML content
     */
    private function parseXml(string $xml): string
    {
        try {
            $doc = new \SimpleXMLElement($xml);
            return $this->xmlToText($doc);
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid XML: ' . $e->getMessage());
        }
    }

    /**
     * Parse PDF file (simplified - in production use a PDF library)
     */
    private function parsePdf(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("PDF file not found: {$path}");
        }

        // In production, use a library like smalot/pdfparser
        // For this example, we'll throw an informative error
        throw new \RuntimeException(
            'PDF parsing requires a PDF library. Install: composer require smalot/pdfparser'
        );
    }

    /**
     * Parse file by detecting type
     */
    private function parseFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $content = file_get_contents($path);

        return match($extension) {
            'txt' => $this->parseText($content),
            'html', 'htm' => $this->parseHtml($content),
            'json' => $this->parseJson($content),
            'csv' => $this->parseCsv($content),
            'xml' => $this->parseXml($content),
            'pdf' => $this->parsePdf($path),
            default => $this->parseText($content)
        };
    }

    /**
     * Parse content from URL
     */
    private function parseUrl(string $url): string
    {
        $this->logger->debug("Fetching URL: {$url}");

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: PHP Data Extractor/1.0',
                'timeout' => 30
            ]
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            throw new \RuntimeException("Failed to fetch URL: {$url}");
        }

        // Detect content type from headers
        $headers = $http_response_header ?? [];
        $contentType = '';

        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $contentType = strtolower(substr($header, 13));
                break;
            }
        }

        // Parse based on content type
        if (str_contains($contentType, 'html')) {
            return $this->parseHtml($content);
        } elseif (str_contains($contentType, 'json')) {
            return $this->parseJson($content);
        } elseif (str_contains($contentType, 'xml')) {
            return $this->parseXml($content);
        } else {
            return $this->parseText($content);
        }
    }

    /**
     * Convert array to readable text
     */
    private function arrayToText(array $data, int $indent = 0): string
    {
        $lines = [];
        $prefix = str_repeat('  ', $indent);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $lines[] = "{$prefix}{$key}:";
                $lines[] = $this->arrayToText($value, $indent + 1);
            } else {
                $lines[] = "{$prefix}{$key}: {$value}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Convert XML to text
     */
    private function xmlToText(\SimpleXMLElement $xml, int $indent = 0): string
    {
        $lines = [];
        $prefix = str_repeat('  ', $indent);

        foreach ($xml->children() as $child) {
            $name = $child->getName();
            $value = (string)$child;

            if ($child->count() > 0) {
                $lines[] = "{$prefix}{$name}:";
                $lines[] = $this->xmlToText($child, $indent + 1);
            } else {
                $lines[] = "{$prefix}{$name}: {$value}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Parse multiple documents
     */
    public function parseMultiple(array $sources, string $type): array
    {
        $results = [];

        foreach ($sources as $index => $source) {
            try {
                $results[$index] = $this->parse($source, $type);
            } catch (\Throwable $e) {
                $this->logger->error("Failed to parse document {$index}", [
                    'error' => $e->getMessage()
                ]);
                $results[$index] = null;
            }
        }

        return $results;
    }

    /**
     * Extract text snippets around keywords
     */
    public function extractSnippets(
        string $text,
        array $keywords,
        int $contextChars = 100
    ): array {
        $snippets = [];

        foreach ($keywords as $keyword) {
            $pos = stripos($text, $keyword);

            if ($pos !== false) {
                $start = max(0, $pos - $contextChars);
                $length = strlen($keyword) + (2 * $contextChars);
                $snippet = substr($text, $start, $length);

                $snippets[$keyword] = [
                    'text' => trim($snippet),
                    'position' => $pos,
                    'context' => $contextChars
                ];
            }
        }

        return $snippets;
    }

    /**
     * Split long text into chunks
     */
    public function chunkText(
        string $text,
        int $maxChunkSize = 5000,
        int $overlap = 200
    ): array {
        $chunks = [];
        $length = strlen($text);
        $start = 0;

        while ($start < $length) {
            $chunk = substr($text, $start, $maxChunkSize);
            $chunks[] = $chunk;
            $start += $maxChunkSize - $overlap;
        }

        return $chunks;
    }
}
