---
title: "34: Prompt Chaining and Workflows"
description: "Master advanced prompt chaining and workflow orchestration. Build sophisticated AI pipelines with sequential processing, conditional branching, loops, error recovery, and stateful execution."
series: "claude-php-developers"
chapter: 34
order: 34
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 1-15"
  - "Understanding of workflow patterns"
  - "Knowledge of state machines"
  - "Experience with pipeline architecture"
---

![34: Prompt Chaining and Workflows](/images/claude-php/chapter-34-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 34</span>
</div>

# Chapter 34: Prompt Chaining and Workflows

## Overview

Prompt chaining connects multiple AI operations in sequence, where each step's output becomes the next step's input. This enables complex workflows that exceed what a single prompt can accomplish, allowing you to build sophisticated AI pipelines with validation, transformation, and refinement at each stage.

This chapter teaches you to build production-ready workflow orchestration systems with sequential processing, conditional branching, loops, error recovery, and state management. You'll learn to design reusable workflow components, implement retry logic, and optimize multi-step AI pipelines.

**What You'll Build**: A complete workflow orchestration framework with step definitions, conditional execution, parallel processing, error handling, and workflow composition.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-15** (Core API usage)
- ✓ **Workflow pattern knowledge** for orchestration
- ✓ **State machine understanding** for flow control
- ✓ **Pipeline architecture experience** for design

**Estimated Time**: 120-150 minutes

## Workflow Framework

```php
<?php
# filename: src/Workflow/Workflow.php
declare(strict_types=1);

namespace App\Workflow;

use Anthropic\Anthropic;

class Workflow
{
    private array $steps = [];
    private array $executionLog = [];
    private WorkflowState $state;

    public function __construct(
        private Anthropic $claude,
        private string $workflowId,
        private string $name
    ) {
        $this->state = new WorkflowState();
    }

    /**
     * Add a step to the workflow
     */
    public function addStep(WorkflowStep $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    /**
     * Execute the workflow
     */
    public function execute(array $initialInput = []): WorkflowResult
    {
        $this->state->set('input', $initialInput);
        $this->state->set('current_step', 0);

        $startTime = microtime(true);

        try {
            foreach ($this->steps as $index => $step) {
                $this->state->set('current_step', $index);

                // Check if step should be executed (conditional logic)
                if (!$this->shouldExecuteStep($step)) {
                    $this->logStep($step, 'skipped', null);
                    continue;
                }

                // Execute step with retry logic
                $stepResult = $this->executeStepWithRetry($step);

                // Update state with step output
                $this->state->set($step->getOutputKey(), $stepResult->output);

                // Log execution
                $this->logStep($step, $stepResult->status, $stepResult);

                // Check if workflow should continue
                if ($stepResult->status === 'stop') {
                    break;
                }

                // Handle errors
                if ($stepResult->status === 'error' && !$step->isContinueOnError()) {
                    throw new WorkflowException(
                        "Step '{$step->getName()}' failed: {$stepResult->error}"
                    );
                }
            }

            $status = 'completed';
            $output = $this->state->getAll();

        } catch (\Exception $e) {
            $status = 'failed';
            $output = ['error' => $e->getMessage()];
        }

        $duration = microtime(true) - $startTime;

        return new WorkflowResult(
            workflowId: $this->workflowId,
            status: $status,
            output: $output,
            executionLog: $this->executionLog,
            duration: $duration
        );
    }

    /**
     * Execute step with retry logic
     */
    private function executeStepWithRetry(WorkflowStep $step): StepResult
    {
        $maxRetries = $step->getMaxRetries();
        $lastError = null;

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($attempt > 0) {
                    // Exponential backoff
                    $delay = min(30, pow(2, $attempt - 1));
                    sleep($delay);
                }

                $context = new StepContext(
                    claude: $this->claude,
                    state: $this->state,
                    attempt: $attempt
                );

                return $step->execute($context);

            } catch (\Exception $e) {
                $lastError = $e->getMessage();

                if ($attempt < $maxRetries) {
                    continue;
                }
            }
        }

        return new StepResult(
            status: 'error',
            output: null,
            error: $lastError
        );
    }

    /**
     * Check if step should execute based on conditions
     */
    private function shouldExecuteStep(WorkflowStep $step): bool
    {
        $condition = $step->getCondition();

        if ($condition === null) {
            return true;
        }

        return $condition($this->state);
    }

    /**
     * Log step execution
     */
    private function logStep(WorkflowStep $step, string $status, ?StepResult $result): void
    {
        $this->executionLog[] = [
            'step' => $step->getName(),
            'status' => $status,
            'timestamp' => microtime(true),
            'output' => $result?->output,
            'error' => $result?->error
        ];
    }

    public function getState(): WorkflowState
    {
        return $this->state;
    }
}
```

## Workflow Step

```php
<?php
# filename: src/Workflow/WorkflowStep.php
declare(strict_types=1);

namespace App\Workflow;

abstract class WorkflowStep
{
    protected string $name;
    protected string $outputKey;
    protected int $maxRetries = 3;
    protected bool $continueOnError = false;
    protected ?\Closure $condition = null;

    public function __construct(string $name, string $outputKey = null)
    {
        $this->name = $name;
        $this->outputKey = $outputKey ?? $name;
    }

    /**
     * Execute the step
     */
    abstract public function execute(StepContext $context): StepResult;

    /**
     * Set condition for conditional execution
     */
    public function when(\Closure $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Set maximum retry attempts
     */
    public function retry(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    /**
     * Continue workflow even if this step fails
     */
    public function continueOnError(bool $continue = true): self
    {
        $this->continueOnError = $continue;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOutputKey(): string
    {
        return $this->outputKey;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function isContinueOnError(): bool
    {
        return $this->continueOnError;
    }

    public function getCondition(): ?\Closure
    {
        return $this->condition;
    }
}
```

## Built-in Workflow Steps

```php
<?php
# filename: src/Workflow/Steps/PromptStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class PromptStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private string $prompt,
        private array $options = []
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        // Interpolate variables from state into prompt
        $prompt = $this->interpolatePrompt($this->prompt, $context->state);

        $response = $context->claude->messages()->create([
            'model' => $this->options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $this->options['max_tokens'] ?? 4096,
            'temperature' => $this->options['temperature'] ?? 0.7,
            'system' => $this->options['system'] ?? null,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return new StepResult(
            status: 'success',
            output: $response->content[0]->text
        );
    }

    private function interpolatePrompt(string $prompt, WorkflowState $state): string
    {
        return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($state) {
            $key = $matches[1];
            $value = $state->get($key);

            if (is_array($value)) {
                return json_encode($value, JSON_PRETTY_PRINT);
            }

            return (string)$value;
        }, $prompt);
    }
}
```

```php
<?php
# filename: src/Workflow/Steps/TransformStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class TransformStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private \Closure $transformer
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        $transformer = $this->transformer;
        $output = $transformer($context->state);

        return new StepResult(
            status: 'success',
            output: $output
        );
    }
}
```

```php
<?php
# filename: src/Workflow/Steps/ValidationStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class ValidationStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private \Closure $validator,
        private ?string $errorMessage = null
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        $validator = $this->validator;
        $isValid = $validator($context->state);

        if (!$isValid) {
            return new StepResult(
                status: 'error',
                output: false,
                error: $this->errorMessage ?? 'Validation failed'
            );
        }

        return new StepResult(
            status: 'success',
            output: true
        );
    }
}
```

```php
<?php
# filename: src/Workflow/Steps/LoopStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;
use App\Workflow\Workflow;

class LoopStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private array $items,
        private Workflow $itemWorkflow,
        private int $maxIterations = 100
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        $results = [];
        $items = is_callable($this->items) ? ($this->items)($context->state) : $this->items;

        $iterations = min(count($items), $this->maxIterations);

        for ($i = 0; $i < $iterations; $i++) {
            $item = $items[$i];

            // Execute sub-workflow for each item
            $result = $this->itemWorkflow->execute(['item' => $item, 'index' => $i]);

            if ($result->status === 'completed') {
                $results[] = $result->output;
            } else {
                return new StepResult(
                    status: 'error',
                    output: $results,
                    error: "Loop failed at iteration {$i}"
                );
            }
        }

        return new StepResult(
            status: 'success',
            output: $results
        );
    }
}
```

```php
<?php
# filename: src/Workflow/Steps/ParallelStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class ParallelStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private array $steps
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        // Note: True parallel execution would require async PHP or process forking
        // This is a simplified sequential execution that could be parallelized

        $results = [];

        foreach ($this->steps as $stepName => $step) {
            $stepResult = $step->execute($context);

            if ($stepResult->status === 'error') {
                return new StepResult(
                    status: 'error',
                    output: $results,
                    error: "Parallel step '{$stepName}' failed: {$stepResult->error}"
                );
            }

            $results[$stepName] = $stepResult->output;
        }

        return new StepResult(
            status: 'success',
            output: $results
        );
    }
}
```

## Workflow Examples

```php
<?php
# filename: src/Workflow/Workflows/ContentCreationWorkflow.php
declare(strict_types=1);

namespace App\Workflow\Workflows;

use App\Workflow\Workflow;
use App\Workflow\Steps\PromptStep;
use App\Workflow\Steps\ValidationStep;
use App\Workflow\Steps\TransformStep;
use Anthropic\Anthropic;

class ContentCreationWorkflow
{
    public static function create(Anthropic $claude, string $topic): Workflow
    {
        $workflow = new Workflow($claude, uniqid('workflow_'), 'Content Creation');

        // Step 1: Research the topic
        $workflow->addStep(
            (new PromptStep('research', <<<PROMPT
Research the topic: {topic}

Provide:
1. Key points to cover
2. Target audience considerations
3. Relevant examples
4. Current trends
PROMPT, ['temperature' => 0.3]))
                ->retry(2)
        );

        // Step 2: Create outline
        $workflow->addStep(
            new PromptStep('outline', <<<PROMPT
Based on this research:

{research}

Create a detailed outline for an article about: {topic}

Include:
- Introduction
- Main sections (3-5)
- Conclusion
- Estimated word count for each section
PROMPT, ['temperature' => 0.5])
        );

        // Step 3: Validate outline
        $workflow->addStep(
            (new ValidationStep(
                'validate_outline',
                fn($state) => !empty($state->get('outline')) && strlen($state->get('outline')) > 100,
                'Outline is too short or empty'
            ))->continueOnError(false)
        );

        // Step 4: Write first draft
        $workflow->addStep(
            new PromptStep('draft', <<<PROMPT
Write a comprehensive article following this outline:

{outline}

Topic: {topic}

Requirements:
- Engaging introduction
- Well-structured sections
- Concrete examples
- Clear conclusion
- Professional tone
PROMPT, ['temperature' => 0.8, 'max_tokens' => 6000])
        );

        // Step 5: Review and improve
        $workflow->addStep(
            new PromptStep('review', <<<PROMPT
Review and improve this article draft:

{draft}

Provide:
1. Overall assessment
2. Specific improvements made
3. Final polished version

Focus on:
- Clarity and readability
- Flow and structure
- Grammar and style
- Completeness
PROMPT, ['temperature' => 0.4])
        );

        // Step 6: Extract final article
        $workflow->addStep(
            new TransformStep('extract_final', function($state) {
                $review = $state->get('review');

                // Extract the final version from the review
                if (preg_match('/Final.*?Version:?\s*(.*)/is', $review, $matches)) {
                    return trim($matches[1]);
                }

                return $review;
            })
        );

        return $workflow;
    }
}
```

```php
<?php
# filename: src/Workflow/Workflows/CodeReviewWorkflow.php
declare(strict_types=1);

namespace App\Workflow\Workflows;

use App\Workflow\Workflow;
use App\Workflow\Steps\PromptStep;
use App\Workflow\Steps\ValidationStep;
use Anthropic\Anthropic;

class CodeReviewWorkflow
{
    public static function create(Anthropic $claude, string $code, string $language = 'php'): Workflow
    {
        $workflow = new Workflow($claude, uniqid('workflow_'), 'Code Review');

        // Step 1: Initial code analysis
        $workflow->addStep(
            new PromptStep('analyze', <<<PROMPT
Analyze this {language} code:

```{language}
{code}
```

Provide:
1. Code structure overview
2. Main functionality
3. Dependencies identified
4. Complexity assessment
PROMPT, ['temperature' => 0.2])
        );

        // Step 2: Security review
        $workflow->addStep(
            new PromptStep('security', <<<PROMPT
Perform security review of this {language} code:

```{language}
{code}
```

Analysis: {analyze}

Identify:
1. Security vulnerabilities
2. Input validation issues
3. Authentication/authorization concerns
4. Data exposure risks
5. Recommendations
PROMPT, ['temperature' => 0.2])
        );

        // Step 3: Performance review
        $workflow->addStep(
            new PromptStep('performance', <<<PROMPT
Review performance of this {language} code:

```{language}
{code}
```

Identify:
1. Performance bottlenecks
2. Inefficient algorithms
3. Resource usage concerns
4. Optimization opportunities
5. Recommendations
PROMPT, ['temperature' => 0.2])
        );

        // Step 4: Best practices review
        $workflow->addStep(
            new PromptStep('best_practices', <<<PROMPT
Review best practices for this {language} code:

```{language}
{code}
```

Assess:
1. Code style and conventions
2. Design patterns usage
3. Error handling
4. Documentation quality
5. Testing considerations
6. Recommendations
PROMPT, ['temperature' => 0.3])
        );

        // Step 5: Synthesize final report
        $workflow->addStep(
            new PromptStep('report', <<<PROMPT
Create a comprehensive code review report based on:

Security Review:
{security}

Performance Review:
{performance}

Best Practices Review:
{best_practices}

Provide:
1. Executive Summary
2. Critical Issues (if any)
3. Recommendations (prioritized)
4. Overall Code Quality Score (1-10)
5. Detailed Findings

Format as a professional code review report.
PROMPT, ['temperature' => 0.4])
        );

        return $workflow;
    }
}
```

## Workflow Builder (Fluent Interface)

```php
<?php
# filename: src/Workflow/WorkflowBuilder.php
declare(strict_types=1);

namespace App\Workflow;

use Anthropic\Anthropic;
use App\Workflow\Steps\PromptStep;
use App\Workflow\Steps\TransformStep;
use App\Workflow\Steps\ValidationStep;

class WorkflowBuilder
{
    private Workflow $workflow;

    public function __construct(
        Anthropic $claude,
        string $name = 'Custom Workflow'
    ) {
        $this->workflow = new Workflow($claude, uniqid('workflow_'), $name);
    }

    /**
     * Add a prompt step
     */
    public function prompt(
        string $name,
        string $prompt,
        array $options = []
    ): self {
        $this->workflow->addStep(new PromptStep($name, $prompt, $options));
        return $this;
    }

    /**
     * Add a transformation step
     */
    public function transform(string $name, \Closure $transformer): self
    {
        $this->workflow->addStep(new TransformStep($name, $transformer));
        return $this;
    }

    /**
     * Add a validation step
     */
    public function validate(
        string $name,
        \Closure $validator,
        ?string $errorMessage = null
    ): self {
        $this->workflow->addStep(new ValidationStep($name, $validator, $errorMessage));
        return $this;
    }

    /**
     * Add a conditional step
     */
    public function when(\Closure $condition): ConditionalBuilder
    {
        return new ConditionalBuilder($this->workflow, $condition);
    }

    /**
     * Build and return the workflow
     */
    public function build(): Workflow
    {
        return $this->workflow;
    }
}

class ConditionalBuilder
{
    public function __construct(
        private Workflow $workflow,
        private \Closure $condition
    ) {}

    public function then(WorkflowStep $step): Workflow
    {
        $step->when($this->condition);
        $this->workflow->addStep($step);
        return $this->workflow;
    }
}
```

## Complete Example

```php
<?php
# filename: examples/workflow-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Workflow\WorkflowBuilder;
use App\Workflow\Workflows\ContentCreationWorkflow;
use App\Workflow\Workflows\CodeReviewWorkflow;

// Initialize Claude
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

echo "=== Workflow Orchestration Demo ===\n\n";

// Example 1: Content Creation Workflow
echo "Example 1: Content Creation Workflow\n";
echo str_repeat('-', 80) . "\n\n";

$contentWorkflow = ContentCreationWorkflow::create($claude, 'Vector Databases in PHP');

$result = $contentWorkflow->execute([
    'topic' => 'Vector Databases in PHP'
]);

echo "Status: {$result->status}\n";
echo "Duration: " . number_format($result->duration, 2) . "s\n";
echo "Steps executed: " . count($result->executionLog) . "\n\n";

echo "Final Article:\n";
echo $result->output['extract_final'] ?? 'N/A';
echo "\n\n";

// Example 2: Code Review Workflow
echo "\nExample 2: Code Review Workflow\n";
echo str_repeat('-', 80) . "\n\n";

$codeToReview = <<<'PHP'
function getUserData($id) {
    $db = new PDO('mysql:host=localhost;dbname=app', 'root', '');
    $result = $db->query("SELECT * FROM users WHERE id = " . $id);
    return $result->fetch();
}
PHP;

$reviewWorkflow = CodeReviewWorkflow::create($claude, $codeToReview, 'php');

$result = $reviewWorkflow->execute([
    'code' => $codeToReview,
    'language' => 'php'
]);

echo "Status: {$result->status}\n";
echo "Duration: " . number_format($result->duration, 2) . "s\n\n";

echo "Code Review Report:\n";
echo $result->output['report'] ?? 'N/A';
echo "\n\n";

// Example 3: Custom Workflow with Fluent Builder
echo "\nExample 3: Custom Workflow (Fluent Builder)\n";
echo str_repeat('-', 80) . "\n\n";

$customWorkflow = (new WorkflowBuilder($claude, 'Email Summary'))
    ->prompt('extract_emails', 'Extract all email addresses from: {text}', ['temperature' => 0.1])
    ->validate(
        'check_emails',
        fn($state) => !empty($state->get('extract_emails')),
        'No emails found'
    )
    ->transform('count_emails', function($state) {
        $emails = $state->get('extract_emails');
        preg_match_all('/[\w\.-]+@[\w\.-]+/', $emails, $matches);
        return count($matches[0]);
    })
    ->prompt('summarize', 'Summarize: Found {count_emails} email addresses')
    ->build();

$result = $customWorkflow->execute([
    'text' => 'Contact us at info@example.com or support@example.com'
]);

echo "Result: " . json_encode($result->output, JSON_PRETTY_PRINT) . "\n";
```

## Data Structures

```php
<?php
# filename: src/Workflow/DataStructures.php
declare(strict_types=1);

namespace App\Workflow;

use Anthropic\Anthropic;

class WorkflowState
{
    private array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function getAll(): array
    {
        return $this->data;
    }
}

readonly class StepContext
{
    public function __construct(
        public Anthropic $claude,
        public WorkflowState $state,
        public int $attempt = 0
    ) {}
}

readonly class StepResult
{
    public function __construct(
        public string $status,
        public mixed $output,
        public ?string $error = null
    ) {}
}

readonly class WorkflowResult
{
    public function __construct(
        public string $workflowId,
        public string $status,
        public array $output,
        public array $executionLog,
        public float $duration
    ) {}
}

class WorkflowException extends \Exception {}
```

## Key Takeaways

- ✓ Prompt chaining breaks complex tasks into manageable steps
- ✓ Each step's output feeds into the next step's input
- ✓ Conditional execution enables dynamic workflow paths
- ✓ Retry logic handles transient failures automatically
- ✓ Validation steps ensure data quality at each stage
- ✓ Transform steps enable data manipulation between prompts
- ✓ Loop steps process collections of items
- ✓ Parallel steps execute independent tasks concurrently
- ✓ Workflow composition creates reusable patterns
- ✓ State management maintains context across steps

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="34"
  label="You've mastered prompt chaining and workflow orchestration!"
/>

---

Continue to [Chapter 35: Fine-tuning Strategies](/series/claude-php-developers/chapters/35-fine-tuning-strategies) to learn when and how to fine-tune models.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 34 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-34)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-34
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/workflow-demo.php
```
