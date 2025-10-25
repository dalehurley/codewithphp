---
title: Getting Started
description: Install PHP and run your first script.
series: php-basics
chapter: Getting Started
order: 1
difficulty: Beginner
prerequisites:
  - A terminal and a text editor
---

# 01 — Getting Started

## Objectives

- Verify PHP is installed
- Run a simple PHP script

## Prerequisites

- Terminal access and a code editor

## Steps

1. Check your PHP version:

```bash
php -v
```

2. Create a new file `hello.php` (already included in this series code):

```php
<?php
echo "Hello, PHP From Scratch!\n";
```

3. Run the script from the series directory:

```bash
php code/hello.php
```

You should see:

```text
Hello, PHP From Scratch!
```

## Exercises

- Modify the message to include your name
- Print the current date using `date('Y-m-d')`

## Code

- Source: ./../code/hello.php
