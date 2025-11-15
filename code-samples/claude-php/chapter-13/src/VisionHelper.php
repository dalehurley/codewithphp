<?php

declare(strict_types=1);

namespace ClaudePHP\Vision;

use GuzzleHttp\Client;

class VisionHelper
{
    private Client $client;
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey, string $model = 'claude-sonnet-4-20250514')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com']);
    }

    public function analyzeImage(string $imagePath, string $prompt = 'Describe this image'): array
    {
        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        $response = $this->client->post('/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function encodeImage(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("Image file not found: {$path}");
        }
        return base64_encode(file_get_contents($path));
    }

    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
