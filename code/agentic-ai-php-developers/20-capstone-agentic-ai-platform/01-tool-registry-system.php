<?php

declare(strict_types=1);

/**
 * 01 - Tool Registry System
 * 
 * Centralized tool management with permissions, rate limiting, and usage tracking.
 * The foundation of a production-ready agentic AI platform.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;

/**
 * Tool Registry - Centralized tool catalog with permissions and tracking
 */
class ToolRegistry
{
    private array $tools = [];
    private array $permissions = [];
    private array $usageStats = [];
    private array $rateLimits = [];

    public function register(
        Tool $tool,
        array $allowedAgents = [],
        ?int $rateLimit = null
    ): void {
        $name = $tool->getName();
        
        $this->tools[$name] = [
            'tool' => $tool,
            'registered_at' => time(),
            'version' => '1.0',
        ];
        
        $this->permissions[$name] = [
            'allowed_agents' => $allowedAgents, // Empty = all agents
            'requires_approval' => false,
        ];
        
        if ($rateLimit) {
            $this->rateLimits[$name] = [
                'max_calls' => $rateLimit,
                'window' => 3600, // 1 hour
                'calls' => [],
            ];
        }
        
        $this->usageStats[$name] = [
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_duration' => 0,
        ];
    }

    public function getTool(string $name, string $agentId): Tool
    {
        if (!isset($this->tools[$name])) {
            throw new \Exception("Tool not found: {$name}");
        }

        // Check permissions
        $permissions = $this->permissions[$name];
        if (!empty($permissions['allowed_agents']) 
            && !in_array($agentId, $permissions['allowed_agents'])
        ) {
            throw new \Exception(
                "Agent {$agentId} not authorized to use tool: {$name}"
            );
        }

        // Check rate limits
        if (isset($this->rateLimits[$name])) {
            $this->enforceRateLimit($name);
        }

        return $this->tools[$name]['tool'];
    }

    public function getToolsForAgent(string $agentId): array
    {
        $availableTools = [];
        
        foreach ($this->tools as $name => $data) {
            $permissions = $this->permissions[$name];
            
            // If no restrictions or agent is in allowed list
            if (empty($permissions['allowed_agents']) 
                || in_array($agentId, $permissions['allowed_agents'])
            ) {
                $availableTools[] = $data['tool'];
            }
        }
        
        return $availableTools;
    }

    public function trackUsage(
        string $toolName,
        bool $success,
        float $duration
    ): void {
        if (!isset($this->usageStats[$toolName])) {
            return;
        }

        $stats = &$this->usageStats[$toolName];
        $stats['total_calls']++;
        
        if ($success) {
            $stats['successful_calls']++;
        } else {
            $stats['failed_calls']++;
        }
        
        $stats['total_duration'] += $duration;
    }

    public function getUsageStats(?string $toolName = null): array
    {
        if ($toolName) {
            return $this->usageStats[$toolName] ?? [];
        }
        
        return $this->usageStats;
    }

    private function enforceRateLimit(string $toolName): void
    {
        $limit = &$this->rateLimits[$toolName];
        $now = time();
        
        // Remove old calls outside window
        $limit['calls'] = array_filter(
            $limit['calls'],
            fn($timestamp) => ($now - $timestamp) < $limit['window']
        );
        
        if (count($limit['calls']) >= $limit['max_calls']) {
            throw new \Exception(
                "Rate limit exceeded for tool: {$toolName}"
            );
        }
        
        $limit['calls'][] = $now;
    }

    public function listTools(): array
    {
        return array_map(
            fn($data) => [
                'name' => $data['tool']->getName(),
                'description' => $data['tool']->getDescription(),
                'registered_at' => $data['registered_at'],
            ],
            $this->tools
        );
    }
}

// Example usage
if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Please set ANTHROPIC_API_KEY environment variable\n");
}

echo "=== Tool Registry System ===\n\n";

// Initialize registry
$registry = new ToolRegistry();

// Define tools
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->parameter('expression', 'string', 'Math expression to evaluate')
    ->required('expression')
    ->handler(function (array $input) {
        $expression = $input['expression'];
        // Safe evaluation using regex validation
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $expression)) {
            throw new Exception('Invalid expression');
        }
        return eval("return {$expression};");
    });

$weatherTool = Tool::create('get_weather')
    ->description('Get weather information for a location')
    ->parameter('location', 'string', 'City name')
    ->required('location')
    ->handler(function (array $input) {
        // Mock implementation
        $temps = [
            'San Francisco' => 65,
            'New York' => 72,
            'London' => 58,
            'Tokyo' => 75,
        ];
        
        $location = $input['location'];
        $temp = $temps[$location] ?? 70;
        
        return [
            'location' => $location,
            'temperature' => $temp,
            'conditions' => 'Partly cloudy',
            'humidity' => 60,
        ];
    });

$databaseTool = Tool::create('query_database')
    ->description('Query the database')
    ->parameter('query', 'string', 'SQL query')
    ->required('query')
    ->handler(function (array $input) {
        // Mock implementation
        return [
            'rows' => [
                ['id' => 1, 'name' => 'Product A', 'price' => 29.99],
                ['id' => 2, 'name' => 'Product B', 'price' => 49.99],
            ],
            'count' => 2,
        ];
    });

// Register tools with different permissions
echo "📝 Registering tools...\n";

$registry->register($calculator, [], 100); // All agents, 100 calls/hour
$registry->register($weatherTool, ['general_agent', 'research_agent']); // Specific agents only
$registry->register($databaseTool, ['admin_agent'], 10); // Admin only, 10 calls/hour

echo "✅ Registered " . count($registry->listTools()) . " tools\n\n";

// List all tools
echo "📋 Available tools:\n";
foreach ($registry->listTools() as $tool) {
    echo "  • {$tool['name']}: {$tool['description']}\n";
}
echo "\n";

// Test tool access
echo "🔐 Testing tool access...\n\n";

// Test 1: General agent accessing calculator (should work)
try {
    $tool = $registry->getTool('calculate', 'general_agent');
    echo "✅ General agent can access calculator\n";
    
    $startTime = microtime(true);
    $result = $tool->execute(['expression' => '25 * 17 + 100']);
    $duration = microtime(true) - $startTime;
    
    $registry->trackUsage('calculate', true, $duration);
    echo "   Result: " . $result->getContent() . "\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 2: General agent accessing weather (should work)
try {
    $tool = $registry->getTool('get_weather', 'general_agent');
    echo "✅ General agent can access weather tool\n";
    
    $startTime = microtime(true);
    $result = $tool->execute(['location' => 'San Francisco']);
    $duration = microtime(true) - $startTime;
    
    $registry->trackUsage('get_weather', true, $duration);
    $weather = is_string($result->getContent()) ? json_decode($result->getContent(), true) : $result->getContent();
    echo "   Weather: {$weather['temperature']}°F, {$weather['conditions']}\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Test 3: General agent accessing database (should fail - permission denied)
try {
    $tool = $registry->getTool('query_database', 'general_agent');
    echo "✅ General agent can access database\n";
} catch (\Exception $e) {
    echo "🚫 General agent blocked from database: {$e->getMessage()}\n";
}

// Test 4: Admin agent accessing database (should work)
try {
    $tool = $registry->getTool('query_database', 'admin_agent');
    echo "✅ Admin agent can access database\n";
    
    $startTime = microtime(true);
    $result = $tool->execute(['query' => 'SELECT * FROM products']);
    $duration = microtime(true) - $startTime;
    
    $registry->trackUsage('query_database', true, $duration);
    $data = is_string($result->getContent()) ? json_decode($result->getContent(), true) : $result->getContent();
    echo "   Found {$data['count']} rows\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n";

// Test rate limiting
echo "⏱️  Testing rate limiting...\n";

try {
    // Calculator has 100 calls/hour limit, database has 10 calls/hour
    for ($i = 1; $i <= 12; $i++) {
        $tool = $registry->getTool('query_database', 'admin_agent');
        $tool->execute(['query' => "SELECT {$i}"]);
        $registry->trackUsage('query_database', true, 0.1);
        echo "  Call {$i}/12 succeeded\n";
    }
} catch (\Exception $e) {
    echo "  🚫 Rate limit hit: {$e->getMessage()}\n";
}

echo "\n";

// Display usage statistics
echo "📊 Usage Statistics:\n";
foreach ($registry->getUsageStats() as $toolName => $stats) {
    if ($stats['total_calls'] > 0) {
        $successRate = ($stats['successful_calls'] / $stats['total_calls']) * 100;
        $avgDuration = $stats['total_duration'] / $stats['total_calls'];
        
        echo "  {$toolName}:\n";
        echo "    Total calls: {$stats['total_calls']}\n";
        echo "    Success rate: " . number_format($successRate, 1) . "%\n";
        echo "    Avg duration: " . number_format($avgDuration * 1000, 2) . "ms\n";
    }
}

echo "\n✅ Tool Registry System demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Centralized tool catalog\n";
echo "   • Permission-based access control\n";
echo "   • Rate limiting per tool\n";
echo "   • Usage tracking and analytics\n";
echo "   • Schema validation\n";
