---
title: PHP Basics
description: Master modern PHP from zero to building your own blog—no frameworks, just fundamentals.
series: php-basics
order: 0
difficulty: Beginner
prerequisites:
  ["Basic computer literacy", "Text editor familiarity", "Willingness to learn"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>PHP Basics</span>
</div>

![PHP Basics](/images/php-basics/chapter-00-series-index-hero-full.webp)

# PHP Basics <span class="difficulty-badge difficulty-beginner">Beginner</span>

## Overview

Welcome to **Code with PHP** — a comprehensive, hands-on course that takes you from absolute beginner to confident PHP developer. By the end of this series, you'll have built a complete blog application from scratch, understood how frameworks work under the hood, and be ready to tackle Laravel or Symfony with confidence.

PHP is one of the most widely-used programming languages in the world, powering everything from the smallest blogs to the largest social networks. Its gentle learning curve, massive community, and rich ecosystem of modern tools make it a fantastic first language for aspiring web developers.

Despite its long history, PHP is more relevant and powerful than ever. The language has evolved dramatically, embracing modern programming paradigms, strong typing, and a robust, professional toolchain. It is fast, flexible, and fun.

## Who This Is For

This series is designed for:

- **Complete beginners** with no prior programming experience
- **Developers transitioning** from other languages (JavaScript, Python, Ruby, etc.)
- **Self-taught developers** who want to fill knowledge gaps and learn best practices
- **Framework users** who want to understand what happens "under the hood" in Laravel or Symfony

You don't need any previous programming knowledge — just basic computer skills, curiosity, and a willingness to type code and experiment.

## Prerequisites

**Software Requirements:**

- **PHP 8.4** (we'll show you how to install it in Chapter 00)
- **Text editor or IDE** (VS Code, PhpStorm, Sublime Text — any will work)
- **Terminal/Command line** access (built into macOS/Linux; we'll help Windows users too)
- **SQLite** (comes bundled with PHP)

::: info PHP Version Compatibility
**PHP 8.4 is recommended** for the full learning experience. While most examples work on PHP 8.0+, some modern features require 8.4:

- **Property hooks** (Chapter 8): Requires PHP 8.4
- **Asymmetric visibility** (Chapter 8): Requires PHP 8.4
- **Constructor property promotion**: Works on PHP 8.0+
- **Named arguments, match expressions**: Work on PHP 8.0+

For the best experience and to future-proof your skills, install PHP 8.4.
:::

**Time Commitment:**

- **Estimated total**: 20–30 hours to complete all chapters
- **Per chapter**: 30 minutes to 2 hours
- **Projects (Chapters 18–19)**: 3–5 hours each

**Skill Assumptions:**

- You can create files and folders on your computer
- You're comfortable typing commands in a terminal
- You can install software
- No prior programming knowledge required

## What You'll Build

<ProgressTracker seriesId="php-basics" :totalChapters="25" title="Your Progress" />

By working through this series, you will create:

1. **Dozens of working scripts** covering every core PHP concept
2. **A custom HTTP router** that handles GET/POST requests and URL parameters
3. **A database-driven blog application** with:
   - Create, read, update, and delete (CRUD) operations
   - User authentication and sessions
   - Form handling and validation
   - Secure database queries with PDO
   - File uploads and management
   - PSR-compliant code structure
4. **Your own MVC architecture** from scratch, giving you deep insight into how frameworks work
5. **Two framework starter projects** (Laravel and Symfony) to transition smoothly

Every code example is production-ready, following modern PHP 8.4 best practices and PSR standards.

## Learning Objectives

By the end of this series, you will be able to:

- **Write and execute PHP scripts** confidently in development and production environments
- **Master PHP fundamentals**: variables, data types, operators, control structures, and functions
- **Work with complex data** using arrays, strings, and PHP's built-in functions
- **Build object-oriented applications** using classes, inheritance, traits, interfaces, and namespaces
- **Handle errors gracefully** with exceptions and custom error handling
- **Manage dependencies** professionally with Composer and autoloading
- **Read and write files** safely and efficiently
- **Design and query databases** using PDO with prepared statements
- **Manage user state** with sessions and cookies
- **Build a custom HTTP router** and understand request/response cycles
- **Structure real applications** with separation of concerns and MVC patterns
- **Write clean, maintainable code** following PSR-1 and PSR-12 standards
- **Graduate confidently** to Laravel or Symfony with deep foundational knowledge

## How This Series Works

This series was designed with a simple philosophy: **the best way to learn is by doing**.

We will not just be reading about programming concepts; we will be applying them immediately. You'll type code, run it, break it, fix it, and build on it. Each chapter includes:

- **Clear learning objectives** so you know what to expect
- **Step-by-step explanations** with runnable code examples
- **Hands-on exercises** to reinforce concepts
- **Troubleshooting tips** for common errors
- **Further reading** for those who want to dive deeper

For the first 19 chapters, you will not touch a single framework. Instead, you'll learn the fundamental principles of the language, object-oriented programming, and modern tooling. You'll build your own router, your own application structure, and your own blog, piece by piece.

**Why?** Because understanding _how_ a framework works under the hood is the key to mastering it.

By Chapter 20, when you finally encounter Laravel and Symfony, everything will click. You'll recognize the patterns, understand the abstractions, and be able to work confidently at any level of the stack.

::: tip
Type the code yourself instead of copy-pasting. Muscle memory and debugging practice are crucial for becoming a confident developer.
:::

## Learning Path Overview

This diagram shows how concepts build on each other throughout the series:

```
┌─────────────────────────────────────────────────────────────┐
│  Part 1: Getting Started (Ch 00-01)                         │
│  • Environment Setup • Hello World                          │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 2: Core Fundamentals (Ch 02-07)                       │
│  • Variables & Types • Control Flow • Functions             │
│  • Forms & Input • Arrays • Strings                         │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 3: Object-Oriented Programming (Ch 08-10)             │
│  • Classes & Objects • Inheritance • Traits & Namespaces    │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 4: Professional Development (Ch 11-16)                │
│  • Exceptions • Composer • Files • Databases                │
│  • Sessions & Cookies • PSR Standards                       │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 5: Real Applications (Ch 17-19)                       │
│  • HTTP Router • App Structure • Complete Blog Project      │
└────────────────────────┬────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────┐
│  Part 6: Frameworks & Beyond (Ch 20-23)                     │
│  • Laravel • Symfony • Next Steps • JSON & APIs             │
└─────────────────────────────────────────────────────────────┘
```

Each part builds essential skills you'll need for the next. By Part 5, you'll combine everything to build a complete application from scratch.

## Quick Start

Want to jump in right now? Here's how to get your first PHP script running in under 5 minutes:

```bash
# 1. Check if PHP is installed (macOS/Linux have it by default)
php --version

# 2. Create a new directory and file
mkdir my-first-php && cd my-first-php
echo '<?php echo "Hello, PHP!";' > hello.php

# 3. Run your script
php hello.php

# Expected output: Hello, PHP!
```

**What's Next?**  
If that worked, you're ready to start! Head to [Chapter 00](/series/php-basics/chapters/00-setting-up-your-development-environment) for proper setup, or continue to [Chapter 01](/series/php-basics/chapters/01-your-first-php-script) to understand what just happened.

If you got an error, don't worry—[Chapter 00](/series/php-basics/chapters/00-setting-up-your-development-environment) will walk you through installing PHP 8.4.

## Chapters

### Part 1: Getting Started (Chapters 00–01)

Set up your environment and write your first working PHP script.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-00-setting-up-development-environment-hero-thumbnail.webp" alt="Chapter 00 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/00-setting-up-your-development-environment">00 — Setting Up Your Development Environment</a></h4>
    <p style="margin-bottom: 0;">Install PHP 8.4, configure your editor, and verify your setup. This chapter walks you through installing PHP on macOS, Linux, and Windows, choosing a text editor or IDE, and running your first verification script to ensure everything works correctly.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-01-first-php-script-hero-thumbnail.webp" alt="Chapter 01 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/01-your-first-php-script">01 — Your First PHP Script</a></h4>
    <p style="margin-bottom: 0;">Write "Hello, World!" and understand how PHP executes. Learn about PHP tags, how to run scripts from the command line, basic output with echo and print, and get comfortable with PHP syntax from the very first line of code.</p>
  </div>
</div>

### Part 2: Core Language Fundamentals (Chapters 02–07)

Master the building blocks: variables, control flow, functions, arrays, and strings.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-02-variables-data-types-hero-thumbnail.webp" alt="Chapter 02 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/02-variables-data-types-and-constants">02 — Variables, Data Types, and Constants</a></h4>
    <p style="margin-bottom: 0;">Learn PHP's type system and how to store data. Understand strings, integers, floats, booleans, arrays, and objects. Master variable naming conventions, type juggling, strict typing, and when to use constants versus variables.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-03-control-structures-hero-thumbnail.webp" alt="Chapter 03 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/03-control-structures">03 — Control Structures</a></h4>
    <p style="margin-bottom: 0;">Make decisions with if/else, switch, and loops. Learn to control program flow with conditional statements, comparison and logical operators, switch statements for multiple conditions, and loops (for, foreach, while, do-while) for repetitive tasks.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-04-understanding-functions-hero-thumbnail.webp" alt="Chapter 04 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/04-understanding-and-using-functions">04 — Understanding and Using Functions</a></h4>
    <p style="margin-bottom: 0;">Write reusable, modular code with functions. Master function syntax, parameters and arguments, return values, variable scope, type declarations, default parameters, variadic functions, and arrow functions for concise code.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-05-handling-html-forms-hero-thumbnail.webp" alt="Chapter 05 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/05-handling-html-forms-and-user-input">05 — Handling HTML Forms and User Input</a></h4>
    <p style="margin-bottom: 0;">Process GET/POST requests and sanitize user data. Learn how PHP receives form data, the difference between GET and POST methods, how to validate and sanitize user input, and basic security practices to prevent common vulnerabilities.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-06-deep-dive-arrays-hero-thumbnail.webp" alt="Chapter 06 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/06-deep-dive-into-arrays">06 — Deep Dive into Arrays</a></h4>
    <p style="margin-bottom: 0;">Work with indexed and associative arrays, plus powerful array functions. Master array creation, accessing and modifying elements, multidimensional arrays, array iteration, and essential functions like array_map, array_filter, array_reduce, and array manipulation.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-07-mastering-string-manipulation-hero-thumbnail.webp" alt="Chapter 07 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/07-mastering-string-manipulation">07 — Mastering String Manipulation</a></h4>
    <p style="margin-bottom: 0;">Format, search, and transform text efficiently. Learn string concatenation, interpolation, escaping, searching and replacing, substring extraction, string formatting, case conversion, and working with multibyte strings for international applications.</p>
  </div>
</div>

### Part 3: Object-Oriented Programming (Chapters 08–10)

Learn modern OOP principles that power professional PHP applications.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-08-introduction-oop-hero-thumbnail.webp" alt="Chapter 08 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/08-introduction-to-object-oriented-programming">08 — Introduction to Object-Oriented Programming</a></h4>
    <p style="margin-bottom: 0;">Classes, objects, properties, methods, and encapsulation. Learn how to define classes, create objects, use constructors and destructors, understand visibility (public/private/protected), implement static methods and properties, and leverage PHP 8.4's property hooks and asymmetric visibility.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-09-oop-inheritance-abstract-interfaces-hero-thumbnail.webp" alt="Chapter 09 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/09-oop-inheritance-abstract-classes-and-interfaces">09 — OOP: Inheritance, Abstract Classes, and Interfaces</a></h4>
    <p style="margin-bottom: 0;">Build flexible, extensible class hierarchies. Understand inheritance and method overriding, abstract classes for partial implementations, interfaces for contracts, polymorphism for flexible code, type hinting with interfaces, and when to use each approach.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-10-oop-traits-namespaces-hero-thumbnail.webp" alt="Chapter 10 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/10-oop-traits-and-namespaces">10 — OOP: Traits and Namespaces</a></h4>
    <p style="margin-bottom: 0;">Code reuse with traits and organize with namespaces. Learn how traits enable horizontal code reuse, resolve trait conflicts, use namespaces to organize code and prevent naming collisions, import classes with use statements, and follow PSR-4 autoloading standards.</p>
  </div>
</div>

### Part 4: Professional PHP Development (Chapters 11–16)

Essential skills for production applications: error handling, dependencies, files, databases, and standards.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-11-error-exception-handling-hero-thumbnail.webp" alt="Chapter 11 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/11-error-and-exception-handling">11 — Error and Exception Handling</a></h4>
    <p style="margin-bottom: 0;">Handle failures gracefully and debug effectively. Learn the difference between errors and exceptions, how to throw and catch exceptions, create custom exception classes, implement try-catch-finally blocks, and build robust error handling for production applications.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-12-dependency-management-composer-hero-thumbnail.webp" alt="Chapter 12 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/12-dependency-management-with-composer">12 — Dependency Management with Composer</a></h4>
    <p style="margin-bottom: 0;">Use Composer to manage packages and autoloading. Master installing third-party packages from Packagist, managing dependencies with composer.json, autoloading classes with PSR-4, semantic versioning, and keeping dependencies up to date.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-13-working-filesystem-hero-thumbnail.webp" alt="Chapter 13 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/13-working-with-the-filesystem">13 — Working with the Filesystem</a></h4>
    <p style="margin-bottom: 0;">Read, write, and manage files and directories safely. Learn to read and write files, check file existence and permissions, work with directories, handle file uploads securely, use file locking for concurrent access, and implement proper error handling.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-14-interacting-databases-pdo-hero-thumbnail.webp" alt="Chapter 14 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/14-interacting-with-databases-using-pdo">14 — Interacting with Databases using PDO</a></h4>
    <p style="margin-bottom: 0;">Connect to databases and run secure queries with prepared statements. Master PDO for database abstraction, execute queries safely with prepared statements to prevent SQL injection, handle transactions, fetch results in various formats, and implement error handling.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-15-managing-state-sessions-cookies-hero-thumbnail.webp" alt="Chapter 15 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies">15 — Managing State with Sessions and Cookies</a></h4>
    <p style="margin-bottom: 0;">Track users across requests and build authentication. Understand HTTP's stateless nature, use sessions to maintain user state across page requests, work with cookies for persistent data, implement user authentication and authorization, and secure session data.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-16-writing-better-code-psr-hero-thumbnail.webp" alt="Chapter 16 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/16-writing-better-code-with-psr-1-and-psr-12">16 — Writing Better Code with PSR-1 and PSR-12</a></h4>
    <p style="margin-bottom: 0;">Follow industry coding standards for readable, maintainable code. Learn PSR-1 basic coding standards, PSR-12 extended coding style guide, naming conventions, file structure best practices, and how to use PHP_CodeSniffer to enforce standards automatically.</p>
  </div>
</div>

### Part 5: Building Real Applications (Chapters 17–19)

Put it all together: build a router, structure an app, and create a complete blog.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-17-building-basic-http-router-hero-thumbnail.webp" alt="Chapter 17 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/17-building-a-basic-http-router">17 — Building a Basic HTTP Router</a></h4>
    <p style="margin-bottom: 0;">Create your own router to handle URLs and requests. Learn how routers work by building one from scratch. Understand URL parsing, route matching, HTTP methods (GET, POST, PUT, DELETE), route parameters, middleware concepts, and request/response handling.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-18-project-structuring-application-hero-thumbnail.webp" alt="Chapter 18 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/18-project-structuring-a-simple-application">18 — Project: Structuring a Simple Application</a></h4>
    <p style="margin-bottom: 0;">Design a clean MVC architecture from scratch. Learn how to organize a real application with Models (business logic and data), Views (presentation), Controllers (request handling), proper directory structure, configuration management, and separation of concerns.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-19-project-building-simple-blog-hero-thumbnail.webp" alt="Chapter 19 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/19-project-building-a-simple-blog">19 — Project: Building a Simple Blog</a></h4>
    <p style="margin-bottom: 0;">Build a full CRUD application with authentication and database. Bring everything together in a complete blog application: user registration and login, creating/editing/deleting posts, comment system, file uploads, security best practices, and deployment preparation.</p>
  </div>
</div>

### Part 6: Frameworks & Beyond (Chapters 20–23)

Graduate to modern frameworks and master essential web technologies.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-20-gentle-introduction-laravel-hero-thumbnail.webp" alt="Chapter 20 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/20-a-gentle-introduction-to-laravel">20 — A Gentle Introduction to Laravel</a></h4>
    <p style="margin-bottom: 0;">Get started with the world's most popular PHP framework. Install Laravel, understand its elegant syntax, explore Artisan commands, work with Eloquent ORM, create routes and controllers, and see how Laravel simplifies everything you've learned.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-21-gentle-introduction-symfony-hero-thumbnail.webp" alt="Chapter 21 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/21-a-gentle-introduction-to-symfony">21 — A Gentle Introduction to Symfony</a></h4>
    <p style="margin-bottom: 0;">Explore Symfony's powerful component architecture. Install Symfony, understand its bundle system, work with Doctrine ORM, create routes and controllers, use Twig templates, and discover how Symfony's flexibility supports enterprise applications.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-22-what-to-learn-next-hero-thumbnail.webp" alt="Chapter 22 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/22-what-to-learn-next">22 — What to Learn Next</a></h4>
    <p style="margin-bottom: 0;">Continue your PHP journey with advanced topics and resources. Explore testing with PHPUnit, caching strategies, queues and async processing, package development, API development, security best practices, performance optimization, and career paths in PHP development.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/php-basics/chapter-23-json-apis-hero-thumbnail.webp" alt="Chapter 23 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/php-basics/chapters/23-working-with-json-and-apis">23 — Working with JSON and APIs</a></h4>
    <p style="margin-bottom: 0;">Master JSON handling and consume RESTful APIs. Learn to encode and decode JSON, make HTTP requests with cURL, consume third-party APIs, handle API authentication, build your own RESTful API endpoints, and implement proper error handling and rate limiting.</p>
  </div>
</div>

---

## Frequently Asked Questions

**I've never programmed before. Can I really do this?**  
Absolutely! This series assumes zero programming knowledge. We start from "What is PHP?" and build up systematically. Thousands of developers have learned PHP as their first language.

**How do I know when I'm ready to move to the next chapter?**  
Each chapter ends with exercises. If you can complete them without looking at the answers, you're ready to continue. Don't rush—mastery takes time.

**Should I memorize all the functions?**  
No! Professional developers look things up constantly. Focus on understanding concepts, not memorizing syntax. The [PHP documentation](https://www.php.net/docs.php) is excellent—learn to use it.

**What if the exercises are too hard?**  
Go back and re-read the chapter. Try the example code yourself. Break the problem into smaller pieces. If you're still stuck, check the troubleshooting section or ask for help (see below).

**Can I use PHP 8.0, 8.1, 8.2, or 8.3 instead of 8.4?**
Most examples will work on PHP 8.0+, but some advanced features (property hooks and asymmetric visibility in Chapter 8) specifically require PHP 8.4. We strongly recommend installing 8.4 to get the complete learning experience and avoid compatibility issues.

**Which IDE/editor should I use?**  
Use whatever you're comfortable with. VS Code (free) is popular and has excellent PHP extensions. PhpStorm (paid, with free student licenses) is the industry standard. Even a simple text editor like Sublime Text works fine.

**How long should each chapter take?**  
Most chapters take 30 minutes to 2 hours depending on your pace and how much you experiment. The project chapters (18–19) will take 3–5 hours each. Don't rush—understanding is more important than speed.

**What comes after this series?**  
After completing this series, you'll be ready for framework-specific learning. We recommend either Laravel (most popular, great for startups and general web apps) or Symfony (powerful, used in enterprises). Both are covered in Chapters 20–21.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Read the troubleshooting section** in each chapter for common issues
- **Check the code samples** in `docs/series/php-basics/code/` for working examples
- **Consult PHP documentation**: [php.net](https://www.php.net/) is comprehensive and well-maintained
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report bugs**: [Open an issue](https://github.com/dalehurley/codewithphp/issues) for unclear explanations or broken examples

## Related Resources

Want to dive deeper? These resources complement the series:

- **[PHP Manual](https://www.php.net/manual/en/)**: Official documentation (bookmark this!)
- **[PHP: The Right Way](https://phptherightway.com/)**: Modern best practices and patterns
- **[PHP Fig (PSR Standards)](https://www.php-fig.org/)**: Learn about community standards
- **[Composer](https://getcomposer.org/)**: Dependency management (covered in Chapter 12)
- **[Laravel Documentation](https://laravel.com/docs)**: After finishing the series
- **[Symfony Documentation](https://symfony.com/doc/current/index.html)**: After finishing the series

---

::: tip Ready to Start?
Head to [Chapter 00: Setting Up Your Development Environment](/series/php-basics/chapters/00-setting-up-your-development-environment) to begin your journey!
:::

---

## Continue Your Learning

Finished this series? Take your skills further:

**→ [AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Add intelligent features to your PHP applications  
**→ [Python to Laravel](/series/python-developers-love-php-laravel/)** — Explore Laravel if you know Python

<style>
:root {
  --primary-teal: #0d9488;
  --primary-teal-dark: #0f766e;
  --php-amber: #f59e0b;
  --php-orange: #ea580c;
  --ai-purple: #7c3aed;
  --ai-violet: #8b5cf6;
  --python-blue: #0ea5e9;
  --python-cyan: #06b6d4;
  --neutral-gray: #64748b;
  --bg-light: #f8fafc;
}

/* Chapter card enhancements */
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--php-amber);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(245, 158, 11, 0.15);
  border-left-color: var(--php-orange);
}

/* Image styling */
div[style*="display: flex"] img[style*="width: 180px"] {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

div[style*="display: flex"]:hover img[style*="width: 180px"] {
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

/* Link styling */
div[style*="display: flex"] h4 a {
  color: var(--php-amber);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--php-orange);
}
</style>
