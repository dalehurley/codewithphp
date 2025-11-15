---
title: Rust for PHP Developers
description: Master Rust programming from the ground up—from syntax and ownership to building production-ready web applications, APIs, and deployment strategies tailored for PHP developers.
series: rust-php-developers
order: 0
difficulty: Intermediate to Advanced
prerequisites:
  [
    "Expert-level PHP knowledge",
    "Familiarity with web development concepts",
    "Understanding of HTTP, APIs, and databases",
    "Basic command-line proficiency",
    "Experience with PHP frameworks (Laravel, Symfony, etc.)",
  ]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Rust for PHP Developers</span>
</div>

![Rust for PHP Developers](/images/rust-php-developers/hero-full.webp)

# Rust for PHP Developers <span class="difficulty-badge difficulty-advanced">Intermediate to Advanced</span>

## Overview

Welcome to **Rust for PHP Developers** — a comprehensive, hands-on course that teaches you Rust programming from the perspective of an expert PHP developer. Whether you're looking to build high-performance microservices, create CLI tools that outperform PHP scripts, or explore systems programming, this series will transform your PHP expertise into Rust mastery.

Rust is a systems programming language that runs blazingly fast, prevents segfaults, and guarantees thread safety. It's being used by companies like Mozilla, Dropbox, Discord, and Cloudflare to build performance-critical infrastructure. For PHP developers, Rust opens doors to:

- **Performance**: 10-100x faster than PHP for CPU-intensive tasks
- **Memory Safety**: No null pointer errors, no buffer overflows
- **Concurrency**: Fearless concurrent programming without data races
- **Type Safety**: Catch bugs at compile time, not in production
- **Ecosystem**: Growing web framework ecosystem (Actix, Rocket, Axum)

This series bridges the gap between PHP and Rust. You'll learn Rust concepts by comparing them to familiar PHP patterns, build real-world web applications, and deploy production-ready Rust services. From understanding ownership to building RESTful APIs, from async programming to Docker deployment—you'll master Rust through hands-on practice.

By the end of this series, you'll have built multiple Rust applications, mastered the borrow checker, understood async/await patterns, created web APIs with popular frameworks, integrated with databases, and deployed production Rust services. More importantly, you'll know when to use Rust vs PHP for different parts of your stack.

## Who This Is For

This series is designed for:

- **Expert PHP developers** who want to learn a systems programming language
- **Backend engineers** looking to build high-performance microservices
- **Full-stack developers** wanting to expand beyond dynamic languages
- **Tech leads** evaluating Rust for performance-critical services
- **PHP developers** curious about memory management and systems programming
- **Anyone** ready to think differently about programming paradigms

You don't need C/C++ experience or computer science degree. If you're comfortable with modern PHP (8.0+), understand web development, databases, and APIs, you're ready to start.

## Prerequisites

**Software Requirements:**

- **Rust 1.75+** (we'll install this together using rustup)
- **PHP 8.0+** (for comparison examples)
- **Text editor or IDE** (VS Code with rust-analyzer, or any editor)
- **Terminal/Command line** access
- **Docker** (optional, for deployment chapters)
- **PostgreSQL/MySQL** (for database chapters)

**Time Commitment:**

- **Estimated total**: 60–80 hours to complete all chapters
- **Per chapter**: 45 minutes to 2 hours
- **Core learning path**: 25 hours
- **Web development path**: 35 hours
- **Complete mastery path**: 60+ hours

**Skill Assumptions:**

- You're expert-level with PHP syntax and concepts
- You understand web development (HTTP, REST, APIs)
- You've worked with databases and ORMs
- You're familiar with async concepts (promises, async/await in JavaScript)
- You can navigate the command line confidently
- No prior Rust or systems programming knowledge required

## What You'll Build

<ProgressTracker seriesId="rust-php-developers" :totalChapters="40" title="Your Progress" />

By working through this series, you will:

1. **Master Rust fundamentals** with PHP comparisons:
   - Variables, types, and ownership (vs PHP's reference counting)
   - Structs and enums (vs PHP classes and arrays)
   - Error handling (Result<T, E> vs exceptions)
   - Traits (vs PHP interfaces and traits)
   - Generics and lifetimes
   - Pattern matching (vs switch statements)

2. **Build real-world applications**:
   - High-performance CLI tools (replacing PHP scripts)
   - RESTful APIs with Actix Web and Axum
   - GraphQL servers with async-graphql
   - WebSocket services for real-time features
   - Background job processors
   - Full-stack web applications

3. **Master web development in Rust**:
   - HTTP servers and routing
   - Middleware and authentication
   - Database integration (SQLx, Diesel)
   - ORM patterns and migrations
   - Caching with Redis
   - Session management

4. **Deploy production-ready services**:
   - Docker containerization
   - CI/CD pipelines
   - Monitoring and logging
   - Performance optimization
   - Security best practices
   - Cloud deployment (AWS, DigitalOcean)

Every code example includes PHP comparisons, production-ready Rust implementations, and comprehensive explanations of why things work differently in Rust.

## Learning Objectives

By the end of this series, you will be able to:

- **Understand ownership and borrowing** — Rust's unique memory management system
- **Write safe concurrent code** — Leverage Rust's fearless concurrency
- **Build web APIs** — Create fast, reliable HTTP services
- **Work with databases** — Query and manage data with type safety
- **Handle errors properly** — Use Result and Option types effectively
- **Master async programming** — Write efficient asynchronous Rust code
- **Deploy Rust applications** — Package and ship production services
- **Choose the right tool** — Know when to use Rust vs PHP
- **Read Rust documentation** — Navigate crates.io and understand Rust idioms
- **Debug Rust code** — Interpret compiler errors and fix ownership issues

## How This Series Works

This series follows a **progressive, comparative approach**: you'll learn each Rust concept by seeing how it relates to PHP, understanding why it's different, and building practical examples.

Each chapter includes:

- **PHP vs Rust comparisons** showing equivalent code in both languages
- **Clear explanations** of Rust concepts from a PHP developer's perspective
- **Step-by-step implementations** with runnable code examples
- **Performance benchmarks** comparing Rust and PHP approaches
- **Practical examples** showing real-world use cases
- **Hands-on exercises** to reinforce learning
- **Common pitfalls** that PHP developers encounter when learning Rust
- **Further reading** for deeper exploration

We'll start with Rust basics (installation, syntax, ownership), progress through intermediate concepts (structs, enums, error handling), explore web development (frameworks, databases, APIs), and finish with production deployment (Docker, monitoring, optimization).

::: tip
Type the code yourself instead of copy-pasting. Rust's strict compiler will teach you through helpful error messages. Embrace the compiler errors—they're your teacher!
:::

## Quick Start

Want to see Rust in action right now? Here's a 2-minute example comparing PHP and Rust:

```php
<?php
// PHP: Finding duplicates in a large array
$items = range(1, 1000000);
$start = microtime(true);

$seen = [];
foreach ($items as $item) {
    if (isset($seen[$item])) {
        echo "Found duplicate: $item\n";
        break;
    }
    $seen[$item] = true;
}

echo "Time: " . round((microtime(true) - $start) * 1000, 2) . "ms\n";
// Output: ~50-100ms
```

```rust
// Rust: Same operation, much faster
use std::collections::HashSet;
use std::time::Instant;

fn main() {
    let items: Vec<i32> = (1..=1_000_000).collect();
    let start = Instant::now();

    let mut seen = HashSet::new();
    for item in items {
        if !seen.insert(item) {
            println!("Found duplicate: {}", item);
            break;
        }
    }

    println!("Time: {:?}", start.elapsed());
    // Output: ~1-5ms (10-50x faster!)
}
```

**What's Next?**
That's just a taste of Rust's performance. Head to [Chapter 00: Quick Start Guide](/series/rust-php-developers/chapters/00-quick-start-guide/) for environment setup, or start with [Chapter 01: Why Rust for PHP Developers](/series/rust-php-developers/chapters/01-why-rust-for-php-developers/) for comprehensive learning.

---

## Learning Paths & Chapters

Choose your learning path based on your goals and time availability, or explore all chapters below.

::: tip Recommended Learning Paths
- **Quick Start** (~8 hours): Chapters 00, 01, 02, 03, 05, 08, 21, 24
- **Core Rust Fundamentals** (~25 hours): Chapters 00-15
- **Web Development Focus** (~35 hours): Chapters 00-10, 16-30
- **Complete Mastery** (~60 hours): All chapters 00-39 + all appendices
:::

### Part 0: Getting Started (Chapter 00)

Get your Rust environment set up and see quick wins.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-00-quick-start-hero-thumbnail.webp" alt="Chapter 00 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/00-quick-start-guide">00 — Quick Start Guide</a></h4>
    <p style="margin-bottom: 0;">Install Rust, set up your development environment, and run your first Rust program. Compare "Hello World" in PHP vs Rust, understand the toolchain (rustup, cargo, rustc), and see immediate performance wins with practical examples.</p>
  </div>
</div>

### Part 1: Foundation (Chapters 01–05)

Build essential Rust knowledge with PHP comparisons.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-01-why-rust-hero-thumbnail.webp" alt="Chapter 01 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/01-why-rust-for-php-developers">01 — Why Rust for PHP Developers</a></h4>
    <p style="margin-bottom: 0;">Understand when and why to choose Rust over PHP. Compare performance, memory usage, type safety, and ecosystem. See real-world use cases where Rust excels and where PHP remains the better choice.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-02-basics-hero-thumbnail.webp" alt="Chapter 02 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/02-variables-and-types">02 — Variables and Types</a></h4>
    <p style="margin-bottom: 0;">Learn Rust's type system compared to PHP's. Understand immutability by default, type inference, primitive types (integers, floats, booleans, chars), and compound types (tuples, arrays). See how Rust prevents null pointer errors.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-03-ownership-hero-thumbnail.webp" alt="Chapter 03 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/03-ownership-and-borrowing">03 — Ownership and Borrowing</a></h4>
    <p style="margin-bottom: 0;">Master Rust's unique ownership system. Understand stack vs heap (vs PHP's reference counting), borrowing rules, mutable vs immutable references, and the borrow checker. This is THE concept that makes Rust different.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-04-functions-hero-thumbnail.webp" alt="Chapter 04 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/04-functions-and-control-flow">04 — Functions and Control Flow</a></h4>
    <p style="margin-bottom: 0;">Write functions with type signatures, understand expressions vs statements, use if/else and loops, and learn pattern matching with match (Rust's supercharged switch). Compare to PHP's control structures.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-05-structs-hero-thumbnail.webp" alt="Chapter 05 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/05-structs-and-enums">05 — Structs and Enums</a></h4>
    <p style="margin-bottom: 0;">Build custom data types with structs (like PHP classes) and enums (algebraic data types). Understand methods, associated functions, and why enums are more powerful than PHP's enums or class constants.</p>
  </div>
</div>

### Part 2: Core Language Features (Chapters 06–10)

Deepen your understanding of Rust's unique features.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-06-error-handling-hero-thumbnail.webp" alt="Chapter 06 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/06-error-handling">06 — Error Handling with Result and Option</a></h4>
    <p style="margin-bottom: 0;">Replace PHP's exceptions with Rust's Result<T, E> and Option<T> types. Learn the ? operator, error propagation, custom error types, and the anyhow crate for ergonomic error handling.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-07-collections-hero-thumbnail.webp" alt="Chapter 07 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/07-collections">07 — Collections: Vectors, HashMaps, and Strings</a></h4>
    <p style="margin-bottom: 0;">Work with Rust's standard collections: Vec<T> (dynamic arrays), HashMap<K, V> (associative arrays), and String/&str (text handling). Compare to PHP arrays and understand UTF-8 string handling.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-08-traits-hero-thumbnail.webp" alt="Chapter 08 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/08-traits-and-generics">08 — Traits and Generics</a></h4>
    <p style="margin-bottom: 0;">Define shared behavior with traits (like PHP interfaces but more powerful), write generic code, understand trait bounds, and learn about common traits (Clone, Debug, Display, Iterator).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-09-lifetimes-hero-thumbnail.webp" alt="Chapter 09 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/09-lifetimes">09 — Understanding Lifetimes</a></h4>
    <p style="margin-bottom: 0;">Demystify lifetime annotations, understand how Rust prevents dangling references, learn lifetime elision rules, and see practical examples. This chapter makes lifetimes click for PHP developers.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-10-modules-hero-thumbnail.webp" alt="Chapter 10 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/10-modules-and-crates">10 — Modules, Crates, and Cargo</a></h4>
    <p style="margin-bottom: 0;">Organize code with modules (vs PHP namespaces), create and publish crates (packages), use Cargo for dependency management (vs Composer), and understand the Rust module system.</p>
  </div>
</div>

### Part 3: Advanced Concepts (Chapters 11–15)

Explore Rust's advanced features and patterns.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-11-iterators-hero-thumbnail.webp" alt="Chapter 11 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/11-iterators-and-closures">11 — Iterators and Closures</a></h4>
    <p style="margin-bottom: 0;">Master functional programming patterns with iterators (map, filter, fold) and closures. Compare to PHP's array functions and arrow functions. Understand zero-cost abstractions and lazy evaluation.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-12-smart-pointers-hero-thumbnail.webp" alt="Chapter 12 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/12-smart-pointers">12 — Smart Pointers: Box, Rc, Arc</a></h4>
    <p style="margin-bottom: 0;">Work with heap-allocated data using Box<T>, share ownership with Rc<T> and Arc<T>, understand reference counting (like PHP objects), and learn interior mutability with RefCell<T> and Mutex<T>.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-13-concurrency-hero-thumbnail.webp" alt="Chapter 13 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/13-concurrency">13 — Fearless Concurrency</a></h4>
    <p style="margin-bottom: 0;">Write concurrent code with threads, message passing (channels), and shared state (Arc + Mutex). Compare to PHP's multi-process model and understand how Rust prevents data races at compile time.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-14-async-hero-thumbnail.webp" alt="Chapter 14 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/14-async-programming">14 — Async Programming Fundamentals</a></h4>
    <p style="margin-bottom: 0;">Master async/await in Rust with Tokio runtime. Understand futures, async functions, spawning tasks, and select! macro. Compare to PHP's async libraries (ReactPHP, Amphp) and JavaScript's async model.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-15-testing-hero-thumbnail.webp" alt="Chapter 15 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/15-testing">15 — Testing in Rust</a></h4>
    <p style="margin-bottom: 0;">Write unit tests, integration tests, and benchmarks. Use cargo test, understand test organization, mock dependencies, and compare to PHPUnit. Learn property-based testing with proptest.</p>
  </div>
</div>

### Part 4: Systems Programming (Chapters 16–20)

Build performance-critical tools and utilities.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-16-cli-hero-thumbnail.webp" alt="Chapter 16 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/16-building-cli-tools">16 — Building CLI Tools</a></h4>
    <p style="margin-bottom: 0;">Create command-line tools with clap (argument parsing), colored output, progress bars, and file I/O. Build a grep-like tool and compare performance to PHP CLI scripts. Learn cross-compilation.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-17-file-io-hero-thumbnail.webp" alt="Chapter 17 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/17-file-io">17 — File I/O and the Filesystem</a></h4>
    <p style="margin-bottom: 0;">Read and write files efficiently, work with paths, handle directories, use buffered I/O, and process large files. Compare to PHP's file functions and learn about memory-mapped files.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-18-json-hero-thumbnail.webp" alt="Chapter 18 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/18-json-and-serialization">18 — JSON and Serialization</a></h4>
    <p style="margin-bottom: 0;">Work with JSON using serde and serde_json. Parse, serialize, and deserialize data structures. Compare to PHP's json_encode/decode and understand type-safe serialization.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-19-logging-hero-thumbnail.webp" alt="Chapter 19 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/19-logging-and-debugging">19 — Logging and Debugging</a></h4>
    <p style="margin-bottom: 0;">Add logging with tracing and log crates, debug with rust-gdb and lldb, use dbg! macro, and understand structured logging. Compare to Monolog and PHP debugging tools.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-20-performance-hero-thumbnail.webp" alt="Chapter 20 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/20-performance-optimization">20 — Performance Optimization</a></h4>
    <p style="margin-bottom: 0;">Profile Rust code with cargo flamegraph, optimize hot paths, understand zero-cost abstractions, use SIMD, and benchmark with criterion. See 10-100x speedups over PHP.</p>
  </div>
</div>

### Part 5: Web Development Basics (Chapters 21–25)

Build HTTP servers and web applications.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-21-http-hero-thumbnail.webp" alt="Chapter 21 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/21-http-fundamentals">21 — HTTP Fundamentals in Rust</a></h4>
    <p style="margin-bottom: 0;">Understand HTTP in Rust with reqwest (HTTP client), build a simple HTTP server, parse requests, send responses, and compare to PHP's $_SERVER and Guzzle.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-22-actix-hero-thumbnail.webp" alt="Chapter 22 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/22-actix-web-framework">22 — Actix Web Framework</a></h4>
    <p style="margin-bottom: 0;">Build your first web API with Actix Web. Set up routes, handlers, extractors, middleware, and JSON responses. Compare to Laravel routing and see how Actix achieves its performance.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-23-axum-hero-thumbnail.webp" alt="Chapter 23 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/23-axum-framework">23 — Axum Framework</a></h4>
    <p style="margin-bottom: 0;">Explore Axum, the ergonomic web framework built on Tower. Learn routing, state management, extractors, and middleware. Compare to Actix and understand trade-offs between frameworks.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-24-routing-hero-thumbnail.webp" alt="Chapter 24 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/24-routing-and-middleware">24 — Routing and Middleware</a></h4>
    <p style="margin-bottom: 0;">Master routing patterns, path parameters, query strings, middleware composition, and request guards. Build authentication middleware and compare to PHP middleware (PSR-15).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-25-templates-hero-thumbnail.webp" alt="Chapter 25 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/25-templates">25 — Template Engines</a></h4>
    <p style="margin-bottom: 0;">Render HTML with Tera (Jinja-like) and Askama (compile-time templates). Compare to Blade, Twig, and learn about type-safe templating. Build server-side rendered pages.</p>
  </div>
</div>

### Part 6: APIs and Data (Chapters 26–30)

Build production-ready APIs with databases.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-26-rest-api-hero-thumbnail.webp" alt="Chapter 26 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/26-building-rest-apis">26 — Building RESTful APIs</a></h4>
    <p style="margin-bottom: 0;">Design and implement REST APIs with proper HTTP methods, status codes, versioning, pagination, and filtering. Build a complete CRUD API and compare to Laravel API resources.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-27-validation-hero-thumbnail.webp" alt="Chapter 27 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/27-request-validation">27 — Request Validation</a></h4>
    <p style="margin-bottom: 0;">Validate incoming data with validator crate, create custom validators, handle validation errors, and return helpful error messages. Compare to Laravel validation and PHP's filter functions.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-28-graphql-hero-thumbnail.webp" alt="Chapter 28 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/28-graphql">28 — GraphQL with async-graphql</a></h4>
    <p style="margin-bottom: 0;">Build GraphQL servers with async-graphql. Define schemas, resolvers, mutations, subscriptions, and N+1 query optimization. Compare to PHP GraphQL libraries and understand Rust's type safety benefits.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-29-websockets-hero-thumbnail.webp" alt="Chapter 29 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/29-websockets">29 — WebSockets and Real-time</a></h4>
    <p style="margin-bottom: 0;">Implement WebSocket servers for real-time features (chat, notifications, live updates). Use tokio-tungstenite, handle connections, broadcast messages, and compare to Ratchet/Swoole.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-30-openapi-hero-thumbnail.webp" alt="Chapter 30 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/30-openapi-docs">30 — OpenAPI Documentation</a></h4>
    <p style="margin-bottom: 0;">Generate OpenAPI specs from Rust code with utoipa. Auto-document your APIs, generate Swagger UI, and maintain API contracts. Compare to PHP's OpenAPI tools.</p>
  </div>
</div>

### Part 7: Database Integration (Chapters 31–35)

Work with databases using type-safe ORMs and query builders.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-31-sqlx-hero-thumbnail.webp" alt="Chapter 31 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/31-sqlx">31 — SQLx: Async Database Queries</a></h4>
    <p style="margin-bottom: 0;">Query PostgreSQL, MySQL, SQLite with SQLx. Write compile-time verified queries, use macros for type safety, handle migrations, and compare to PDO/Doctrine DBAL.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-32-diesel-hero-thumbnail.webp" alt="Chapter 32 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/32-diesel-orm">32 — Diesel ORM</a></h4>
    <p style="margin-bottom: 0;">Use Diesel, the type-safe ORM for Rust. Define models, write queries with the query builder, handle relationships, and run migrations. Compare to Eloquent and Doctrine ORM.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-33-sea-orm-hero-thumbnail.webp" alt="Chapter 33 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/33-sea-orm">33 — SeaORM: Async ORM</a></h4>
    <p style="margin-bottom: 0;">Explore SeaORM, the async-first ORM. Compare async vs sync database access, build relationships, use eager loading, and understand when to choose SeaORM vs Diesel.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-34-redis-hero-thumbnail.webp" alt="Chapter 34 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/34-redis-caching">34 — Redis and Caching</a></h4>
    <p style="margin-bottom: 0;">Integrate Redis for caching, sessions, and pub/sub. Use redis-rs, implement cache-aside pattern, handle cache invalidation, and compare to Predis/phpredis.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-35-migrations-hero-thumbnail.webp" alt="Chapter 35 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/35-migrations-seeds">35 — Database Migrations and Seeding</a></h4>
    <p style="margin-bottom: 0;">Manage database schemas with migrations (SQLx, Diesel), seed test data, rollback changes, and version control your database. Compare to Laravel migrations and Phinx.</p>
  </div>
</div>

### Part 8: Production & Deployment (Chapters 36–39)

Ship production-ready Rust applications to the cloud.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-36-authentication-hero-thumbnail.webp" alt="Chapter 36 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/36-authentication">36 — Authentication and Authorization</a></h4>
    <p style="margin-bottom: 0;">Implement JWT authentication, session-based auth, role-based access control (RBAC), password hashing with argon2, and API key authentication. Compare to Laravel Auth and Passport.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-37-docker-hero-thumbnail.webp" alt="Chapter 37 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/37-docker-deployment">37 — Docker Deployment</a></h4>
    <p style="margin-bottom: 0;">Containerize Rust applications with multi-stage builds, optimize image size, use alpine/distroless images, docker-compose for local development, and compare to PHP Docker setups.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-38-monitoring-hero-thumbnail.webp" alt="Chapter 38 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/38-monitoring-observability">38 — Monitoring and Observability</a></h4>
    <p style="margin-bottom: 0;">Add metrics with Prometheus, distributed tracing with OpenTelemetry, health checks, graceful shutdown, and error tracking. Compare to PHP APM solutions (New Relic, DataDog).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/rust-php-developers/chapter-39-production-hero-thumbnail.webp" alt="Chapter 39 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/rust-php-developers/chapters/39-production-deployment">39 — Production Deployment</a></h4>
    <p style="margin-bottom: 0;">Deploy to AWS (ECS, Lambda), DigitalOcean, Fly.io, and Kubernetes. Set up CI/CD with GitHub Actions, manage secrets, configure load balancing, and monitor production Rust services.</p>
  </div>
</div>

---

## Appendices

Quick reference materials to support your Rust learning journey.

- **[Appendix A: Rust vs PHP Quick Reference](/series/rust-php-developers/appendices/a-rust-php-reference/)** — Side-by-side syntax comparison for quick lookup
- **[Appendix B: Cargo Commands Cheat Sheet](/series/rust-php-developers/appendices/b-cargo-cheat-sheet/)** — Essential cargo commands and flags
- **[Appendix C: Common Errors and Solutions](/series/rust-php-developers/appendices/c-common-errors/)** — Decode compiler errors and find solutions
- **[Appendix D: Crates Every Web Developer Needs](/series/rust-php-developers/appendices/d-essential-crates/)** — Curated list of must-have crates

---

## Frequently Asked Questions

**Do I need C/C++ experience to learn Rust?**
No! This series teaches Rust from a PHP perspective. We'll explain systems concepts (stack, heap, memory) without assuming C knowledge.

**How long does it take to become productive in Rust?**
The basics (~15 hours) let you build CLI tools. Web development proficiency takes ~35 hours. Most PHP developers are productive with Rust web apps after 4-6 weeks of consistent practice.

**Should I use Rust for everything now?**
No! Use the right tool for the job. Rust excels at performance-critical services, CLIs, and systems programming. PHP is still better for rapid prototyping, CMS sites, and many web apps. We'll discuss trade-offs throughout.

**How does Rust compare to Go?**
Both are modern languages, but Go is simpler (easier learning curve) while Rust is more powerful (memory safety, zero-cost abstractions). Rust is generally faster and uses less memory. Choose based on your needs.

**Can I call PHP code from Rust or vice versa?**
Yes! You can use FFI (Foreign Function Interface) or build HTTP APIs. Many teams run PHP for web frontends and Rust for performance-critical microservices.

**Is the Rust web ecosystem mature enough for production?**
Yes! Companies like Discord, Cloudflare, and AWS use Rust in production. Frameworks like Actix and Axum are battle-tested and production-ready.

**Will Rust replace PHP?**
No. They serve different purposes. PHP excels at rapid web development and has a massive ecosystem. Rust is better for performance-critical services and systems programming. They complement each other.

**How do I convince my team to adopt Rust?**
Start small: build a CLI tool or microservice in Rust. Show performance improvements and reliability gains. This series includes case studies and benchmarks to help make the business case.

**What's the hardest part of learning Rust for PHP developers?**
The borrow checker and ownership system. Chapters 03 and 09 are dedicated to making these concepts click. Be patient—it's worth it!

**Can I get a job knowing Rust?**
Yes! Rust developer demand is growing rapidly. The Rust Jobs Report shows strong job growth, and Rust developers command premium salaries.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Check the appendices first**:
  - [Appendix A: Rust vs PHP Quick Reference](/series/rust-php-developers/appendices/a-rust-php-reference/)
  - [Appendix C: Common Errors and Solutions](/series/rust-php-developers/appendices/c-common-errors/)
- **Review chapter troubleshooting sections** for common issues
- **Check code samples** in `/code-samples/rust-php-developers/` for working examples
- **Rust Book**: [doc.rust-lang.org/book](https://doc.rust-lang.org/book/)
- **Rust by Example**: [doc.rust-lang.org/rust-by-example](https://doc.rust-lang.org/rust-by-example/)
- **Rust Community**: [users.rust-lang.org](https://users.rust-lang.org/)
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)

## Related Resources

Want to dive deeper? These resources complement the series:

### Rust Resources

- **[The Rust Book](https://doc.rust-lang.org/book/)**: The official Rust programming language book
- **[Rust by Example](https://doc.rust-lang.org/rust-by-example/)**: Learn Rust with examples
- **[Rustlings](https://github.com/rust-lang/rustlings/)**: Small exercises to get you used to Rust
- **[crates.io](https://crates.io/)**: The Rust package registry

### Web Development

- **[Actix Web](https://actix.rs/)**: Fast web framework
- **[Axum](https://github.com/tokio-rs/axum)**: Ergonomic web framework
- **[Rocket](https://rocket.rs/)**: Type-safe web framework
- **[Are We Web Yet?](https://www.arewewebyet.org/)**: Rust web ecosystem status

### Related Code with PHP Series

- **[PHP Algorithms](/series/php-algorithms/)** — Apply algorithmic thinking in both languages
- **[Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** — PHP web development patterns
- **[PHP Basics](/series/php-basics/)** — Refresh your PHP fundamentals

---

::: tip Ready to Start?
Head to [Chapter 00: Quick Start Guide](/series/rust-php-developers/chapters/00-quick-start-guide) to set up Rust, or begin comprehensive learning with [Chapter 01: Why Rust for PHP Developers](/series/rust-php-developers/chapters/01-why-rust-for-php-developers)!
:::

---

## Continue Your Learning

Master other aspects of modern development:

**→ [PHP Algorithms](/series/php-algorithms/)** — Master algorithms in PHP first
**→ [AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Combine Rust performance with ML
**→ [Build a CRM with Laravel 12](/series/build-crm-laravel-12/)** — PHP web application patterns
