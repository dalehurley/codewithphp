<?php

return [
    'name' => 'Smart Product Recommender',
    'version' => '1.0.0',
    'environment' => $_ENV['APP_ENV'] ?? 'development',
    
    // Recommendation settings
    'recommendation' => [
        'min_common_items' => 2,
        'max_recommendations' => 10,
        'similarity_threshold' => 0.1,
        'cache_ttl' => 3600, // 1 hour
    ],
    
    // Model settings
    'model' => [
        'retrain_interval' => 86400, // 24 hours
        'validation_split' => 0.2,
        'min_accuracy' => 0.70,
    ],
    
    // Monitoring
    'monitoring' => [
        'track_predictions' => true,
        'alert_threshold' => 0.65,
        'log_level' => 'info',
    ],
];
