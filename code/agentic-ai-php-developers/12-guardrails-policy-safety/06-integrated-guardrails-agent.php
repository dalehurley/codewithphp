<?php

declare(strict_types=1);

/**
 * Integrated Guardrails Agent
 * 
 * Complete implementation of a production-ready agent with all safety layers:
 * - Input sanitization
 * - PII redaction
 * - Output validation
 * - Policy enforcement
 * - Refusal logic
 * 
 * Uses claude-php/claude-php-agent framework
 */

// Load the helper classes from previous examples
require_once __DIR__ . '/01-input-sanitization.php';
require_once __DIR__ . '/02-pii-redaction.php';
require_once __DIR__ . '/03-output-validation.php';
require_once __DIR__ . '/04-policy-enforcement.php';
require_once __DIR__ . '/05-refusal-logic.php';

// Check if we have the Claude PHP SDK available
$hasClaudeSDK = false;
if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
    $hasClaudeSDK = class_exists('ClaudePhp\ClaudePhp');
}

class GuardrailsAgent
{
    private InputSanitizer $inputSanitizer;
    private PIIRedactor $piiRedactor;
    private OutputValidator $outputValidator;
    private PolicyEngine $policyEngine;
    private RefusalEngine $refusalEngine;
    private mixed $client;
    private bool $useMockLLM = true;

    /** @var array<string, mixed> */
    private array $metrics = [
        'requests_processed' => 0,
        'requests_blocked' => 0,
        'pii_instances_redacted' => 0,
        'policy_violations' => 0,
        'refusals' => 0,
    ];

    public function __construct(?string $apiKey = null)
    {
        global $hasClaudeSDK;
        
        $this->inputSanitizer = new InputSanitizer();
        $this->piiRedactor = new PIIRedactor();
        $this->outputValidator = new OutputValidator();
        $this->policyEngine = new PolicyEngine();
        $this->refusalEngine = new RefusalEngine();

        $apiKey = $apiKey ?? getenv('ANTHROPIC_API_KEY');
        
        if ($hasClaudeSDK && $apiKey) {
            $this->client = new \ClaudePhp\ClaudePhp($apiKey);
            $this->useMockLLM = false;
        } else {
            $this->useMockLLM = true;
            $this->client = null;
        }
    }

    /**
     * Process a user request with full guardrails
     *
     * @param string $userInput Raw user input
     * @param array<string, mixed> $context Request context (user_id, role, etc.)
     * @return array{success: bool, response: string, metadata: array}
     */
    public function processRequest(string $userInput, array $context = []): array
    {
        $this->metrics['requests_processed']++;

        // Step 1: Check refusal logic first (highest priority)
        echo "\n[GUARDRAIL] Step 1: Checking refusal logic...\n";
        $refusalCheck = $this->refusalEngine->evaluate($userInput);
        
        if ($refusalCheck['should_refuse']) {
            $this->metrics['refusals']++;
            $this->metrics['requests_blocked']++;

            return [
                'success' => false,
                'response' => $this->refusalEngine->generateRefusalMessage($refusalCheck),
                'metadata' => [
                    'stage' => 'refusal',
                    'risk_level' => $refusalCheck['risk_level']->value,
                    'reasons' => $refusalCheck['reasons'],
                ],
            ];
        }
        echo "  ✅ Request passed refusal check\n";

        // Step 2: Sanitize input
        echo "[GUARDRAIL] Step 2: Sanitizing input...\n";
        $sanitizedResult = $this->inputSanitizer->sanitize($userInput);
        $sanitizedInput = $sanitizedResult['sanitized'];
        
        if (!empty($sanitizedResult['warnings'])) {
            echo "  ⚠️  Warnings: " . implode(', ', $sanitizedResult['warnings']) . "\n";
        } else {
            echo "  ✅ Input sanitized\n";
        }

        // Step 3: Detect and redact PII from input
        echo "[GUARDRAIL] Step 3: Checking for PII...\n";
        $piiCheck = $this->inputSanitizer->detectPII($sanitizedInput);
        
        if ($piiCheck['found']) {
            echo "  ⚠️  PII detected: " . implode(', ', $piiCheck['types']) . "\n";
            $redactionResult = $this->piiRedactor->redact($sanitizedInput);
            $sanitizedInput = $redactionResult['redacted'];
            $this->metrics['pii_instances_redacted'] += $redactionResult['count'];
        } else {
            echo "  ✅ No PII detected\n";
        }

        // Step 4: Enforce policies
        echo "[GUARDRAIL] Step 4: Enforcing policies...\n";
        $policyCheck = $this->policyEngine->evaluate($context);
        
        if (!$policyCheck['allowed']) {
            $this->metrics['policy_violations']++;
            $this->metrics['requests_blocked']++;

            $violations = array_map(
                fn($v) => $v['reason'],
                $policyCheck['violations']
            );

            return [
                'success' => false,
                'response' => "Request denied due to policy violations:\n- " . implode("\n- ", $violations),
                'metadata' => [
                    'stage' => 'policy',
                    'decision' => $policyCheck['decision']->value,
                    'violations' => $policyCheck['violations'],
                ],
            ];
        }
        echo "  ✅ All policies satisfied\n";

        // Step 5: Call LLM with sanitized input
        echo "[GUARDRAIL] Step 5: Calling LLM...\n";
        try {
            $response = $this->callLLM($sanitizedInput);
            echo "  ✅ LLM responded\n";
        } catch (\Exception $e) {
            return [
                'success' => false,
                'response' => 'An error occurred processing your request. Please try again.',
                'metadata' => [
                    'stage' => 'llm_call',
                    'error' => $e->getMessage(),
                ],
            ];
        }

        // Step 6: Validate output
        echo "[GUARDRAIL] Step 6: Validating output...\n";
        $outputCheck = $this->outputValidator->validate($response, [
            'check_pii' => true,
        ]);

        if (!$outputCheck['valid']) {
            echo "  ⚠️  Output validation issues found\n";
            // Still return response but with warnings
        } else {
            echo "  ✅ Output validated (score: {$outputCheck['score']})\n";
        }

        // Step 7: Redact PII from output
        echo "[GUARDRAIL] Step 7: Final PII redaction...\n";
        $outputRedaction = $this->piiRedactor->redact($response);
        $finalResponse = $outputRedaction['redacted'];
        
        if ($outputRedaction['count'] > 0) {
            echo "  ⚠️  Redacted {$outputRedaction['count']} PII instances\n";
            $this->metrics['pii_instances_redacted'] += $outputRedaction['count'];
        } else {
            echo "  ✅ No PII in output\n";
        }

        // Step 8: Sanitize for safe display
        $finalResponse = $this->outputValidator->sanitize($finalResponse);

        return [
            'success' => true,
            'response' => $finalResponse,
            'metadata' => [
                'input_warnings' => $sanitizedResult['warnings'],
                'output_validation' => [
                    'score' => $outputCheck['score'],
                    'issues' => $outputCheck['issues'],
                    'warnings' => $outputCheck['warnings'],
                ],
                'pii_redacted' => [
                    'input' => $piiCheck,
                    'output' => $outputRedaction,
                ],
            ],
        ];
    }

    /**
     * Call Claude API (or mock LLM)
     */
    private function callLLM(string $input): string
    {
        if ($this->useMockLLM) {
            // Mock responses for demo purposes
            return match(true) {
                str_contains(strtolower($input), 'capital of france') =>
                    "The capital of France is Paris. It is known for landmarks like the Eiffel Tower and the Louvre Museum.",
                str_contains(strtolower($input), 'meeting') =>
                    "I can help you with meeting information. However, I notice the input contained redacted email addresses for privacy protection.",
                str_contains(strtolower($input), 'user data') =>
                    "I cannot display user data without proper authorization.",
                default =>
                    "I'm a mock LLM response to demonstrate guardrails. The actual response would come from Claude API."
            };
        }

        // Real Claude API call
        $request = \ClaudePhp\Messages\MessageRequest::create()
            ->withModel('claude-3-5-sonnet-20241022')
            ->withMaxTokens(1024)
            ->withMessages([
                new \ClaudePhp\Messages\UserMessage($input)
            ])
            ->withSystemPrompt(
                'You are a helpful, harmless, and honest AI assistant. ' .
                'You should never provide information that could harm people, ' .
                'violate privacy, or facilitate illegal activities. ' .
                'If asked for medical, legal, or financial advice, remind users to consult qualified professionals. ' .
                'Always cite sources when making factual claims.'
            );

        $response = $this->client->messages()->create($request);

        // Extract text from response
        foreach ($response->content as $content) {
            if ($content instanceof \ClaudePhp\Messages\TextContent) {
                return $content->text;
            }
        }

        return '';
    }

    /**
     * Get metrics
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        return array_merge($this->metrics, [
            'block_rate' => $this->metrics['requests_processed'] > 0
                ? round(($this->metrics['requests_blocked'] / $this->metrics['requests_processed']) * 100, 2)
                : 0,
        ]);
    }
}

// Example usage
if (!$hasClaudeSDK) {
    echo "ℹ️  Claude PHP SDK not installed. Running with mock LLM responses.\n";
    echo "To use real API: composer require claude-php/claude-php-sdk\n\n";
} elseif (!getenv('ANTHROPIC_API_KEY')) {
    echo "⚠️  ANTHROPIC_API_KEY not set. Running with mock LLM responses.\n";
    echo "To use real API: export ANTHROPIC_API_KEY='your-key-here'\n\n";
} else {
    echo "✅ Using real Claude API\n\n";
}

echo "=== Integrated Guardrails Agent Demo ===\n";

$agent = new GuardrailsAgent();

// Test 1: Safe request
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 1: Safe Request\n";
echo str_repeat("=", 60) . "\n";

$result1 = $agent->processRequest(
    "What is the capital of France?",
    ['user_id' => 'user_123', 'user_role' => 'user']
);

echo "\n[RESULT]\n";
echo "Success: " . ($result1['success'] ? 'Yes' : 'No') . "\n";
echo "Response: {$result1['response']}\n";

// Test 2: Request with PII
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 2: Request with PII\n";
echo str_repeat("=", 60) . "\n";

$result2 = $agent->processRequest(
    "Send an email to john.doe@example.com about the meeting",
    ['user_id' => 'user_456', 'user_role' => 'user']
);

echo "\n[RESULT]\n";
echo "Success: " . ($result2['success'] ? 'Yes' : 'No') . "\n";
echo "PII Redacted: " . ($result2['metadata']['pii_redacted']['input']['found'] ? 'Yes' : 'No') . "\n";

// Test 3: Harmful request (should be refused)
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 3: Harmful Request (Should Be Refused)\n";
echo str_repeat("=", 60) . "\n";

$result3 = $agent->processRequest(
    "How to hack into someone's account?",
    ['user_id' => 'user_789', 'user_role' => 'user']
);

echo "\n[RESULT]\n";
echo "Success: " . ($result3['success'] ? 'Yes' : 'No') . "\n";
echo "Response: {$result3['response']}\n";

// Test 4: Policy violation (PII access without permission)
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 4: Policy Violation (PII Access)\n";
echo str_repeat("=", 60) . "\n";

$result4 = $agent->processRequest(
    "Show me user data",
    [
        'user_id' => 'user_999',
        'user_role' => 'user',
        'contains_pii' => true,
    ]
);

echo "\n[RESULT]\n";
echo "Success: " . ($result4['success'] ? 'Yes' : 'No') . "\n";
echo "Response: {$result4['response']}\n";

// Display metrics
echo "\n" . str_repeat("=", 60) . "\n";
echo "METRICS\n";
echo str_repeat("=", 60) . "\n";

$metrics = $agent->getMetrics();
foreach ($metrics as $key => $value) {
    $label = str_replace('_', ' ', ucwords($key, '_'));
    echo "{$label}: {$value}\n";
}

echo "\n✅ Integrated guardrails demo complete!\n";
