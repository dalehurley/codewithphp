<?php

declare(strict_types=1);

namespace App;

/**
 * Chunking Service
 *
 * Split documents into semantic chunks for RAG processing.
 */
class ChunkingService
{
    public function __construct(
        private int $chunkSize = 1000,
        private int $overlap = 200
    ) {}

    /**
     * Chunk text using sliding window
     */
    public function chunkBySize(string $text): array
    {
        $chunks = [];
        $length = strlen($text);
        $position = 0;

        while ($position < $length) {
            $chunk = substr($text, $position, $this->chunkSize);

            // Try to break at sentence boundary
            if ($position + $this->chunkSize < $length) {
                $lastPeriod = strrpos($chunk, '.');
                $lastNewline = strrpos($chunk, "\n");
                $breakPoint = max($lastPeriod, $lastNewline);

                if ($breakPoint > $this->chunkSize / 2) {
                    $chunk = substr($chunk, 0, $breakPoint + 1);
                }
            }

            $chunks[] = [
                'text' => trim($chunk),
                'start' => $position,
                'end' => $position + strlen($chunk),
                'index' => count($chunks)
            ];

            $position += strlen($chunk) - $this->overlap;
        }

        return $chunks;
    }

    /**
     * Chunk by paragraphs
     */
    public function chunkByParagraph(string $text): array
    {
        $paragraphs = preg_split('/\n\s*\n/', $text);
        $chunks = [];
        $current = '';
        $index = 0;

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            if (strlen($current) + strlen($para) > $this->chunkSize && !empty($current)) {
                $chunks[] = [
                    'text' => trim($current),
                    'index' => $index++,
                    'type' => 'paragraph'
                ];
                $current = '';
            }

            $current .= ($current ? "\n\n" : '') . $para;
        }

        if (!empty($current)) {
            $chunks[] = [
                'text' => trim($current),
                'index' => $index,
                'type' => 'paragraph'
            ];
        }

        return $chunks;
    }

    /**
     * Chunk by sentences
     */
    public function chunkBySentence(string $text, int $sentencesPerChunk = 5): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $index = 0;

        for ($i = 0; $i < count($sentences); $i += $sentencesPerChunk) {
            $chunk = array_slice($sentences, $i, $sentencesPerChunk);
            $chunks[] = [
                'text' => implode(' ', $chunk),
                'index' => $index++,
                'sentences' => count($chunk)
            ];
        }

        return $chunks;
    }

    /**
     * Semantic chunking (simplified)
     */
    public function semanticChunk(string $text): array
    {
        $sections = $this->extractSections($text);
        $chunks = [];

        foreach ($sections as $section) {
            if (strlen($section['content']) > $this->chunkSize) {
                $subChunks = $this->chunkBySize($section['content']);
                foreach ($subChunks as $chunk) {
                    $chunks[] = [
                        'text' => $chunk['text'],
                        'heading' => $section['heading'],
                        'index' => count($chunks)
                    ];
                }
            } else {
                $chunks[] = [
                    'text' => $section['content'],
                    'heading' => $section['heading'],
                    'index' => count($chunks)
                ];
            }
        }

        return $chunks;
    }

    /**
     * Extract sections from text
     */
    private function extractSections(string $text): array
    {
        $sections = [];
        $lines = explode("\n", $text);
        $currentSection = ['heading' => 'Introduction', 'content' => ''];

        foreach ($lines as $line) {
            // Detect headings (lines that start with # or are all caps)
            if (preg_match('/^#{1,6}\s+(.+)$/', $line, $matches)) {
                if (!empty($currentSection['content'])) {
                    $sections[] = $currentSection;
                }
                $currentSection = ['heading' => $matches[1], 'content' => ''];
            } elseif (preg_match('/^[A-Z][A-Z\s]{5,}$/', $line)) {
                if (!empty($currentSection['content'])) {
                    $sections[] = $currentSection;
                }
                $currentSection = ['heading' => $line, 'content' => ''];
            } else {
                $currentSection['content'] .= $line . "\n";
            }
        }

        if (!empty($currentSection['content'])) {
            $sections[] = $currentSection;
        }

        return $sections;
    }

    /**
     * Add metadata to chunks
     */
    public function enrichChunks(array $chunks, array $metadata = []): array
    {
        return array_map(function($chunk) use ($metadata) {
            return array_merge($chunk, [
                'metadata' => $metadata,
                'created_at' => date('c'),
                'id' => md5($chunk['text'])
            ]);
        }, $chunks);
    }
}
