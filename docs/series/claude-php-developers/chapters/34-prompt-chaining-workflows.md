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

## What You'll Build

By the end of this chapter, you will have created:

- **Complete workflow orchestration framework** with step definitions, conditional execution, and state management
- **Reusable workflow steps** including PromptStep, TransformStep, ValidationStep, LoopStep, and ParallelStep
- **Production-ready workflow examples** for content creation and code review pipelines
- **Fluent workflow builder** for creating workflows with a clean, chainable API
- **Error handling and retry logic** with exponential backoff and error recovery
- **Workflow composition patterns** for building complex multi-step AI pipelines
- **State management system** that maintains context across workflow steps

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-15** (Core API usage)
- ✓ **Workflow pattern knowledge** for orchestration
- ✓ **State machine understanding** for flow control
- ✓ **Pipeline architecture experience** for design

**Estimated Time**: 120-150 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Verify Anthropic SDK is installed
composer show anthropic/claude-php/Claude-PHP-SDK

# Check if API key is set
echo $ANTHROPIC_API_KEY | cut -c1-10
```

## Quick Start

Here's a quick 5-minute example demonstrating a simple workflow:

```php
<?php
# filename: examples/quick-start-workflow.php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Workflow\WorkflowBuilder;

// Initialize Claude
$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create a simple 3-step workflow
$workflow = (new WorkflowBuilder($claude, 'Quick Start'))
    ->prompt('analyze', 'Analyze this topic: {topic}', ['temperature' => 0.3])
    ->transform('format', function($state) {
        $analysis = $state->get('analyze');
        return "Analysis Summary:\n" . substr($analysis, 0, 200) . '...';
    })
    ->prompt('summarize', 'Create a brief summary of: {format}')
    ->build();

// Execute workflow
$result = $workflow->execute(['topic' => 'PHP 8.4 Features']);

echo "Status: {$result->status}\n";
echo "Duration: " . number_format($result->duration, 2) . "s\n";
echo "Summary: " . ($result->output['summarize'] ?? 'N/A') . "\n";
```

Run this example:

```bash
export ANTHROPIC_API_KEY=sk-ant-api03-your-key-here
php examples/quick-start-workflow.php
```

Expected output:

```
Status: completed
Duration: 3.45s
Summary: [Generated summary text]
```

## Objectives

By completing this chapter, you will:

- Understand prompt chaining patterns and when to use sequential vs parallel processing
- Build a reusable workflow orchestration framework with step definitions and state management
- Implement conditional execution and branching logic in workflows
- Create specialized workflow steps for prompts, transformations, validation, loops, and parallel tasks
- Design error handling and retry strategies with exponential backoff
- Compose complex workflows from reusable components
- Optimize multi-step AI pipelines for performance and reliability
- Apply workflow patterns to real-world use cases like content creation and code review

## Workflow Framework

```php
<?php
# filename: src/Workflow/Workflow.php
declare(strict_types=1);

namespace App\Workflow;

use ClaudePhp\ClaudePhp;

class Workflow
{
    private array $steps = [];
    private array $executionLog = [];
    private WorkflowState $state;

    public function __construct(
        private ClaudePhp $claude,
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

### Why It Works

The `Workflow` class orchestrates step execution with several key features:

1. **State Management**: Each workflow maintains a `WorkflowState` object that stores data between steps. Step outputs are automatically saved using their `outputKey`, making them available to subsequent steps via variable interpolation.

2. **Conditional Execution**: The `shouldExecuteStep()` method evaluates each step's condition closure against the current state. If a condition returns `false`, the step is skipped and logged.

3. **Retry Logic**: `executeStepWithRetry()` implements exponential backoff (1s, 2s, 4s, up to 30s max) for transient failures. Each retry attempt receives a `StepContext` with the attempt number.

4. **Error Handling**: Steps can be configured to `continueOnError()`, allowing workflows to proceed even if a non-critical step fails. Critical steps will throw `WorkflowException` and stop execution.

5. **Execution Logging**: Every step execution is logged with status, timestamp, output, and errors. This provides complete observability for debugging and monitoring.

6. **Result Tracking**: The workflow returns a `WorkflowResult` containing the final state, execution log, duration, and status, enabling comprehensive workflow analysis.

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

This section covers the core step types. Chapter 34 focuses on foundational patterns; for streaming, tool use, and vision workflows, see the integration patterns section below.

```php
<?php
# filename: src/Workflow/Steps/PromptStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;
use App\Workflow\WorkflowState;

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
            output: $response->content[0]['text']
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

### Why It Works

`PromptStep` executes Claude API calls with state interpolation:

1. **Variable Interpolation**: The `interpolatePrompt()` method uses regex to find `{key}` placeholders and replaces them with values from the workflow state. Arrays are JSON-encoded for readability.

2. **State Access**: Steps receive a `StepContext` containing the Claude client and current state, enabling them to access previous step outputs.

3. **Configurable Options**: Model selection, temperature, max tokens, and system prompts can be customized per step, allowing fine-tuned control over each AI interaction.

4. **Error Propagation**: API exceptions are caught by the workflow's retry mechanism, enabling automatic recovery from transient failures.

```php
<?php
# filename: src/Workflow/Steps/TransformStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;
use App\Workflow\WorkflowState;

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
use App\Workflow\WorkflowState;

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

### Why It Works

**TransformStep** enables data manipulation between AI calls:

- **Pure Functions**: Transformers receive the entire state and return new values, enabling data cleaning, formatting, extraction, and aggregation.
- **No API Calls**: Transform steps execute synchronously without network calls, making them fast and cost-free.
- **State Mutation**: The transformer's output replaces the step's output key in state, updating the workflow context.

**ValidationStep** ensures data quality:

- **Early Failure**: Invalid data stops workflow execution immediately (unless `continueOnError()` is set), preventing downstream errors.
- **Custom Validators**: Closures can implement complex validation logic, checking multiple state values and relationships.
- **Error Messages**: Custom error messages provide context for debugging validation failures.

**LoopStep** processes collections:

- **Sub-workflows**: Each item is processed by a separate workflow instance, enabling complex per-item processing pipelines.
- **Iteration Limits**: `maxIterations` prevents infinite loops and runaway costs.
- **Failure Handling**: If any iteration fails, the loop stops and returns partial results.

**ParallelStep** executes independent tasks:

- **Sequential Execution**: This implementation runs steps sequentially. True parallelism requires async PHP (ReactPHP, Swoole) or process forking.
- **Early Termination**: If any parallel step fails, execution stops immediately (can be modified for partial success).
- **Shared Context**: All parallel steps share the same state context, enabling coordination when needed.

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
use ClaudePhp\ClaudePhp;

class ContentCreationWorkflow
{
    public static function create(ClaudePhp $claude, string $topic): Workflow
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
use ClaudePhp\ClaudePhp;

class CodeReviewWorkflow
{
    public static function create(ClaudePhp $claude, string $code, string $language = 'php'): Workflow
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

### Why It Works

**ContentCreationWorkflow** demonstrates a complete content generation pipeline:

1. **Research Phase**: Low temperature (0.3) ensures factual, focused research output.
2. **Outline Generation**: Medium temperature (0.5) balances creativity with structure.
3. **Validation Gate**: Ensures outline quality before proceeding to expensive draft generation.
4. **Draft Writing**: High temperature (0.8) and large token limit (6000) enable creative, comprehensive content.
5. **Review & Refinement**: Lower temperature (0.4) focuses on editing and improvement.
6. **Extraction**: Transform step extracts the final polished version from the review output.

**CodeReviewWorkflow** shows parallel analysis patterns:

1. **Multi-Perspective Review**: Security, performance, and best practices are analyzed independently, then synthesized.
2. **Low Temperature**: All review steps use low temperature (0.2-0.3) for consistent, objective analysis.
3. **Synthesis Step**: Final report combines all perspectives into a comprehensive review.
4. **Reusable Pattern**: This pattern works for any multi-faceted analysis task (documentation, architecture, etc.).

## Workflow Builder (Fluent Interface)

```php
<?php
# filename: src/Workflow/WorkflowBuilder.php
declare(strict_types=1);

namespace App\Workflow;

use ClaudePhp\ClaudePhp;
use App\Workflow\Steps\PromptStep;
use App\Workflow\Steps\TransformStep;
use App\Workflow\Steps\ValidationStep;

class WorkflowBuilder
{
    private Workflow $workflow;

    public function __construct(
        ClaudePhp $claude,
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

### Why It Works

The `WorkflowBuilder` provides a fluent, chainable API for constructing workflows:

1. **Method Chaining**: Each method returns `$this`, enabling readable, sequential workflow definition.
2. **Type Safety**: Methods accept typed parameters, providing IDE autocomplete and compile-time checking.
3. **Conditional Building**: The `when()` method returns a `ConditionalBuilder` that applies conditions to subsequent steps.
4. **Immutability**: Steps are added to the workflow but the builder itself remains unchanged, enabling reuse.

This pattern makes workflow creation more intuitive than manually instantiating and configuring steps.

## Advanced Workflow Step Types

While the core framework covers basic operations, workflows can be extended with specialized steps for advanced features covered in other chapters. Here are the patterns for integration:

### Tool Use Workflows

Integrate tool calling (Chapter 12) into workflows:

```php
<?php
# filename: src/Workflow/Steps/ToolUseStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class ToolUseStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private string $prompt,
        private array $tools = [],
        private array $options = []
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        // Build tool definitions from the tools array
        $toolDefinitions = array_map(function($tool) {
            return [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'input_schema' => $tool['schema']
            ];
        }, $this->tools);

        // Call Claude with tools available
        $response = $context->claude->messages()->create([
            'model' => $this->options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $this->options['max_tokens'] ?? 4096,
            'tools' => $toolDefinitions,
            'messages' => [[
                'role' => 'user',
                'content' => $this->prompt
            ]]
        ]);

        // Handle tool calls
        $toolResults = [];
        foreach ($response->content as $block) {
            if ($block['type'] === 'tool_use') {
                // Execute the tool
                $result = $this->executeTool($block->name, $block->input);
                $toolResults[] = $result;
            }
        }

        return new StepResult(
            status: 'success',
            output: [
                'response' => $response->content,
                'tool_results' => $toolResults
            ]
        );
    }

    private function executeTool(string $toolName, array $input): mixed
    {
        // This should be implemented based on your available tools
        // For now, return a placeholder
        return "Tool '{$toolName}' executed with input: " . json_encode($input);
    }
}
```

### Vision Workflows

Integrate image processing (Chapter 13) into workflows:

```php
<?php
# filename: src/Workflow/Steps/VisionStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class VisionStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private string $prompt,
        private array $images = [], // Array of image URLs or base64 data
        private array $options = []
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        // Build content with image blocks
        $content = [];

        // Add images
        foreach ($this->images as $image) {
            if (str_starts_with($image, 'http')) {
                // URL-based image
                $content[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'url',
                        'url' => $image
                    ]
                ];
            } else {
                // Base64-encoded image
                $content[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $this->options['media_type'] ?? 'image/jpeg',
                        'data' => $image
                    ]
                ];
            }
        }

        // Add prompt text
        $content[] = [
            'type' => 'text',
            'text' => $this->prompt
        ];

        $response = $context->claude->messages()->create([
            'model' => $this->options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $this->options['max_tokens'] ?? 4096,
            'messages' => [[
                'role' => 'user',
                'content' => $content
            ]]
        ]);

        return new StepResult(
            status: 'success',
            output: $response->content[0]['text']
        );
    }
}
```

### Streaming Workflows

For real-time streaming in long-running workflows:

```php
<?php
# filename: src/Workflow/Steps/StreamingStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class StreamingStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private string $prompt,
        private ?callable $onChunk = null, // Callback for each chunk
        private array $options = []
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        $fullResponse = '';

        // Stream the response
        $stream = $context->claude->messages()->stream([
            'model' => $this->options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $this->options['max_tokens'] ?? 4096,
            'messages' => [[
                'role' => 'user',
                'content' => $this->prompt
            ]]
        ]);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta' && $event->delta->type === 'text_delta') {
                $chunk = $event->delta->text;
                $fullResponse .= $chunk;

                // Call callback if provided
                if ($this->onChunk) {
                    ($this->onChunk)($chunk);
                }
            }
        }

        return new StepResult(
            status: 'success',
            output: $fullResponse
        );
    }
}
```

### Structured Output Workflows

For schema-validated responses (Chapter 15):

```php
<?php
# filename: src/Workflow/Steps/StructuredStep.php
declare(strict_types=1);

namespace App\Workflow\Steps;

use App\Workflow\WorkflowStep;
use App\Workflow\StepContext;
use App\Workflow\StepResult;

class StructuredStep extends WorkflowStep
{
    public function __construct(
        string $name,
        private string $prompt,
        private array $schema,
        private array $options = []
    ) {
        parent::__construct($name);
    }

    public function execute(StepContext $context): StepResult
    {
        $response = $context->claude->messages()->create([
            'model' => $this->options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $this->options['max_tokens'] ?? 4096,
            'messages' => [[
                'role' => 'user',
                'content' => $this->prompt
            ]],
            'thinking' => [
                'type' => 'enabled',
                'budget_tokens' => $this->options['thinking_budget'] ?? 1000
            ]
        ]);

        // Use Extended Thinking for complex structured outputs
        $output = null;
        foreach ($response->content as $block) {
            if (property_exists($block, 'text')) {
                $output = json_decode($block['text'], true);
                break;
            }
        }

        return new StepResult(
            status: 'success',
            output: $output
        );
    }
}
```

## Complete Example

```php
<?php
# filename: examples/workflow-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Workflow\WorkflowBuilder;
use App\Workflow\Workflows\ContentCreationWorkflow;
use App\Workflow\Workflows\CodeReviewWorkflow;

// Initialize Claude
$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

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

### Why It Works

This complete example demonstrates three workflow patterns:

1. **Pre-built Workflows**: `ContentCreationWorkflow` and `CodeReviewWorkflow` show how to encapsulate common patterns as reusable classes.

2. **Fluent Builder**: The custom workflow example shows how `WorkflowBuilder` enables rapid prototyping and ad-hoc workflow creation.

3. **State Flow**: Notice how each step's output becomes available to subsequent steps via `{variable}` interpolation.

4. **Error Handling**: The workflow framework handles retries, validation failures, and API errors automatically, returning structured results.

5. **Observability**: Execution logs provide complete visibility into step execution, timing, and outcomes.

## Best Practices

### Workflow Design

**Keep Steps Focused**: Each step should have a single, well-defined responsibility. If a step does multiple things, consider splitting it.

**Use Appropriate Temperatures**: 
- Low (0.1-0.3): Factual extraction, analysis, validation
- Medium (0.4-0.6): Balanced creativity and accuracy
- High (0.7-1.0): Creative generation, brainstorming

**Validate Early**: Place validation steps as early as possible to fail fast and avoid wasting API calls.

**Limit Token Usage**: Set `max_tokens` appropriately for each step. Large limits increase cost and latency.

**Handle Errors Gracefully**: Use `continueOnError()` for non-critical steps, but fail fast for critical validation.

### State Management

**Use Descriptive Keys**: Choose clear, consistent naming for state keys (e.g., `research_output` not `r1`).

**Avoid State Pollution**: Remove temporary data from state between steps to keep it manageable.

**Document State Shape**: Comment on what keys each step expects and produces.

**Validate State Access**: Check that required state keys exist before accessing them in prompts.

### Performance Optimization

**Parallelize Independent Steps**: Use `ParallelStep` for steps that don't depend on each other.

**Cache Expensive Operations**: Store results of expensive steps in state for reuse.

**Optimize Prompt Sizes**: Keep prompts focused and remove unnecessary context to reduce token usage.

**Profile Execution**: Use execution logs to identify slow steps and optimize them.

### Error Handling

**Set Appropriate Retries**: Critical steps should retry more (3-5), optional steps less (1-2).

**Use Exponential Backoff**: The built-in backoff prevents overwhelming APIs during outages.

**Log Everything**: Execution logs are invaluable for debugging production issues.

**Fail Fast**: Don't continue workflows with invalid data—validate early and fail immediately.

## Data Structures

```php
<?php
# filename: src/Workflow/DataStructures.php
declare(strict_types=1);

namespace App\Workflow;

use ClaudePhp\ClaudePhp;

/**
 * WorkflowState manages data shared between workflow steps.
 * Keys are arbitrary strings, values can be any PHP type.
 */
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

/**
 * StepContext provides execution context to workflow steps.
 * Contains the Claude client, current state, and retry attempt number.
 */
readonly class StepContext
{
    public function __construct(
        public ClaudePhp $claude,
        public WorkflowState $state,
        public int $attempt = 0
    ) {}
}

/**
 * StepResult represents the outcome of a workflow step execution.
 * Status can be: 'success', 'error', 'stop', or 'skipped'.
 */
readonly class StepResult
{
    public function __construct(
        public string $status,
        public mixed $output,
        public ?string $error = null
    ) {}
}

/**
 * WorkflowResult contains the final outcome of workflow execution.
 * Status can be: 'completed', 'failed', or 'stopped'.
 * Output contains all state values at completion.
 */
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

/**
 * WorkflowException is thrown when workflow execution fails critically.
 * Use this to distinguish workflow errors from step-level errors.
 */
class WorkflowException extends \Exception {}
```

### Why It Works

These data structures provide type safety and clear contracts:

1. **WorkflowState**: Simple key-value store with type hints for common operations. The `mixed` type allows flexibility while maintaining clarity.

2. **StepContext**: Readonly class ensures immutability during step execution, preventing accidental state mutation.

3. **StepResult**: Standardized result format enables consistent error handling and logging across all step types.

4. **WorkflowResult**: Comprehensive result object provides everything needed for monitoring, debugging, and downstream processing.

5. **WorkflowException**: Custom exception type enables specific error handling for workflow failures vs. step failures.

## Integrating with Other Claude Features

Chapter 34 focuses on workflow orchestration fundamentals. These patterns show how to integrate workflows with advanced features from other chapters:

### Workflows with RAG (Chapter 31)

Combine retrieval steps with Claude calls:

```php
<?php
// Add a retrieval step that queries a vector database
$workflow->addStep(
    (new PromptStep('retrieve_context', 'Search knowledge base for: {user_query}'))
        ->retry(2)
);

// Then use retrieved context in analysis
$workflow->addStep(
    new PromptStep('analyze', <<<PROMPT
Based on this context:

{retrieve_context}

Answer the user's question: {user_query}

Be specific and cite sources when relevant.
PROMPT)
);
```

### Workflows with Tool Use (Chapter 12)

Execute tools between prompt steps:

```php
<?php
// Step 1: Claude decides which tools to use
$workflow->addStep(new ToolUseStep(
    'decide_action',
    'Given this request: {request}, decide what to do',
    ['fetch_data', 'search_database', 'calculate_result']
));

// Step 2: Process the tool results
$workflow->addStep(new PromptStep(
    'synthesize',
    'Using these results: {decide_action}, provide final answer'
));
```

### Workflows with Vision (Chapter 13)

Process images and documents in pipelines:

```php
<?php
// Extract information from documents
$workflow->addStep(new VisionStep(
    'analyze_document',
    'Extract structured data: {extraction_prompt}',
    [$documentImageUrl] // Can be URLs or base64
));

// Then process the extracted data
$workflow->addStep(new PromptStep(
    'classify',
    'Classify this data: {analyze_document}'
));
```

### Workflows with Streaming (Chapter 06)

Stream responses for real-time UX:

```php
<?php
// Use streaming for long-running analysis
$workflow->addStep(new StreamingStep(
    'stream_analysis',
    'Analyze in detail: {data}',
    onChunk: function($chunk) {
        // Send chunk to WebSocket client in real time
        echo $chunk;
    }
));
```

### Workflows with Caching (Chapter 18)

Optimize costs in repeated workflows:

```php
<?php
// Pre-cache expensive prompts using a transform step
$workflow->addStep(new TransformStep('setup_cache', function($state) {
    // Cache this context for reuse
    $cachedContext = 'long_system_prompt_or_context...';
    return $cachedContext;
}));

// Then use cached context in multiple steps
$workflow->addStep(
    new PromptStep('analysis1', '{setup_cache}\n\nAnalyze: {data}')
);
$workflow->addStep(
    new PromptStep('analysis2', '{setup_cache}\n\nClassify: {data}')
);
```

### Workflows with Batch Processing (Chapter 39)

Process large datasets efficiently:

```php
<?php
// Use loop step for batch processing
$workflow->addStep(new LoopStep(
    'batch_process',
    fn($state) => $state->get('items'), // Large array of items
    $this->createItemProcessingWorkflow(),
    maxIterations: 1000 // Prevent runaway costs
));

// Summarize results
$workflow->addStep(new PromptStep(
    'summarize',
    'Summarize these results: {batch_process}'
));
```

### Workflows with Extended Thinking

For complex reasoning in workflows:

```php
<?php
// Use thinking for complex multi-step reasoning
$workflow->addStep(new StructuredStep(
    'reason',
    'Solve this complex problem: {problem}',
    schema: ['type' => 'object', 'properties' => [...]],
    options: ['thinking_budget' => 5000]
));
```

## Scaling Workflows

### Queue-Based Execution (Chapter 19)

For long-running workflows, queue them for background processing:

```php
<?php
// Laravel example - dispatch workflow to queue
$workflow = ContentCreationWorkflow::create($claude, 'AI Integration Patterns');

// Dispatch to queue
dispatch(function() use ($workflow) {
    $result = $workflow->execute(['topic' => 'Advanced Patterns']);
    // Store result in database
    WorkflowResult::create([
        'workflow_id' => $result->workflowId,
        'status' => $result->status,
        'output' => $result->output,
        'duration' => $result->duration
    ]);
})->onQueue('workflows');
```

### WebSocket Streaming (Chapter 20)

Stream workflow progress to connected clients:

```php
<?php
// Broadcast workflow progress in real-time
$workflow = new Workflow($claude, uniqid(), 'Real-time Workflow');

$workflow->addStep(new StreamingStep(
    'stream_content',
    'Generate: {topic}',
    onChunk: function($chunk) use ($clientId) {
        // Stream to WebSocket client
        broadcast(new WorkflowChunkStreamed($clientId, $chunk));
    }
));

$result = $workflow->execute(['topic' => 'AI Architecture']);
broadcast(new WorkflowCompleted($clientId, $result));
```

### Monitoring Workflows (Chapter 37)

Track workflow execution and performance:

```php
<?php
// Log workflow metrics
$result = $workflow->execute(['topic' => 'Advanced Techniques']);

Log::info('Workflow execution', [
    'workflow_id' => $result->workflowId,
    'status' => $result->status,
    'duration' => $result->duration,
    'steps' => count($result->executionLog),
    'step_timings' => array_map(fn($log) => [
        'step' => $log['step'],
        'status' => $log['status'],
        'timestamp' => $log['timestamp']
    ], $result->executionLog)
]);

// Alert on slow workflows
if ($result->duration > 60) {
    Log::warning('Slow workflow detected', ['workflow_id' => $result->workflowId]);
}
```

### Cost Optimization (Chapter 39)

Track and optimize workflow costs:

```php
<?php
// Calculate workflow costs based on model usage
class WorkflowCostCalculator
{
    private array $modelPricing = [
        'claude-haiku-4-20250514' => 0.80 / 1_000_000, // per input token
        'claude-sonnet-4-20250514' => 3.00 / 1_000_000,
        'claude-opus-4-20250514' => 15.00 / 1_000_000,
    ];

    public function calculateCost(WorkflowResult $result): float
    {
        $totalCost = 0;

        foreach ($result->executionLog as $log) {
            if ($log['status'] === 'success' && isset($log['output'])) {
                // Estimate tokens from response
                $tokens = strlen(json_encode($log['output'])) / 4; // Rough estimate
                $model = 'claude-sonnet-4-20250514'; // Track model per step
                $totalCost += $tokens * $this->modelPricing[$model];
            }
        }

        return $totalCost;
    }
}
```

## Memory and Persistence (Chapter Memory Tool - Beta)

Workflows can leverage the Memory Tool for cross-workflow context:

```php
<?php
// Workflows can accumulate memory across executions
class WorkflowWithMemory
{
    public function __construct(
        private ClaudePhp $claude,
        private string $userId
    ) {}

    public function executeWithMemory(array $input): WorkflowResult
    {
        $workflow = new Workflow($this->claude, uniqid(), 'With Memory');

        // Retrieve user's workflow memory
        $workflow->addStep(new PromptStep(
            'load_context',
            'What context should I know about {user_id}?'
        ));

        // Main workflow steps using context
        $workflow->addStep(new PromptStep(
            'process',
            'With this context: {load_context}\n\nProcess: {request}'
        ));

        // Store insights in memory for next time
        $workflow->addStep(new TransformStep('save_memory', function($state) {
            return 'Learned: ' . json_encode($state->get('process'));
        }));

        return $workflow->execute(array_merge($input, ['user_id' => $this->userId]));
    }
}
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

## Exercises

### Exercise 1: Email Processing Workflow

Build a workflow that processes emails through multiple stages:

```php
<?php
class EmailProcessingWorkflow
{
    public static function create(ClaudePhp $claude): Workflow
    {
        // TODO: Create workflow that:
        // 1. Extracts key information from email
        // 2. Categorizes email (support, sales, spam, etc.)
        // 3. Validates that critical fields are present
        // 4. Generates response draft if needed
        // 5. Routes to appropriate team/workflow
        
        // Use WorkflowBuilder for fluent API
    }
}
```

**Validation**: Test with sample emails and verify each step produces expected output.

### Exercise 2: Document Analysis Pipeline

Create a multi-stage document analysis workflow:

```php
<?php
class DocumentAnalysisWorkflow
{
    public static function create(ClaudePhp $claude): Workflow
    {
        // TODO: Build workflow that:
        // 1. Extracts text from document (PDF, Word, etc.)
        // 2. Summarizes key points
        // 3. Identifies entities (people, organizations, dates)
        // 4. Extracts structured data (tables, lists)
        // 5. Generates analysis report
        // 6. Validates report completeness
        
        // Include conditional steps based on document type
    }
}
```

**Validation**: Process different document types and verify structured output.

### Exercise 3: Conditional Workflow with Branching

Implement a workflow with conditional branching:

```php
<?php
class ConditionalWorkflow
{
    public static function create(ClaudePhp $claude): Workflow
    {
        // TODO: Create workflow that:
        // 1. Analyzes input to determine complexity
        // 2. Branches based on complexity:
        //    - Simple: Single-step processing
        //    - Medium: Two-step refinement
        //    - Complex: Multi-agent collaboration
        // 3. Validates output based on branch taken
        // 4. Logs decision path for debugging
        
        // Use when() conditions for branching
    }
}
```

**Validation**: Test with inputs of varying complexity and verify correct branch execution.

### Exercise 4: Workflow with Error Recovery

Build a robust workflow with comprehensive error handling:

```php
<?php
class ResilientWorkflow
{
    public static function create(ClaudePhp $claude): Workflow
    {
        // TODO: Implement workflow that:
        // 1. Attempts primary processing path
        // 2. Falls back to alternative path on failure
        // 3. Implements circuit breaker pattern
        // 4. Logs all errors for analysis
        // 5. Provides graceful degradation
        
        // Use retry() and continueOnError() strategically
    }
}
```

**Validation**: Simulate failures and verify recovery mechanisms work correctly.

## Troubleshooting

**Workflow hangs or times out?**

- Check for infinite loops in conditional logic
- Verify maxIterations is set for LoopStep
- Review exponential backoff delays (may be too long)
- Check if parallel steps are blocking each other
- Monitor state size (large state can slow execution)

**Steps not executing in expected order?**

- Verify step conditions are not preventing execution
- Check that previous step outputs are available in state
- Review conditional logic in `shouldExecuteStep()`
- Ensure step dependencies are correctly ordered
- Check execution log for skipped steps

**State not persisting between steps?**

- Verify output keys match state keys being accessed
- Check that step results are being saved to state
- Review state key naming conventions
- Ensure state is not being reset between steps
- Check for key collisions overwriting values

**Retry logic not working?**

- Verify maxRetries is set correctly on steps
- Check that exceptions are being caught properly
- Review exponential backoff calculation
- Ensure retry conditions are appropriate
- Check if errors are transient vs permanent

**Workflow performance issues?**

- Consider parallelizing independent steps
- Review prompt sizes (large prompts slow execution)
- Optimize state management (remove unused data)
- Cache frequently used intermediate results
- Profile individual step execution times

**Conditional steps always executing?**

- Verify condition closures have correct logic
- Check that state values exist before evaluation
- Review condition return type (must be bool)
- Ensure state keys match what conditions expect
- Test conditions independently before adding to workflow

## Wrap-up

Congratulations! You've mastered prompt chaining and workflow orchestration. In this chapter, you've:

- ✓ **Built a complete workflow framework** with step definitions, state management, and execution engine
- ✓ **Created reusable workflow steps** for prompts, transformations, validation, loops, and parallel processing
- ✓ **Implemented error handling** with retry logic, exponential backoff, and graceful failure recovery
- ✓ **Designed conditional execution** that enables dynamic workflow paths based on state
- ✓ **Composed complex workflows** from simple, reusable components
- ✓ **Applied workflow patterns** to real-world use cases like content creation and code review
- ✓ **Optimized workflows** for performance, reliability, and maintainability

Workflow orchestration is a powerful pattern for building sophisticated AI applications. By breaking complex tasks into manageable steps, you can create systems that are easier to understand, debug, and maintain. The patterns you've learned here form the foundation for building production-ready AI pipelines.

## Further Reading

- [Workflow Patterns](https://www.workflowpatterns.com/) — Comprehensive catalog of workflow patterns and anti-patterns
- [State Machine Design Patterns](https://refactoring.guru/design-patterns/state) — State pattern for workflow state management
- [Retry Pattern](https://docs.microsoft.com/en-us/azure/architecture/patterns/retry) — Best practices for implementing retry logic
- [Pipeline Architecture](https://martinfowler.com/articles/pipelines.html) — Martin Fowler's guide to pipeline patterns
- [PHP Design Patterns](https://refactoring.guru/design-patterns/php) — Design patterns applicable to workflow systems
- [Anthropic API Documentation](https://docs.claude.com) — Official Claude API reference for building workflow steps

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
