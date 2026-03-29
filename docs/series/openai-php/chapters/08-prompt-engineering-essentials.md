---
title: "08: Prompt Engineering Essentials"
description: "Master advanced prompt engineering techniques including few-shot learning, chain-of-thought, and optimization strategies"
series: "openai-php"
chapter: 8
order: 8
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/07-chat-completions-api-fundamentals"
  - "Understanding of AI model capabilities"
---

![Prompt Engineering Essentials](/images/openai-php/chapter-08-prompt-engineering-hero-full.webp)

[Home](/series/openai-php) > [Chapter 07](/series/openai-php/chapters/07-chat-completions-api-fundamentals) > Prompt Engineering Essentials

# Chapter 08: Prompt Engineering Essentials

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">75-90 minutes</span>

## Overview

Prompt engineering is the art and science of crafting inputs that coax the best possible outputs from AI models. The same model can produce genius-level insights or nonsense depending entirely on how you prompt it. This isn't just about getting answers—it's about getting the *right* answers consistently.

The difference between amateur and expert AI integration often comes down to prompt engineering. A well-engineered prompt can eliminate hallucinations, ensure consistent formatting, and unlock capabilities you didn't know the model had. A poor prompt wastes tokens, produces unreliable outputs, and frustrates users.

In this comprehensive chapter, you'll master the fundamental techniques used by professionals: zero-shot, one-shot, and few-shot prompting; chain-of-thought reasoning; prompt templates; and systematic optimization. You'll learn not just what works, but why it works, so you can adapt these techniques to any use case.

This is a PRIORITY chapter because these skills directly impact every OpenAI application you build. Invest time here—it pays dividends throughout your AI development journey.

## What You'll Learn

- 🎯 **Prompting Fundamentals**: Core principles for effective prompts
- 📚 **Few-Shot Learning**: Teach through examples for better outputs
- 🧠 **Chain-of-Thought**: Enable step-by-step reasoning
- 🔨 **Prompt Templates**: Build reusable, maintainable prompts
- 📊 **Output Control**: Format responses exactly as needed
- 🎨 **Creativity vs Precision**: Balance exploration and accuracy
- 🔬 **Testing & Optimization**: Systematically improve prompts
- ⚠️ **Common Pitfalls**: Avoid mistakes that waste tokens and quality

## Prerequisites

- ✅ Completed Chapters 01-07
- ✅ Understanding of AI capabilities and limitations
- ✅ Experience with the Chat Completions API
- ✅ Familiarity with different output formats

---

## Prompt Engineering Principles

### The Six Core Principles

```php
<?php

/**
 * Core prompt engineering principles
 */

class PromptPrinciples
{
    /**
     * 1. BE SPECIFIC - Vague prompts get vague answers
     */
    public static function example_vague(): string
    {
        return "Tell me about PHP.";
        // Too broad - could be anything about PHP
    }

    public static function example_specific(): string
    {
        return "Explain the difference between PDO and MySQLi for database " .
               "connections in PHP, focusing on security and ease of use. " .
               "Provide code examples for each.";
    }

    /**
     * 2. PROVIDE CONTEXT - Give the model what it needs to understand
     */
    public static function example_no_context(): string
    {
        return "Is this code secure?";
        // What code? Secure against what?
    }

    public static function example_with_context(): string
    {
        return "I'm building a user authentication system for a PHP web app. " .
               "Is the following password hashing code secure against " .
               "brute force attacks?\n\n[code here]";
    }

    /**
     * 3. DEFINE OUTPUT FORMAT - Specify exactly what you want
     */
    public static function example_undefined_format(): string
    {
        return "Give me PHP best practices.";
    }

    public static function example_defined_format(): string
    {
        return "List 5 PHP security best practices. For each:\n" .
               "1. State the practice\n" .
               "2. Explain why it matters (one sentence)\n" .
               "3. Provide a code example\n\n" .
               "Format as numbered list.";
    }

    /**
     * 4. SET CONSTRAINTS - Define boundaries and limitations
     */
    public static function example_unconstrained(): string
    {
        return "Write about design patterns.";
    }

    public static function example_constrained(): string
    {
        return "Explain the Singleton pattern in PHP. " .
               "Requirements:\n" .
               "- Maximum 100 words\n" .
               "- Include one code example\n" .
               "- Mention when NOT to use it\n" .
               "- Use simple language for intermediate developers";
    }

    /**
     * 5. USE DELIMITERS - Separate different parts clearly
     */
    public static function example_with_delimiters(string $code): string
    {
        return "Review this PHP code for security issues:\n\n" .
               "###CODE START###\n" .
               $code . "\n" .
               "###CODE END###\n\n" .
               "Provide feedback in this format:\n" .
               "ISSUES: [list any security problems]\n" .
               "FIXES: [specific code improvements]\n" .
               "SEVERITY: [low/medium/high]";
    }

    /**
     * 6. GIVE EXAMPLES - Show, don't just tell
     */
    public static function example_with_examples(): string
    {
        return "Convert these function names from snake_case to camelCase:\n\n" .
               "Examples:\n" .
               "get_user_data → getUserData\n" .
               "calculate_total_price → calculateTotalPrice\n\n" .
               "Now convert:\n" .
               "find_by_email\n" .
               "update_user_profile\n" .
               "delete_old_records";
    }
}
```

---

## Zero-Shot, One-Shot, Few-Shot Prompting

### Zero-Shot Prompting

No examples provided—the model works from instructions alone.

```php
<?php

class ZeroShotPrompt
{
    public function analyze(string $text): array
    {
        $prompt = "Analyze the sentiment of this text. " .
                  "Classify as: positive, negative, or neutral.\n\n" .
                  "Text: {$text}\n\n" .
                  "Sentiment:";

        return [
            'role' => 'user',
            'content' => $prompt
        ];
    }
}

// Usage
$prompt = new ZeroShotPrompt();
$message = $prompt->analyze("I absolutely love this new PHP framework!");

// Model relies solely on understanding of "sentiment analysis"
```

### One-Shot Prompting

Provide one example to demonstrate the pattern.

```php
<?php

class OneShotPrompt
{
    public function extractData(string $text): array
    {
        $prompt = "Extract structured data from customer inquiries.\n\n" .
                  "Example:\n" .
                  "Input: \"Hi, I'm John Doe from Acme Corp. My email is john@acme.com. " .
                  "I need help with invoice #12345.\"\n" .
                  "Output:\n" .
                  "{\n" .
                  "  \"name\": \"John Doe\",\n" .
                  "  \"company\": \"Acme Corp\",\n" .
                  "  \"email\": \"john@acme.com\",\n" .
                  "  \"invoice\": \"12345\"\n" .
                  "}\n\n" .
                  "Now extract from:\n" .
                  "Input: \"{$text}\"\n" .
                  "Output:";

        return [
            'role' => 'user',
            'content' => $prompt
        ];
    }
}
```

### Few-Shot Prompting

Multiple examples for complex patterns.

```php
<?php

class FewShotPrompt
{
    private array $examples = [];

    public function addExample(string $input, string $output): self
    {
        $this->examples[] = [
            'input' => $input,
            'output' => $output
        ];
        return $this;
    }

    public function generate(string $input, string $task): string
    {
        $prompt = "{$task}\n\n";

        // Add examples
        foreach ($this->examples as $i => $example) {
            $prompt .= "Example " . ($i + 1) . ":\n";
            $prompt .= "Input: {$example['input']}\n";
            $prompt .= "Output: {$example['output']}\n\n";
        }

        // Add the actual input
        $prompt .= "Now do the same for:\n";
        $prompt .= "Input: {$input}\n";
        $prompt .= "Output:";

        return $prompt;
    }
}

// Usage: Code documentation generator
$fewShot = new FewShotPrompt();

$fewShot->addExample(
    'function add($a, $b) { return $a + $b; }',
    '/**
 * Adds two numbers together
 * @param int|float $a First number
 * @param int|float $b Second number
 * @return int|float Sum of a and b
 */'
);

$fewShot->addExample(
    'function getUser($id) { return User::find($id); }',
    '/**
 * Retrieves a user by ID
 * @param int $id User identifier
 * @return User|null User object or null if not found
 */'
);

$prompt = $fewShot->generate(
    'function deletePost($postId) { Post::destroy($postId); }',
    'Generate PHPDoc comments for these functions'
);

echo $prompt;
```

---

## Chain-of-Thought Prompting

### Basic Chain-of-Thought

Encourage step-by-step reasoning for complex problems.

```php
<?php

class ChainOfThought
{
    public static function solve(string $problem): array
    {
        $prompt = "Solve this problem step by step. " .
                  "Show your reasoning at each step.\n\n" .
                  "Problem: {$problem}\n\n" .
                  "Solution:\n" .
                  "Step 1:";

        return [
            'role' => 'user',
            'content' => $prompt
        ];
    }

    public static function analyze(string $code, string $question): array
    {
        $prompt = "Analyze this PHP code and answer the question. " .
                  "Break down your analysis into steps.\n\n" .
                  "Code:\n```php\n{$code}\n```\n\n" .
                  "Question: {$question}\n\n" .
                  "Analysis:\n" .
                  "1. First, I'll examine...";

        return [
            'role' => 'user',
            'content' => $prompt
        ];
    }

    public static function debug(string $error, string $code): array
    {
        $prompt = "Debug this error using a systematic approach:\n\n" .
                  "Error: {$error}\n\n" .
                  "Code:\n```php\n{$code}\n```\n\n" .
                  "Debugging process:\n" .
                  "1. Understanding the error:\n" .
                  "2. Identifying the cause:\n" .
                  "3. Proposed solution:\n" .
                  "4. Explanation:";

        return [
            'role' => 'user',
            'content' => $prompt
        ];
    }
}

// Usage
$problem = "A function processes 1000 database records. " .
           "It takes 0.1 seconds per record. " .
           "How can we optimize it to run in under 10 seconds total?";

$messages = [
    ['role' => 'system', 'content' => 'You are an expert PHP performance consultant.'],
    ChainOfThought::solve($problem)
];
```

### Self-Consistency Chain-of-Thought

Generate multiple reasoning paths and choose the most consistent answer.

```php
<?php

class SelfConsistentCOT
{
    private \OpenAI\Client $client;
    private int $samples;

    public function __construct(string $apiKey, int $samples = 3)
    {
        $this->client = \OpenAI::client($apiKey);
        $this->samples = $samples;
    }

    public function solve(string $problem): array
    {
        $prompt = "Solve this problem step by step:\n\n{$problem}\n\n" .
                  "Show your reasoning and provide a final answer.";

        $responses = [];

        // Generate multiple reasoning paths
        for ($i = 0; $i < $this->samples; $i++) {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7 + ($i * 0.1), // Vary temperature
            ]);

            $responses[] = $response->choices[0]->message->content;
        }

        // Aggregate responses
        $aggregationPrompt = "I received these different solutions to a problem:\n\n";

        foreach ($responses as $i => $response) {
            $aggregationPrompt .= "Solution " . ($i + 1) . ":\n{$response}\n\n";
        }

        $aggregationPrompt .= "Analyze these solutions and provide:\n" .
                              "1. The most likely correct answer\n" .
                              "2. Confidence level (low/medium/high)\n" .
                              "3. Reasoning for your choice";

        $final = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $aggregationPrompt]
            ],
            'temperature' => 0.3,
        ]);

        return [
            'individual_responses' => $responses,
            'final_answer' => $final->choices[0]->message->content,
        ];
    }
}
```

---

## Prompt Templates

### Template System

```php
<?php

/**
 * Flexible prompt template system
 */

class PromptTemplate
{
    private string $template;
    private array $variables = [];

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function set(string $key, mixed $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function setMultiple(array $variables): self
    {
        $this->variables = array_merge($this->variables, $variables);
        return $this;
    }

    public function render(): string
    {
        $output = $this->template;

        foreach ($this->variables as $key => $value) {
            $placeholder = "{{" . $key . "}}";
            $output = str_replace($placeholder, $value, $output);
        }

        return $output;
    }

    public static function load(string $name): self
    {
        return new self(self::getTemplate($name));
    }

    private static function getTemplate(string $name): string
    {
        $templates = [
            'code_review' => <<<'TEMPLATE'
Review this {{language}} code for:
- Code quality
- Best practices
- Security issues
- Performance concerns

Code:
```{{language}}
{{code}}
```

Focus areas: {{focus_areas}}

Provide feedback in this format:
STRENGTHS: [what's good]
ISSUES: [what needs improvement]
RECOMMENDATIONS: [specific improvements]
TEMPLATE,

            'documentation' => <<<'TEMPLATE'
Generate comprehensive documentation for this {{language}} {{type}}.

{{code}}

Include:
1. Description: {{description_length}} sentence summary
2. Parameters: Type and purpose of each
3. Return value: What it returns
4. Example usage: Practical example
5. Notes: Any important considerations

Format as {{format}}.
TEMPLATE,

            'test_generation' => <<<'TEMPLATE'
Generate {{test_framework}} unit tests for this {{language}} code:

{{code}}

Requirements:
- Test {{coverage}} code paths
- Include edge cases
- Use meaningful test names
- Add assertions for: {{assertions}}
- Follow {{test_framework}} best practices
TEMPLATE,

            'translation' => <<<'TEMPLATE'
Translate this code from {{source_language}} to {{target_language}}.

Original code:
```{{source_language}}
{{code}}
```

Requirements:
- Maintain functionality
- Follow {{target_language}} conventions
- Optimize for {{target_language}} idioms
- Add comments explaining translations
TEMPLATE,

            'explanation' => <<<'TEMPLATE'
Explain this {{language}} code to a {{audience}} developer.

Code:
```{{language}}
{{code}}
```

Explanation style: {{style}}
Detail level: {{detail_level}}
Include: {{include_sections}}

Break down the explanation into:
1. Overview: What the code does
2. Step-by-step: How it works
3. Key concepts: Important ideas used
4. Use cases: When to use this pattern
TEMPLATE,
        ];

        return $templates[$name] ?? throw new \InvalidArgumentException("Template not found: $name");
    }
}

// Usage: Code review
$review = PromptTemplate::load('code_review')
    ->set('language', 'PHP')
    ->set('code', $codeToReview)
    ->set('focus_areas', 'security and performance')
    ->render();

// Usage: Documentation generation
$docs = PromptTemplate::load('documentation')
    ->setMultiple([
        'language' => 'PHP',
        'type' => 'function',
        'code' => $functionCode,
        'description_length' => '2-3',
        'format' => 'PHPDoc',
    ])
    ->render();

// Usage: Test generation
$tests = PromptTemplate::load('test_generation')
    ->setMultiple([
        'test_framework' => 'PHPUnit',
        'language' => 'PHP',
        'code' => $codeToTest,
        'coverage' => 'all',
        'assertions' => 'correctness, edge cases, error handling',
    ])
    ->render();
```

---

## Output Format Control

### Structured Output Prompts

```php
<?php

class OutputFormatter
{
    /**
     * Request JSON output
     */
    public static function asJson(array $schema): string
    {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);

        return "Respond with valid JSON matching this exact structure:\n" .
               $schemaJson . "\n\n" .
               "Return ONLY the JSON, no additional text.";
    }

    /**
     * Request markdown output
     */
    public static function asMarkdown(array $sections): string
    {
        $sectionList = implode("\n", array_map(
            fn($s) => "- {$s}",
            $sections
        ));

        return "Format your response as markdown with these sections:\n" .
               $sectionList . "\n\n" .
               "Use proper markdown formatting (headers, lists, code blocks, etc.).";
    }

    /**
     * Request table output
     */
    public static function asTable(array $columns): string
    {
        $columnList = implode(' | ', $columns);

        return "Format your response as a markdown table with these columns:\n" .
               $columnList . "\n\n" .
               "Include a header row and at least 3 data rows.";
    }

    /**
     * Request code only
     */
    public static function codeOnly(string $language): string
    {
        return "Provide ONLY the {$language} code, no explanations.\n" .
               "Format as a code block with proper syntax highlighting.\n" .
               "Do not include any text before or after the code.";
    }

    /**
     * Request step-by-step
     */
    public static function stepByStep(int $maxSteps): string
    {
        return "Provide a step-by-step guide with maximum {$maxSteps} steps.\n" .
               "Format:\n" .
               "Step 1: [Title]\n" .
               "[Detailed description]\n\n" .
               "Step 2: [Title]\n" .
               "[Detailed description]\n" .
               "etc.";
    }
}

// Usage examples
$jsonPrompt = OutputFormatter::asJson([
    'function_name' => 'string',
    'parameters' => [
        ['name' => 'string', 'type' => 'string']
    ],
    'returns' => 'string',
    'complexity' => 'O(n) notation',
]);

$markdownPrompt = OutputFormatter::asMarkdown([
    '## Overview',
    '## Installation',
    '## Usage',
    '## Examples',
]);
```

---

## Prompt Optimization

### A/B Testing Prompts

```php
<?php

class PromptTester
{
    private \OpenAI\Client $client;
    private array $results = [];

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function comparePrompts(
        array $prompts,
        string $model = 'gpt-3.5-turbo',
        int $samples = 3
    ): array {
        foreach ($prompts as $name => $prompt) {
            $this->results[$name] = [
                'prompt' => $prompt,
                'responses' => [],
                'avg_tokens' => 0,
                'avg_time' => 0,
            ];

            for ($i = 0; $i < $samples; $i++) {
                $start = microtime(true);

                $response = $this->client->chat()->create([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ]);

                $duration = (microtime(true) - $start) * 1000;

                $this->results[$name]['responses'][] = [
                    'content' => $response->choices[0]->message->content,
                    'tokens' => $response->usage->totalTokens,
                    'time_ms' => $duration,
                ];

                $this->results[$name]['avg_tokens'] += $response->usage->totalTokens;
                $this->results[$name]['avg_time'] += $duration;
            }

            $this->results[$name]['avg_tokens'] /= $samples;
            $this->results[$name]['avg_time'] /= $samples;
        }

        return $this->results;
    }

    public function getBestPrompt(string $metric = 'tokens'): string
    {
        $best = null;
        $bestValue = $metric === 'tokens' ? PHP_INT_MAX : PHP_INT_MIN;

        foreach ($this->results as $name => $result) {
            $value = $result["avg_{$metric}"];

            if ($metric === 'tokens' && $value < $bestValue) {
                $best = $name;
                $bestValue = $value;
            }
        }

        return $best ?? '';
    }

    public function printComparison(): void
    {
        echo "Prompt Comparison Results\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach ($this->results as $name => $result) {
            echo "Prompt: {$name}\n";
            echo "Average tokens: " . round($result['avg_tokens']) . "\n";
            echo "Average time: " . round($result['avg_time']) . "ms\n";
            echo "\nSample response:\n";
            echo $result['responses'][0]['content'] . "\n";
            echo "\n" . str_repeat('-', 80) . "\n\n";
        }
    }
}

// Usage
$tester = new PromptTester($_ENV['OPENAI_API_KEY']);

$prompts = [
    'verbose' => "Please explain to me in detail what dependency injection is in PHP and why it is considered a best practice in modern application development.",

    'concise' => "Explain dependency injection in PHP. Include: definition, benefits, example.",

    'structured' => "Explain dependency injection in PHP:\n1. Definition (1 sentence)\n2. Key benefit (1 sentence)\n3. Code example",
];

$results = $tester->comparePrompts($prompts, samples: 3);
$tester->printComparison();

echo "Best prompt (by tokens): " . $tester->getBestPrompt('tokens') . "\n";
```

---

## Common Pitfalls

```php
<?php

/**
 * Common prompt engineering mistakes and fixes
 */

class PromptPitfalls
{
    /**
     * PITFALL: Assuming the model "knows" context
     */
    public static function bad_assumption(): string
    {
        return "Is it secure?";  // What is "it"?
    }

    public static function good_explicit(): string
    {
        return "Is this password hashing implementation secure:\n" .
               "[code here]";
    }

    /**
     * PITFALL: Asking multiple unrelated questions
     */
    public static function bad_multiple(): string
    {
        return "Explain MVC, tell me about Laravel, and how do I deploy to AWS?";
    }

    public static function good_focused(): string
    {
        return "Explain the MVC pattern in the context of Laravel applications.";
    }

    /**
     * PITFALL: Ambiguous instructions
     */
    public static function bad_ambiguous(): string
    {
        return "Make this code better.";  // Better how?
    }

    public static function good_specific(): string
    {
        return "Optimize this code for:\n" .
               "1. Reduced memory usage\n" .
               "2. Better error handling\n" .
               "3. PSR-12 compliance";
    }

    /**
     * PITFALL: Not specifying output format
     */
    public static function bad_format(): string
    {
        return "Give me PHP best practices.";
    }

    public static function good_format(): string
    {
        return "Provide 5 PHP security best practices.\n" .
               "Format: [Practice] - [One sentence why] - [Code example]";
    }

    /**
     * PITFALL: Contradictory instructions
     */
    public static function bad_contradictory(): string
    {
        return "Give me a comprehensive detailed explanation in under 50 words.";
    }

    public static function good_consistent(): string
    {
        return "Provide a brief explanation (50-100 words) covering the key points.";
    }
}
```

---

## Exercises

### Exercise 1: Prompt Optimization

Take this prompt:
"Write code for a login system"

Optimize it through:
1. Adding specificity
2. Defining requirements
3. Specifying output format
4. Including constraints

### Exercise 2: Few-Shot Template Creator

Build a system that:
1. Accepts any task
2. Generates optimal few-shot examples
3. Tests different example counts (2, 3, 5)
4. Measures quality vs token cost

### Exercise 3: Prompt Analyzer

Create a tool that:
1. Analyzes prompt clarity
2. Identifies ambiguous language
3. Suggests improvements
4. Estimates expected token usage

### Exercise 4: Dynamic Prompt Builder

Implement a builder that:
1. Adapts prompts based on model
2. Adjusts complexity for different audiences
3. Optimizes for cost or quality
4. A/B tests automatically

---

## Key Takeaways

- ✅ Specificity and clarity are more important than length
- ✅ Few-shot examples dramatically improve output quality
- ✅ Chain-of-thought enables complex reasoning
- ✅ Prompt templates ensure consistency and maintainability
- ✅ Output format should be explicitly specified
- ✅ Test and optimize prompts systematically
- ✅ Context and examples are more powerful than instructions alone
- ✅ Different tasks require different prompting strategies

---

## Next Steps

With prompt engineering mastered, you're ready to explore streaming responses!

👉 **[Chapter 09: Streaming Responses](/series/openai-php/chapters/09-streaming-responses)**

In the next chapter, you'll learn:
- Server-sent events (SSE) implementation
- Real-time response processing
- Building responsive UIs
- Stream error handling

---

[← Previous: Chapter 07](/series/openai-php/chapters/07-chat-completions-api-fundamentals) | [Next: Chapter 09 →](/series/openai-php/chapters/09-streaming-responses)
