<?php

declare(strict_types=1);

namespace ClaudePHP\DocumentProcessing;

use GuzzleHttp\ClaudePhp;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentProcessor
{
    private ClaudePhp $client;
    private string $apiKey;
    private PdfParser $pdfParser;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new ClaudePhp(['base_uri' => 'https://api.anthropic.com']);
        $this->pdfParser = new PdfParser();
    }

    public function processPDF(string $pdfPath, string $prompt): array
    {
        if (!file_exists($pdfPath)) {
            throw new \Exception("PDF file not found: {$pdfPath}");
        }

        // Parse PDF
        $pdf = $this->pdfParser->parseFile($pdfPath);
        $text = $pdf->getText();

        // Send to Claude
        return $this->sendToClaude($text, $prompt);
    }

    public function processText(string $text, string $prompt): array
    {
        return $this->sendToClaude($text, $prompt);
    }

    private function sendToClaude(string $text, string $prompt): array
    {
        $response = $this->client->post('/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 8192,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Document content:\n\n{$text}\n\n{$prompt}",
                    ],
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
