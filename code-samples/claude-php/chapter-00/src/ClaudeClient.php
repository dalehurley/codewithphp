<?php

declare(strict_types=1);

namespace ClaudePhp\QuickStart;

use Anthropic\Anthropic;
use Anthropic\Client as AnthropicClient;
use Anthropic\Exceptions\ErrorException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * ClaudeClient - A simplified wrapper around the Anthropic PHP SDK
 *
 * This class provides a convenient interface for making Claude API calls
 * with built-in error handling, logging, and usage tracking.
 */
class ClaudeClient
{
    private AnthropicClient $client;
    private Logger $logger;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    /**
     * Create a new Claude client instance
     *
     * @param string $apiKey Anthropic API key
     * @param string $model Model to use (default: claude-sonnet-4-20250514)
     * @param int $maxTokens Maximum tokens to generate (default: 4096)
     * @param float $temperature Sampling temperature (default: 1.0)
     */
    public function __construct(
        string $apiKey,
        string $model = 'claude-sonnet-4-20250514',
        int $maxTokens = 4096,
        float $temperature = 1.0
    ) {
        $this->client = Anthropic::factory()
            ->withApiKey($apiKey)
            ->make();

        $this->model = $model;
        $this->maxTokens = $maxTokens;
        $this->temperature = $temperature;

        // Set up logger
        $this->logger = new Logger('claude');
        $logFile = getenv('LOG_FILE') ?: 'php://stdout';
        $this->logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
    }

    /**
     * Send a simple text message to Claude
     *
     * @param string $message The message to send
     * @param array<string, mixed> $options Additional options to override defaults
     * @return object The API response
     * @throws ErrorException If the API request fails
     */
    public function sendMessage(string $message, array $options = []): object
    {
        $params = array_merge([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ]
        ], $options);

        $this->logger->info('Sending message to Claude', [
            'model' => $params['model'],
            'message_length' => strlen($message)
        ]);

        try {
            $response = $this->client->messages()->create($params);

            $this->logger->info('Received response from Claude', [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'stop_reason' => $response->stopReason
            ]);

            return $response;
        } catch (ErrorException $e) {
            $this->logger->error('Claude API error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * Send a message with a system prompt
     *
     * @param string $systemPrompt The system prompt to set context
     * @param string $message The user message
     * @param array<string, mixed> $options Additional options
     * @return object The API response
     * @throws ErrorException If the API request fails
     */
    public function sendWithSystem(string $systemPrompt, string $message, array $options = []): object
    {
        $options['system'] = $systemPrompt;
        return $this->sendMessage($message, $options);
    }

    /**
     * Send a multi-turn conversation to Claude
     *
     * @param array<int, array<string, string>> $messages Array of message objects with 'role' and 'content'
     * @param array<string, mixed> $options Additional options
     * @return object The API response
     * @throws ErrorException If the API request fails
     */
    public function sendConversation(array $messages, array $options = []): object
    {
        $params = array_merge([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => $messages
        ], $options);

        $this->logger->info('Sending conversation to Claude', [
            'model' => $params['model'],
            'message_count' => count($messages)
        ]);

        try {
            $response = $this->client->messages()->create($params);

            $this->logger->info('Received response from Claude', [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens
            ]);

            return $response;
        } catch (ErrorException $e) {
            $this->logger->error('Claude API error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Extract text from API response
     *
     * @param object $response The API response
     * @return string The extracted text content
     */
    public function extractText(object $response): string
    {
        return $response->content[0]->text ?? '';
    }

    /**
     * Get usage information from API response
     *
     * @param object $response The API response
     * @return array<string, int> Array with inputTokens and outputTokens
     */
    public function getUsage(object $response): array
    {
        return [
            'inputTokens' => $response->usage->inputTokens,
            'outputTokens' => $response->usage->outputTokens,
            'totalTokens' => $response->usage->inputTokens + $response->usage->outputTokens
        ];
    }
}
