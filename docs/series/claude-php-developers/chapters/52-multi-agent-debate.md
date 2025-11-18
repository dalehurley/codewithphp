---
title: "52: Multi-Agent Debate"
description: "Build multi-agent debate with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: 52
order: 52
difficulty: "Advanced"
prerequisites:
  - "/series/claude-php-developers/chapters/51-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![52: Multi-Agent Debate](/images/claude-php/chapter-52-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 52</span>
</div>

# Chapter 52: Multi-Agent Debate

## Overview

This chapter is based on Tutorial 12 from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: 60 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 51** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

- Implement multi-agent debate protocols
- Design agents with different perspectives
- Build consensus mechanisms
- Handle disagreements and conflicts
- Synthesize insights from debates
- Apply debate to decision-making
- Understand when debate improves outcomes

## Tutorial Content

> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).
> For the complete tutorial with working code examples, visit the SDK repository.



Multi-agent debate systems leverage diverse perspectives and critical thinking by having multiple agents discuss, challenge, and refine ideas. This pattern produces more robust, well-reasoned outputs through collaborative argumentation.

### 🎯 Learning Objectives

By the end of this tutorial, you'll be able to:

- Implement multi-agent debate protocols
- Design agents with different perspectives
- Build consensus mechanisms
- Handle disagreements and conflicts
- Synthesize insights from debates
- Apply debate to decision-making
- Understand when debate improves outcomes

### 🏗️ What We're Building

A debate system featuring:

1. **Multiple Agents** - Different viewpoints and roles
2. **Debate Protocol** - Structured argumentation rounds
3. **Challenger Agents** - Devil's advocate and critics
4. **Moderator** - Manages flow and synthesizes
5. **Consensus Builder** - Finds common ground
6. **Decision Framework** - Converts debate to action

### 📋 Prerequisites

Make sure you have:

- Completed [Tutorial 11: Hierarchical Agents](../11-hierarchical-agents/)
- Understanding of argumentation and logic
- PHP 8.1+ installed
- Claude PHP SDK configured

### 🤔 What is Multi-Agent Debate?

Multi-agent debate involves multiple AI agents with different roles or perspectives discussing a topic to reach better conclusions.

#### Debate vs Single Agent

**Single Agent:**
```
Question: Should we adopt technology X?
Agent: Yes, because... [one perspective]
Done.
```

**Multi-Agent Debate:**
```
Question: Should we adopt technology X?

Proponent: Yes, because benefits A, B, C...
Opponent: No, because risks X, Y, Z...
Analyst: Data shows...
Critic: Both sides overlook...
Moderator: Considering all views...

Result: Nuanced, well-reasoned decision
```

### 🔑 Key Concepts

#### 1. Agent Roles

Different agents bring different perspectives:

```php
$roles = [
    'proponent' => [
        'perspective' => 'Support the proposal',
        'system' => 'You advocate for the proposal. Find benefits and opportunities.'
    ],
    'opponent' => [
        'perspective' => 'Challenge the proposal',
        'system' => 'You oppose the proposal. Identify risks and drawbacks.'
    ],
    'analyst' => [
        'perspective' => 'Objective analysis',
        'system' => 'You analyze facts objectively. Focus on data and evidence.'
    ],
    'critic' => [
        'perspective' => 'Critical thinking',
        'system' => 'You identify logical flaws and assumptions in arguments.'
    ],
    'moderator' => [
        'perspective' => 'Synthesis and balance',
        'system' => 'You synthesize viewpoints and find balanced conclusions.'
    ]
];
```

#### 2. Debate Protocol

Structured rounds ensure comprehensive discussion:

```php
$protocol = [
    'round_1' => [
        'type' => 'opening_statements',
        'agents' => ['proponent', 'opponent'],
        'purpose' => 'Present initial positions'
    ],
    'round_2' => [
        'type' => 'rebuttals',
        'agents' => ['opponent', 'proponent'],
        'purpose' => 'Challenge opponent arguments'
    ],
    'round_3' => [
        'type' => 'analysis',
        'agents' => ['analyst', 'critic'],
        'purpose' => 'Objective evaluation'
    ],
    'final' => [
        'type' => 'synthesis',
        'agents' => ['moderator'],
        'purpose' => 'Unified conclusion'
    ]
];
```

#### 3. Argument Quality

Evaluate argument strength:

```php
$argumentMetrics = [
    'logic' => 'Is reasoning sound?',
    'evidence' => 'Are claims supported?',
    'completeness' => 'Are counterarguments addressed?',
    'relevance' => 'Does it address the question?'
];
```

#### 4. Consensus Building

Find common ground:

```php
function findConsensus($debateHistory) {
    $agreementPoints = [];
    $disagreementPoints = [];
    
    foreach ($debateHistory as $round) {
        // Identify where agents agree
        $commonalities = extractCommonPoints($round);
        $agreementPoints = array_merge($agreementPoints, $commonalities);
        
        // Track persistent disagreements
        $conflicts = extractConflicts($round);
        $disagreementPoints = array_merge($disagreementPoints, $conflicts);
    }
    
    return [
        'consensus' => $agreementPoints,
        'open_issues' => $disagreementPoints
    ];
}
```

### 💡 Implementation Patterns

#### Basic Debate System

```php
class DebateSystem {
    private $client;
    private $agents = [];
    private $history = [];
    
    public function __construct($client) {
        $this->client = $client;
    }
    
    public function addAgent($name, $role, $systemPrompt) {
        $this->agents[$name] = [
            'role' => $role,
            'system' => $systemPrompt
        ];
    }
    
    public function debate($topic, $rounds = 3) {
        $context = "Topic: {$topic}\n\n";
        
        for ($round = 1; $round <= $rounds; $round++) {
            echo "Round {$round}:\n";
            
            foreach ($this->agents as $name => $config) {
                $prompt = $context . "As the {$config['role']}, provide your perspective.";
                
                $response = $this->client->messages()->create([
                    'model' => 'claude-sonnet-4-5',
                    'max_tokens' => 1024,
                    'system' => $config['system'],
                    'messages' => [['role' => 'user', 'content' => $prompt]]
                ]);
                
                $statement = extractTextContent($response);
                $this->history[] = [

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter 53](/series/claude-php-developers/chapters/53-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial 12 Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/12-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="52"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter 53](/series/claude-php-developers/chapters/53-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial 12 Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/12-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/12-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
