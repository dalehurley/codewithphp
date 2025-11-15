<?php

declare(strict_types=1);

namespace ClaudePhp\LaravelIntegration\Facades;

use Illuminate\Support\Facades\Facade;
use ClaudePhp\LaravelIntegration\ClaudeService;
use ClaudePhp\LaravelIntegration\ClaudeResponse;
use ClaudePhp\LaravelIntegration\Conversation;

/**
 * Claude Facade
 *
 * @method static ClaudeResponse message(string|array $messages, ?string $system = null, ?string $model = null, ?int $maxTokens = null, ?float $temperature = null)
 * @method static void stream(string|array $messages, callable $onChunk, ?string $system = null, ?string $model = null, ?int $maxTokens = null)
 * @method static Conversation conversation(?string $system = null)
 * @method static \Anthropic\Anthropic client()
 *
 * @see \ClaudePhp\LaravelIntegration\ClaudeService
 */
class Claude extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ClaudeService::class;
    }
}
