<?php

declare(strict_types=1);

namespace ClaudePHP\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use UnexpectedValueException;

/** Add application middleware to a direct Messages API request. */
class SDKWrapper
{
    private readonly ClientInterface $client;

    /** @var list<callable(string, array): array> */
    private array $middleware = [];

    public function __construct(private readonly string $apiKey, ?ClientInterface $client = null)
    {
        $this->client = $client ?? new Client(['connect_timeout' => 5.0, 'timeout' => 30.0]);
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function sendMessage(array $params): array
    {
        foreach ($this->middleware as $middleware) {
            $params = $middleware('request', $params);
        }

        $response = $this->client->request('POST', 'https://api.anthropic.com/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ],
            'json' => $params,
        ]);

        $result = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($result) || !isset($result['content'], $result['usage'])) {
            throw new UnexpectedValueException('Expected a Claude message with content and usage.');
        }

        foreach ($this->middleware as $middleware) {
            $result = $middleware('response', $result);
        }

        return $result;
    }
}
