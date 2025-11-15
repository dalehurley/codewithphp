<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration\Tests;

use ClaudePhp\LaravelIntegration\ClaudeService;
use ClaudePhp\LaravelIntegration\ClaudeResponse;
use Orchestra\Testbench\TestCase;
use Mockery;

class ClaudeServiceTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \ClaudePhp\LaravelIntegration\ClaudeServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Claude' => \ClaudePhp\LaravelIntegration\Facades\Claude::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('claude.api_key', 'test-key');
        $app['config']->set('claude.cache.enabled', false);
    }

    public function test_can_resolve_claude_service(): void
    {
        $service = $this->app->make(ClaudeService::class);

        $this->assertInstanceOf(ClaudeService::class, $service);
    }

    public function test_can_use_claude_facade(): void
    {
        $this->assertTrue(class_exists(\Claude::class));
    }

    public function test_conversation_builder(): void
    {
        $conversation = \Claude::conversation('You are a helpful assistant');

        $conversation
            ->user('Hello')
            ->assistant('Hi there!')
            ->user('How are you?');

        $messages = $conversation->messages();

        $this->assertCount(3, $messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('assistant', $messages[1]['role']);
        $this->assertEquals('How are you?', $messages[2]['content']);
    }

    public function test_conversation_can_be_cleared(): void
    {
        $conversation = \Claude::conversation()
            ->user('Test message');

        $this->assertCount(1, $conversation->messages());

        $conversation->clear();

        $this->assertCount(0, $conversation->messages());
    }

    public function test_claude_response_wrapper(): void
    {
        $data = [
            'id' => 'msg_123',
            'content' => 'Hello world',
            'model' => 'claude-sonnet-4-20250514',
            'role' => 'assistant',
            'stop_reason' => 'end_turn',
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 20,
            ],
        ];

        $response = new ClaudeResponse($data);

        $this->assertEquals('msg_123', $response->id());
        $this->assertEquals('Hello world', $response->content());
        $this->assertEquals('claude-sonnet-4-20250514', $response->model());
        $this->assertEquals(10, $response->inputTokens());
        $this->assertEquals(20, $response->outputTokens());
        $this->assertEquals(30, $response->totalTokens());
        $this->assertEquals('Hello world', (string)$response);
    }

    public function test_response_to_array(): void
    {
        $data = [
            'id' => 'msg_123',
            'content' => 'Test',
            'model' => 'claude-sonnet-4-20250514',
            'role' => 'assistant',
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 5, 'output_tokens' => 10],
        ];

        $response = new ClaudeResponse($data);

        $this->assertEquals($data, $response->toArray());
    }

    public function test_response_to_json(): void
    {
        $data = [
            'id' => 'msg_123',
            'content' => 'Test',
            'model' => 'claude-sonnet-4-20250514',
            'role' => 'assistant',
            'stop_reason' => 'end_turn',
            'usage' => ['input_tokens' => 5, 'output_tokens' => 10],
        ];

        $response = new ClaudeResponse($data);

        $this->assertEquals(json_encode($data), $response->toJson());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
