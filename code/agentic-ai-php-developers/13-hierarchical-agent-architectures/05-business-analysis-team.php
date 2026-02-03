#!/usr/bin/env php
<?php
/**
 * Business Analysis Team
 * 
 * A strategic business analysis system using hierarchical agents.
 * Market analysts, financial experts, competitive intelligence, and
 * strategy consultants work together to evaluate business opportunities.
 * 
 * This example shows:
 * - Multi-domain business analysis
 * - Financial modeling and projections
 * - Competitive positioning analysis
 * - Strategic recommendations
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   Business Analysis Team                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Build Analysis Team
// ============================================================================

echo "Building business analysis team...\n\n";

// Market Analyst
$marketAnalyst = new WorkerAgent($client, [
    'name' => 'market_analyst',
    'specialty' => 'market analysis, industry trends, consumer behavior, TAM/SAM/SOM, and growth opportunities',
    'system' => 'You are a market analyst. Analyze:\n' .
                '- Market size and growth trends (TAM, SAM, SOM)\n' .
                '- Consumer behavior and preferences\n' .
                '- Industry dynamics and key players\n' .
                '- Market entry barriers and risks\n' .
                '- Growth opportunities and white space\n\n' .
                'Provide analysis with:\n' .
                '1. Market size estimates\n' .
                '2. Growth projections\n' .
                '3. Key trends and drivers\n' .
                '4. Opportunity assessment',
    'max_tokens' => 2000,
]);

echo "  ✓ Market Analyst configured\n";

// Financial Analyst
$financialAnalyst = new WorkerAgent($client, [
    'name' => 'financial_analyst',
    'specialty' => 'financial analysis, revenue projections, ROI calculations, cost modeling, and risk assessment',
    'system' => 'You are a financial analyst. Analyze:\n' .
                '- Revenue projections and growth rates\n' .
                '- Cost structure and margins\n' .
                '- ROI and payback period\n' .
                '- Break-even analysis\n' .
                '- Financial risks and mitigation\n' .
                '- Cash flow implications\n\n' .
                'Show all calculations clearly and provide:\n' .
                '1. Financial projections (3-5 years)\n' .
                '2. Key financial metrics\n' .
                '3. Risk assessment\n' .
                '4. Sensitivity analysis',
    'max_tokens' => 2000,
]);

echo "  ✓ Financial Analyst configured\n";

// Competitive Analyst
$competitiveAnalyst = new WorkerAgent($client, [
    'name' => 'competitive_analyst',
    'specialty' => 'competitive intelligence, market positioning, differentiation strategy, and SWOT analysis',
    'system' => 'You are a competitive intelligence analyst. Analyze:\n' .
                '- Competitor landscape and positioning\n' .
                '- Competitive advantages and weaknesses\n' .
                '- Differentiation opportunities\n' .
                '- Market share dynamics\n' .
                '- Competitive threats and responses\n\n' .
                'Provide:\n' .
                '1. Competitive landscape map\n' .
                '2. SWOT analysis\n' .
                '3. Differentiation strategy\n' .
                '4. Competitive positioning recommendations',
    'max_tokens' => 2000,
]);

echo "  ✓ Competitive Analyst configured\n";

// Strategy Consultant
$strategist = new WorkerAgent($client, [
    'name' => 'strategist',
    'specialty' => 'business strategy, strategic planning, go-to-market strategy, and implementation roadmaps',
    'system' => 'You are a strategy consultant. Synthesize analysis into actionable strategy:\n' .
                '- Clear go/no-go recommendation\n' .
                '- Strategic rationale based on evidence\n' .
                '- Prioritized action items\n' .
                '- Implementation roadmap\n' .
                '- Success metrics and KPIs\n' .
                '- Risk mitigation strategies\n\n' .
                'Provide:\n' .
                '1. Executive summary with recommendation\n' .
                '2. Strategic rationale\n' .
                '3. Implementation roadmap\n' .
                '4. Success criteria and metrics',
    'max_tokens' => 2000,
]);

echo "  ✓ Strategy Consultant configured\n\n";

// Create master coordinator
$businessStrategist = new HierarchicalAgent($client, [
    'name' => 'business_strategist',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);

$businessStrategist->registerWorker('market_analyst', $marketAnalyst);
$businessStrategist->registerWorker('financial_analyst', $financialAnalyst);
$businessStrategist->registerWorker('competitive_analyst', $competitiveAnalyst);
$businessStrategist->registerWorker('strategist', $strategist);

echo "Strategic analysis team ready with 4 specialists\n\n";

// ============================================================================
// Business Scenario
// ============================================================================

$scenario = [
    'question' => 'Should we expand our SaaS project management tool into the healthcare market?',
    'current_state' => [
        'arr' => '$2.5M',
        'customers' => 180,
        'sector' => 'technology startups and small businesses',
        'growth_rate' => '25% YoY',
        'team_size' => '15 employees',
    ],
    'opportunity' => [
        'target_sector' => 'healthcare providers (clinics, small hospitals)',
        'estimated_market' => '$500M TAM, $50M SAM',
        'key_requirements' => 'HIPAA compliance, medical workflow features, integration with EHR systems',
    ],
    'competitors' => [
        ['name' => 'HealthPM Pro', 'size' => '$50M ARR', 'strength' => 'Enterprise focus, deep EHR integrations'],
        ['name' => 'MedFlow', 'size' => '$25M ARR', 'strength' => 'HIPAA certified, medical-specific features'],
        ['name' => 'Generic PM tools', 'size' => 'Various', 'strength' => 'Lower price, general purpose'],
    ],
    'investment' => [
        'development_cost' => '$400K (HIPAA compliance, medical features)',
        'marketing_cost' => '$200K',
        'timeline' => '12 months to market',
        'ongoing_costs' => '$50K/month additional',
    ],
];

echo "Business Scenario:\n";
echo str_repeat("-", 80) . "\n";
echo "Question: {$scenario['question']}\n\n";

echo "Current State:\n";
foreach ($scenario['current_state'] as $key => $value) {
    echo "  • " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
}

echo "\nTarget Opportunity:\n";
foreach ($scenario['opportunity'] as $key => $value) {
    echo "  • " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
}

echo "\nKey Competitors:\n";
foreach ($scenario['competitors'] as $competitor) {
    echo "  • {$competitor['name']} ({$competitor['size']}) - {$competitor['strength']}\n";
}

echo "\nRequired Investment:\n";
foreach ($scenario['investment'] as $key => $value) {
    echo "  • " . ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
}

echo str_repeat("-", 80) . "\n\n";

// ============================================================================
// Execute Strategic Analysis
// ============================================================================

echo "Starting comprehensive business analysis...\n";
echo "This will take ~30-40 seconds as all analysts collaborate\n\n";

$task = <<<TASK
Evaluate this business expansion opportunity:

{$scenario['question']}

## Current Business
- Annual Recurring Revenue: {$scenario['current_state']['arr']}
- Customer Count: {$scenario['current_state']['customers']}
- Current Market: {$scenario['current_state']['sector']}
- Growth Rate: {$scenario['current_state']['growth_rate']}
- Team Size: {$scenario['current_state']['team_size']}

## Target Market
- Sector: {$scenario['opportunity']['target_sector']}
- Market Size: {$scenario['opportunity']['estimated_market']}
- Key Requirements: {$scenario['opportunity']['key_requirements']}

## Competitive Landscape
TASK;

foreach ($scenario['competitors'] as $i => $competitor) {
    $task .= "\n" . ($i + 1) . ". {$competitor['name']} - {$competitor['size']} ARR - {$competitor['strength']}";
}

$task .= <<<TASK


## Investment Required
- Development: {$scenario['investment']['development_cost']}
- Marketing: {$scenario['investment']['marketing_cost']}
- Timeline: {$scenario['investment']['timeline']}
- Ongoing Costs: {$scenario['investment']['ongoing_costs']}

Provide comprehensive analysis covering:
1. Market opportunity assessment
2. Financial projections and ROI analysis
3. Competitive positioning strategy
4. Strategic recommendation with clear go/no-go decision
TASK;

$startTime = microtime(true);

$result = $businessStrategist->run($task);

$duration = microtime(true) - $startTime;

// ============================================================================
// Display Strategic Analysis
// ============================================================================

if ($result->isSuccess()) {
    echo "✅ Strategic Analysis Complete!\n\n";
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                   STRATEGIC ANALYSIS REPORT                                ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo $result->getAnswer() . "\n\n";
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        ANALYSIS METADATA                                   ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    $metadata = $result->getMetadata();
    
    echo "👥 Analysis Team:\n";
    echo "  • Analysts consulted: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Individual analyses: {$metadata['subtasks']}\n";
    echo "  • Total workflow steps: {$result->getIterations()}\n\n";
    
    echo "⏱️ Performance:\n";
    echo "  • Analysis duration: " . round($duration, 2) . " seconds\n";
    echo "  • Average per analyst: " . round($duration / count($metadata['workers_used']), 2) . "s\n\n";
    
    echo "💰 Analysis Cost:\n";
    $usage = $result->getTokenUsage();
    echo "  • Total tokens: " . number_format($usage['total']) . "\n";
    
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    
    echo "  • Analysis cost: $" . number_format($totalCost, 4) . "\n";
    echo "  • Cost vs. consultant fees: ~$500-1000/hr human analysts\n\n";
    
} else {
    echo "❌ Strategic analysis failed: {$result->getError()}\n";
    exit(1);
}

// ============================================================================
// Decision Framework
// ============================================================================

echo str_repeat("═", 80) . "\n";
echo "Multi-Analyst Decision Framework\n";
echo str_repeat("═", 80) . "\n\n";

echo "✓ Each Analyst's Contribution:\n\n";

echo "  Market Analyst:\n";
echo "    • Assessed healthcare PM market size and growth\n";
echo "    • Identified target segment and opportunity\n";
echo "    • Evaluated market entry feasibility\n\n";

echo "  Financial Analyst:\n";
echo "    • Projected revenue and costs\n";
echo "    • Calculated ROI and break-even\n";
echo "    • Assessed financial risks\n\n";

echo "  Competitive Analyst:\n";
echo "    • Mapped competitive landscape\n";
echo "    • Identified differentiation opportunities\n";
echo "    • Recommended positioning strategy\n\n";

echo "  Strategy Consultant:\n";
echo "    • Synthesized all analyses\n";
echo "    • Provided go/no-go recommendation\n";
echo "    • Created implementation roadmap\n\n";

echo "✓ Advantages of Multi-Analyst Approach:\n";
echo "  • Comprehensive: All angles covered\n";
echo "  • Objective: Multiple perspectives reduce bias\n";
echo "  • Thorough: Deep expertise in each domain\n";
echo "  • Actionable: Clear strategic direction\n\n";

echo "✓ Use Cases:\n";
echo "  • New market expansion decisions\n";
echo "  • Acquisition opportunity evaluation\n";
echo "  • Product strategy planning\n";
echo "  • Competitive response planning\n";
echo "  • Investment decision support\n\n";

echo "✓ Cost Comparison:\n";
echo "  • AI Analysis: ~$" . number_format($totalCost, 2) . "\n";
echo "  • Human Consultants: $2,000-5,000 (4 specialists × 2-4 hours)\n";
echo "  • Time Saved: Days → Minutes\n\n";

echo "Example completed successfully!\n";
