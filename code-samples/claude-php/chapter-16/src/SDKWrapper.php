<?php

declare(strict_types=1);

namespace ClaudePHP\SDK;

use Anthropic\Client;
use Anthropic\Messages\MessageParam;

/**
 * Thin wrapper around the official Anthropic PHP SDK that lets you attach
 * middleware-style callbacks without modifying the SDK itself.
 */
class SDKWrapper
{
    private Client $client;

    /** @var callable|null */
    private $transport;

    /** @var array<int, callable> */
    private array $middleware = [];

    public function __construct(string $apiKey, ?callable $transport = null)
    {
        $this->client = new Client(apiKey: $apiKey);
        $this->transport = $transport;
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Send a message using the official client. Accepts an array payload that matches
     * the SDK's create() signature (model, max_tokens/maxTokens, messages, etc.).
     */
    public function sendMessage(array $params): mixed
    {
        $preparedParams = $this->normalizeMessages($params);

        foreach ($this->middleware as $mw) {
            $preparedParams = $mw('request', $preparedParams);
        }

        $sender = $this->transport ?? function (array $payload) {
            return $this->client->messages->create(...$payload);
        };

        $response = $sender($preparedParams);

        foreach ($this->middleware as $mw) {
            $response = $mw('response', $response);
        }

        return $response;
    }

    /**
     * Stream a response from the API and invoke middleware on each streamed event.
     */
    public function streamMessage(array $params): iterable
    {
        $preparedParams = $this->normalizeMessages($params);

        foreach ($this->middleware as $mw) {
            $preparedParams = $mw('request', $preparedParams);
        }

        $stream = $this->client->messages->createStream(...$preparedParams);

        foreach ($stream as $event) {
            foreach ($this->middleware as $mw) {
                $mw('stream', $event);
            }
            yield $event;
        }
    }

    /**
     * Ensure message payloads use the typed helpers where possible while
     * preserving array compatibility for injected transports.
     */
    private function normalizeMessages(array $params): array
    {
        if (isset($params['messages'])) {
            $params['messages'] = array_map(function ($message) {
                if ($message instanceof MessageParam) {
                    return $message;
                }

                if (is_array($message) && ($message['role'] ?? null) === 'user') {
                    return MessageParam::fromUser($message['content']);
                }

                return $message;
            }, $params['messages']);
        }

        if (array_key_exists('max_tokens', $params) && !isset($params['maxTokens'])) {
            $params['maxTokens'] = $params['max_tokens'];
        }

        return $params;
    }
}
