---
title: PHP Basics
description: Master modern PHP from zero to building your own blog—no frameworks, just fundamentals.
series: php-basics
order: 0
difficulty: Beginner
prerequisites:
  ["Basic computer literacy", "Text editor familiarity", "Willingness to learn"]
---

# PHP Basics

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

- [**00 — Setting Up Your Development Environment**](/series/php-basics/chapters/00-setting-up-your-development-environment)  
  Install PHP 8.4, configure your editor, and verify your setup
- [**01 — Your First PHP Script**](/series/php-basics/chapters/01-your-first-php-script)  
  Write "Hello, World!" and understand how PHP executes

### Part 2: Core Language Fundamentals (Chapters 02–07)

Master the building blocks: variables, control flow, functions, arrays, and strings.

- [**02 — Variables, Data Types, and Constants**](/series/php-basics/chapters/02-variables-data-types-and-constants)  
  Learn PHP's type system and how to store data
- [**03 — Control Structures**](/series/php-basics/chapters/03-control-structures)  
  Make decisions with if/else, switch, and loops
- [**04 — Understanding and Using Functions**](/series/php-basics/chapters/04-understanding-and-using-functions)  
  Write reusable, modular code with functions
- [**05 — Handling HTML Forms and User Input**](/series/php-basics/chapters/05-handling-html-forms-and-user-input)  
  Process GET/POST requests and sanitize user data
- [**06 — Deep Dive into Arrays**](/series/php-basics/chapters/06-deep-dive-into-arrays)  
  Work with indexed and associative arrays, plus powerful array functions
- [**07 — Mastering String Manipulation**](/series/php-basics/chapters/07-mastering-string-manipulation)  
  Format, search, and transform text efficiently

### Part 3: Object-Oriented Programming (Chapters 08–10)

Learn modern OOP principles that power professional PHP applications.

- [**08 — Introduction to Object-Oriented Programming**](/series/php-basics/chapters/08-introduction-to-object-oriented-programming)  
  Classes, objects, properties, methods, and encapsulation
- [**09 — OOP: Inheritance, Abstract Classes, and Interfaces**](/series/php-basics/chapters/09-oop-inheritance-abstract-classes-and-interfaces)  
  Build flexible, extensible class hierarchies
- [**10 — OOP: Traits and Namespaces**](/series/php-basics/chapters/10-oop-traits-and-namespaces)  
  Code reuse with traits and organize with namespaces

### Part 4: Professional PHP Development (Chapters 11–16)

Essential skills for production applications: error handling, dependencies, files, databases, and standards.

- [**11 — Error and Exception Handling**](/series/php-basics/chapters/11-error-and-exception-handling)  
  Handle failures gracefully and debug effectively
- [**12 — Dependency Management with Composer**](/series/php-basics/chapters/12-dependency-management-with-composer)  
  Use Composer to manage packages and autoloading
- [**13 — Working with the Filesystem**](/series/php-basics/chapters/13-working-with-the-filesystem)  
  Read, write, and manage files and directories safely
- [**14 — Interacting with Databases using PDO**](/series/php-basics/chapters/14-interacting-with-databases-using-pdo)  
  Connect to databases and run secure queries with prepared statements
- [**15 — Managing State with Sessions and Cookies**](/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies)  
  Track users across requests and build authentication
- [**16 — Writing Better Code with PSR-1 and PSR-12**](/series/php-basics/chapters/16-writing-better-code-with-psr-1-and-psr-12)  
  Follow industry coding standards for readable, maintainable code

### Part 5: Building Real Applications (Chapters 17–19)

Put it all together: build a router, structure an app, and create a complete blog.

- [**17 — Building a Basic HTTP Router**](/series/php-basics/chapters/17-building-a-basic-http-router)  
  Create your own router to handle URLs and requests
- [**18 — Project: Structuring a Simple Application**](/series/php-basics/chapters/18-project-structuring-a-simple-application)  
  Design a clean MVC architecture from scratch
- [**19 — Project: Building a Simple Blog**](/series/php-basics/chapters/19-project-building-a-simple-blog)  
  Build a full CRUD application with authentication and database

### Part 6: Frameworks & Beyond (Chapters 20–23)

Graduate to modern frameworks and master essential web technologies.

- [**20 — A Gentle Introduction to Laravel**](/series/php-basics/chapters/20-a-gentle-introduction-to-laravel)
  Get started with the world's most popular PHP framework
- [**21 — A Gentle Introduction to Symfony**](/series/php-basics/chapters/21-a-gentle-introduction-to-symfony)
  Explore Symfony's powerful component architecture
- [**22 — What to Learn Next**](/series/php-basics/chapters/22-what-to-learn-next)
  Continue your PHP journey with advanced topics and resources
- [**23 — Working with JSON and APIs**](/series/php-basics/chapters/23-working-with-json-and-apis)
  Master JSON handling and consume RESTful APIs

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
