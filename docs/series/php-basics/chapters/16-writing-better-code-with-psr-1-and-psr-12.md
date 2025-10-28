---
title: "16: Writing Better Code with PSR-1 and PSR-12"
description: "Learn how professional PHP developers write clean, consistent, and interoperable code by following the PSR-1 and PSR-12 coding style standards."
series: "php-basics"
chapter: 16
order: 16
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies"
---

![Writing Better Code with PSR-1 and PSR-12](/images/php-basics/chapter-16-psr-standards-hero-full.webp)

# Chapter 16: Writing Better Code with PSR-1 and PSR-12

## Overview

As you start working on larger projects or with other developers, a new challenge emerges: keeping the codebase clean, readable, and consistent. If every developer formats their code differently—some use tabs, some use spaces; some put curly braces on new lines, some don't—the project quickly becomes a mess.

To solve this, the PHP community, via a group called the **PHP-FIG** (Framework Interoperability Group), has created a set of **PHP Standards Recommendations (PSRs)**. These are documents that define a shared standard for how to write PHP code.

While there are many PSRs, the two most fundamental ones define coding style:

- **PSR-1 (Basic Coding Standard)**: Covers the absolute basics of naming and file structure.
- **PSR-12 (Extended Coding Style)**: Provides a comprehensive set of rules for formatting PHP code, from indentation to spacing around operators.

Following these standards is the mark of a professional PHP developer. It makes your code instantly familiar to others and allows you to seamlessly contribute to open-source projects. In this chapter, we'll learn the key rules and, more importantly, how to automatically enforce them using modern tooling.

## Prerequisites

Before you begin, ensure you have:

- **PHP 8.4** installed and available in your terminal
- **Composer** installed (from Chapter 12)
- A working PHP project (we'll use examples from the blog project built in Chapter 19, but any PHP project works)
- A text editor or IDE (VS Code, PHPStorm, or similar)
- **Estimated time**: 30–35 minutes
- **Skill level**: Beginner (understanding of basic PHP syntax required)

**Check your setup**:

```bash
# Verify PHP version
php --version
# Should show: PHP 8.4.x

# Verify Composer is available
composer --version
# Should show: Composer version 2.x.x
```

## What You'll Build

By the end of this chapter, you'll have:

- **A configured code style checker** that enforces PSR-1 and PSR-12 standards automatically
- **A working `.php-cs-fixer.dist.php` configuration file** with PHPDoc rules in your project
- **Composer scripts** for quick code formatting
- **Professional PHPDoc documentation** on your classes and methods
- **An `.editorconfig` file** ensuring editor consistency across your team
- **Understanding** of the most important coding style rules used in professional PHP projects
- **Validated results** showing your code meets industry standards

## Objectives

- Understand the purpose of the PHP-FIG and PSRs
- Learn the key rules of the PSR-1 and PSR-12 standards
- Install and configure PHP-CS-Fixer to automatically format your code
- Write professional code documentation using PHPDoc
- Set up EditorConfig for cross-editor consistency
- Run the fixer on your project to ensure 100% compliance
- Set up automation for continuous code quality

## Quick Start

Want to get coding standards working in under 5 minutes? Run these commands in your project directory:

```bash
# Install PHP-CS-Fixer
composer require --dev friendsofphp/php-cs-fixer:^3.0

# Create configuration file (macOS/Linux)
cat > .php-cs-fixer.dist.php << 'EOF'
<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
EOF

# Check what would be fixed
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix all code
./vendor/bin/php-cs-fixer fix
```

**Windows users**: Instead of using `cat`, create the `.php-cs-fixer.dist.php` file manually using a text editor with the contents shown above.

**Result**: All your PHP files now comply with PSR-12 standards!

For a deeper understanding of what's happening and how to customize it, continue with the step-by-step guide below.

::: tip
**Starting a New Project?** Add PHP-CS-Fixer from day one. It's much easier to maintain standards from the start than to retrofit them later.

**Existing Project?** Run `--dry-run` first to see the scope of changes. Consider fixing one directory at a time if the project is large.
:::

## Step 1: Understanding the Key Rules (~5 min)

You don't need to memorize the entire specifications. Here are the most important rules you'll encounter from PSR-1 and PSR-12.

### PSR-1: Basic Coding Standard

- Files **MUST** use only `<?php` and `<?= ` tags.
- Files **MUST** use only UTF-8 without BOM for PHP code.
- Class names **MUST** be declared in `PascalCase`.
- Class constants **MUST** be declared in all upper case with underscore separators (`UPPER_SNAKE_CASE`).
- Method names **MUST** be declared in `camelCase`.

### PSR-12: Extended Coding Style

- Code **MUST** use 4 spaces for indenting, not tabs.
- There **MUST NOT** be a hard limit on line length (but a soft limit of 120 characters is recommended).
- There **MUST** be one blank line after the `namespace` declaration, and one blank line after the block of `use` declarations.
- The opening brace `{` for classes and methods **MUST** be on its own line.
- The closing brace `}` for classes and methods **MUST** be on the line after the body.
- Visibility (`public`, `protected`, `private`) **MUST** be declared on all properties and methods.
- Control structure keywords (`if`, `foreach`, etc.) **MUST** have one space after them.
- Opening parentheses for control structures **MUST NOT** have a space after them, and closing parentheses **MUST NOT** have a space before them.

Here's an example of perfectly formatted PSR-12 code:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserExporter
{
    public const FORMAT_JSON = 'json';

    public function exportUser(User $user, string $format): string
    {
        if ($format === self::FORMAT_JSON) {
            return json_encode($user);
        }

        // Other formats could go here...
        throw new \InvalidArgumentException('Invalid format provided.');
    }
}
```

### PHP 8.4 Considerations

PSR-12 was written before PHP 8.4, but the standards still apply. When using modern PHP 8.4 features, maintain the same formatting principles:

**Property Hooks** (PHP 8.4):

```php
class User
{
    public string $name {
        get => strtoupper($this->name);
        set => $this->name = trim($value);
    }
}
```

**Asymmetric Visibility** (PHP 8.4):

```php
class Counter
{
    // Public read, private write
    public private(set) int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
}
```

PHP-CS-Fixer is actively maintained and supports PHP 8.4 features. As new syntax is added to PHP, the tool is updated to format it correctly according to PSR-12 principles.

## Step 2: Setting Up an Automatic Code Styler (~6 min)

**Goal**: Install and configure PHP-CS-Fixer to automatically enforce PSR-12 standards on your codebase.

Memorizing and manually applying all these rules is tedious and error-prone. The professional way is to use a tool that does it for you automatically. One of the most popular tools is **PHP-CS-Fixer** (PHP Coding Standards Fixer).

### Actions

1.  **Navigate to Your Project Directory**:

    ```bash
    # Change to your project directory
    cd /path/to/your/php-project
    ```

2.  **Install the Tool with Composer**:

    We'll install `php-cs-fixer` as a "dev dependency"—a tool that's needed for development but not for running the application in production. We're pinning to version 3.x for stability.

    ```bash
    # Install PHP-CS-Fixer as a development dependency
    composer require --dev friendsofphp/php-cs-fixer:^3.0
    ```

    **Expected Output**:

    ```
    Using version ^3.64.0 for friendsofphp/php-cs-fixer
    ./composer.json has been updated
    Loading composer repositories with package information
    Updating dependencies
    ...
    Package manifest generated successfully.
    ```

3.  **Verify Installation**:

    ```bash
    # Check that PHP-CS-Fixer is available
    ./vendor/bin/php-cs-fixer --version
    ```

    **Expected Output**:

    ```
    PHP CS Fixer 3.64.0 ...
    ```

4.  **Create a Configuration File**:

    PHP-CS-Fixer is configured with a PHP file in your project's root directory. Create a file named `.php-cs-fixer.dist.php`. The `.dist` part is a convention meaning it's a distributable, default configuration that gets committed to version control.

    **File: `.php-cs-fixer.dist.php`**

    ```php
    <?php

    $finder = (new PhpCsFixer\Finder())
        ->in(__DIR__)
        ->exclude('vendor');

    return (new PhpCsFixer\Config())
        ->setRules([
            '@PSR12' => true,
            'strict_param' => true,
            'array_syntax' => ['syntax' => 'short'],
        ])
        ->setFinder($finder);
    ```

### Why It Works

This configuration does three important things:

- **`$finder`**: Tells PHP-CS-Fixer where to look for PHP files. It scans the current directory (`__DIR__`) recursively but excludes the `vendor` folder (which contains third-party code you shouldn't modify).

- **`setRules`**:
  - `'@PSR12' => true`: Applies all the rules from the PSR-12 standard. The `@` prefix indicates a rule set (a bundle of related rules).
  - `'strict_param' => true`: An extra rule that automatically adds `declare(strict_types=1);` to files that are missing it. This makes PHP enforce strict type checking, catching bugs early.
  - `'array_syntax' => ['syntax' => 'short']`: Ensures arrays use modern PHP 8.4 syntax `[]` instead of the old `array()` syntax.

### Troubleshooting

**Problem**: `composer: command not found`

**Solution**: Composer isn't installed or isn't in your PATH. Revisit Chapter 12 or run:

```bash
# Check if Composer is installed
which composer
```

**Problem**: Installation fails with "your requirements could not be resolved"

**Solution**: Your PHP version might be incompatible. Check that you're running PHP 8.4:

```bash
php --version
composer show --platform
```

**Problem**: On Windows, `./vendor/bin/php-cs-fixer` doesn't work

**Solution**: Windows uses backslashes and may need `.bat` extension:

```bash
# Windows Command Prompt
vendor\bin\php-cs-fixer.bat --version

# Windows PowerShell
.\vendor\bin\php-cs-fixer.bat --version
```

Alternatively, use `php` explicitly:

```bash
php vendor/bin/php-cs-fixer --version
```

## Step 3: Running the Fixer (~5 min)

**Goal**: Use PHP-CS-Fixer to detect and automatically fix code style violations in your project.

Now that PHP-CS-Fixer is configured, let's see it in action by creating some poorly formatted code and then watching it get fixed automatically.

### Actions

1.  **Create a File with Bad Formatting**:

    To see the fixer in action, let's create a file `bad-code.php` in your project root with deliberately messy formatting.

    ```php
    <?php
    namespace App;

    class   BadlyFormattedClass
    {
    public function some_function( $arg1,  $arg2){
    if($arg1==$arg2){
    return true;
    }
    return false;
    }
    }
    ```

    Notice the problems:

    - Extra spaces in `class   BadlyFormattedClass`
    - Wrong indentation (should be 4 spaces)
    - Missing space after `if`
    - No spaces around `==`
    - Braces not on their own lines
    - No `declare(strict_types=1);`

    ::: warning
    The method name `some_function` uses snake_case instead of camelCase. While PSR-1 requires camelCase for method names, PHP-CS-Fixer won't rename methods automatically—that would be a breaking code change. You'll need to rename methods manually and update all code that calls them.
    :::

2.  **Perform a "Dry Run"**:

    The `fix` command has a `--dry-run` option that shows you what _would_ be changed without actually modifying the files. This is a safe way to preview changes.

    ```bash
    # Preview what will be fixed (safe, no changes made)
    ./vendor/bin/php-cs-fixer fix --dry-run --diff
    ```

    **Expected Output**:

    ```
    Loaded config default from ".php-cs-fixer.dist.php".
       1) bad-code.php

       ---------- begin diff ----------
    --- bad-code.php
    +++ bad-code.php
    @@ -1,13 +1,18 @@
     <?php
    +
    +declare(strict_types=1);
    +
     namespace App;

    -class   BadlyFormattedClass
    +class BadlyFormattedClass
     {
    -public function some_function( $arg1,  $arg2){
    -if($arg1==$arg2){
    -return true;
    -}
    -return false;
    -}
    +    public function some_function($arg1, $arg2)
    +    {
    +        if ($arg1 == $arg2) {
    +            return true;
    +        }
    +        return false;
    +    }
     }
       ----------- end diff -----------

    Checked all files in 0.012 seconds, 10.000 MB memory used
    ```

    The diff shows exactly what will change:

    - Lines with `-` will be removed
    - Lines with `+` will be added
    - Proper indentation, spacing, and structure will be applied

3.  **Run the Fixer for Real**:

    Now, run the command without `--dry-run` to apply the changes.

    ```bash
    # Fix all code style violations
    ./vendor/bin/php-cs-fixer fix
    ```

    **Expected Output**:

    ```
    Loaded config default from ".php-cs-fixer.dist.php".
       1) bad-code.php

    Fixed all files in 0.015 seconds, 10.000 MB memory used
    ```

4.  **Verify the Results**:

    Open `bad-code.php` and see that it's now perfectly formatted:

    ```php
    <?php

    declare(strict_types=1);

    namespace App;

    class BadlyFormattedClass
    {
        public function some_function($arg1, $arg2)
        {
            if ($arg1 == $arg2) {
                return true;
            }
            return false;
        }
    }
    ```

### Why It Works

PHP-CS-Fixer parses your PHP code into an Abstract Syntax Tree (AST), applies hundreds of rules to check formatting, and then rewrites the code according to PSR-12 standards. Because it understands PHP's syntax, it won't break your code—it only changes formatting and style.

### Validation

To confirm everything is working correctly, run the dry-run command again:

```bash
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

**Expected Output**:

```
Loaded config default from ".php-cs-fixer.dist.php".
No files need fixing.
Checked all files in 0.010 seconds, 10.000 MB memory used
```

If you see "No files need fixing," congratulations! Your code is now 100% PSR-12 compliant.

### Troubleshooting

**Problem**: "No files need fixing" but you know your code has issues

**Solution**: Check that your `.php-cs-fixer.dist.php` file is in the project root and that the `$finder` paths are correct. Try running with verbose output:

```bash
./vendor/bin/php-cs-fixer fix --dry-run --diff -v
```

**Problem**: Fixer modifies files you don't want touched

**Solution**: Add them to the `exclude` list in `.php-cs-fixer.dist.php`:

```php
$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['vendor', 'storage', 'cache', 'public/assets']);
```

**Problem**: "PHP Fatal error: Allowed memory size exhausted"

**Solution**: Increase PHP's memory limit:

```bash
php -d memory_limit=512M ./vendor/bin/php-cs-fixer fix
```

**Problem**: Fixer is too slow on large projects

**Solution**: Use the `--using-cache` option (enabled by default) or specify which directories to scan:

```bash
# Fix only specific directories
./vendor/bin/php-cs-fixer fix src/ tests/
```

## Step 4: Automating with Composer Scripts (~3 min)

**Goal**: Create convenient shortcuts for running PHP-CS-Fixer so you don't have to type long commands.

Typing `./vendor/bin/php-cs-fixer fix` repeatedly gets tedious. Composer allows you to create shortcuts in the `scripts` section of your `composer.json`.

### Actions

1.  **Open `composer.json`** in your project root.

2.  **Add a `scripts` section** (or update it if it already exists):

    ```json
    {
      "require-dev": {
        "friendsofphp/php-cs-fixer": "^3.0"
      },
      "scripts": {
        "style": "php-cs-fixer fix",
        "style:check": "php-cs-fixer fix --dry-run --diff"
      }
    }
    ```

3.  **Test the new commands**:

    ```bash
    # Check for style violations without fixing
    composer style:check

    # Fix all style violations
    composer style
    ```

### Why It Works

Composer scripts are shortcuts that run commands in the context of your project. They automatically find executables in `vendor/bin/`, so you don't need to type the full path or worry about cross-platform differences.

## Step 5: Documenting Your Code with PHPDoc (~5 min)

**Goal**: Learn how to write professional code documentation using PHPDoc comments that IDEs and tools can understand.

While PSR-12 covers _formatting_, it doesn't cover _documentation_. Professional PHP code uses **PHPDoc** (also called DocBlocks) to document classes, methods, properties, and parameters. These special comments help IDEs provide better autocomplete, catch type errors, and make your code self-documenting.

### Understanding PHPDoc Syntax

A PHPDoc comment starts with `/**` (note the double asterisk) and uses special tags like `@param`, `@return`, and `@throws`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Handles user data export in multiple formats.
 *
 * This class provides methods to export user information
 * to various formats for reporting and data portability.
 */
class UserExporter
{
    /**
     * The default export format.
     */
    public const FORMAT_JSON = 'json';

    /**
     * Exports user data to the specified format.
     *
     * @param User $user The user object to export
     * @param string $format The export format (json, xml, csv)
     * @return string The formatted user data
     * @throws \InvalidArgumentException If the format is not supported
     */
    public function exportUser(User $user, string $format): string
    {
        if ($format === self::FORMAT_JSON) {
            return json_encode($user);
        }

        throw new \InvalidArgumentException('Invalid format provided.');
    }

    /**
     * Gets the list of supported export formats.
     *
     * @return string[] Array of supported format names
     */
    public function getSupportedFormats(): array
    {
        return ['json', 'xml', 'csv'];
    }
}
```

### Common PHPDoc Tags

- **`@param type $name Description`** — Documents a method parameter
- **`@return type Description`** — Documents the return value
- **`@throws ExceptionClass Description`** — Documents exceptions that might be thrown
- **`@var type Description`** — Documents a property or variable
- **`@deprecated`** — Marks code that shouldn't be used anymore
- **`@see SomeClass::method()`** — Cross-references related code

### Actions

1.  **Update Your PHP-CS-Fixer Configuration**:

    Add rules to enforce consistent PHPDoc formatting:

    ```php
    <?php

    $finder = (new PhpCsFixer\Finder())
        ->in(__DIR__)
        ->exclude('vendor');

    return (new PhpCsFixer\Config())
        ->setRules([
            '@PSR12' => true,
            'strict_param' => true,
            'array_syntax' => ['syntax' => 'short'],

            // PHPDoc rules
            'phpdoc_align' => ['align' => 'left'],
            'phpdoc_indent' => true,
            'phpdoc_no_empty_return' => true,
            'phpdoc_order' => true,
            'phpdoc_separation' => true,
            'phpdoc_single_line_var_spacing' => true,
            'phpdoc_trim' => true,
            'phpdoc_types' => true,
            'phpdoc_var_annotation_correct_order' => true,
        ])
        ->setFinder($finder);
    ```

2.  **Create a Sample File to Test PHPDoc**:

    **File: `calculator.php`**

    ```php
    <?php

    declare(strict_types=1);

    /**
     * Simple calculator class
     */
    class Calculator
    {
        /**
         * Adds two numbers together
         *
         * @param float $a First number
         * @param float $b Second number
         * @return float The sum of both numbers
         */
        public function add(float $a, float $b): float
        {
            return $a + $b;
        }

        /**
         * Divides two numbers
         *
         * @param float $a The dividend
         * @param float $b The divisor
         * @return float The quotient
         * @throws \DivisionByZeroError If divisor is zero
         */
        public function divide(float $a, float $b): float
        {
            if ($b === 0.0) {
                throw new \DivisionByZeroError('Cannot divide by zero');
            }
            return $a / $b;
        }
    }
    ```

3.  **Run PHP-CS-Fixer**:

    ```bash
    # Format the PHPDoc comments
    composer style
    ```

    PHP-CS-Fixer will standardize your DocBlock formatting for consistency.

### Why It Works

PHPDoc is not just for humans—it's machine-readable. Modern IDEs parse these comments to:

- Provide accurate autocomplete suggestions
- Show parameter hints as you type
- Warn about type mismatches
- Generate documentation automatically
- Enable better refactoring

Static analysis tools like PHPStan and Psalm also use PHPDoc to catch bugs that PHP's type system alone cannot detect, such as:

- Arrays with specific key types
- Union types in older PHP versions
- Generic types (like `array<int, User>`)

### Validation

Open your IDE (VS Code, PHPStorm, etc.) and hover over a method that has PHPDoc. You should see a tooltip with the formatted documentation, making it easy for you (and other developers) to understand what the method does without reading the implementation.

### Troubleshooting

**Problem**: IDE doesn't show PHPDoc hints

**Solution**: Ensure your IDE has PHP support installed:

- **VS Code**: Install the "PHP Intelephense" or "PHP IntelliSense" extension
- **PHPStorm**: PHP support is built-in and enabled by default

**Problem**: Not sure what to document

**Solution**: Focus on the "why" and "what," not the "how":

- ✅ Document _what_ a method does and _why_ it exists
- ✅ Document parameters that aren't obvious from their names
- ✅ Document possible exceptions
- ❌ Don't repeat what the code obviously does

```php
// Bad: Obvious from code
/** Sets the name */
public function setName(string $name): void

// Good: Adds context
/**
 * Sets the display name for the user.
 *
 * The name will be trimmed and validated before storage.
 * @throws \InvalidArgumentException If name is empty after trimming
 */
public function setName(string $name): void
```

## Step 6: Ensuring Editor Consistency with EditorConfig (~2 min)

**Goal**: Create an `.editorconfig` file to ensure all developers use consistent formatting settings, regardless of their editor.

Even with PHP-CS-Fixer, inconsistencies can creep in _while_ developers are typing. Different editors have different defaults for tabs vs. spaces, line endings, and character encoding. **EditorConfig** solves this by providing a standard configuration file that most modern editors support.

### Actions

1.  **Create an `.editorconfig` File**:

    In your project root, create a file named `.editorconfig`:

    **File: `.editorconfig`**

    ```ini
    # EditorConfig is awesome: https://editorconfig.org

    # Top-most EditorConfig file
    root = true

    # Defaults for all files
    [*]
    charset = utf-8
    end_of_line = lf
    insert_final_newline = true
    trim_trailing_whitespace = true

    # PHP files
    [*.php]
    indent_style = space
    indent_size = 4

    # JSON, YAML, Markdown
    [*.{json,yml,yaml,md}]
    indent_style = space
    indent_size = 2

    # Makefiles require tabs
    [Makefile]
    indent_style = tab
    ```

2.  **Verify Your Editor Supports EditorConfig**:

    Most modern editors support EditorConfig out of the box or via a plugin:

    - **VS Code**: Install "EditorConfig for VS Code" extension
    - **PHPStorm**: Built-in support (enabled by default)
    - **Sublime Text**: Install "EditorConfig" package
    - **Vim/Neovim**: Install `editorconfig-vim` plugin

3.  **Test It**:

    Create a new PHP file and press `Tab`. Your editor should automatically insert 4 spaces (not a tab character) because of the EditorConfig rules.

### Why It Works

EditorConfig acts as a pre-emptive quality gate:

- **Before you save**: EditorConfig configures your editor
- **When you save**: PHP-CS-Fixer fixes anything that slipped through

This two-layer approach means:

- Less churn in version control (no endless whitespace changes)
- Faster code reviews (no formatting debates)
- Seamless onboarding (new developers get the right settings automatically)

### Key Settings Explained

- **`charset = utf-8`** — Ensures all files use UTF-8 encoding (required by PSR-1)
- **`end_of_line = lf`** — Uses Unix-style line endings (`\n`), which Git and CI tools prefer
- **`insert_final_newline = true`** — Adds a newline at the end of files (PSR-12 requirement)
- **`trim_trailing_whitespace = true`** — Removes spaces at line ends automatically
- **`indent_style = space`** — Uses spaces, not tabs (PSR-12 requirement)
- **`indent_size = 4`** — Uses 4 spaces per indentation level (PSR-12 requirement)

### Validation

To confirm EditorConfig is working:

1. Open any PHP file
2. Add some trailing spaces at the end of a line
3. Save the file
4. The trailing spaces should be automatically removed (if `trim_trailing_whitespace = true` is working)

::: tip
**Pro Tip**: Commit `.editorconfig` to version control so all team members benefit automatically. It works across Windows, macOS, and Linux with no extra setup needed.
:::

## Exercises

1.  **Fix Your Old Code**:

    - Go back to code you wrote for exercises in previous chapters.
    - Deliberately mess up the formatting (add extra spaces, use tabs, put braces on the wrong line).
    - Run `composer style` and watch the tool instantly clean it all up.
    - **Validation**: Run `composer style:check` and confirm "No files need fixing."

2.  **Add PHPDoc to Previous Code**:

    - Choose a class from Chapter 8, 9, or 10 (OOP chapters)
    - Add complete PHPDoc comments to all public methods
    - Include `@param`, `@return`, and `@throws` tags where appropriate
    - Run `composer style` to format your DocBlocks
    - **Validation**: Open the file in your IDE and hover over a method—you should see a formatted tooltip with your documentation

3.  **Document Return Types with Arrays**:

    - Create a method that returns an array of specific types, like:
      ```php
      /**
       * Gets all active users.
       *
       * @return User[] Array of User objects
       */
      public function getActiveUsers(): array
      {
          return [new User('Alice'), new User('Bob')];
      }
      ```
    - Notice how `@return User[]` specifies it's an array _of User objects_, which PHP's type system alone cannot express
    - This helps IDEs and static analysis tools understand your code better

4.  **Test EditorConfig**:

    - Create a new file `test-editorconfig.php`
    - Press `Tab` and verify 4 spaces are inserted (not a tab character)
    - Add trailing spaces at the end of a line and save—they should disappear automatically
    - Try changing the `indent_size` to `2` in `.editorconfig`, reload your editor, and press `Tab` again
    - **Validation**: The behavior should change to 2 spaces, proving EditorConfig is active

5.  **Explore Other PSR-12 Rules**:

    - Visit the [PHP-CS-Fixer rules documentation](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/rules/index.rst)
    - Add a new rule to your `.php-cs-fixer.dist.php`, such as `'ordered_imports' => true` (sorts use statements alphabetically)
    - Run `composer style` and see what changes
    - **Tip**: Start conservative; don't add too many rules at once

6.  **Pre-commit Hook (Advanced)**:

    - Set up a Git hook that automatically runs the style checker before you commit
    - Create `.git/hooks/pre-commit`:
      ```bash
      #!/bin/sh
      composer style:check
      if [ $? -ne 0 ]; then
          echo "Code style violations found. Run 'composer style' to fix."
          exit 1
      fi
      ```
    - Make it executable: `chmod +x .git/hooks/pre-commit`
    - Now your code will be checked automatically before every commit!

## Wrap-up

Writing clean, standards-compliant code is a non-negotiable skill for professional developers. In this chapter, you've achieved:

✅ **Understanding** of PSR-1 and PSR-12 coding standards and why they matter  
✅ **Installed and configured** PHP-CS-Fixer to enforce these standards automatically  
✅ **Created automation** via Composer scripts for quick code formatting  
✅ **Mastered PHPDoc** for professional code documentation that IDEs and tools understand  
✅ **Set up EditorConfig** to ensure consistency across editors and team members  
✅ **Validated** your code meets industry standards

More importantly, you've learned that you don't need to memorize hundreds of formatting rules. Modern tools like PHP-CS-Fixer, PHPDoc, and EditorConfig handle the details for you, freeing you up to focus on what your code _does_ rather than how it _looks_.

**What You Can Do Now**:

- Apply PSR-12 and PHPDoc to all your PHP projects
- Contribute to open-source PHP projects with confidence
- Work on teams with consistent, readable, well-documented code
- Automate code quality checks in your workflow
- Leverage IDE features powered by PHPDoc for better autocomplete and refactoring

In the next chapter, we'll start putting all these pieces together by designing and building a basic HTTP router, the entry point for modern web applications.

## Further Reading

**Official Specifications**:

- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/) — The official PHP-FIG specification
- [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/) — Complete formatting rules
- [PSR-5: PHPDoc Standard (Draft)](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md) — Proposed standard for PHPDoc
- [PSR-19: PHPDoc Tags (Draft)](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc-tags.md) — Tag definitions
- [PHP-FIG](https://www.php-fig.org/) — The group behind PHP standards

**Tools and Resources**:

- [PHP-CS-Fixer Documentation](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) — Full configuration options and rules
- [PHP-CS-Fixer Rules Index](https://mlocati.github.io/php-cs-fixer-configurator/) — Interactive rule browser
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) — Alternative style checker and fixer
- [EditorConfig](https://editorconfig.org/) — Official documentation and editor plugin list
- [PHPStan](https://phpstan.org/) — Static analysis tool (next-level code quality)
- [Psalm](https://psalm.dev/) — Another excellent static analysis tool

**Documentation Standards**:

- [PHPDoc Documentation](https://docs.phpdoc.org/) — Comprehensive guide to PHPDoc syntax
- [PHPDoc Tags Reference](https://docs.phpdoc.org/guide/references/phpdoc/tags/index.html) — All available tags
- [Generating Documentation with phpDocumentor](https://www.phpdoc.org/) — Tool to create HTML docs from PHPDoc

**IDE Integration**:

- Most modern IDEs (PHPStorm, VS Code with PHP extensions) can automatically format code on save
- Configure your IDE to use `.php-cs-fixer.dist.php` for consistent formatting
- Search for "PHP-CS-Fixer integration" in your IDE's plugin/extension marketplace
- **VS Code Extensions**: PHP Intelephense, EditorConfig for VS Code
- **PHPStorm**: Built-in support for PSR-12, EditorConfig, and PHPDoc

**Other Important PSRs**:

- [PSR-4: Autoloading](https://www.php-fig.org/psr/psr-4/) — How to organize classes and namespaces
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/) — Standard for HTTP requests/responses
- [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11/) — Dependency injection containers
- [PSR-15: HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/) — Middleware interface
