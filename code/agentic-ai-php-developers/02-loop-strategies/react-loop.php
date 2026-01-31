<?php

/**
 * ReactLoop Example - Reason-Act-Observe Pattern
 * 
 * Demonstrates the default ReAct loop strategy where the agent:
 * 1. Reasons about what to do next
 * 2. Acts by calling a tool or responding
 * 3. Observes the result
 * 4. Repeats until the task is complete
 * 
 * Use case: General-purpose tasks with flexible tool orchestration
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define tools for travel planning
$getWeather = Tool::create('get_weather')
    ->description('Get current weather conditions for a city')
    ->parameter('city', 'string', 'City name')
    ->required('city')
    ->handler(function (array $input): string {
        // Simulate weather API call
        $conditions = ['sunny', 'cloudy', 'rainy', 'partly cloudy'];
        $temp = rand(60, 85);
        $condition = $conditions[array_rand($conditions)];
        
        return json_encode([
            'city' => $input['city'],
            'temperature' => $temp,
            'conditions' => $condition,
            'humidity' => rand(40, 80) . '%'
        ]);
    });

$calculateDistance = Tool::create('calculate_distance')
    ->description('Calculate driving distance and time between two cities')
    ->parameter('from', 'string', 'Starting city')
    ->parameter('to', 'string', 'Destination city')
    ->required(['from', 'to'])
    ->handler(function (array $input): string {
        // Simulate distance calculation
        $distance = rand(50, 500);
        $hours = round($distance / 60, 1);
        
        return json_encode([
            'from' => $input['from'],
            'to' => $input['to'],
            'distance_miles' => $distance,
            'drive_time_hours' => $hours,
            'traffic' => ['light', 'moderate', 'heavy'][rand(0, 2)]
        ]);
    });

$findHotels = Tool::create('find_hotels')
    ->description('Search for hotels in a city')
    ->parameter('city', 'string', 'City to search in')
    ->parameter('budget', 'string', 'Budget range (low, medium, high)', false)
    ->required('city')
    ->handler(function (array $input): string {
        $budget = $input['budget'] ?? 'medium';
        $prices = [
            'low' => [80, 120],
            'medium' => [150, 250],
            'high' => [300, 500]
        ];
        
        $range = $prices[$budget] ?? $prices['medium'];
        $price = rand($range[0], $range[1]);
        
        return json_encode([
            'city' => $input['city'],
            'hotels_found' => rand(10, 50),
            'average_price' => $price,
            'budget_category' => $budget
        ]);
    });

// Create agent with ReactLoop
$agent = Agent::create($client)
    ->withSystemPrompt(
        'You are a helpful travel planning assistant. ' .
        'Use the available tools to gather information and provide comprehensive travel advice. ' .
        'Be specific and include relevant details from tool results.'
    )
    ->withTools([$getWeather, $calculateDistance, $findHotels])
    ->withLoopStrategy(new ReactLoop(
        maxToolCalls: 10,
        allowParallelTools: false,
        requireExplicitFinish: true
    ))
    ->maxIterations(8)
    ->onUpdate(function ($update) {
        $data = $update->getData();
        $message = $data['message'] ?? $data['text'] ?? '';
        if ($message) {
            echo "📍 {$update->getType()}: {$message}\n";
        }
    });

// Example tasks demonstrating ReactLoop's flexibility
$tasks = [
    "I'm planning a road trip from San Francisco to Los Angeles. What's the weather like, " .
    "how long will it take, and can you suggest some hotels?",
    
    "What's the weather in Seattle, and are there any budget hotels available?"
];

foreach ($tasks as $i => $task) {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Task " . ($i + 1) . ": {$task}\n";
    echo str_repeat('=', 80) . "\n\n";
    
    try {
        $result = $agent->run($task);
        
        echo "\n📊 Agent Response:\n";
        echo str_repeat('-', 80) . "\n";
        echo $result->getAnswer() . "\n";
        echo str_repeat('-', 80) . "\n";
        
        // Show loop statistics
        $metadata = $result->getMetadata();
        echo "\n📈 Statistics:\n";
        echo "  - Iterations: {$result->getIterations()}\n";
        echo "  - Tool calls: " . count($result->getToolCalls()) . "\n";
        echo "  - Duration: {$duration}ms\n";
        
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "\n✅ ReactLoop example completed\n";
echo "\nKey takeaways:\n";
echo "  - ReactLoop adapts reasoning based on tool results\n";
echo "  - Flexible tool selection without upfront planning\n";
echo "  - Efficient for tasks with 1-5 tool calls\n";
echo "  - Low latency compared to planning-based loops\n";
