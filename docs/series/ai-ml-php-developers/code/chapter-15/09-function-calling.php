<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Function Calling Demo (Tools API)
 *
 * Demonstrates how to let GPT call PHP functions to access external data or perform actions.
 * This is essential for building AI agents that can interact with databases, APIs, etc.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.002-0.003 per request (may require multiple API calls)
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

/**
 * Example functions that GPT can call
 */

function getCurrentWeather(string $location, string $unit = 'celsius'): array
{
    // In production, this would call a real weather API
    $weatherData = [
        'san francisco' => ['temp' => 18, 'condition' => 'Partly cloudy'],
        'new york' => ['temp' => 12, 'condition' => 'Rainy'],
        'tokyo' => ['temp' => 15, 'condition' => 'Clear'],
        'london' => ['temp' => 10, 'condition' => 'Overcast'],
    ];

    $location = strtolower($location);
    $weather = $weatherData[$location] ?? ['temp' => 20, 'condition' => 'Unknown'];

    if ($unit === 'fahrenheit') {
        $weather['temp'] = ($weather['temp'] * 9 / 5) + 32;
    }

    return [
        'location' => $location,
        'temperature' => $weather['temp'],
        'unit' => $unit,
        'condition' => $weather['condition'],
    ];
}

function getUserInfo(int $userId): array
{
    // In production, this would query a database
    $users = [
        1 => ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Developer'],
        2 => ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'Designer'],
        3 => ['name' => 'Carol White', 'email' => 'carol@example.com', 'role' => 'Manager'],
    ];

    return $users[$userId] ?? ['error' => 'User not found'];
}

function calculatePrice(string $product, int $quantity): array
{
    // In production, this would check inventory and pricing
    $prices = [
        'laptop' => 1200,
        'mouse' => 25,
        'keyboard' => 80,
        'monitor' => 350,
    ];

    $product = strtolower($product);
    $unitPrice = $prices[$product] ?? 0;
    $total = $unitPrice * $quantity;
    $tax = $total * 0.10; // 10% tax

    return [
        'product' => $product,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'subtotal' => $total,
        'tax' => $tax,
        'total' => $total + $tax,
    ];
}

// Define available functions for GPT
$tools = [
    [
        'type' => 'function',
        'function' => [
            'name' => 'getCurrentWeather',
            'description' => 'Get the current weather for a specific location',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => [
                        'type' => 'string',
                        'description' => 'The city name, e.g., San Francisco',
                    ],
                    'unit' => [
                        'type' => 'string',
                        'enum' => ['celsius', 'fahrenheit'],
                        'description' => 'Temperature unit',
                    ],
                ],
                'required' => ['location'],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'getUserInfo',
            'description' => 'Get information about a user by their ID',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'userId' => [
                        'type' => 'integer',
                        'description' => 'The user ID',
                    ],
                ],
                'required' => ['userId'],
            ],
        ],
    ],
    [
        'type' => 'function',
        'function' => [
            'name' => 'calculatePrice',
            'description' => 'Calculate the total price for a product order including tax',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'product' => [
                        'type' => 'string',
                        'description' => 'Product name (laptop, mouse, keyboard, monitor)',
                    ],
                    'quantity' => [
                        'type' => 'integer',
                        'description' => 'Number of items to purchase',
                    ],
                ],
                'required' => ['product', 'quantity'],
            ],
        ],
    ],
];

echo "=== Function Calling Demo ===\n\n";

// Example queries that will trigger function calls
$queries = [
    "What's the weather like in Tokyo?",
    "Can you look up info for user ID 2?",
    "How much would 3 laptops cost including tax?",
];

foreach ($queries as $i => $query) {
    echo ($i + 1) . ". User: {$query}\n";

    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant with access to functions.'],
        ['role' => 'user', 'content' => $query],
    ];

    try {
        // Initial request with function definitions
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto', // Let GPT decide when to use functions
        ]);

        $responseMessage = $response->choices[0]->message;

        // Check if GPT wants to call a function
        if (isset($responseMessage->toolCalls) && count($responseMessage->toolCalls) > 0) {
            // Add GPT's response to messages
            $messages[] = [
                'role' => 'assistant',
                'content' => $responseMessage->content ?? null,
                'tool_calls' => $responseMessage->toolCalls,
            ];

            // Execute each function call
            foreach ($responseMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $functionArgs = json_decode($toolCall->function->arguments, true);

                echo "   → Calling function: {$functionName}(";
                echo json_encode($functionArgs) . ")\n";

                // Execute the function
                $functionResult = match ($functionName) {
                    'getCurrentWeather' => getCurrentWeather(
                        $functionArgs['location'],
                        $functionArgs['unit'] ?? 'celsius'
                    ),
                    'getUserInfo' => getUserInfo($functionArgs['userId']),
                    'calculatePrice' => calculatePrice(
                        $functionArgs['product'],
                        $functionArgs['quantity']
                    ),
                    default => ['error' => 'Unknown function'],
                };

                echo "   → Result: " . json_encode($functionResult) . "\n";

                // Add function result to messages
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => json_encode($functionResult),
                ];
            }

            // Get final response from GPT with function results
            $finalResponse = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
            ]);

            $finalMessage = $finalResponse->choices[0]->message->content;
            echo "   Assistant: {$finalMessage}\n\n";
        } else {
            // No function call needed
            echo "   Assistant: {$responseMessage->content}\n\n";
        }
    } catch (\Exception $e) {
        echo "   Error: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Key Takeaways ===\n";
echo "• GPT can automatically decide when to call functions\n";
echo "• Functions must be properly described with JSON schema\n";
echo "• Results are sent back to GPT for natural language response\n";
echo "• This enables building AI agents that interact with real systems\n";
