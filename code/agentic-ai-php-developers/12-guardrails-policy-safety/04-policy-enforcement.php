<?php

declare(strict_types=1);

/**
 * Policy Enforcement Layer
 * 
 * Demonstrates how to implement and enforce organizational policies
 * for agent behavior, including access control and usage limits.
 */

enum PolicyDecision: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
    case REQUIRE_APPROVAL = 'require_approval';
}

class Policy
{
    public readonly string $name;
    public readonly string $description;
    public readonly int $priority;
    private $evaluator;

    public function __construct(
        string $name,
        string $description,
        callable $evaluator,
        int $priority = 100
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->evaluator = $evaluator;
        $this->priority = $priority;
    }

    /**
     * Evaluate policy against context
     *
     * @param array<string, mixed> $context Evaluation context
     * @return array{decision: PolicyDecision, reason: string}
     */
    public function evaluate(array $context): array
    {
        return ($this->evaluator)($context);
    }
}

class PolicyEngine
{
    /** @var array<Policy> */
    private array $policies = [];

    /** @var array<string, int> Usage tracking */
    private array $usageTracking = [];

    public function __construct()
    {
        $this->initializeDefaultPolicies();
    }

    /**
     * Initialize default policies
     */
    private function initializeDefaultPolicies(): void
    {
        // Rate limiting policy
        $this->addPolicy(new Policy(
            name: 'rate_limit',
            description: 'Limit requests per user per hour',
            evaluator: function (array $context): array {
                $userId = $context['user_id'] ?? 'anonymous';
                $limit = $context['hourly_limit'] ?? 100;
                
                $key = "user:{$userId}:hour:" . date('YmdH');
                $count = $this->usageTracking[$key] ?? 0;

                if ($count >= $limit) {
                    return [
                        'decision' => PolicyDecision::DENY,
                        'reason' => "Rate limit exceeded: {$count}/{$limit} requests this hour"
                    ];
                }

                $this->usageTracking[$key] = $count + 1;

                return [
                    'decision' => PolicyDecision::ALLOW,
                    'reason' => "Within rate limit: {$count}/{$limit}"
                ];
            },
            priority: 10 // High priority
        ));

        // PII access policy
        $this->addPolicy(new Policy(
            name: 'pii_access',
            description: 'Control access to PII data',
            evaluator: function (array $context): array {
                $hasPII = $context['contains_pii'] ?? false;
                $userRole = $context['user_role'] ?? 'user';
                $allowedRoles = ['admin', 'compliance_officer'];

                if ($hasPII && !in_array($userRole, $allowedRoles)) {
                    return [
                        'decision' => PolicyDecision::DENY,
                        'reason' => "Role '{$userRole}' not authorized for PII access"
                    ];
                }

                return [
                    'decision' => PolicyDecision::ALLOW,
                    'reason' => 'PII access authorized'
                ];
            },
            priority: 20
        ));

        // Sensitive operation policy
        $this->addPolicy(new Policy(
            name: 'sensitive_operations',
            description: 'Require approval for sensitive operations',
            evaluator: function (array $context): array {
                $operation = $context['operation'] ?? '';
                $sensitiveOps = ['delete', 'update_billing', 'change_permissions'];

                if (in_array($operation, $sensitiveOps)) {
                    $hasApproval = $context['approval_token'] ?? false;

                    if (!$hasApproval) {
                        return [
                            'decision' => PolicyDecision::REQUIRE_APPROVAL,
                            'reason' => "Operation '{$operation}' requires approval"
                        ];
                    }
                }

                return [
                    'decision' => PolicyDecision::ALLOW,
                    'reason' => 'Operation approved or not sensitive'
                ];
            },
            priority: 30
        ));

        // Business hours policy
        $this->addPolicy(new Policy(
            name: 'business_hours',
            description: 'Restrict certain operations to business hours',
            evaluator: function (array $context): array {
                $requiresBusinessHours = $context['business_hours_only'] ?? false;

                if ($requiresBusinessHours) {
                    $hour = (int)date('G');
                    $dayOfWeek = (int)date('N'); // 1-7, Monday-Sunday

                    $isWeekday = $dayOfWeek <= 5;
                    $isBusinessHours = $hour >= 9 && $hour < 17;

                    if (!($isWeekday && $isBusinessHours)) {
                        return [
                            'decision' => PolicyDecision::DENY,
                            'reason' => 'Operation restricted to business hours (Mon-Fri, 9am-5pm)'
                        ];
                    }
                }

                return [
                    'decision' => PolicyDecision::ALLOW,
                    'reason' => 'Within business hours or not restricted'
                ];
            },
            priority: 40
        ));

        // Data residency policy
        $this->addPolicy(new Policy(
            name: 'data_residency',
            description: 'Enforce data residency requirements',
            evaluator: function (array $context): array {
                $userRegion = $context['user_region'] ?? 'US';
                $dataRegion = $context['data_region'] ?? 'US';
                $restrictedRegions = ['EU', 'UK'];

                if (in_array($userRegion, $restrictedRegions) && $dataRegion !== $userRegion) {
                    return [
                        'decision' => PolicyDecision::DENY,
                        'reason' => "Data residency violation: User in {$userRegion}, data in {$dataRegion}"
                    ];
                }

                return [
                    'decision' => PolicyDecision::ALLOW,
                    'reason' => 'Data residency requirements met'
                ];
            },
            priority: 15
        ));
    }

    /**
     * Add a policy
     */
    public function addPolicy(Policy $policy): void
    {
        $this->policies[] = $policy;
        $this->sortPolicies();
    }

    /**
     * Sort policies by priority (lower = higher priority)
     */
    private function sortPolicies(): void
    {
        usort($this->policies, fn($a, $b) => $a->priority <=> $b->priority);
    }

    /**
     * Evaluate all policies
     *
     * @param array<string, mixed> $context Evaluation context
     * @return array{allowed: bool, decision: PolicyDecision, violations: array, warnings: array}
     */
    public function evaluate(array $context): array
    {
        $violations = [];
        $warnings = [];
        $finalDecision = PolicyDecision::ALLOW;

        foreach ($this->policies as $policy) {
            $result = $policy->evaluate($context);
            $decision = $result['decision'];

            if ($decision === PolicyDecision::DENY) {
                $violations[] = [
                    'policy' => $policy->name,
                    'reason' => $result['reason']
                ];
                $finalDecision = PolicyDecision::DENY;
            } elseif ($decision === PolicyDecision::REQUIRE_APPROVAL) {
                if ($finalDecision !== PolicyDecision::DENY) {
                    $finalDecision = PolicyDecision::REQUIRE_APPROVAL;
                }
                $warnings[] = [
                    'policy' => $policy->name,
                    'reason' => $result['reason']
                ];
            }
        }

        return [
            'allowed' => $finalDecision === PolicyDecision::ALLOW,
            'decision' => $finalDecision,
            'violations' => $violations,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get policy by name
     */
    public function getPolicy(string $name): ?Policy
    {
        foreach ($this->policies as $policy) {
            if ($policy->name === $name) {
                return $policy;
            }
        }
        return null;
    }

    /**
     * Get all policies
     *
     * @return array<Policy>
     */
    public function getAllPolicies(): array
    {
        return $this->policies;
    }
}

// Example usage
echo "=== Policy Enforcement Demo ===\n\n";

$engine = new PolicyEngine();

// Test 1: Allowed operation
echo "Test 1: Allowed Operation\n";
$context1 = [
    'user_id' => 'user_123',
    'user_role' => 'user',
    'operation' => 'read',
    'hourly_limit' => 100,
];
$result1 = $engine->evaluate($context1);
echo "Context: " . json_encode($context1, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result1['allowed'] ? 'Yes' : 'No') . "\n";
echo "Decision: {$result1['decision']->value}\n\n";

// Test 2: Rate limit exceeded
echo "Test 2: Rate Limit Test\n";
$context2 = [
    'user_id' => 'user_456',
    'hourly_limit' => 3,
];

// Make multiple requests
for ($i = 1; $i <= 5; $i++) {
    $result = $engine->evaluate($context2);
    echo "Request {$i}: " . ($result['allowed'] ? 'Allowed' : 'Denied');
    if (!$result['allowed']) {
        echo " - " . $result['violations'][0]['reason'];
    }
    echo "\n";
}
echo "\n";

// Test 3: PII access violation
echo "Test 3: PII Access Violation\n";
$context3 = [
    'user_id' => 'user_789',
    'user_role' => 'user',
    'contains_pii' => true,
];
$result3 = $engine->evaluate($context3);
echo "Context: " . json_encode($context3, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result3['allowed'] ? 'Yes' : 'No') . "\n";
if (!empty($result3['violations'])) {
    echo "Violations:\n";
    foreach ($result3['violations'] as $violation) {
        echo "  - {$violation['policy']}: {$violation['reason']}\n";
    }
}
echo "\n";

// Test 4: PII access with proper role
echo "Test 4: PII Access with Admin Role\n";
$context4 = [
    'user_id' => 'admin_001',
    'user_role' => 'admin',
    'contains_pii' => true,
];
$result4 = $engine->evaluate($context4);
echo "Context: " . json_encode($context4, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result4['allowed'] ? 'Yes' : 'No') . "\n\n";

// Test 5: Sensitive operation requiring approval
echo "Test 5: Sensitive Operation Without Approval\n";
$context5 = [
    'user_id' => 'user_999',
    'operation' => 'delete',
];
$result5 = $engine->evaluate($context5);
echo "Context: " . json_encode($context5, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result5['allowed'] ? 'Yes' : 'No') . "\n";
echo "Decision: {$result5['decision']->value}\n";
if (!empty($result5['warnings'])) {
    echo "Warnings:\n";
    foreach ($result5['warnings'] as $warning) {
        echo "  - {$warning['policy']}: {$warning['reason']}\n";
    }
}
echo "\n";

// Test 6: Sensitive operation with approval
echo "Test 6: Sensitive Operation With Approval\n";
$context6 = [
    'user_id' => 'user_999',
    'operation' => 'delete',
    'approval_token' => true,
];
$result6 = $engine->evaluate($context6);
echo "Context: " . json_encode($context6, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result6['allowed'] ? 'Yes' : 'No') . "\n";
echo "Decision: {$result6['decision']->value}\n\n";

// Test 7: Data residency violation
echo "Test 7: Data Residency Violation\n";
$context7 = [
    'user_id' => 'eu_user_123',
    'user_region' => 'EU',
    'data_region' => 'US',
];
$result7 = $engine->evaluate($context7);
echo "Context: " . json_encode($context7, JSON_PRETTY_PRINT) . "\n";
echo "Allowed: " . ($result7['allowed'] ? 'Yes' : 'No') . "\n";
if (!empty($result7['violations'])) {
    echo "Violations:\n";
    foreach ($result7['violations'] as $violation) {
        echo "  - {$violation['policy']}: {$violation['reason']}\n";
    }
}
echo "\n";

// Test 8: List all policies
echo "Test 8: All Registered Policies\n";
$policies = $engine->getAllPolicies();
echo "Total Policies: " . count($policies) . "\n";
foreach ($policies as $policy) {
    echo "  - {$policy->name} (Priority: {$policy->priority}): {$policy->description}\n";
}

echo "\n✅ Policy enforcement complete!\n";
