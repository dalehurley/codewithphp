<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Anthropic\Messages\MessageParam;
use ClaudePHP\SDK\SDKWrapper;

echo "SDK - Testing Strategies\n\n";

class ClaudeServiceTest
{
    private SDKWrapper $sdk;

    public function __construct()
    {
        $transport = function (array $payload) {
            return [
                'content' => [['type' => 'text', 'text' => 'Hello from mock']],
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
                'model' => $payload['model'],
            ];
        };

        $this->sdk = new SDKWrapper(apiKey: 'test-key', transport: $transport);
    }

    public function testBasicResponse(): void
    {
        $result = $this->sdk->sendMessage([
            'model' => 'claude-3-5-sonnet-20240620',
            'maxTokens' => 64,
            'messages' => [MessageParam::fromUser('Say hello')],
        ]);

        assert(is_array($result));
        assert($result['content'][0]['text'] === 'Hello from mock');
        echo "✓ Basic response test passed\n";
    }

    public function testTokenCounting(): void
    {
        $result = $this->sdk->sendMessage([
            'model' => 'claude-3-5-sonnet-20240620',
            'maxTokens' => 64,
            'messages' => [MessageParam::fromUser('Count tokens')],
        ]);

        assert($result['usage']['input_tokens'] === 5);
        assert($result['usage']['output_tokens'] === 2);
        echo "✓ Token counting test passed\n";
    }
}

$tester = new ClaudeServiceTest();
$tester->testBasicResponse();
$tester->testTokenCounting();

echo "\n✓ All tests passed\n";
