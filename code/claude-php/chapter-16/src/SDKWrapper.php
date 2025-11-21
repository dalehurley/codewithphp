<?php

declare(strict_types=1);

namespace ClaudePHP\SDK;

use GuzzleHttp\ClaudePhp;

/**
 * Wrapper around Claude API with middleware support
 */
class SDKWrapper
{
    private ClaudePhp $client;
    private string $apiKey;
    private array $middleware = [];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new ClaudePhp(['base_uri' => 'https://api.anthropic.com']);
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function sendMessage(array $params): array
    {
        // Apply pre-request middleware
        foreach ($this->middleware as $mw) {
            $params = $mw('request', $params);
        }

        $response = $this->client->post('/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => $params,
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        // Apply post-response middleware
        foreach ($this->middleware as $mw) {
            $result = $mw('response', $result);
        }

        return $result;
    }
}
