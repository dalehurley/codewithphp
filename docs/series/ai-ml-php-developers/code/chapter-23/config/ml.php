<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | ML Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for machine learning services including caching,
    | timeouts, and model paths.
    |
    */

    'cache' => [
        'enabled' => env('ML_CACHE_ENABLED', true),
        'ttl' => env('ML_CACHE_TTL', 3600), // 1 hour default
        'prefix' => 'ml:',
    ],

    'timeout' => env('ML_TIMEOUT', 30), // seconds

    'models' => [
        'sentiment' => [
            'path' => env('SENTIMENT_MODEL_PATH', storage_path('ml/sentiment_model.json')),
            'type' => 'naive_bayes',
        ],
        'recommendations' => [
            'path' => env('RECOMMENDATION_MODEL_PATH', storage_path('ml/recommendations.json')),
            'type' => 'collaborative_filtering',
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 150),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    'fallback' => [
        'enabled' => env('ML_FALLBACK_ENABLED', true),
        'response' => 'ML service temporarily unavailable',
    ],
];

