---
title: Computer Science Fundamentals
description: Master essential computer science concepts from algorithms to data structures with practical implementations in PHP.
series: computer-science
order: 0
difficulty: Intermediate
prerequisites:
  ["Basic programming knowledge", "PHP fundamentals", "Command line familiarity"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Computer Science Fundamentals</span>
</div>

# Computer Science Fundamentals <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Welcome to **Computer Science Fundamentals** — a comprehensive series that bridges the gap between writing code and understanding the theoretical foundations that make software efficient, scalable, and elegant. By the end of this series, you'll have a deep understanding of algorithms, data structures, design patterns, and computational thinking that will elevate your programming to the next level.

Computer science isn't just about writing code—it's about solving problems efficiently. Whether you're optimizing a database query, designing a scalable system, or choosing the right data structure, the principles you learn here will be invaluable throughout your career.

This series uses PHP for all implementations, allowing you to apply computer science concepts in a language you already know while building a strong theoretical foundation that transfers to any programming language.

## Who This Is For

This series is designed for:

- **PHP developers** who want to deepen their understanding of computer science fundamentals
- **Self-taught programmers** looking to fill gaps in their CS knowledge
- **Bootcamp graduates** who want to supplement their practical skills with theory
- **Developers preparing for technical interviews** at tech companies
- **Anyone curious** about how computers solve complex problems efficiently

You should be comfortable with basic programming concepts (variables, functions, loops, and objects) before starting this series. Familiarity with PHP is recommended but not required—the concepts apply to any language.

## Prerequisites

**Knowledge Requirements:**

- **Basic programming** in any language (variables, loops, functions, conditionals)
- **PHP basics** recommended (but not required)
- **Object-oriented programming** fundamentals (classes, objects, methods)
- **Command line** basic familiarity

**Software Requirements:**

- **PHP 8.2+** installed on your system
- **Text editor or IDE** (VS Code, PhpStorm, Sublime Text)
- **Terminal access** for running code examples

**Time Commitment:**

- **Estimated total**: 25–35 hours to complete all chapters
- **Per chapter**: 1–3 hours depending on complexity
- **Projects**: 3–5 hours each

**Skill Assumptions:**

- You can write and run PHP scripts
- You understand basic control flow (if/else, loops)
- You're familiar with functions and classes
- You can read and understand code examples

## What You'll Build

<ProgressTracker seriesId="computer-science" :totalChapters="20" title="Your Progress" />

By working through this series, you will:

1. **Implement classic algorithms** including sorting, searching, and graph traversal
2. **Build fundamental data structures** from scratch (linked lists, trees, heaps, hash tables)
3. **Analyze algorithmic complexity** using Big O notation
4. **Solve classic CS problems** like pathfinding, scheduling, and optimization
5. **Master design patterns** used in professional software development
6. **Understand computational theory** including recursion, dynamic programming, and greedy algorithms
7. **Apply CS concepts to real-world problems** in web development and software engineering

Every implementation is written in PHP, demonstrating that CS fundamentals aren't limited to "academic" languages like Python or Java.

## Learning Objectives

By the end of this series, you will be able to:

- **Analyze algorithm efficiency** using Big O notation and choose optimal approaches
- **Implement fundamental data structures** including arrays, linked lists, stacks, queues, trees, graphs, and hash tables
- **Apply classic algorithms** for sorting, searching, and graph traversal
- **Solve problems recursively** and understand when recursion is appropriate
- **Use dynamic programming** to optimize complex problems
- **Implement common design patterns** like Factory, Observer, Strategy, and Decorator
- **Understand computational complexity** and recognize P vs NP problems
- **Apply computer science theory** to practical web development challenges
- **Prepare for technical interviews** with confidence in CS fundamentals
- **Think algorithmically** and approach problems with computational thinking

## How This Series Works

This series follows a **theory-to-practice** approach:

1. **Concept introduction**: Learn the theory behind each algorithm or data structure
2. **Visual explanation**: Understand how it works with diagrams and step-by-step breakdowns
3. **PHP implementation**: Build it yourself with guided, practical code
4. **Complexity analysis**: Understand time and space efficiency with Big O notation
5. **Real-world applications**: See where and when to use each concept
6. **Practice problems**: Solve exercises to reinforce your understanding

Each chapter includes:

- Clear explanations of theoretical concepts
- Step-by-step implementations in PHP
- Performance analysis and optimization techniques
- Real-world use cases and applications
- Practice exercises with solutions
- Interview-style questions

::: tip
Type the code yourself instead of copy-pasting. Understanding comes from implementation and experimentation.
:::

## Learning Path Overview

This diagram shows how concepts build on each other throughout the series:

```
┌─────────────────────────────────────────────────────────────┐
│  Part 1: Introduction (Ch 00-01)                            │
│  • Computational Thinking • Algorithm Analysis              │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 2: Fundamental Data Structures (Ch 02-06)             │
│  • Arrays & Lists • Stacks & Queues • Linked Lists          │
│  • Trees • Hash Tables                                       │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 3: Algorithms (Ch 07-12)                              │
│  • Sorting • Searching • Recursion                          │
│  • Graph Algorithms • Greedy Algorithms • Backtracking      │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 4: Advanced Topics (Ch 13-16)                         │
│  • Dynamic Programming • Design Patterns                    │
│  • Complexity Theory • Optimization Techniques              │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 5: Applications (Ch 17-20)                            │
│  • System Design • Problem Solving • Interview Prep         │
│  • Practical CS in Web Development                          │
└─────────────────────────────────────────────────────────────┘
```

Each part builds on previous knowledge, taking you from fundamental concepts to advanced applications.

## Quick Start

Want to see what computer science in PHP looks like? Here's a simple algorithm comparison:

```php
<?php
// Linear search - O(n)
function linearSearch(array $arr, $target): ?int {
    foreach ($arr as $index => $value) {
        if ($value === $target) {
            return $index;
        }
    }
    return null;
}

// Binary search - O(log n) - much faster!
function binarySearch(array $arr, $target): ?int {
    $left = 0;
    $right = count($arr) - 1;

    while ($left <= $right) {
        $mid = (int)(($left + $right) / 2);

        if ($arr[$mid] === $target) {
            return $mid;
        }

        if ($arr[$mid] < $target) {
            $left = $mid + 1;
        } else {
            $right = $mid - 1;
        }
    }

    return null;
}

// Test with a sorted array
$numbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19];

echo "Linear search for 13: " . linearSearch($numbers, 13) . "\n";
echo "Binary search for 13: " . binarySearch($numbers, 13) . "\n";
```

**What's Next?**
If this code intrigues you, you're ready to start! Head to [Chapter 00](#) to begin your computer science journey.

## Chapters

### Part 1: Foundations (Chapters 00–01)

Learn how computer scientists think and analyze algorithms.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/00-computational-thinking">00 — Computational Thinking and Problem Solving</a></h4>
    <p style="margin-bottom: 0;">Learn to think like a computer scientist. Understand abstraction, decomposition, pattern recognition, and algorithmic thinking—the fundamental problem-solving approaches that power all of computer science.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/01-algorithm-analysis-big-o">01 — Algorithm Analysis and Big O Notation</a></h4>
    <p style="margin-bottom: 0;">Master Big O notation and algorithmic complexity. Learn to analyze time and space complexity, compare algorithm efficiency, and understand O(1), O(n), O(log n), O(n²), and beyond.</p>
  </div>
</div>

### Part 2: Data Structures (Chapters 02–06)

Build the fundamental data structures that power all software.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/02-arrays-and-lists">02 — Arrays and Dynamic Lists</a></h4>
    <p style="margin-bottom: 0;">Understand arrays, dynamic arrays, and lists. Learn about contiguous memory, array operations, resizing strategies, and when to use arrays versus other data structures.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/03-stacks-and-queues">03 — Stacks and Queues</a></h4>
    <p style="margin-bottom: 0;">Master LIFO and FIFO data structures. Implement stacks and queues from scratch, understand their applications in function calls, parsing, scheduling, and breadth-first search.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/04-linked-lists">04 — Linked Lists</a></h4>
    <p style="margin-bottom: 0;">Build singly and doubly linked lists. Understand pointer-based data structures, node traversal, insertion and deletion operations, and when linked lists outperform arrays.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/05-trees-and-binary-trees">05 — Trees and Binary Search Trees</a></h4>
    <p style="margin-bottom: 0;">Explore hierarchical data structures. Implement binary trees, binary search trees, and tree traversal algorithms (inorder, preorder, postorder). Understand balanced trees and their importance.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/06-hash-tables">06 — Hash Tables and Hash Functions</a></h4>
    <p style="margin-bottom: 0;">Master O(1) lookups with hash tables. Understand hash functions, collision resolution strategies (chaining, open addressing), and how PHP's associative arrays work under the hood.</p>
  </div>
</div>

### Part 3: Algorithms (Chapters 07–12)

Learn the essential algorithms every developer should know.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/07-sorting-algorithms">07 — Sorting Algorithms</a></h4>
    <p style="margin-bottom: 0;">Implement bubble sort, selection sort, insertion sort, merge sort, quick sort, and heap sort. Compare their time complexity and learn when to use each algorithm.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/08-searching-algorithms">08 — Searching Algorithms</a></h4>
    <p style="margin-bottom: 0;">Master linear search, binary search, and interpolation search. Understand search complexity, when binary search fails, and how to search in different data structures.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/09-recursion">09 — Recursion and Recursive Thinking</a></h4>
    <p style="margin-bottom: 0;">Think recursively and solve problems with self-referential solutions. Understand base cases, recursive cases, call stacks, tail recursion, and when recursion is the right tool.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/10-graph-algorithms">10 — Graph Algorithms</a></h4>
    <p style="margin-bottom: 0;">Model and traverse graphs with BFS and DFS. Understand graph representations (adjacency matrix, adjacency list), shortest path algorithms (Dijkstra's, Bellman-Ford), and minimum spanning trees.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/11-greedy-algorithms">11 — Greedy Algorithms</a></h4>
    <p style="margin-bottom: 0;">Make locally optimal choices that lead to globally optimal solutions. Learn the greedy approach, solve coin change problems, activity selection, and understand when greedy algorithms work.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/12-backtracking">12 — Backtracking and Constraint Satisfaction</a></h4>
    <p style="margin-bottom: 0;">Explore all possibilities systematically. Solve puzzles like N-Queens, Sudoku, and maze traversal. Understand the backtracking pattern and pruning strategies.</p>
  </div>
</div>

### Part 4: Advanced Topics (Chapters 13–16)

Level up with advanced algorithms and design patterns.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/13-dynamic-programming">13 — Dynamic Programming</a></h4>
    <p style="margin-bottom: 0;">Optimize recursive solutions with memoization and tabulation. Solve classic DP problems like Fibonacci, knapsack, longest common subsequence, and edit distance.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/14-design-patterns">14 — Design Patterns in PHP</a></h4>
    <p style="margin-bottom: 0;">Master software design patterns. Implement Factory, Singleton, Observer, Strategy, Decorator, and more. Understand when and why to use each pattern in real applications.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/15-computational-complexity">15 — Computational Complexity and P vs NP</a></h4>
    <p style="margin-bottom: 0;">Understand the limits of computation. Explore P, NP, NP-Complete, and NP-Hard problems. Learn about the P vs NP question and practical implications for problem solving.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/16-optimization-techniques">16 — Optimization Techniques and Trade-offs</a></h4>
    <p style="margin-bottom: 0;">Balance time and space complexity. Learn memoization, lazy evaluation, caching strategies, bit manipulation tricks, and how to make informed trade-offs in algorithm design.</p>
  </div>
</div>

### Part 5: Applications (Chapters 17–20)

Apply computer science to real-world problems.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #a78bfa 0%, #c4b5fd 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/17-system-design-basics">17 — System Design Basics</a></h4>
    <p style="margin-bottom: 0;">Design scalable systems with CS principles. Understand load balancing, caching layers, database indexing, message queues, and how computer science concepts apply to distributed systems.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #c4b5fd 0%, #ddd6fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/18-problem-solving-strategies">18 — Problem Solving Strategies</a></h4>
    <p style="margin-bottom: 0;">Develop a systematic approach to coding challenges. Learn how to break down problems, recognize patterns, choose data structures, and write clean, efficient solutions.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #ddd6fe 0%, #ede9fe 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/19-technical-interview-preparation">19 — Technical Interview Preparation</a></h4>
    <p style="margin-bottom: 0;">Ace technical interviews with confidence. Practice common interview questions, learn to communicate your thought process, analyze problems on the spot, and optimize solutions in real-time.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <div style="width: 180px; height: 120px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 4px; flex-shrink: 0;"></div>
  <div>
    <h4 style="margin-top: 0;"><a href="/series/computer-science/chapters/20-cs-in-web-development">20 — Computer Science in Modern Web Development</a></h4>
    <p style="margin-bottom: 0;">Apply CS fundamentals to web development. See how data structures power databases, algorithms optimize searches, and design patterns structure frameworks. Bridge theory and practice.</p>
  </div>
</div>

---

## Frequently Asked Questions

**Do I need a CS degree to understand this series?**
Not at all! This series is designed for self-taught developers and bootcamp graduates. We explain everything from first principles with practical PHP examples.

**Why PHP instead of Python or Java?**
Computer science concepts are language-agnostic. PHP is a powerful, expressive language that handles CS implementations beautifully. If you know PHP, you can learn CS in PHP!

**How is this different from LeetCode or HackerRank?**
Those platforms focus on practicing problems. This series teaches the underlying theory, implementation details, and when to apply each concept. It's comprehensive learning, not just practice.

**Will this help me in technical interviews?**
Absolutely! You'll learn exactly what interviewers expect: algorithm analysis, data structure implementation, problem-solving strategies, and the ability to explain your approach.

**Do I need to memorize all the algorithms?**
No! Focus on understanding how they work and when to use them. Pattern recognition and problem-solving skills matter more than memorization.

**Can I skip chapters?**
Some chapters build on previous ones. The learning path diagram shows dependencies. Feel free to jump ahead if you're confident in the prerequisites.

**How long will this take?**
Most learners complete the series in 4–6 weeks studying 1–2 hours per day. Take your time—depth of understanding matters more than speed.

## Getting Help

**Stuck on a concept?** Here's where to get help:

- **Review the code examples** in `/code/computer-science/` for working implementations
- **Check the diagrams and visualizations** in each chapter
- **Consult additional resources** linked at the end of each chapter
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report issues**: [Open an issue](https://github.com/dalehurley/codewithphp/issues) for unclear explanations

## Related Resources

Complement your learning with these excellent resources:

- **[Big-O Cheat Sheet](https://www.bigocheatsheet.com/)**: Quick reference for algorithm complexity
- **[VisuAlgo](https://visualgo.net/)**: Visualize data structures and algorithms
- **[Introduction to Algorithms (CLRS)](https://mitpress.mit.edu/books/introduction-algorithms-third-edition)**: Classic CS textbook
- **[Algorithm Design Manual](https://www.algorist.com/)**: Practical algorithm reference
- **[Design Patterns (Gang of Four)](https://en.wikipedia.org/wiki/Design_Patterns)**: Classic design patterns book

---

::: tip Ready to Start?
Head to [Chapter 00: Computational Thinking and Problem Solving](#) to begin your computer science journey!
:::

---

## Continue Your Learning

Looking for more programming series?

**→ [PHP Basics](/series/php-basics/)** — Master PHP from scratch
**→ [AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Add intelligent features to your applications

<style>
:root {
  --cs-indigo: #6366f1;
  --cs-violet: #8b5cf6;
  --cs-purple: #a855f7;
  --cs-dark: #4c1d95;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

/* Chapter card enhancements */
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--cs-indigo);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(99, 102, 241, 0.15);
  border-left-color: var(--cs-violet);
}

/* Gradient backgrounds for chapter cards */
div[style*="display: flex"] div[style*="background: linear-gradient"] {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

div[style*="display: flex"]:hover div[style*="background: linear-gradient"] {
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transform: scale(1.02);
}

/* Link styling */
div[style*="display: flex"] h4 a {
  color: var(--cs-indigo);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--cs-violet);
}
</style>
