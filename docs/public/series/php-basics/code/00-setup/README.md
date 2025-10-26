# Chapter 00: Setting Up Your Development Environment - Code Examples

This directory contains working code examples from Chapter 00.

## Files Overview

### 1. `phpinfo-test.php`

Displays comprehensive information about your PHP installation.

**What it demonstrates:**

- How to check your PHP configuration
- Viewing loaded extensions
- Confirming PHP version

**Run it:**

```bash
php phpinfo-test.php
```

### 2. `hello-world.php`

Your first PHP script - the classic "Hello, World!" example.

**What it demonstrates:**

- Basic PHP syntax
- Using `echo` for output
- The `PHP_EOL` constant for cross-platform newlines

**Run it:**

```bash
php hello-world.php
```

**Expected output:**

```
Hello, World!
```

### 3. `debug-test.php`

A script for testing your Xdebug installation and learning to debug.

**What it demonstrates:**

- Setting breakpoints
- Stepping through code
- Inspecting variable values
- Using VS Code debugger

**Run it:**

1. Open `debug-test.php` in VS Code
2. Set a breakpoint on line 20 (click in the left margin)
3. Press F5 to start debugging
4. Run in terminal: `php debug-test.php`
5. VS Code should pause at your breakpoint

## Quick Verification Test

Run all examples to verify your setup:

```bash
# Test 1: Check PHP is installed
php --version

# Test 2: Run phpinfo (output will be long)
php phpinfo-test.php | head -20

# Test 3: Run hello world
php hello-world.php

# Test 4: Test debugging (requires VS Code)
# Follow the instructions above for debug-test.php
```

## Troubleshooting

**Problem:** `php: command not found`

- **Solution:** PHP is not installed or not in your PATH. Return to Chapter 00 Step 1 for installation instructions.

**Problem:** Xdebug not working in debug-test.php

- **Solution:**
  1. Check Xdebug is installed: `php -v` should show "with Xdebug"
  2. Verify `xdebug.mode=debug` in your php.ini
  3. Ensure PHP Debug extension is installed in VS Code
  4. Check launch.json exists in .vscode/ folder

## Next Steps

Once all scripts run successfully, you're ready to proceed to Chapter 01!

## Related Chapter

[Chapter 00: Setting Up Your Development Environment](../../chapters/00-setting-up-your-development-environment.md)
