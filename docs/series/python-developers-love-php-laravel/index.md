---
title: Why Python Developers Will Love PHP and Laravel
description: Discover how your Python skills translate beautifully to PHP and Laravel—modern web development with familiar patterns and delightful surprises.
series: python-developers-love-php-laravel
order: 0
difficulty: Intermediate
prerequisites:
  [
    "Python web development experience (Django/Flask)",
    "Basic understanding of MVC patterns",
    "Familiarity with ORMs and databases",
  ]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Python to Laravel</span>
</div>

![Why Python Developers Will Love PHP and Laravel](/images/python-developers-love-php-laravel/chapter-00-series-index-hero-full.webp)

# Why Python Developers Will Love PHP and Laravel <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Welcome to **Why Python Developers Will Love PHP and Laravel** — a comprehensive guide designed specifically for Python developers who want to explore the PHP and Laravel ecosystem. If you've built web applications with Django or Flask, written data scripts, or automated tasks with Python, this series will show you how your existing skills map naturally to PHP and Laravel—while introducing you to areas where PHP/Laravel may surprise and delight you.

PHP has evolved dramatically over the past decade. Modern PHP (versions 7 and 8) brings typed properties, JIT compilation, improved performance, and a thriving ecosystem of professional tools. Laravel, PHP's most popular framework, embodies many of the same principles Python developers value: clean code, expressive syntax, "batteries included" philosophy, and exceptional developer experience.

This series isn't about convincing you that Python is wrong or that PHP is better. Instead, it's an honest exploration of how your Python mindset, tools, and best practices translate to PHP and Laravel—and where PHP/Laravel might offer advantages you haven't considered.

## Who This Is For

This series is designed for:

- **Python web developers** who work with Django, Flask, FastAPI, or similar frameworks
- **Data scientists and scripters** who want to explore web development in PHP
- **Full-stack developers** curious about expanding their toolkit
- **Developers considering PHP/Laravel** for new projects but unsure about the learning curve
- **Python developers** who've heard PHP is "old" or "messy" and want to see the modern reality

You don't need to abandon Python or choose sides. This series helps you understand PHP and Laravel so you can make informed decisions about when each ecosystem makes sense.

## Prerequisites

**Software Requirements:**

- **Python experience** (Django or Flask web development preferred)
- **PHP 8.4** (we'll help you install it if needed)
- **Composer** (PHP's dependency manager, similar to pip)
- **Laravel** (we'll install it together)
- **Text editor or IDE** (VS Code, PhpStorm, or your preferred editor)
- **Terminal/Command line** access
- **SQLite or MySQL** (for database examples)

**Time Commitment:**

- **Estimated total**: 15–20 hours to complete all chapters
- **Per chapter**: 30 minutes to 2 hours
- **Bonus project (Chapter 10)**: 3–4 hours

**Skill Assumptions:**

- You're comfortable with Python syntax and web frameworks
- You understand MVC/MVT patterns (from Django/Flask)
- You've worked with ORMs (Django ORM, SQLAlchemy)
- You're familiar with REST APIs and HTTP concepts
- You can use the command line and install software
- No prior PHP knowledge required

## What You'll Build

<ProgressTracker seriesId="python-developers-love-php-laravel" :totalChapters="11" title="Your Progress" />

By working through this series, you will:

1. **Understand PHP/Laravel equivalents** for every Python concept you know
2. **Build comparison examples** showing Python vs PHP/Laravel side-by-side
3. **Create a complete Laravel application** demonstrating routing, models, views, and APIs
4. **Master Laravel's developer tools** (Artisan CLI, migrations, testing)
5. **Learn modern PHP 8.4 syntax** and how it compares to Python
6. **Build REST APIs** in Laravel and compare with Flask/Django REST approaches
7. **Deploy a Laravel application** and understand the deployment ecosystem
8. **Complete a hands-on project** porting Python patterns to Laravel

Every code example includes both Python (familiar) and PHP/Laravel (new) versions, so you can see exactly how concepts translate.

## Learning Objectives

By the end of this series, you will be able to:

- **Map Python concepts to PHP/Laravel** equivalents confidently
- **Write modern PHP 8.4 code** following best practices
- **Build Laravel applications** using familiar MVC patterns
- **Use Eloquent ORM** effectively (comparing to Django ORM/SQLAlchemy)
- **Create REST APIs** in Laravel (comparing to Flask/Django REST)
- **Understand Laravel's ecosystem** and when it excels vs Python
- **Make informed decisions** about when to use PHP/Laravel vs Python
- **Deploy and maintain** Laravel applications professionally

## How This Series Works

This series follows a **comparison-first approach**: we'll show you Python code you already know, then demonstrate the Laravel equivalent. This helps you leverage your existing knowledge while learning new syntax and patterns.

Each chapter includes:

- **Side-by-side code comparisons** (Python → PHP/Laravel)
- **Concept mapping tables** (Django vs Laravel, Flask vs Laravel)
- **Practical examples** you can run immediately
- **Honest discussions** about when each ecosystem makes sense
- **Troubleshooting tips** for common transition challenges

We'll start by mapping familiar concepts (routing, templates, ORMs), then dive into PHP-specific syntax, explore Laravel's developer experience, and finish with a hands-on project that brings everything together.

::: tip
Type the code yourself instead of copy-pasting. The muscle memory of typing PHP syntax will help you internalize the differences from Python.
:::

## Quick Start

Want to see Laravel in action right now? Here's how to get a Laravel app running in under 5 minutes:

```bash
# 1. Install Laravel installer (if you don't have it)
composer global require laravel/installer

# 2. Create a new Laravel project
laravel new quickstart
cd quickstart

# 3. Start the development server
php artisan serve

# Expected: Server running at http://localhost:8000
```

**What's Next?**  
If that worked, you're ready to start! Head to [Chapter 00](/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel) to understand why PHP and Laravel deserve your attention, or jump to [Chapter 01](/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel) to start mapping Python concepts to Laravel.

If you got an error, don't worry—we'll walk you through the complete setup in Chapter 00.

## Learning Path Overview

### Part 1: Introduction and Concept Mapping (Chapters 00–01)

Get oriented and understand how Python concepts translate to PHP/Laravel.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-00-introduction-why-look-at-php-laravel-hero-thumbnail.webp" alt="Chapter 00 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel">00 — Introduction: Why Look at PHP & Laravel</a></h4>
    <p style="margin-bottom: 0;">If you're a Python developer, you might wonder why PHP and Laravel deserve your attention. This chapter sets the stage by exploring why modern PHP (versions 7.4+ and 8.0+) and Laravel have evolved into something that might surprise you. We'll explore PHP's evolution, what modern PHP brings to the table, and how your Python skills translate naturally to PHP/Laravel. By the end, you'll understand the value proposition and have a clear roadmap for the series ahead.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-01-mapping-concepts-python-web-frameworks-vs-laravel-hero-thumbnail.webp" alt="Chapter 01 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/01-mapping-concepts-python-web-frameworks-vs-laravel">01 — Mapping Concepts: Python Web Frameworks vs Laravel</a></h4>
    <p style="margin-bottom: 0;">If you've worked with Django or Flask, you already understand web frameworks—you just need to see how those concepts translate to Laravel. This chapter shows you Python code you know, then demonstrates the Laravel equivalent. You'll see that Laravel isn't fundamentally different from Django or Flask—it's the same concepts with different syntax and conventions. You'll understand routing, views, templates, MVC patterns, and ORM comparisons, recognizing that your Python knowledge accelerates your Laravel learning.</p>
  </div>
</div>

### Part 2: Modern PHP and Syntax (Chapters 02–04)

Discover what's changed in PHP and learn the language differences.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-02-modern-php-whats-changed-hero-thumbnail.webp" alt="Chapter 02 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/02-modern-php-whats-changed">02 — Modern PHP: What's Changed</a></h4>
    <p style="margin-bottom: 0;">Before diving deeper into Laravel, it's crucial to understand the language itself. Modern PHP (versions 7.4+ and especially 8.0+) is fundamentally different from PHP 5.x. PHP 7.x introduced type declarations, massive performance improvements, and modern error handling. PHP 8.0+ added JIT compilation, union types, match expressions, and constructor property promotion. PHP 8.4 continues with property hooks, asymmetric visibility, and improved JIT performance. You'll see side-by-side comparisons showing how PHP's type system, modern syntax, and performance characteristics compare to Python.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-03-laravel-developer-experience-productivity-tools-hero-thumbnail.webp" alt="Chapter 03 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/03-laravel-developer-experience-productivity-tools">03 — Laravel's Developer Experience: Productivity, Conventions and Tools</a></h4>
    <p style="margin-bottom: 0;">Laravel's developer experience tools are where Laravel truly shines. If you've worked with Django's `manage.py` or Flask's CLI tools, you'll find Laravel's Artisan CLI familiar yet more powerful. Laravel follows a "conventions over configuration" philosophy similar to Django, meaning you spend less time configuring and more time building. You'll learn how Laravel's command-line tools, migrations, testing framework, and naming conventions compare to Python equivalents—and where Laravel might surprise you with its developer-friendly features.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-04-php-syntax-language-differences-for-python-devs-hero-thumbnail.webp" alt="Chapter 04 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/04-php-syntax-language-differences-for-python-devs">04 — The PHP Syntax & Language Differences for Python Devs</a></h4>
    <p style="margin-bottom: 0;">When you start writing PHP code, you'll immediately notice syntax differences. The concepts are the same, but PHP uses different symbols, keywords, and conventions. If you're comfortable with Python, you already understand variables, functions, classes, namespaces, and type hints. PHP does all of these things too—just with different syntax. We'll show you Python code you know, then demonstrate the PHP equivalent side-by-side. You'll understand PHP's variable prefixes (`$`), OOP syntax differences, type system variations, namespace conventions, and string handling.</p>
  </div>
</div>

### Part 3: Data and APIs (Chapters 05–06)

Master working with data and building APIs in Laravel.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-05-working-with-data-eloquent-orm-database-workflow-hero-thumbnail.webp" alt="Chapter 05 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow">05 — Working with Data: Eloquent ORM & Database Workflow</a></h4>
    <p style="margin-bottom: 0;">If you've worked with Django ORM or SQLAlchemy, you already understand the Active Record pattern, migrations, relationships, and query builders. Eloquent ORM—Laravel's database abstraction layer—follows the same principles with PHP syntax. The concepts are identical: models represent tables, relationships connect data, migrations version your schema, and query builders let you chain methods. We'll compare every major feature to Django ORM and SQLAlchemy, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master model definitions, migrations, relationships, query building, eager loading, scopes, and model events.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-06-building-rest-apis-integrations-python-to-laravel-hero-thumbnail.webp" alt="Chapter 06 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/06-building-rest-apis-integrations-python-to-laravel">06 — Building REST APIs & Integrations: From Python Flask/Django to Laravel</a></h4>
    <p style="margin-bottom: 0;">If you've built REST APIs with Flask-RESTful or Django REST Framework, you already understand the fundamentals: routes map to endpoints, controllers handle requests, serializers format responses, and authentication protects resources. Laravel's API development follows the same principles with PHP syntax. We'll compare every major feature to Flask-RESTful and Django REST Framework, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master API routes, controllers, resources (response formatting), request validation, authentication (API tokens, Sanctum), pagination, filtering, external API integrations, and API versioning.</p>
  </div>
</div>

### Part 4: Professional Development (Chapters 07–09)

Testing, deployment, ecosystem, and honest decision-making.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-07-testing-deployment-devops-best-practices-hero-thumbnail.webp" alt="Chapter 07 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/07-testing-deployment-devops-best-practices">07 — Testing, Deployment, DevOps: Best Practices You Know + Laravel Workflow</a></h4>
    <p style="margin-bottom: 0;">Professional development requires testing your code, automating deployments, containerizing applications, and managing background jobs. If you've worked with pytest, GitHub Actions, Docker, Celery, or deployment platforms like Heroku or Railway, you already understand the fundamentals. Laravel's testing, deployment, and DevOps tooling follows the same principles with PHP syntax. We'll compare every major DevOps tool to Python equivalents, showing you Python code you know, then demonstrating the Laravel equivalent. You'll master testing with PHPUnit (comparing to pytest), CI/CD workflows with GitHub Actions, Docker containerization, deployment platforms (Laravel Forge/Vapor vs Heroku/Railway), and background jobs with Laravel Queues (comparing to Celery).</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-08-ecosystem-community-packages-where-laravel-excels-hero-thumbnail.webp" alt="Chapter 08 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/08-ecosystem-community-packages-where-laravel-excels">08 — Ecosystem, Community, Packages & Where Laravel Excels</a></h4>
    <p style="margin-bottom: 0;">A framework is only as strong as its ecosystem: the packages, community, and tools that extend its capabilities. If you've worked with Python's package ecosystem—pip, PyPI, Django packages, Flask extensions—you already understand the importance of a thriving ecosystem. Laravel's ecosystem follows the same principles, but with PHP syntax and Laravel's unique strengths. We'll compare every major aspect to Python equivalents, showing you Python patterns you know, then demonstrating the Laravel equivalent. You'll master package management with Composer (comparing to pip), discover popular Laravel packages (Spatie, Livewire, Inertia), compare Python's ecosystem to PHP's, understand community dynamics, and learn where Laravel excels.</p>
  </div>
</div>

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-09-when-to-use-laravel-when-python-still-makes-sense-hero-thumbnail.webp" alt="Chapter 09 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/09-when-to-use-laravel-when-python-still-makes-sense">09 — When to Use Laravel (and When Python Still Makes Sense)</a></h4>
    <p style="margin-bottom: 0;">Understanding a technology isn't enough. You need to know **when to use it**—and when your existing Python stack is still the better choice. This chapter is an honest, practical guide to technology decision-making. We'll give you a clear framework for evaluating your specific situation: project type, team expertise, budget, performance requirements, and long-term maintenance. You'll learn when Laravel's rapid development, exceptional developer experience, and deployment simplicity make it the right choice—and when Python's data science ecosystem, machine learning libraries, or team expertise make Python the better fit. This chapter provides real-world scenarios, cost comparisons, performance considerations, and a practical decision-making framework.</p>
  </div>
</div>

### Part 5: Hands-On Project (Chapter 10)

Build a complete application using everything you've learned.

<div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
  <img src="/images/python-developers-love-php-laravel/chapter-10-bonus-hands-on-mini-project-hero-thumbnail.webp" alt="Chapter 10 thumbnail" style="width: 180px; height: auto; flex-shrink: 0; border-radius: 4px;" />
  <div>
    <h4 style="margin-top: 0;"><a href="/series/python-developers-love-php-laravel/chapters/10-bonus-hands-on-mini-project">10 — Bonus: Hands-On Mini Project</a></h4>
    <p style="margin-bottom: 0;">Bring everything together by building a **complete, production-ready Task Manager application**. Instead of comparing Python to Laravel, we'll build a full application that demonstrates everything you've learned. We'll create **both a traditional full-stack Laravel application with Blade views AND an API-only backend**, showing how Laravel excels at both approaches. You'll implement authentication with Sanctum, Eloquent relationships, comprehensive testing, and deploy to production using Laravel Forge's latest features. This project will showcase how easy Laravel makes common development tasks. By the end, you'll have a working application you can extend, deploy, and use as a portfolio piece.</p>
  </div>
</div>

---

## Frequently Asked Questions

**I'm happy with Python. Why should I learn PHP/Laravel?**  
You don't have to choose! This series helps you understand PHP/Laravel so you can make informed decisions. Many developers use both languages depending on the project. Laravel excels at rapid web application development, while Python remains superior for data science, ML, and certain microservices.

**Will I need to forget Python to learn PHP?**  
Not at all! Your Python knowledge is an asset. This series maps Python concepts to PHP/Laravel equivalents, so you're building on existing knowledge rather than starting from scratch.

**Is PHP really modern? I've heard it's outdated.**  
Modern PHP (7.4+ and especially 8.0+) is a completely different language from PHP 5. It has typed properties, JIT compilation, modern OOP features, and excellent performance. We'll show you exactly what's changed in Chapter 02.

**How long will it take to become productive in Laravel?**  
If you're already comfortable with Django or Flask, you can become productive in Laravel within a few days. The concepts are similar; it's mainly syntax and tooling differences. This series accelerates that learning curve.

**Can I use Laravel for APIs only, or do I need Blade templates?**  
Laravel works excellently as an API-only backend. Many teams use Laravel for APIs with separate frontends (React, Vue, mobile apps). We'll cover both approaches in the series.

**What about performance? Is PHP/Laravel slower than Python?**  
Modern PHP 8+ with JIT is actually faster than Python for web applications in most benchmarks. Laravel's performance is excellent for typical web apps. We'll discuss performance considerations in Chapter 09.

**Do I need to know HTML/CSS/JavaScript?**  
Basic familiarity helps, but Laravel can be used as a pure API backend. We'll focus on backend concepts, though we'll touch on Blade templates for comparison with Django templates.

**Which is better: Laravel or Django?**  
Neither is universally better—they excel in different areas. Laravel offers exceptional developer experience and rapid development. Django provides excellent admin panels and is deeply integrated with Python's data science ecosystem. We'll help you understand when each makes sense.

## Getting Help

**Stuck on something?** Here's where to get help:

- **Read the troubleshooting sections** in each chapter for common issues
- **Check the code samples** in `docs/series/python-developers-love-php-laravel/code/` for working examples
- **Consult Laravel documentation**: [laravel.com/docs](https://laravel.com/docs) is comprehensive and well-maintained
- **PHP Manual**: [php.net](https://www.php.net/) for language reference
- **GitHub Discussions**: [Ask questions and share progress](https://github.com/dalehurley/codewithphp/discussions)
- **Report issues**: [Open an issue](https://github.com/dalehurley/codewithphp/issues) for unclear explanations or broken examples

## Related Resources

Want to dive deeper? These resources complement the series:

- **[Laravel Documentation](https://laravel.com/docs)**: Official Laravel docs (bookmark this!)
- **[PHP: The Right Way](https://phptherightway.com/)**: Modern PHP best practices
- **[PHP Manual](https://www.php.net/manual/en/)**: Official PHP language reference
- **[Laracasts](https://laracasts.com/)**: Excellent Laravel video tutorials
- **[Laravel News](https://laravel-news.com/)**: Stay updated on Laravel ecosystem
- **[PHP Fig (PSR Standards)](https://www.php-fig.org/)**: PHP community standards
- **[Composer](https://getcomposer.org/)**: PHP dependency management (covered in series)

For Python developers specifically:

- **[Django Documentation](https://docs.djangoproject.com/)**: Reference for comparison
- **[Flask Documentation](https://flask.palletsprojects.com/)**: Reference for comparison
- **[Python vs PHP Comparison Articles](https://www.google.com/search?q=python+vs+php+comparison)**: Broader ecosystem comparisons

---

::: tip Ready to Start?
Head to [Chapter 00: Introduction: Why Look at PHP & Laravel](/series/python-developers-love-php-laravel/chapters/00-introduction-why-look-at-php-laravel) to begin your journey!
:::

---

## Continue Your Learning

Want to go deeper with PHP and Laravel?

**→ [PHP Basics](/series/php-basics/)** — Master PHP fundamentals from scratch  
**→ [AI/ML for PHP Developers](/series/ai-ml-php-developers/)** — Add AI capabilities to your Laravel apps

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
.chapter-card,
div[style*="display: flex"][style*="align-items: flex-start"] {
  transition: all 0.3s ease;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--python-blue);
}

div[style*="display: flex"][style*="align-items: flex-start"]:hover {
  background: var(--bg-light);
  transform: translateX(4px);
  box-shadow: 0 2px 12px rgba(14, 165, 233, 0.15);
  border-left-color: var(--python-cyan);
}

/* Image styling */
div[style*="display: flex"] img[style*="width: 180px"] {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

div[style*="display: flex"]:hover img[style*="width: 180px"] {
  box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
}

/* Link styling */
div[style*="display: flex"] h4 a {
  color: var(--python-blue);
  transition: color 0.2s ease;
}

div[style*="display: flex"] h4 a:hover {
  color: var(--python-cyan);
}
</style>
