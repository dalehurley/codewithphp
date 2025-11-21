<?php

declare(strict_types=1);

namespace ClaudePHP\Vision;

use ClaudePhp\ClaudePhp;

class VisionHelper
{
    private ClaudePhp $client;
    private string $model;

    public function __construct(string $apiKey, string $model = 'claude-sonnet-4-5')
    {
        $this->client = new ClaudePhp(
            apiKey: $apiKey
        );
        $this->model = $model;
    }

    public function analyzeImage(string $imagePath, string $prompt = 'Describe this image')
    {
        $imageData = $this->encodeImage($imagePath);
        $mimeType = $this->getMimeType($imagePath);

        return $this->client->messages()->create(
            model: $this->model,
            maxTokens: 4096,
            messages: [
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
            ]
        );
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
