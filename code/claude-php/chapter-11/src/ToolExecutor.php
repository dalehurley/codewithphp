<?php

declare(strict_types=1);

namespace ClaudePHP\ToolUse;

use GuzzleHttp\ClaudePhp;

/**
 * Executes Claude API requests with tool use support
 */
class ToolExecutor
{
    private ClaudePhp $client;
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private array $tools = [];
    private array $toolFunctions = [];

    public function __construct(
        string $apiKey,
        string $model = 'claude-sonnet-4-5',
        int $maxTokens = 4096
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->maxTokens = $maxTokens;

        $this->client = new ClaudePhp([
            'base_uri' => 'https://api.anthropic.com',
            'timeout' => 30,
        ]);
    }

    /**
     * Register a tool
     */
    public function registerTool(
        string $name,
        string $description,
        array $inputSchema,
        callable $function
    ): void {
        $this->tools[] = [
            'name' => $name,
            'description' => $description,
            'input_schema' => $inputSchema,
        ];

        $this->toolFunctions[$name] = $function;
    }

    /**
     * Execute a conversation with tool use
     */
    public function execute(
        string $userMessage,
        string $systemPrompt = '',
        int $maxIterations = 10
    ): array {
        $messages = [
            ['role' => 'user', 'content' => $userMessage],
        ];

        $iteration = 0;

        while ($iteration < $maxIterations) {
            $response = $this->makeRequest($messages, $systemPrompt);

            // Check stop reason
            if ($response['stop_reason'] === 'end_turn') {
                return [
                    'response' => $this->extractText($response),
                    'iterations' => $iteration + 1,
                    'usage' => $response['usage'],
                ];
            }

            if ($response['stop_reason'] === 'tool_use') {
                // Process tool calls
                $toolResults = $this->processToolCalls($response['content']);

                // Add assistant response to messages
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $response['content'],
                ];

                // Add tool results to messages
                $messages[] = [
                    'role' => 'user',
                    'content' => $toolResults,
                ];

                $iteration++;
                continue;
            }

            // Max tokens reached or error
            break;
        }

        throw new \Exception('Max iterations reached without completion');
    }

    /**
     * Make API request
     */
    private function makeRequest(array $messages, string $systemPrompt = ''): array
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $messages,
        ];

        if (!empty($this->tools)) {
            $payload['tools'] = $this->tools;
        }

        if (!empty($systemPrompt)) {
            $payload['system'] = $systemPrompt;
        }

        $response = $this->client->post('/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Process tool calls from Claude
     */
    private function processToolCalls(array $content): array
    {
        $toolResults = [];

        foreach ($content as $block) {
            if ($block['type'] === 'tool_use') {
                $toolName = $block['name'];
                $toolInput = $block['input'];
                $toolUseId = $block['id'];

                echo "🔧 Calling tool: {$toolName}\n";
                echo "   Input: " . json_encode($toolInput) . "\n";

                if (!isset($this->toolFunctions[$toolName])) {
                    $result = ['error' => "Tool '{$toolName}' not found"];
                } else {
                    try {
                        $result = ($this->toolFunctions[$toolName])($toolInput);
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage()];
                    }
                }

                echo "   Result: " . json_encode($result) . "\n\n";

                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUseId,
                    'content' => json_encode($result),
                ];
            }
        }

        return $toolResults;
    }

    /**
     * Extract text from response
     */
    private function extractText(array $response): string
    {
        $text = '';

        foreach ($response['content'] as $block) {
            if ($block['type'] === 'text') {
                $text .= $block['text'];
            }
        }

        return $text;
    }
}
