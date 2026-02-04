<?php

declare(strict_types=1);

/**
 * 06 - Admin Management API
 * 
 * Administrative interface for platform management, configuration,
 * monitoring, and debugging. Essential for production operations.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


/**
 * Admin Management API
 */
class AdminManagementAPI
{
    private array $config = [];
    private array $auditLog = [];

    public function __construct(private mixed $agentHub, private mixed $toolRegistry, private mixed $monitoring)
    {
        $this->config = [
            'platform_name' => 'Agentic AI Platform',
            'version' => '1.0.0',
            'environment' => 'production',
            'max_concurrent_tasks' => 10,
            'default_timeout' => 30,
        ];
    }

    /**
     * Get platform health status
     */
    public function getHealth(): array
    {
        return [
            'status' => 'healthy',
            'timestamp' => time(),
            'version' => $this->config['version'],
            'environment' => $this->config['environment'],
            'components' => [
                'agent_hub' => $this->checkAgentHubHealth(),
                'tool_registry' => $this->checkToolRegistryHealth(),
                'monitoring' => $this->checkMonitoringHealth(),
            ],
        ];
    }

    /**
     * List all registered agents
     */
    public function listAgents(array $filters = []): array
    {
        $agents = $this->agentHub->listAgents();
        
        // Apply filters
        if (isset($filters['capability'])) {
            $agents = array_filter(
                $agents,
                fn($a) => in_array($filters['capability'], $a['capabilities'])
            );
        }
        
        if (isset($filters['status'])) {
            $agents = array_filter(
                $agents,
                fn($a) => $a['status'] === $filters['status']
            );
        }
        
        return array_values($agents);
    }

    /**
     * Get agent details including performance
     */
    public function getAgentDetails(string $agentId): array
    {
        $profiles = $this->agentHub->listAgents();
        
        if (!isset($profiles[$agentId])) {
            throw new \Exception("Agent not found: {$agentId}");
        }
        
        return [
            'profile' => $profiles[$agentId],
            'performance' => $this->agentHub->getPerformanceStats($agentId),
        ];
    }

    /**
     * Update agent status
     */
    public function setAgentStatus(string $agentId, string $status): array
    {
        $validStatuses = ['available', 'busy', 'maintenance', 'disabled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid status: {$status}");
        }
        
        $this->agentHub->setAgentStatus($agentId, $status);
        
        $this->audit('agent_status_changed', [
            'agent_id' => $agentId,
            'new_status' => $status,
        ]);
        
        return [
            'success' => true,
            'agent_id' => $agentId,
            'status' => $status,
        ];
    }

    /**
     * List all registered tools
     */
    public function listTools(): array
    {
        return $this->toolRegistry->listTools();
    }

    /**
     * Get tool usage statistics
     */
    public function getToolStats(?string $toolName = null): array
    {
        return $this->toolRegistry->getUsageStats($toolName);
    }

    /**
     * Get monitoring statistics
     */
    public function getMonitoringStats(int $lastNMinutes = 60): array
    {
        return $this->monitoring->getStats($lastNMinutes);
    }

    /**
     * Get recent alerts
     */
    public function getAlerts(?string $level = null, int $limit = 50): array
    {
        $alerts = $this->monitoring->getAlerts($level);
        return array_slice($alerts, -$limit);
    }

    /**
     * Get recent evaluations
     */
    public function getEvaluations(int $limit = 50): array
    {
        $evaluations = $this->monitoring->getEvaluations();
        return array_slice($evaluations, -$limit);
    }

    /**
     * Get platform configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update platform configuration
     */
    public function updateConfig(array $updates): array
    {
        $allowedKeys = [
            'max_concurrent_tasks',
            'default_timeout',
        ];
        
        foreach ($updates as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $this->config[$key] = $value;
                
                $this->audit('config_updated', [
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
        
        return $this->config;
    }

    /**
     * Export platform metrics
     */
    public function exportMetrics(string $format = 'json'): string
    {
        $data = [
            'timestamp' => time(),
            'agents' => $this->agentHub->listAgents(),
            'tools' => $this->toolRegistry->listTools(),
            'monitoring' => $this->monitoring->getStats(),
        ];
        
        return match($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'csv' => $this->arrayToCsv($data),
            default => throw new \Exception("Unsupported format: {$format}"),
        };
    }

    /**
     * Get audit log
     */
    public function getAuditLog(int $limit = 100): array
    {
        return array_slice($this->auditLog, -$limit);
    }

    /**
     * Create system snapshot for debugging
     */
    public function createSnapshot(): array
    {
        return [
            'timestamp' => time(),
            'platform' => [
                'name' => $this->config['platform_name'],
                'version' => $this->config['version'],
                'environment' => $this->config['environment'],
            ],
            'health' => $this->getHealth(),
            'agents' => $this->listAgents(),
            'tools' => $this->listTools(),
            'monitoring' => $this->getMonitoringStats(),
            'alerts' => $this->getAlerts(),
            'recent_audit' => $this->getAuditLog(50),
        ];
    }

    private function checkAgentHubHealth(): array
    {
        $agents = $this->agentHub->listAgents();
        $available = array_filter($agents, fn($a) => $a['status'] === 'available');
        
        return [
            'status' => count($available) > 0 ? 'healthy' : 'degraded',
            'total_agents' => count($agents),
            'available_agents' => count($available),
        ];
    }

    private function checkToolRegistryHealth(): array
    {
        $tools = $this->toolRegistry->listTools();
        
        return [
            'status' => count($tools) > 0 ? 'healthy' : 'degraded',
            'total_tools' => count($tools),
        ];
    }

    private function checkMonitoringHealth(): array
    {
        $stats = $this->monitoring->getStats();
        
        return [
            'status' => 'healthy',
            'total_evaluations' => $stats['total_evaluations'],
            'pass_rate' => $stats['pass_rate'],
        ];
    }

    private function audit(string $action, array $data): void
    {
        $this->auditLog[] = [
            'timestamp' => time(),
            'action' => $action,
            'data' => $data,
            'user' => 'system', // In production, use actual user ID
        ];
    }

    private function arrayToCsv(array $data): string
    {
        // Simple CSV conversion (in production, use a proper library)
        return json_encode($data);
    }
}

// Example usage
echo "=== Admin Management API ===\n\n";

// Mock dependencies
$mockAgentHub = new class {
    public function listAgents() {
        return [
            'agent_1' => ['id' => 'agent_1', 'capabilities' => ['code'], 'status' => 'available'],
            'agent_2' => ['id' => 'agent_2', 'capabilities' => ['research'], 'status' => 'available'],
        ];
    }
    
    public function getPerformanceStats(string $id) {
        return [
            'tasks_completed' => 10,
            'tasks_failed' => 1,
            'avg_latency' => 2.5,
            'total_cost' => 0.05,
        ];
    }
    
    public function setAgentStatus(string $id, string $status) {
        // Mock implementation
    }
};

$mockToolRegistry = new class {
    public function listTools() {
        return [
            ['name' => 'calculator', 'description' => 'Math tool'],
            ['name' => 'search', 'description' => 'Web search'],
        ];
    }
    
    public function getUsageStats(?string $name = null) {
        return [
            'calculator' => ['total_calls' => 50, 'successful_calls' => 48],
            'search' => ['total_calls' => 30, 'successful_calls' => 29],
        ];
    }
};

$mockMonitoring = new class {
    public function getStats(int $minutes = 60) {
        return [
            'total_evaluations' => 45,
            'avg_scores' => [
                'quality' => 0.85,
                'safety' => 0.95,
                'cost' => 0.80,
                'performance' => 0.90,
            ],
            'pass_rate' => 0.93,
            'alert_count' => 2,
        ];
    }
    
    public function getAlerts(?string $level = null) {
        return [
            ['level' => 'warning', 'message' => 'High latency detected'],
            ['level' => 'info', 'message' => 'Agent rotated successfully'],
        ];
    }
    
    public function getEvaluations() {
        return [
            ['execution_id' => 'exec_1', 'overall' => 0.90, 'passed' => true],
            ['execution_id' => 'exec_2', 'overall' => 0.75, 'passed' => true],
        ];
    }
};

$admin = new AdminManagementAPI($mockAgentHub, $mockToolRegistry, $mockMonitoring);

// Test various admin operations
echo "🏥 Platform Health Check:\n";
$health = $admin->getHealth();
echo "  Status: {$health['status']}\n";
echo "  Version: {$health['version']}\n";
echo "  Environment: {$health['environment']}\n";
echo "  Components:\n";
foreach ($health['components'] as $component => $status) {
    echo "    {$component}: {$status['status']}\n";
}
echo "\n";

echo "🤖 Registered Agents:\n";
$agents = $admin->listAgents();
foreach ($agents as $agent) {
    echo "  • {$agent['id']} - Status: {$agent['status']}\n";
    echo "    Capabilities: " . implode(', ', $agent['capabilities']) . "\n";
}
echo "\n";

echo "📊 Agent Performance:\n";
foreach ($agents as $agent) {
    $details = $admin->getAgentDetails($agent['id']);
    $perf = $details['performance'];
    
    echo "  {$agent['id']}:\n";
    echo "    Completed: {$perf['tasks_completed']}\n";
    echo "    Failed: {$perf['tasks_failed']}\n";
    echo "    Avg Latency: {$perf['avg_latency']}s\n";
    echo "    Total Cost: \${$perf['total_cost']}\n";
}
echo "\n";

echo "🛠️ Registered Tools:\n";
$tools = $admin->listTools();
foreach ($tools as $tool) {
    echo "  • {$tool['name']}: {$tool['description']}\n";
}
echo "\n";

echo "📈 Tool Usage Statistics:\n";
$toolStats = $admin->getToolStats();
foreach ($toolStats as $toolName => $stats) {
    $successRate = ($stats['successful_calls'] / $stats['total_calls']) * 100;
    echo "  {$toolName}:\n";
    echo "    Total calls: {$stats['total_calls']}\n";
    echo "    Success rate: " . number_format($successRate, 1) . "%\n";
}
echo "\n";

echo "🔍 Monitoring Statistics:\n";
$monStats = $admin->getMonitoringStats();
echo "  Total evaluations: {$monStats['total_evaluations']}\n";
echo "  Pass rate: " . number_format($monStats['pass_rate'] * 100, 1) . "%\n";
echo "  Average scores:\n";
foreach ($monStats['avg_scores'] as $dimension => $score) {
    echo "    " . ucfirst($dimension) . ": " . number_format($score, 2) . "\n";
}
echo "\n";

echo "🚨 Recent Alerts:\n";
$alerts = $admin->getAlerts(limit: 10);
foreach ($alerts as $alert) {
    echo "  [{$alert['level']}] {$alert['message']}\n";
}
echo "\n";

echo "⚙️ Platform Configuration:\n";
$config = $admin->getConfig();
foreach ($config as $key => $value) {
    echo "  {$key}: {$value}\n";
}
echo "\n";

echo "🔧 Updating Configuration:\n";
$admin->updateConfig([
    'max_concurrent_tasks' => 20,
    'default_timeout' => 45,
]);
echo "  ✅ Configuration updated\n\n";

echo "📸 Creating System Snapshot:\n";
$snapshot = $admin->createSnapshot();
echo "  ✅ Snapshot created with " . count($snapshot) . " sections\n";
echo "  Timestamp: " . date('Y-m-d H:i:s', $snapshot['timestamp']) . "\n\n";

echo "📜 Audit Log (last 5 entries):\n";
$auditLog = $admin->getAuditLog(5);
foreach ($auditLog as $entry) {
    echo "  [" . date('Y-m-d H:i:s', $entry['timestamp']) . "] ";
    echo "{$entry['action']} by {$entry['user']}\n";
}
echo "\n";

echo "✅ Admin Management API demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Health monitoring\n";
echo "   • Agent management\n";
echo "   • Tool statistics\n";
echo "   • Configuration management\n";
echo "   • Audit logging\n";
echo "   • System snapshots\n";
echo "\n💡 Production enhancements:\n";
echo "   • Add authentication and authorization\n";
echo "   • Implement role-based access control (RBAC)\n";
echo "   • Add API rate limiting\n";
echo "   • Build web UI dashboard\n";
echo "   • Add webhook notifications\n";
echo "   • Implement backup/restore\n";
