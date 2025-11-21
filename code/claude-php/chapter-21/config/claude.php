<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Claude API Key
    |--------------------------------------------------------------------------
    |
    | Your Anthropic API key. Get one at https://console.anthropic.com/
    |
    */

    'api_key' => env('CLAUDE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default Claude model to use for requests.
    |
    | Options: claude-opus-4-1, claude-sonnet-4-5, etc.
    |
    */

    'model' => env('CLAUDE_MODEL', 'claude-sonnet-4-5'),

    /*
    |--------------------------------------------------------------------------
    | Max Tokens
    |--------------------------------------------------------------------------
    |
    | The maximum number of tokens to generate in responses.
    |
    */

    'max_tokens' => env('CLAUDE_MAX_TOKENS', 4096),

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    |
    | Controls randomness in responses (0.0 to 1.0).
    | Higher values make output more random, lower more deterministic.
    |
    */

    'temperature' => env('CLAUDE_TEMPERATURE', null),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure response caching to reduce API calls and costs.
    |
    */

    'cache' => [
        'enabled' => env('CLAUDE_CACHE_ENABLED', true),
        'driver' => env('CLAUDE_CACHE_DRIVER', 'redis'),
        'ttl' => env('CLAUDE_CACHE_TTL', 3600), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Log all Claude API requests and responses.
    |
    */

    'logging' => [
        'enabled' => env('CLAUDE_LOG_REQUESTS', false),
        'channel' => env('CLAUDE_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompts Configuration
    |--------------------------------------------------------------------------
    |
    | Configure prompt template storage and management.
    |
    */

    'prompts' => [
        'path' => resource_path('prompts'),
        'cache' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure async processing of Claude requests.
    |
    */

    'queue' => [
        'enabled' => env('CLAUDE_QUEUE_ENABLED', false),
        'connection' => env('CLAUDE_QUEUE_CONNECTION', 'redis'),
        'name' => env('CLAUDE_QUEUE_NAME', 'claude'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for Claude API calls.
    |
    */

    'rate_limit' => [
        'enabled' => env('CLAUDE_RATE_LIMIT_ENABLED', true),
        'requests_per_minute' => env('CLAUDE_RATE_LIMIT_RPM', 50),
    ],

];
