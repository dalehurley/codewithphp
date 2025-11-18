---
title: "50: Reflection & Self-Critique"
description: "Build reflection & self-critique with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 50
order: 50
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/49-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![50: Reflection & Self-Critique](/images/claude-php/chapter-50-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 50</span>
</div>

# Chapter 50: Reflection & Self-Critique

## Overview

This chapter is based on Tutorial 10 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 45 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 49** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement reflection loops for self-evaluation
- Build agents that critique their own work
- Use iterative refinement to improve outputs
- Define quality criteria for different tasks
- Apply reflection to code, writing, and decisions
- Combine reflection with other patterns
- Understand when reflection adds value vs overhead

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Reflection enables agents to evaluate their own outputs, identify issues, and iteratively improve results. This meta-cognitive capability is key to building high-quality, self-correcting AI systems.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement reflection loops for self-evaluation
- Build agents that critique their own work
- Use iterative refinement to improve outputs
- Define quality criteria for different tasks
- Apply reflection to code, writing, and decisions
- Combine reflection with other patterns
- Understand when reflection adds value vs overhead

### 🏗️ What We're Building

We'll implement reflection agents that:

1. **Generate** - Create initial output
2. **Reflect** - Evaluate quality and identify issues  
3. **Refine** - Improve based on reflection
4. **Iterate** - Repeat until quality threshold met
5. **Compare** - Show before/after improvements

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 9: Plan-and-Execute](../09-plan-and-execute/)
- Understanding of quality assessment
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is Reflection?

Reflection is the ability to examine and evaluate one's own outputs, thoughts, and processes. In AI agents, reflection enables:

- **Self-evaluation** - Assess quality of outputs
- **Error detection** - Find mistakes and issues
- **Iterative improvement** - Refine through multiple passes
- **Learning** - Understand what works and what doesn't

#### Simple Example

**Without Reflection:**
```
Task: Write a function to reverse a string
Output: function reverse($s) { return strrev($s); }
Done!
```

**With Reflection:**
```
Task: Write a function to reverse a string

Generate:
function reverse($s) { return strrev($s); }

Reflect:
- Uses built-in function (good)
- No input validation (issue)
- No documentation (issue)
- No edge case handling (issue)

Refine:
/**
 * Reverses a string safely
 * @param string|null $s Input string
 * @return string Reversed string
 */
function reverse(?string $s): string {
    if ($s === null || $s === '') {
        return '';
    }
    return strrev($s);
}

Better!
```

### 🔑 Key Concepts

#### 1. Generate-Reflect-Refine Loop

The core pattern:

```php
$output = generate($task);

for ($iteration = 1; $iteration <= $maxIterations; $iteration++) {
    $reflection = reflect($output, $criteria);
    
    $score = extractScore($reflection);
    
    if ($score >= $qualityThreshold) {
        echo "Quality threshold reached!\n";
        break;
    }
    
    $issues = extractIssues($reflection);
    $output = refine($output, $issues);
}

return $output;
```

#### 2. Quality Criteria

Define what "good" means for your task:

```php
$criteria = [
    'correctness' => [
        'weight' => 0.4,
        'description' => 'Is the solution correct and accurate?'
    ],
    'completeness' => [
        'weight' => 0.3,
        'description' => 'Are all requirements addressed?'
    ],
    'clarity' => [
        'weight' => 0.2,
        'description' => 'Is it easy to understand?'
    ],
    'efficiency' => [
        'weight' => 0.1,
        'description' => 'Is it reasonably optimal?'
    ]
];
```

#### 3. Reflection Prompts

Different types of reflection questions:

**Quality Assessment:**
```
"Evaluate this output on a scale of 1-10 for:
- Correctness (1-10)
- Completeness (1-10)
- Clarity (1-10)
Overall score and reasoning?"
```

**Issue Identification:**
```
"Review this carefully and identify:
1. Errors or mistakes
2. Missing information
3. Unclear explanations
4. Potential improvements"
```

**Comparative Analysis:**
```
"Compare this output to best practices:
- What aligns with standards?
- What deviates from best practices?
- What could be better?"
```

#### 4. Targeted Refinement

Fix specific issues:

```php
$refinementPrompt = "Improve this output by:\n";
foreach ($issues as $issue) {
    $refinementPrompt .= "- {$issue['type']}: {$issue['description']}\n";
}
$refinementPrompt .= "\nOriginal output:\n{$output}";
```

### 💡 Reflection Implementations

#### Basic Reflection Function

```php
function reflectAndRefine($client, $task, $initialOutput, $maxIterations = 3) {
    $output = $initialOutput;
    $history = [];
    
    for ($i = 0; $i < $maxIterations; $i++) {
        echo "Iteration " . ($i + 1) . "\n";
        echo str_repeat("-", 60) . "\n";
        
        // Reflect
        $reflectionPrompt = "Task: {$task}\n\n" .
                           "Current output:\n{$output}\n\n" .
                           "Evaluate this output:\n" .
                           "1. What's working well?\n" .
                           "2. What issues exist?\n" .
                           "3. How can it be improved?\n" .
                           "4. Overall quality score (1-10)";
        
        $reflection = $client->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 51](/series/claude-php-developers/chapters/51-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 10 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/10-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="50"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 51](/series/claude-php-developers/chapters/51-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 10 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/10-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/10-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
