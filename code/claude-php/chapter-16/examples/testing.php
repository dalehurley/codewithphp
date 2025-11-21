<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "SDK - Testing Strategies\n\n";

class ClaudeServiceTest
{
    private $mockResponses = [];

    public function mockResponse(array $response): void
    {
        $this->mockResponses[] = $response;
    }

    public function callClaude(string $prompt): array
    {
        if (empty($this->mockResponses)) {
            throw new \Exception('No mock responses configured');
        }
        return array_shift($this->mockResponses);
    }

    public function testBasicResponse(): void
    {
        $this->mockResponse([
            'content' => [['type' => 'text', 'text' => 'Hello!']],
            'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
        ]);

        $result = $this->callClaude('Say hello');

        assert($result['content'][0]['text'] === 'Hello!');
        echo "✓ Basic response test passed\n";
    }

    public function testTokenCounting(): void
    {
        $this->mockResponse([
            'content' => [['type' => 'text', 'text' => 'Response']],
            'usage' => ['input_tokens' => 10, 'output_tokens' => 5],
        ]);

        $result = $this->callClaude('Test');

        assert($result['usage']['input_tokens'] === 10);
        assert($result['usage']['output_tokens'] === 5);
        echo "✓ Token counting test passed\n";
    }
}

$tester = new ClaudeServiceTest();
$tester->testBasicResponse();
$tester->testTokenCounting();

echo "\n✓ All tests passed\n";
