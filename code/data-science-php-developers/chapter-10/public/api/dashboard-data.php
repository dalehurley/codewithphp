<?php

declare(strict_types=1);

/**
 * Dashboard Data API Endpoint
 * 
 * Provides JSON data for live dashboard updates.
 * Returns current metrics, chart data, and table data.
 * 
 * Usage: Call this endpoint from AJAX to update dashboard without page reload
 */

require __DIR__ . '/../../../vendor/autoload.php';

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *'); // Adjust for production

try {
    // In real application, fetch data from database or analytics service
    // This example generates mock data for demonstration
    
    $data = [
        'timestamp' => date('Y-m-d H:i:s'),
        
        // Stat card updates
        'stats' => [
            'total-users' => [
                'value' => number_format(rand(12000, 13000)),
                'trend' => '+' . rand(5, 10) / 10 . '%',
            ],
            'revenue' => [
                'value' => '$' . number_format(rand(40000, 50000)),
                'trend' => '+' . rand(10, 15) / 10 . '%',
            ],
            'conversion' => [
                'value' => rand(30, 35) / 10 . '%',
                'trend' => '+' . rand(5, 10) / 10 . '%',
            ],
            'avg-order' => [
                'value' => '$' . rand(120, 135),
                'trend' => (rand(0, 1) ? '+' : '-') . rand(1, 5) / 10 . '%',
            ],
        ],
        
        // Chart data updates
        'charts' => [
            'modelPerformance' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'datasets' => [
                    [
                        'data' => [
                            rand(85, 90) / 100,
                            rand(87, 92) / 100,
                            rand(89, 94) / 100,
                            rand(88, 93) / 100,
                            rand(90, 95) / 100,
                            rand(89, 94) / 100,
                            rand(91, 96) / 100,
                        ],
                    ],
                ],
            ],
            'conversionFunnel' => [
                'datasets' => [
                    [
                        'data' => [
                            rand(9000, 11000),
                            rand(1400, 1600),
                            rand(400, 500),
                            rand(300, 400),
                        ],
                    ],
                ],
            ],
        ],
        
        // Table data updates
        'tables' => [
            'top-products' => [
                'rows' => [
                    ['Product A', number_format(rand(1200, 1300)), '$' . number_format(rand(44000, 46000)), '+' . rand(10, 14) . '%'],
                    ['Product B', number_format(rand(850, 950)), '$' . number_format(rand(30000, 32000)), '+' . rand(5, 10) . '%'],
                    ['Product C', number_format(rand(700, 800)), '$' . number_format(rand(21000, 23000)), '-' . rand(1, 5) . '%'],
                    ['Product D', number_format(rand(600, 650)), '$' . number_format(rand(17000, 19000)), '+' . rand(12, 17) . '%'],
                    ['Product E', number_format(rand(480, 520)), '$' . number_format(rand(14000, 16000)), '+' . rand(3, 8) . '%'],
                ],
            ],
        ],
    ];
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch dashboard data',
        'message' => $e->getMessage(),
    ]);
}
