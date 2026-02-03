<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 6: Contract-Driven Collaboration with Structured Outputs
 *
 * Demonstrates defining contracts between agents to ensure structured,
 * type-safe communication with validation.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{Message, SimpleCollaborativeAgent};
use ClaudePhp\ClaudePhp;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "=== Contract-Driven Collaboration Demo ===\n\n";

// ============================================================================
// Agent Contract Definitions
// ============================================================================

/**
 * Research Agent Contract
 * Input: research_request with topic and depth
 * Output: research_result with findings array and sources
 */
class ResearchContract
{
    public static function validateRequest(array $data): bool
    {
        return isset($data['topic']) && isset($data['depth']) && 
               in_array($data['depth'], ['basic', 'detailed', 'comprehensive']);
    }
    
    public static function validateResponse(array $data): bool
    {
        return isset($data['findings']) && is_array($data['findings']) &&
               isset($data['sources']) && is_array($data['sources']) &&
               isset($data['confidence_score']);
    }
    
    public static function formatRequest(string $topic, string $depth): array
    {
        return [
            'type' => 'research_request',
            'topic' => $topic,
            'depth' => $depth,
            'timestamp' => time(),
        ];
    }
    
    public static function formatResponse(array $findings, array $sources, float $confidence): array
    {
        return [
            'type' => 'research_result',
            'findings' => $findings,
            'sources' => $sources,
            'confidence_score' => $confidence,
            'timestamp' => time(),
        ];
    }
}

/**
 * Analysis Agent Contract
 * Input: analysis_request with data and method
 * Output: analysis_result with insights and metrics
 */
class AnalysisContract
{
    public static function validateRequest(array $data): bool
    {
        return isset($data['data']) && isset($data['method']);
    }
    
    public static function validateResponse(array $data): bool
    {
        return isset($data['insights']) && is_array($data['insights']) &&
               isset($data['metrics']) && is_array($data['metrics']);
    }
    
    public static function formatRequest(array $data, string $method): array
    {
        return [
            'type' => 'analysis_request',
            'data' => $data,
            'method' => $method,
            'timestamp' => time(),
        ];
    }
    
    public static function formatResponse(array $insights, array $metrics): array
    {
        return [
            'type' => 'analysis_result',
            'insights' => $insights,
            'metrics' => $metrics,
            'timestamp' => time(),
        ];
    }
}

/**
 * Report Agent Contract
 * Input: report_request with content and format
 * Output: report_result with formatted output
 */
class ReportContract
{
    public static function validateRequest(array $data): bool
    {
        return isset($data['content']) && isset($data['format']) &&
               in_array($data['format'], ['executive', 'technical', 'markdown']);
    }
    
    public static function validateResponse(array $data): bool
    {
        return isset($data['formatted_output']) && 
               isset($data['word_count']) &&
               isset($data['readability_score']);
    }
    
    public static function formatRequest(array $content, string $format): array
    {
        return [
            'type' => 'report_request',
            'content' => $content,
            'format' => $format,
            'timestamp' => time(),
        ];
    }
    
    public static function formatResponse(string $output, int $wordCount, float $readability): array
    {
        return [
            'type' => 'report_result',
            'formatted_output' => $output,
            'word_count' => $wordCount,
            'readability_score' => $readability,
            'timestamp' => time(),
        ];
    }
}

// ============================================================================
// Example 1: Structured Request/Response
// ============================================================================

echo "--- Example 1: Structured Communication ---\n\n";

$researcher = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'researcher',
    capabilities: ['research'],
    options: [
        'name' => 'Researcher',
        'system_prompt' => 'You are a researcher. Provide accurate findings with sources.',
    ]
);

// Create structured research request
$researchRequest = ResearchContract::formatRequest(
    topic: 'Benefits of microservices architecture',
    depth: 'detailed'
);

echo "Research Request (structured):\n";
echo json_encode($researchRequest, JSON_PRETTY_PRINT) . "\n\n";

// Validate request
$isValid = ResearchContract::validateRequest($researchRequest);
echo "Request validation: " . ($isValid ? '✓ Valid' : '✗ Invalid') . "\n\n";

// Simulate researcher processing (in real system, researcher would process and return structured data)
echo "Researcher processing...\n\n";

// Create structured response
$researchResponse = ResearchContract::formatResponse(
    findings: [
        'Microservices enable independent scaling of components',
        'Teams can use different technologies per service',
        'Deployment is more flexible with smaller units',
    ],
    sources: [
        'Martin Fowler - Microservices Architecture',
        'AWS Well-Architected Framework',
        'Google Cloud Architecture Center',
    ],
    confidence: 0.92
);

echo "Research Response (structured):\n";
echo json_encode($researchResponse, JSON_PRETTY_PRINT) . "\n\n";

// Validate response
$responseValid = ResearchContract::validateResponse($researchResponse);
echo "Response validation: " . ($responseValid ? '✓ Valid' : '✗ Invalid') . "\n\n";

// ============================================================================
// Example 2: Contract Chain
// ============================================================================

echo "--- Example 2: Multi-Agent Contract Chain ---\n\n";

echo "Flow: Research → Analysis → Report\n\n";

// Step 1: Research contract
echo "Step 1: Research Contract\n";
$researchData = [
    'findings' => [
        'Remote work increased by 300% in 2020-2023',
        'Employee satisfaction up 25% with hybrid models',
        'Productivity metrics show mixed results',
    ],
    'sources' => ['Gallup', 'McKinsey', 'Harvard Business Review'],
    'confidence_score' => 0.88,
];

echo "  Research completed with " . count($researchData['findings']) . " findings\n";
echo "  Confidence: " . ($researchData['confidence_score'] * 100) . "%\n\n";

// Step 2: Analysis contract
echo "Step 2: Analysis Contract\n";
$analysisRequest = AnalysisContract::formatRequest(
    data: $researchData,
    method: 'trend_analysis'
);

echo "  Analysis request created\n";
echo "  Validation: " . (AnalysisContract::validateRequest($analysisRequest) ? '✓' : '✗') . "\n\n";

$analysisResponse = AnalysisContract::formatResponse(
    insights: [
        'Clear trend toward remote/hybrid work adoption',
        'Employee preference drives business decisions',
        'Need for productivity measurement tools',
    ],
    metrics: [
        'growth_rate' => 3.0,
        'satisfaction_increase' => 0.25,
        'adoption_score' => 0.75,
    ]
);

echo "  Analysis completed\n";
echo "  Insights: " . count($analysisResponse['insights']) . "\n";
echo "  Metrics: " . count($analysisResponse['metrics']) . "\n";
echo "  Validation: " . (AnalysisContract::validateResponse($analysisResponse) ? '✓' : '✗') . "\n\n";

// Step 3: Report contract
echo "Step 3: Report Contract\n";
$reportRequest = ReportContract::formatRequest(
    content: [
        'research' => $researchData,
        'analysis' => $analysisResponse,
    ],
    format: 'executive'
);

echo "  Report request created\n";
echo "  Format: executive summary\n";
echo "  Validation: " . (ReportContract::validateRequest($reportRequest) ? '✓' : '✗') . "\n\n";

// ============================================================================
// Example 3: Message Contracts with Metadata
// ============================================================================

echo "--- Example 3: Message-Level Contracts ---\n\n";

// Define message schema
$messageSchema = [
    'required_fields' => ['from', 'to', 'content', 'type', 'contract_version'],
    'optional_fields' => ['metadata', 'reply_to', 'expires_at'],
    'valid_types' => ['request', 'response', 'notification', 'error'],
];

echo "Message Schema:\n";
echo "  Required: " . implode(', ', $messageSchema['required_fields']) . "\n";
echo "  Optional: " . implode(', ', $messageSchema['optional_fields']) . "\n";
echo "  Valid types: " . implode(', ', $messageSchema['valid_types']) . "\n\n";

// Create contract-compliant message
$contractMessage = [
    'from' => 'analyst',
    'to' => 'reporter',
    'content' => json_encode($analysisResponse),
    'type' => 'response',
    'contract_version' => '1.0',
    'metadata' => [
        'contract_type' => 'AnalysisContract',
        'validated' => true,
        'schema_version' => '1.0',
    ],
];

echo "Contract-compliant message:\n";

// Validate message
$valid = true;
foreach ($messageSchema['required_fields'] as $field) {
    if (!isset($contractMessage[$field])) {
        $valid = false;
        echo "  ✗ Missing required field: {$field}\n";
    } else {
        echo "  ✓ {$field}: present\n";
    }
}

if ($valid && !in_array($contractMessage['type'], $messageSchema['valid_types'])) {
    $valid = false;
    echo "  ✗ Invalid message type\n";
}

echo "\nOverall validation: " . ($valid ? '✓ Valid' : '✗ Invalid') . "\n\n";

// ============================================================================
// Example 4: Error Handling with Contracts
// ============================================================================

echo "--- Example 4: Contract Violation Handling ---\n\n";

// Invalid research request (missing required field)
$invalidRequest = [
    'type' => 'research_request',
    'topic' => 'AI trends',
    // Missing 'depth' field
];

echo "Testing invalid request:\n";
$valid = ResearchContract::validateRequest($invalidRequest);
echo "  Validation result: " . ($valid ? 'Valid' : 'Invalid') . "\n";

if (!$valid) {
    echo "  Error: Request does not meet ResearchContract requirements\n";
    echo "  Required fields: topic, depth\n";
    echo "  Provided fields: " . implode(', ', array_keys($invalidRequest)) . "\n";
    
    // Create error response
    $errorResponse = [
        'type' => 'error',
        'error_code' => 'CONTRACT_VIOLATION',
        'message' => 'Request does not meet contract requirements',
        'details' => [
            'contract' => 'ResearchContract',
            'missing_fields' => ['depth'],
            'valid_values' => ['depth' => ['basic', 'detailed', 'comprehensive']],
        ],
    ];
    
    echo "\n  Error response:\n";
    echo json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n\n";
}

// ============================================================================
// Example 5: Contract Versioning
// ============================================================================

echo "--- Example 5: Contract Versioning ---\n\n";

// V1 contract
$contractV1 = [
    'version' => '1.0',
    'name' => 'DataAnalysisContract',
    'fields' => ['data', 'method'],
];

// V2 contract (with additional fields)
$contractV2 = [
    'version' => '2.0',
    'name' => 'DataAnalysisContract',
    'fields' => ['data', 'method', 'options', 'callback'],
    'backward_compatible' => true,
];

echo "Contract Evolution:\n";
echo "  V1.0: " . count($contractV1['fields']) . " fields\n";
echo "  V2.0: " . count($contractV2['fields']) . " fields\n";
echo "  Backward compatible: " . ($contractV2['backward_compatible'] ? 'Yes' : 'No') . "\n\n";

// V1 request (should work with V2 agent if backward compatible)
$v1Request = [
    'contract_version' => '1.0',
    'data' => ['values' => [1, 2, 3]],
    'method' => 'average',
];

echo "Sending V1 request to V2 agent:\n";
echo "  Compatible: " . ($contractV2['backward_compatible'] ? 'Yes' : 'No') . "\n";
echo "  Status: Request will be processed\n\n";

// ============================================================================
// Example 6: Production Contract System
// ============================================================================

echo "--- Example 6: Production Contract Registry ---\n\n";

class ContractRegistry
{
    private array $contracts = [];
    
    public function register(string $name, string $version, array $schema): void
    {
        $this->contracts["{$name}:{$version}"] = $schema;
    }
    
    public function validate(string $name, string $version, array $data): array
    {
        $key = "{$name}:{$version}";
        if (!isset($this->contracts[$key])) {
            return ['valid' => false, 'error' => 'Contract not found'];
        }
        
        $schema = $this->contracts[$key];
        $errors = [];
        
        foreach ($schema['required'] ?? [] as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    public function getContract(string $name, string $version): ?array
    {
        return $this->contracts["{$name}:{$version}"] ?? null;
    }
}

$registry = new ContractRegistry();

// Register contracts
$registry->register('Research', '1.0', [
    'required' => ['topic', 'depth'],
    'optional' => ['deadline', 'sources_limit'],
]);

$registry->register('Analysis', '1.0', [
    'required' => ['data', 'method'],
    'optional' => ['options'],
]);

echo "Contract Registry:\n";
echo "  Registered: Research:1.0, Analysis:1.0\n\n";

// Validate data against registered contracts
$testData = ['topic' => 'AI trends', 'depth' => 'detailed'];
$validation = $registry->validate('Research', '1.0', $testData);

echo "Validation test:\n";
echo "  Contract: Research:1.0\n";
echo "  Data: " . json_encode($testData) . "\n";
echo "  Result: " . ($validation['valid'] ? '✓ Valid' : '✗ Invalid') . "\n\n";

echo "=== Demo Complete ===\n\n";

echo "Contract-Driven Benefits:\n";
echo "• Type-safe communication between agents\n";
echo "• Clear expectations and interfaces\n";
echo "• Validation catches errors early\n";
echo "• Enables contract versioning and evolution\n";
echo "• Better debugging and error messages\n";
echo "• Documentation through code\n";
echo "• Facilitates testing and mocking\n";
