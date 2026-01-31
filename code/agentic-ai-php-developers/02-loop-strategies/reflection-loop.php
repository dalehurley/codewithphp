<?php

/**
 * ReflectionLoop Example - Self-Critique and Refinement
 * 
 * Demonstrates quality-focused execution where the agent:
 * 1. Generates initial output
 * 2. Reflects and critiques the output
 * 3. Refines based on self-identified issues
 * 4. Repeats until quality criteria are met
 * 
 * Use case: Code generation, technical writing, quality-critical tasks
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define code validation and testing tools
$checkPhpSyntax = Tool::create('check_php_syntax')
    ->description('Validate PHP code syntax and detect parse errors')
    ->parameter('code', 'string', 'PHP code to validate')
    ->required('code')
    ->handler(function (array $input): string {
        echo "  🔍 Checking PHP syntax...\n";
        
        // Simulate syntax checking
        $hasPhpTag = str_contains($input['code'], '<?php');
        $hasParenMismatch = substr_count($input['code'], '(') !== substr_count($input['code'], ')');
        $hasBraceMismatch = substr_count($input['code'], '{') !== substr_count($input['code'], '}');
        
        $errors = [];
        if (!$hasPhpTag) {
            $errors[] = 'Missing PHP opening tag <?php';
        }
        if ($hasParenMismatch) {
            $errors[] = 'Mismatched parentheses';
        }
        if ($hasBraceMismatch) {
            $errors[] = 'Mismatched braces';
        }
        
        return json_encode([
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => []
        ]);
    });

$checkPsr12Compliance = Tool::create('check_psr12_compliance')
    ->description('Check if PHP code follows PSR-12 coding standards')
    ->parameter('code', 'string', 'PHP code to check')
    ->required('code')
    ->handler(function (array $input): string {
        echo "  📏 Checking PSR-12 compliance...\n";
        
        // Simulate PSR-12 checking
        $issues = [];
        
        if (!preg_match('/declare\(strict_types=1\);/', $input['code'])) {
            $issues[] = 'Missing strict_types declaration';
        }
        
        if (preg_match('/\t/', $input['code'])) {
            $issues[] = 'Uses tabs instead of spaces';
        }
        
        if (!preg_match('/@return\s+\w+/', $input['code'])) {
            $issues[] = 'Missing @return type documentation';
        }
        
        return json_encode([
            'compliant' => empty($issues),
            'issues' => $issues,
            'severity' => empty($issues) ? 'none' : 'minor'
        ]);
    });

$runUnitTests = Tool::create('run_unit_tests')
    ->description('Execute unit tests for PHP code')
    ->parameter('code', 'string', 'Code containing test methods')
    ->required('code')
    ->handler(function (array $input): string {
        echo "  🧪 Running unit tests...\n";
        sleep(1); // Simulate test execution
        
        // Simulate test results
        $hasTestMethods = str_contains($input['code'], 'test');
        $hasAssertions = str_contains($input['code'], 'assert');
        
        if (!$hasTestMethods) {
            return json_encode([
                'passed' => false,
                'total' => 0,
                'failures' => [],
                'message' => 'No test methods found'
            ]);
        }
        
        $passed = rand(0, 100) > 20; // 80% success rate
        
        return json_encode([
            'passed' => $passed,
            'total' => rand(5, 15),
            'failures' => $passed ? [] : ['test_edge_case failed: expected true, got false'],
            'coverage' => rand(70, 100) . '%'
        ]);
    });

$checkDocumentation = Tool::create('check_documentation')
    ->description('Validate docblock comments and documentation quality')
    ->parameter('code', 'string', 'PHP code to check for documentation')
    ->required('code')
    ->handler(function (array $input): string {
        echo "  📚 Checking documentation...\n";
        
        $hasClassDoc = preg_match('/\/\*\*[\s\S]*?\*\/\s*class/', $input['code']);
        $hasMethodDoc = preg_match('/\/\*\*[\s\S]*?\*\/\s*public function/', $input['code']);
        $hasParamDoc = preg_match('/@param\s+\w+/', $input['code']);
        
        $missing = [];
        if (!$hasClassDoc) $missing[] = 'Class docblock';
        if (!$hasMethodDoc) $missing[] = 'Method docblocks';
        if (!$hasParamDoc) $missing[] = '@param annotations';
        
        return json_encode([
            'complete' => empty($missing),
            'missing' => $missing,
            'score' => empty($missing) ? 100 : (100 - (count($missing) * 20))
        ]);
    });

// Create agent with ReflectionLoop
$reflectionIteration = 0;

$agent = Agent::create($client)
    ->withSystemPrompt(
        'You are an expert PHP developer focused on writing clean, tested, standards-compliant code. ' .
        'Generate code that:\n' .
        '  - Follows PSR-12 coding standards\n' .
        '  - Includes comprehensive docblock comments\n' .
        '  - Has proper type declarations\n' .
        '  - Includes unit tests with good coverage\n\n' .
        'When reviewing your code, critique it harshly and identify specific improvements.'
    )
    ->withTools([$checkPhpSyntax, $checkPsr12Compliance, $runUnitTests, $checkDocumentation])
    ->withLoopStrategy(new ReflectionLoop(
        maxReflections: 3,
        reflectionPrompt: 
            'Review the generated code for:\n' .
            '1. Syntax errors or parse issues\n' .
            '2. PSR-12 compliance violations\n' .
            '3. Missing or incomplete documentation\n' .
            '4. Test coverage and edge cases\n\n' .
            'List specific issues, then provide an improved version.',
        qualityCriteria: [
            'syntax_valid',
            'psr12_compliant',
            'well_documented',
            'has_tests'
        ],
        stopOnQuality: true
    ))
    ->maxIterations(12)
    ->onReflection(function (array $reflection) use (&$reflectionIteration) {
        $reflectionIteration++;
        echo "\n" . str_repeat('─', 80) . "\n";
        echo "🔍 Reflection Pass #{$reflectionIteration}\n";
        echo str_repeat('─', 80) . "\n";
        echo "Critique: {$reflection['critique']}\n";
        
        if (!empty($reflection['issues'])) {
            echo "\nIssues identified:\n";
            foreach ($reflection['issues'] as $issue) {
                echo "  ❌ {$issue}\n";
            }
        }
        
        echo "\n";
    })
    ->onUpdate(function ($update) {
        if ($update->getType() === 'generation') {
            echo "✏️  Generating code...\n";
        } elseif ($update->getType() === 'refinement') {
            echo "🔧 Refining code based on feedback...\n";
        }
    });

// Task: Generate a validated, tested email validator class
$task = "Create a PHP class called EmailValidator that:\n" .
        "1. Validates email addresses using multiple strategies (syntax, DNS check, disposable detection)\n" .
        "2. Follows PSR-12 coding standards strictly\n" .
        "3. Includes comprehensive docblock comments\n" .
        "4. Has complete unit tests with edge case coverage\n" .
        "5. Uses proper type declarations and error handling\n\n" .
        "Make it production-ready.";

echo str_repeat('=', 80) . "\n";
echo "Quality-Critical Task (ReflectionLoop)\n";
echo str_repeat('=', 80) . "\n\n";
echo "Task: Generate a production-ready EmailValidator class\n\n";

try {
    $startTime = microtime(true);
    $result = $agent->run($task);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "📊 Final Output:\n";
    echo str_repeat('=', 80) . "\n";
    echo $result->getAnswer() . "\n";
    echo str_repeat('=', 80) . "\n";
    
    // Show reflection statistics
    $metadata = $result->getMetadata();
    echo "\n📈 Reflection Statistics:\n";
    echo "  - Total duration: {$duration}ms\n";
    echo "  - Reflection passes: " . ($metadata['reflections'] ?? 0) . "\n";
    echo "  - Quality checks: " . ($metadata['quality_checks'] ?? 0) . "\n";
    echo "  - Tool calls: " . count($result->getToolCalls()) . "\n";
    echo "  - Refinements made: " . ($metadata['refinements'] ?? 0) . "\n";
    
    if (isset($metadata['quality_score'])) {
        echo "  - Final quality score: {$metadata['quality_score']}/100\n";
    }
    
    // Show improvement trajectory
    if (isset($metadata['reflection_history'])) {
        echo "\n📉 Quality Improvement Trajectory:\n";
        foreach ($metadata['reflection_history'] as $i => $reflection) {
            $passNum = $i + 1;
            $issueCount = count($reflection['issues'] ?? []);
            echo "  Pass #{$passNum}: {$issueCount} issues found\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n✅ ReflectionLoop example completed\n";
echo "\nKey takeaways:\n";
echo "  - ReflectionLoop iteratively improves output quality\n";
echo "  - Self-critique identifies issues without human review\n";
echo "  - Ideal for code generation and quality-critical tasks\n";
echo "  - Higher token usage (2-3x) but better final quality\n";
echo "  - Can validate against explicit quality criteria\n";
