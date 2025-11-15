---
title: "05: Prompt Engineering Basics"
description: "Master the art of prompt engineering for Claude. Learn effective prompting techniques, few-shot learning, chain-of-thought reasoning, role-playing, structured outputs, and best practices for PHP applications."
series: "claude-php-developers"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "PHP 8.2+ installed"
  - "Completion of Chapters 01-04"
  - "Understanding of Claude's message structure"
---

![05: Prompt Engineering Basics](/images/claude-php/chapter-05-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 05</span>
</div>

# Chapter 05: Prompt Engineering Basics

## Overview

Prompt engineering is the art and science of crafting effective instructions for AI models. The quality of Claude's responses is directly proportional to the quality of your prompts. A well-engineered prompt can mean the difference between generic, unhelpful outputs and precise, actionable results.

This chapter provides comprehensive coverage of prompt engineering fundamentals, including clear instruction writing, few-shot learning, chain-of-thought reasoning, role-playing techniques, structured output formats, and practical PHP implementation patterns. You'll learn to write prompts that consistently produce high-quality results.

**What You'll Learn:**
- Core principles of effective prompting
- Few-shot learning with examples
- Chain-of-thought reasoning techniques
- Role-playing and persona assignment
- Structured output formatting (JSON, XML, CSV)
- Prompt templates and reusability
- Common pitfalls and how to avoid them

**Estimated Time**: 45-55 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 01-04**
- ✓ **PHP 8.2+** with good understanding
- ✓ **Anthropic API key** configured
- ✓ **Experience with Claude API** from previous chapters

## Principles of Effective Prompts

### 1. Be Clear and Specific

Vague prompts produce vague results. Be explicit about what you want.

```php
<?php
# filename: examples/01-clarity.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// ✗ Bad: Vague prompt
$vagueResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Tell me about PHP.'
    ]]
]);

echo "Vague prompt result:\n";
echo $vagueResponse->content[0]->text . "\n\n";

// ✓ Good: Specific prompt
$specificResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain the benefits of PHP 8.3\'s typed class constants in 3 bullet points. Include one code example.'
    ]]
]);

echo "Specific prompt result:\n";
echo $specificResponse->content[0]->text . "\n\n";
```

### 2. Provide Context

Give Claude the information it needs to understand your request.

```php
<?php
# filename: examples/02-context.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$userCode = <<<'PHP'
class UserRepository {
    public function find($id) {
        return DB::table('users')->where('id', $id)->first();
    }
}
PHP;

// ✗ Bad: No context
$noContextResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => "Review this code:\n\n{$userCode}"
    ]]
]);

// ✓ Good: With context
$withContextResponse = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => "You are reviewing a Laravel 11 application that handles sensitive user data. Review this repository class for security issues, type safety, and error handling:\n\n{$userCode}"
    ]]
]);

echo "With context:\n";
echo $withContextResponse->content[0]->text . "\n";
```

### 3. Specify Output Format

Tell Claude exactly how you want the response formatted.

```php
<?php
# filename: examples/03-output-format.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'List 5 PHP design patterns. Format as JSON with fields: name, category, use_case, difficulty. Return only valid JSON.'
    ]]
]);

echo $response->content[0]->text . "\n";

// Parse the JSON
$text = $response->content[0]->text;
if (preg_match('/```json\s*(\{.*?\}|\[.*?\])\s*```/s', $text, $matches)) {
    $json = $matches[1];
} else {
    $json = $text;
}

$data = json_decode($json, true);
print_r($data);
```

### 4. Use Delimiters for Clarity

Separate instructions from data using clear delimiters.

```php
<?php
# filename: examples/04-delimiters.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$emailContent = "Hi, I need help with my order #12345. It hasn't arrived yet. Please refund ASAP!";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Analyze the customer email below and extract:
1. Order number
2. Issue category (delivery, refund, product, etc.)
3. Urgency level (low, medium, high)
4. Sentiment (positive, neutral, negative)

Return as JSON.

--- EMAIL STARTS ---
{$emailContent}
--- EMAIL ENDS ---
PROMPT
    ]]
]);

echo $response->content[0]->text . "\n";
```

## Few-Shot Learning

Few-shot learning involves providing examples to guide Claude's responses.

### Zero-Shot (No Examples)

```php
<?php
# filename: examples/05-zero-shot.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// No examples provided
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => 'Classify this text as positive, negative, or neutral: "The product is okay, nothing special."'
    ]]
]);

echo "Zero-shot classification:\n";
echo $response->content[0]->text . "\n\n";
```

### One-Shot (One Example)

```php
<?php
# filename: examples/06-one-shot.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Classify customer feedback sentiment.

Example:
Input: "This product exceeded my expectations! Absolutely love it."
Output: positive

Now classify this:
Input: "The product is okay, nothing special."
Output:
PROMPT
    ]]
]);

echo "One-shot classification:\n";
echo $response->content[0]->text . "\n\n";
```

### Few-Shot (Multiple Examples)

```php
<?php
# filename: examples/07-few-shot.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Classify customer feedback sentiment as: positive, negative, or neutral.

Examples:

Input: "This product exceeded my expectations! Absolutely love it."
Output: positive

Input: "Terrible quality, broke after one day. Very disappointed."
Output: negative

Input: "The product is okay, nothing special."
Output: neutral

Input: "Fast shipping and good packaging."
Output: positive

Now classify this:
Input: "Not what I expected, but it works fine."
Output:
PROMPT
    ]]
]);

echo "Few-shot classification:\n";
echo $response->content[0]->text . "\n\n";
```

### Few-Shot with Structured Output

```php
<?php
# filename: src/Services/FewShotClassifier.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;

class FewShotClassifier
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function classify(string $text, array $examples, array $categories): string
    {
        $examplesText = $this->formatExamples($examples);
        $categoriesText = implode(', ', $categories);

        $prompt = <<<PROMPT
Classify the text into one of these categories: {$categoriesText}

Examples:
{$examplesText}

Now classify this:
Input: {$text}
Output:
PROMPT;

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 100,
            'temperature' => 0.0, // Deterministic for classification
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return trim($response->content[0]->text);
    }

    private function formatExamples(array $examples): string
    {
        $formatted = [];

        foreach ($examples as $example) {
            $formatted[] = "Input: {$example['input']}\nOutput: {$example['output']}";
        }

        return implode("\n\n", $formatted);
    }
}
```

**Usage:**

```php
<?php
# filename: examples/08-few-shot-classifier.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\FewShotClassifier;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$classifier = new FewShotClassifier($client);

$examples = [
    ['input' => 'This Laravel package is amazing!', 'output' => 'positive'],
    ['input' => 'Terrible documentation, bugs everywhere.', 'output' => 'negative'],
    ['input' => 'It works as expected.', 'output' => 'neutral'],
];

$result = $classifier->classify(
    text: 'Great performance improvements in this version!',
    examples: $examples,
    categories: ['positive', 'negative', 'neutral']
);

echo "Classification: {$result}\n";
```

## Chain-of-Thought Reasoning

Chain-of-thought prompting encourages Claude to show its reasoning process.

### Basic Chain-of-Thought

```php
<?php
# filename: examples/09-chain-of-thought.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Without chain-of-thought
$direct = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 512,
    'messages' => [[
        'role' => 'user',
        'content' => 'Should I use a singleton pattern for my database connection in PHP?'
    ]]
]);

echo "Direct answer:\n";
echo $direct->content[0]->text . "\n\n";

// With chain-of-thought
$cot = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => 'Should I use a singleton pattern for my database connection in PHP? Think through this step by step, considering: 1) Performance implications, 2) Testing challenges, 3) Modern alternatives like dependency injection.'
    ]]
]);

echo "Chain-of-thought answer:\n";
echo $cot->content[0]->text . "\n\n";
```

### Explicit Step-by-Step Instructions

```php
<?php
# filename: examples/10-explicit-steps.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$code = <<<'PHP'
function processOrder($order) {
    $total = 0;
    foreach ($order['items'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    if ($order['discount']) {
        $total = $total - ($total * $order['discount']);
    }
    return $total;
}
PHP;

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Refactor this PHP function following these steps:

1. Identify issues (type safety, error handling, naming)
2. List specific improvements needed
3. Provide refactored code with PHPDoc
4. Explain the changes made

Code to refactor:
{$code}
PROMPT
    ]]
]);

echo $response->content[0]->text . "\n";
```

### Self-Consistency Chain-of-Thought

```php
<?php
# filename: src/Services/ChainOfThoughtSolver.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;

class ChainOfThoughtSolver
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function solve(string $problem, int $attempts = 3): array
    {
        $solutions = [];

        for ($i = 0; $i < $attempts; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 2048,
                'temperature' => 0.7, // Some variation
                'messages' => [[
                    'role' => 'user',
                    'content' => <<<PROMPT
{$problem}

Think through this step by step:
1. Analyze the problem
2. Consider different approaches
3. Evaluate trade-offs
4. Recommend solution
5. Provide implementation

Format your response with clear sections.
PROMPT
                ]]
            ]);

            $solutions[] = $response->content[0]->text;
        }

        return [
            'solutions' => $solutions,
            'consensus' => $this->findConsensus($solutions)
        ];
    }

    private function findConsensus(array $solutions): string
    {
        // Ask Claude to find consensus among solutions
        $combined = implode("\n\n--- SOLUTION ---\n\n", $solutions);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
I have multiple solutions to a problem. Identify the consensus or best approach:

{$combined}

Provide a concise summary of the best solution.
PROMPT
            ]]
        ]);

        return $response->content[0]->text;
    }
}
```

## Role-Playing and Personas

Assign Claude a specific role or expertise to improve response quality.

### Basic Role Assignment

```php
<?php
# filename: examples/11-roles.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Different roles produce different perspectives
$roles = [
    'senior_developer' => 'You are a senior PHP developer with 15 years of experience. Focus on maintainability and best practices.',
    'security_expert' => 'You are a security expert specializing in web applications. Focus on vulnerabilities and security implications.',
    'performance_engineer' => 'You are a performance optimization engineer. Focus on speed, efficiency, and scalability.',
];

$code = <<<'PHP'
function getUser($id) {
    return DB::query("SELECT * FROM users WHERE id = " . $id);
}
PHP;

foreach ($roles as $roleName => $rolePrompt) {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => $rolePrompt,
        'messages' => [[
            'role' => 'user',
            'content' => "Review this code:\n\n{$code}"
        ]]
    ]);

    echo "=== {$roleName} ===\n";
    echo $response->content[0]->text . "\n\n";
}
```

### Multi-Attribute Personas

```php
<?php
# filename: examples/12-personas.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$persona = <<<PERSONA
You are an expert PHP architect with these attributes:
- 10+ years experience with Laravel
- Specialized in e-commerce platforms
- Strong advocate for SOLID principles
- Prefers composition over inheritance
- Values type safety and static analysis
- Writes comprehensive tests

When reviewing code:
1. Focus on architecture and design patterns
2. Suggest improvements for maintainability
3. Provide specific, actionable feedback
4. Include code examples
PERSONA;

$code = <<<'PHP'
class OrderProcessor {
    public function process($order) {
        // Validate order
        if (!$order) return false;

        // Calculate total
        $total = 0;
        foreach ($order->items as $item) {
            $total += $item->price;
        }

        // Save to database
        DB::save($order);

        // Send email
        Mail::send($order->email, 'Order confirmed');

        return true;
    }
}
PHP;

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 3000,
    'system' => $persona,
    'messages' => [[
        'role' => 'user',
        'content' => "Review this OrderProcessor class and suggest architectural improvements:\n\n{$code}"
    ]]
]);

echo $response->content[0]->text . "\n";
```

### Persona Builder Pattern

```php
<?php
# filename: src/Services/PersonaBuilder.php
declare(strict_types=1);

namespace App\Services;

class PersonaBuilder
{
    private string $role = '';
    private int $experience = 0;
    private array $specializations = [];
    private array $principles = [];
    private array $guidelines = [];

    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function experience(int $years): self
    {
        $this->experience = $years;
        return $this;
    }

    public function specialization(string $specialization): self
    {
        $this->specializations[] = $specialization;
        return $this;
    }

    public function principle(string $principle): self
    {
        $this->principles[] = $principle;
        return $this;
    }

    public function guideline(string $guideline): self
    {
        $this->guidelines[] = $guideline;
        return $this;
    }

    public function build(): string
    {
        $parts = [];

        if ($this->role) {
            $experienceText = $this->experience > 0
                ? " with {$this->experience}+ years of experience"
                : '';

            $parts[] = "You are a {$this->role}{$experienceText}.";
        }

        if (!empty($this->specializations)) {
            $parts[] = "\nSpecializations:";
            foreach ($this->specializations as $spec) {
                $parts[] = "- {$spec}";
            }
        }

        if (!empty($this->principles)) {
            $parts[] = "\nCore principles you follow:";
            foreach ($this->principles as $principle) {
                $parts[] = "- {$principle}";
            }
        }

        if (!empty($this->guidelines)) {
            $parts[] = "\nWhen responding:";
            foreach ($this->guidelines as $i => $guideline) {
                $parts[] = ($i + 1) . ". {$guideline}";
            }
        }

        return implode("\n", $parts);
    }
}
```

**Usage:**

```php
<?php
# filename: examples/13-persona-builder.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\PersonaBuilder;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$persona = (new PersonaBuilder())
    ->role('Laravel Security Consultant')
    ->experience(12)
    ->specialization('OWASP Top 10 vulnerabilities')
    ->specialization('Authentication and authorization')
    ->specialization('Input validation and sanitization')
    ->principle('Security first, always')
    ->principle('Defense in depth')
    ->principle('Least privilege principle')
    ->guideline('Identify specific vulnerabilities')
    ->guideline('Explain the risk level')
    ->guideline('Provide secure code examples')
    ->guideline('Reference OWASP guidelines')
    ->build();

echo "Generated Persona:\n{$persona}\n\n";

$code = <<<'PHP'
Route::get('/user/{id}', function($id) {
    $user = DB::select("SELECT * FROM users WHERE id = " . $id);
    return view('profile', ['user' => $user]);
});
PHP;

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'system' => $persona,
    'messages' => [[
        'role' => 'user',
        'content' => "Review this Laravel route for security issues:\n\n{$code}"
    ]]
]);

echo "Security Review:\n";
echo $response->content[0]->text . "\n";
```

## Structured Output Formats

### JSON Output

```php
<?php
# filename: examples/14-json-output.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Extract information from this text and return as JSON:

"John Doe, senior developer at Acme Corp, can be reached at john@acme.com or 555-1234. He specializes in PHP and Laravel."

Required JSON structure:
{
    "name": "string",
    "title": "string",
    "company": "string",
    "email": "string",
    "phone": "string",
    "skills": ["array", "of", "strings"]
}

Return only valid JSON, no explanation.
PROMPT
    ]]
]);

$text = $response->content[0]->text;

// Extract JSON
if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
    $jsonText = $matches[1];
} else {
    $jsonText = $text;
}

$data = json_decode($jsonText, true);

echo "Extracted data:\n";
print_r($data);
```

### XML Output

```php
<?php
# filename: examples/15-xml-output.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Generate PHPUnit test configuration XML for a Laravel project with these requirements:
- Test suite: Unit tests in tests/Unit
- Test suite: Feature tests in tests/Feature
- Coverage: HTML report in coverage/
- Stop on failure: yes
- Colors: enabled

Return only valid XML, no explanation.
PROMPT
    ]]
]);

$xml = $response->content[0]->text;

// Extract XML from markdown if wrapped
if (preg_match('/```xml\s*(.*?)\s*```/s', $xml, $matches)) {
    $xml = $matches[1];
}

echo $xml . "\n";

// Validate XML
$doc = new DOMDocument();
if ($doc->loadXML($xml)) {
    echo "\n✓ Valid XML generated\n";
}
```

### CSV Output

```php
<?php
# filename: examples/16-csv-output.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Generate a CSV file with 5 sample PHP developers with these columns:
name, email, years_experience, primary_framework, hourly_rate

Include header row. Return only CSV, no explanation.
PROMPT
    ]]
]);

$csv = $response->content[0]->text;

// Extract CSV from markdown if wrapped
if (preg_match('/```csv\s*(.*?)\s*```/s', $csv, $matches)) {
    $csv = $matches[1];
} elseif (preg_match('/```\s*(.*?)\s*```/s', $csv, $matches)) {
    $csv = $matches[1];
}

echo $csv . "\n";

// Parse CSV
$lines = str_getcsv($csv, "\n");
$data = array_map('str_getcsv', $lines);

echo "\nParsed data:\n";
print_r($data);
```

### Markdown Output

```php
<?php
# filename: examples/17-markdown-output.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [[
        'role' => 'user',
        'content' => <<<PROMPT
Create technical documentation for a Laravel middleware that handles API rate limiting.

Format as Markdown with:
- Title (h1)
- Overview section
- Installation instructions
- Configuration section with code example
- Usage examples
- Best practices
PROMPT
    ]]
]);

$markdown = $response->content[0]->text;

echo $markdown . "\n";

// Optionally save to file
file_put_contents('middleware-docs.md', $markdown);
echo "\n✓ Saved to middleware-docs.md\n";
```

## Prompt Templates

### Template Engine

```php
<?php
# filename: src/Services/PromptTemplate.php
declare(strict_types=1);

namespace App\Services;

class PromptTemplate
{
    public function __construct(
        private readonly string $template
    ) {}

    public function render(array $variables): string
    {
        $result = $this->template;

        foreach ($variables as $key => $value) {
            $placeholder = "{{" . $key . "}}";
            $result = str_replace($placeholder, $value, $result);
        }

        return $result;
    }

    public static function codeReview(): self
    {
        return new self(<<<'TEMPLATE'
You are a {{role}} with {{experience}} years of experience.

Review the following {{language}} code for:
1. {{aspect1}}
2. {{aspect2}}
3. {{aspect3}}

Code to review:
{{code}}

Provide:
- List of issues found
- Severity of each issue (critical, high, medium, low)
- Specific recommendations
- Refactored code if needed
TEMPLATE
        );
    }

    public static function dataExtraction(): self
    {
        return new self(<<<'TEMPLATE'
Extract the following information from the text below:
{{fields}}

Return as JSON with this structure:
{{json_structure}}

Text to analyze:
--- START ---
{{text}}
--- END ---

Return only valid JSON, no explanation.
TEMPLATE
        );
    }

    public static function documentation(): self
    {
        return new self(<<<'TEMPLATE'
Generate {{doc_type}} documentation for this {{code_type}}:

{{code}}

Include:
{{requirements}}

Format: {{format}}
TEMPLATE
        );
    }
}
```

**Usage:**

```php
<?php
# filename: examples/18-templates.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\PromptTemplate;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Code review template
$template = PromptTemplate::codeReview();

$prompt = $template->render([
    'role' => 'Senior Laravel Developer',
    'experience' => '10',
    'language' => 'PHP',
    'aspect1' => 'Security vulnerabilities',
    'aspect2' => 'Performance issues',
    'aspect3' => 'Code maintainability',
    'code' => 'function getUser($id) { return DB::query("SELECT * FROM users WHERE id = " . $id); }'
]);

echo "Generated prompt:\n{$prompt}\n\n";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

echo "Response:\n{$response->content[0]->text}\n";
```

### Prompt Library

```php
<?php
# filename: src/Services/PromptLibrary.php
declare(strict_types=1);

namespace App\Services;

class PromptLibrary
{
    private const PROMPTS = [
        'code_review' => [
            'template' => 'Review this {{language}} code for {{focus}}:\n\n{{code}}',
            'defaults' => ['language' => 'PHP', 'focus' => 'best practices']
        ],
        'bug_fix' => [
            'template' => 'Debug this {{language}} code. The error is: {{error}}\n\nCode:\n{{code}}',
            'defaults' => ['language' => 'PHP']
        ],
        'optimization' => [
            'template' => 'Optimize this {{language}} code for {{metric}}:\n\n{{code}}',
            'defaults' => ['language' => 'PHP', 'metric' => 'performance']
        ],
        'documentation' => [
            'template' => 'Generate {{doc_type}} for this {{language}} {{code_type}}:\n\n{{code}}',
            'defaults' => ['doc_type' => 'PHPDoc', 'language' => 'PHP', 'code_type' => 'function']
        ],
        'test_generation' => [
            'template' => 'Generate {{test_framework}} tests for this {{language}} {{code_type}}:\n\n{{code}}',
            'defaults' => ['test_framework' => 'PHPUnit', 'language' => 'PHP', 'code_type' => 'class']
        ],
    ];

    public static function get(string $name, array $variables = []): string
    {
        if (!isset(self::PROMPTS[$name])) {
            throw new \InvalidArgumentException("Prompt template '{$name}' not found");
        }

        $prompt = self::PROMPTS[$name];
        $merged = array_merge($prompt['defaults'] ?? [], $variables);

        return self::renderTemplate($prompt['template'], $merged);
    }

    private static function renderTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }

        return $template;
    }

    public static function list(): array
    {
        return array_keys(self::PROMPTS);
    }
}
```

**Usage:**

```php
<?php
# filename: examples/19-prompt-library.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\PromptLibrary;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// List available prompts
echo "Available prompts:\n";
foreach (PromptLibrary::list() as $name) {
    echo "- {$name}\n";
}
echo "\n";

// Use a prompt
$code = <<<'PHP'
function calculateTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'];
    }
    return $total;
}
PHP;

$prompt = PromptLibrary::get('test_generation', [
    'code' => $code,
    'test_framework' => 'PHPUnit',
    'code_type' => 'function'
]);

echo "Generated prompt:\n{$prompt}\n\n";

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [['role' => 'user', 'content' => $prompt]]
]);

echo "Generated tests:\n{$response->content[0]->text}\n";
```

## Common Pitfalls and Solutions

### Pitfall 1: Ambiguous Instructions

```php
<?php
# ✗ Bad: Ambiguous
$bad = "Make this code better.";

# ✓ Good: Specific
$good = "Refactor this code to: 1) Add type hints, 2) Extract magic numbers to constants, 3) Add error handling for null values.";
```

### Pitfall 2: Missing Context

```php
<?php
# ✗ Bad: No context
$bad = "Is this secure?";

# ✓ Good: With context
$good = "Review this user authentication function for security vulnerabilities. The app handles financial transactions and must comply with PCI DSS.";
```

### Pitfall 3: Unclear Output Format

```php
<?php
# ✗ Bad: Vague format
$bad = "List the benefits.";

# ✓ Good: Specific format
$good = "List 5 benefits of using PHP 8.3. Format as JSON array with fields: benefit (string), impact (string), example (string).";
```

### Pitfall 4: Too Many Instructions

```php
<?php
# ✗ Bad: Overwhelming
$bad = "Analyze the code, identify bugs, refactor it, write tests, generate documentation, suggest performance improvements, check security, and create a deployment guide.";

# ✓ Good: Focused
$good = "Analyze this code for security vulnerabilities. Focus specifically on: SQL injection, XSS, CSRF, and authentication issues.";
```

### Pitfall 5: Inconsistent Examples

```php
<?php
# ✗ Bad: Inconsistent examples
$bad = <<<PROMPT
Examples:
Input: "Great product!" → positive
Input: "It's terrible" → Output: negative
Input: "Okay" -> Classification: neutral

Classify: "I love it"
PROMPT;

# ✓ Good: Consistent format
$good = <<<PROMPT
Examples:
Input: "Great product!"
Output: positive

Input: "It's terrible"
Output: negative

Input: "Okay"
Output: neutral

Classify this:
Input: "I love it"
Output:
PROMPT;
```

## Exercises

### Exercise 1: Dynamic Prompt Builder

Build a flexible prompt building system:

```php
<?php
class DynamicPromptBuilder
{
    public function forTask(string $task): self
    {
        // TODO: Set task type
        return $this;
    }

    public function withContext(string $context): self
    {
        // TODO: Add context
        return $this;
    }

    public function withExamples(array $examples): self
    {
        // TODO: Add few-shot examples
        return $this;
    }

    public function withFormat(string $format): self
    {
        // TODO: Specify output format
        return $this;
    }

    public function build(): string
    {
        // TODO: Construct final prompt
    }
}
```

### Exercise 2: Prompt Validator

Create a validator to check prompt quality:

```php
<?php
class PromptValidator
{
    public function validate(string $prompt): array
    {
        // TODO: Check for clarity
        // TODO: Check for specificity
        // TODO: Check for context
        // TODO: Check for output format specification
        // TODO: Return validation results with suggestions
    }
}
```

### Exercise 3: A/B Prompt Tester

Build a system to test prompt variations:

```php
<?php
class PromptABTester
{
    public function test(array $prompts, string $input): array
    {
        // TODO: Run each prompt variation
        // TODO: Collect responses
        // TODO: Compare quality metrics
        // TODO: Return best performing prompt
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use builder pattern with method chaining, store components in properties, assemble in build() method using string concatenation or template engine.

**Exercise 2**: Check for question marks, specific requirements, word count, presence of examples. Provide specific improvement suggestions for each issue found.

**Exercise 3**: Send same input to Claude multiple times with different prompts, measure response length/quality, compare token usage, use Claude to judge which response is better.

</details>

## Troubleshooting

**Inconsistent responses?**
- Lower temperature (0.0-0.3) for deterministic outputs
- Add more specific instructions
- Use few-shot examples to guide behavior
- Check for ambiguous language in prompt

**Response doesn't match format?**
- Explicitly state format requirements
- Provide example of desired output
- Add "Return only [format], no explanation"
- Use delimiters to separate instructions from data

**Claude refuses to follow instructions?**
- Check if instructions conflict with safety guidelines
- Rephrase to be clearer and more specific
- Break complex tasks into smaller steps
- Provide context for why you need the output

**Poor quality outputs?**
- Add relevant context
- Use appropriate model (Opus for complex tasks)
- Implement chain-of-thought reasoning
- Provide few-shot examples

## Key Takeaways

- ✓ **Clarity is crucial** - Be specific about what you want
- ✓ **Context matters** - Provide relevant background information
- ✓ **Examples guide** - Few-shot learning improves accuracy
- ✓ **Format explicitly** - Always specify desired output format
- ✓ **Roles work** - Persona assignment improves responses
- ✓ **Chain-of-thought** - Helps with complex reasoning tasks
- ✓ **Templates save time** - Reusable prompts ensure consistency
- ✓ **Test variations** - A/B test prompts to find what works best
- ✓ **Delimiters help** - Separate instructions from data clearly
- ✓ **Temperature matters** - Lower for consistency, higher for creativity

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="05"
  label="You've mastered prompt engineering fundamentals!"
/>

---

Continue to [Chapter 06: System Prompts and Configuration](/series/claude-php-developers/chapters/06-system-prompts) to learn advanced configuration techniques.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 05 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-05)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-05
composer install
cp .env.example .env
# Add your API key to .env
php examples/01-clarity.php
```
