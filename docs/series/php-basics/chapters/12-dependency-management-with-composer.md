---
title: "12: Dependency Management with Composer"
description: "Step into the world of modern PHP by learning to manage third-party packages and autoload your own classes with Composer, the essential dependency manager."
series: "php-basics"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/11-error-and-exception-handling"
---

# Chapter 12: Dependency Management with Composer

## Overview

Welcome to Part 3 of the series! From here on, we'll be focusing on the tools and techniques used to build modern, professional PHP applications. We're going to start with the single most important tool in the modern PHP ecosystem: **Composer**.

So far, we've written all our code from scratch. But what if you need a powerful logging library, a tool to handle HTTP requests, or a library to generate fake data for testing? Building these yourself would take a huge amount of time. Composer is a **dependency manager** for PHP. It allows you to declare the libraries (or "packages") your project depends on, and it will manage installing and updating them for you.

Crucially, Composer also provides a powerful **autoloader**. This means we no longer have to litter our files with `require_once` statements. We can simply `use` a class, and Composer will automatically find and load the correct file for us. This is a game-changer for building organized, large-scale applications.

## Prerequisites

Before starting this chapter, you should:

- Have completed [Chapter 11: Error and Exception Handling](/series/php-basics/chapters/11-error-and-exception-handling)
- Have PHP 8.4 installed and accessible via the command line
- Have basic command-line experience (navigating directories, running commands)
- Have internet access to download packages from Packagist
- **Estimated time**: ~32 minutes

## What You'll Build

By the end of this chapter, you will have:

- Composer installed and verified on your system
- A new Composer-managed PHP project with a `composer.json` file
- A working integration with Monolog, a third-party logging library
- A PSR-4 autoloader configured for your own application code
- Development dependencies properly separated using `require-dev`
- Hands-on experience with the `composer install` workflow
- A clean project structure ready for building larger applications

## Objectives

- Understand the role of a dependency manager.
- Install Composer on your system.
- Initialize a new Composer project and understand the `composer.json` file.
- Install a third-party package from [Packagist](https://packagist.org/).
- Configure and use Composer's PSR-4 autoloader for your own application's classes.

## Quick Start

If you already have Composer installed and want to jump straight in:

```bash
# Create project directory
mkdir simple-blog && cd simple-blog

# Initialize Composer project
composer init --no-interaction

# Install Monolog
composer require monolog/monolog

# Set up PSR-4 autoloading
mkdir -p src/Models
# Add "autoload": { "psr-4": { "App\\": "src/" } } to composer.json
composer dump-autoload

# Create index.php with the examples below and run
php index.php
```

For detailed explanations and troubleshooting, follow the step-by-step instructions.

## Step 1: Installing Composer (~5 min)

First, you need to install Composer globally on your system so you can use the `composer` command from anywhere.

1.  **Follow the Official Instructions**:
    The installation process can change, so the best way to install Composer is to follow the official, up-to-date instructions on the Composer website.

    - **Go to:** [https://getcomposer.org/download/](https://getcomposer.org/download/)

    Follow the command-line installation instructions for your operating system. For Linux and macOS, this typically involves running a few commands in your terminal. For Windows, there's a handy `Composer-Setup.exe` you can download and run.

2.  **Verify the Installation**:
    Once the installation is complete, open a **new** terminal and run:

    ```bash
    # Check Composer is installed and accessible
    composer --version
    ```

    **Expected output:**

    ```
    Composer version 2.7.2 2024-04-20 12:30:42
    ```

    The exact version number may differ. As long as you see `Composer version` followed by a version number, Composer is ready to go!

### Troubleshooting

**Problem**: `composer: command not found`

- **Solution 1**: Make sure you opened a **new** terminal window after installation. The `PATH` changes don't take effect in existing terminal sessions.
- **Solution 2**: On Linux/macOS, verify Composer is in your PATH by running `which composer`. If nothing appears, you may need to move the composer binary to `/usr/local/bin/` or add its location to your `PATH` environment variable.
- **Solution 3**: On Windows, reboot your computer if reopening the terminal doesn't work.

**Problem**: Permission errors during installation

- **Solution**: On Linux/macOS, you may need to use `sudo` for the final installation step, or install Composer to a directory you have write access to.

**Problem**: PHP version errors

- **Solution**: Composer requires PHP 7.2 or higher. Run `php --version` to check your PHP version. If it's too old, refer back to [Chapter 0: Setting Up Your Development Environment](/series/php-basics/chapters/00-setting-up-your-development-environment) to upgrade PHP.

## Step 2: Starting a New Composer Project (~4 min)

Let's start a new project for the simple blog we'll be building in the upcoming chapters.

1.  **Create a Project Directory**:
    From your main coding folder, create a new directory for our blog application and navigate into it.

    ```bash
    # Create and enter the project directory
    mkdir simple-blog
    cd simple-blog
    ```

2.  **Initialize the Project**:
    Run the `composer init` command. This will launch an interactive wizard to help you create your `composer.json` file.

    ```bash
    # Start the interactive project setup
    composer init
    ```

    You can accept the defaults for most of the questions by just pressing Enter, but fill in your own information for the author part. When it asks if you want to define dependencies interactively, say `no` for now.

    **Interactive prompts you'll see:**

    ```
    Package name (<vendor>/<name>): your-name/simple-blog
    Description []: A simple blog application
    Author [Your Name <you@example.com>, n to skip]: (press Enter)
    Minimum Stability []: (press Enter)
    Package Type (e.g. library, project, metapackage, composer-plugin) []: (press Enter)
    License []: (press Enter)
    Would you like to define your dependencies (require) interactively [yes]? no
    Would you like to define your dev dependencies (require-dev) interactively [yes]? no
    Add PSR-4 autoload mapping? Maps namespace "YourName\SimpleBlog" to the entered relative path. [src/, n to skip]: n
    ```

3.  **Verify the Result**:
    After the wizard is done, you will have a new `composer.json` file in your directory. View it:

    ```bash
    # Display the contents of composer.json
    cat composer.json
    ```

    **Expected output:**

    ```json
    {
      "name": "your-name/simple-blog",
      "description": "A simple blog application",
      "authors": [
        {
          "name": "Your Name",
          "email": "you@example.com"
        }
      ],
      "require": {}
    }
    ```

    This JSON file describes your project. The empty `require` object will soon contain your dependencies.

> **Why These Fields Matter:**
>
> - `name`: Identifies your package if you ever publish it to Packagist.
> - `authors`: Credits the creators.
> - `require`: Lists production dependencies (libraries your app needs to run).
> - `require-dev`: Lists development dependencies (tools for testing, debugging, etc. – we'll use this later).

## Step 3: Adding a Third-Party Package (~5 min)

Let's add our first dependency. We'll install `monolog/monolog`, a very popular and powerful logging library.

1.  **Require the Package**:
    The `composer require` command is used to add a new dependency to your project.

    ```bash
    # Install Monolog and add it to composer.json
    composer require monolog/monolog
    ```

    **Expected output:**

    ```
    Info from https://repo.packagist.org: #StandWithUkraine
    Using version ^3.5 for monolog/monolog
    ./composer.json has been updated
    Running composer update monolog/monolog
    Loading composer repositories with package information
    Updating dependencies
    Lock file operations: 2 installs, 0 updates, 0 removals
      - Locking monolog/monolog (3.5.0)
      - Locking psr/log (3.0.0)
    Writing lock file
    Installing dependencies from lock file (including require-dev)
    Package operations: 2 installs, 0 updates, 0 removals
      - Downloading monolog/monolog (3.5.0)
      - Downloading psr/log (3.0.0)
      - Installing psr/log (3.0.0): Extracting archive
      - Installing monolog/monolog (3.5.0): Extracting archive
    Generating autoload files
    ```

    Composer has done several important things:

    - **Found** the latest compatible version of Monolog on [Packagist](https://packagist.org/).
    - **Downloaded** the library and its dependencies into a new `vendor/` directory.
    - **Updated** your `composer.json` file to include Monolog in the `require` section.
    - **Created** a `composer.lock` file, which records the exact versions of all packages installed. This ensures everyone on your team uses identical versions.
    - **Generated** the autoloader files inside `vendor/composer/`.

2.  **Verify the Changes**:
    Check your `composer.json` file:

    ```bash
    # View the updated composer.json
    cat composer.json
    ```

    You should now see:

    ```json
    {
      "name": "your-name/simple-blog",
      "require": {
        "monolog/monolog": "^3.5"
      }
    }
    ```

    The `^3.5` is a version constraint using semantic versioning. It means "any version >= 3.5.0 but < 4.0.0".

    > **Important**: You should commit `composer.json` and `composer.lock` to version control (like Git), but **ignore** the `vendor/` directory. Other developers run `composer install` to download the exact dependencies from `composer.lock`.

3.  **Use the Package**:
    Create a new file `index.php` and add the following code to see the autoloader in action.

    **File: `index.php`**

    ```php
    <?php

    // This one line is all we need to activate Composer's autoloader.
    require_once 'vendor/autoload.php';

    // Now we can `use` the classes from our installed package.
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    // Create a log channel
    $log = new Logger('MyFirstLogger');
    $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));

    // Add records to the log
    $log->warning('This is a warning message.');
    $log->error('This is an error message.');

    echo "Log messages have been written to app.log!" . PHP_EOL;
    ```

4.  **Run and Verify**:
    Execute the script:

    ```bash
    # Run the script
    php index.php
    ```

    **Expected output:**

    ```
    Log messages have been written to app.log!
    ```

    Now check the log file:

    ```bash
    # View the generated log file
    cat app.log
    ```

    **Expected output:**

    ```
    [2024-04-20T14:23:45.123456+00:00] MyFirstLogger.WARNING: This is a warning message. [] []
    [2024-04-20T14:23:45.234567+00:00] MyFirstLogger.ERROR: This is an error message. [] []
    ```

    Success! We used the `Logger` and `StreamHandler` classes without a single manual `require` statement. The autoloader handled everything.

### Troubleshooting

**Problem**: `Fatal error: Uncaught Error: Class "Monolog\Logger" not found`

- **Solution**: Make sure you included `require_once 'vendor/autoload.php';` at the top of your PHP file. The autoloader must be loaded before you can use any Composer packages.

**Problem**: `Warning: require_once(vendor/autoload.php): Failed to open stream`

- **Solution**: You're running the script from the wrong directory. Make sure you're in the `simple-blog` directory where the `vendor/` folder exists. Use `pwd` (Linux/macOS) or `cd` (Windows) to check your current directory.

**Problem**: Composer takes a very long time or times out

- **Solution**: This is usually a network issue. If you're behind a corporate proxy, you may need to configure Composer to use it. See the [Composer proxy documentation](https://getcomposer.org/doc/03-cli.md#composer-home).

## Step 4: Autoloading Your Own Code with PSR-4 (~6 min)

The real power comes when we use Composer to autoload our _own_ application's classes. The standard for this is **PSR-4**. It's a convention that maps a namespace prefix to a directory.

Let's set up a `src/` directory for our app's code, with the namespace `App`.

1.  **Create a `src/` Directory**:
    Create a new directory named `src` in your project root, along with a `Models` subdirectory.

    ```bash
    # Create the directory structure for our application code
    mkdir -p src/Models
    ```

2.  **Configure `composer.json`**:
    Add an `autoload` section to your `composer.json` file to map the `App\\` namespace prefix to the `src/` directory.

    **File: `composer.json`**

    ```json
    {
      "name": "your-name/simple-blog",
      "authors": [
        {
          "name": "Your Name",
          "email": "you@example.com"
        }
      ],
      "require": {
        "monolog/monolog": "^3.5"
      },
      "autoload": {
        "psr-4": {
          "App\\": "src/"
        }
      }
    }
    ```

    > **Understanding the Mapping:**
    >
    > - `"App\\"` → `"src/"` means: any class with namespace `App\Something` will be found in `src/Something.php`
    > - `App\Models\User` → `src/Models/User.php`
    > - `App\Controllers\BlogController` → `src/Controllers/BlogController.php`
    > - The namespace structure must exactly match the directory structure.

3.  **Update the Autoloader**:
    After changing `composer.json`, you must regenerate the autoloader files.

    ```bash
    # Regenerate the autoloader to include our new mapping
    composer dump-autoload
    ```

    **Expected output:**

    ```
    Generating autoload files
    Generated autoload files
    ```

4.  **Create a Namespaced Class**:
    Now, create a new class inside the `src/` directory that follows the PSR-4 mapping.

    - Namespace: `App\Models`
    - Class: `User`
    - File location: `src/Models/User.php`

    **File: `src/Models/User.php`**

    ```php
    <?php

    namespace App\Models;

    class User
    {
        public string $name;

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        public function greet(): string
        {
            return "Hello, my name is {$this->name}!";
        }
    }
    ```

    > **Note**: This code uses typed properties (`public string $name`) and return type declarations (`public function greet(): string`), which are fully supported in PHP 8.4. If you see these type declarations in modern PHP code, they help prevent bugs by ensuring variables contain the expected data types.

5.  **Use Your Class**:
    Update your `index.php` to use your new `User` class alongside Monolog.

    **File: `index.php`**

    ```php
    <?php
    require_once 'vendor/autoload.php';

    // Use a class from a third-party package
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    // Use a class from our own application
    use App\Models\User;

    // Set up logging
    $logger = new Logger('UserLogger');
    $logger->pushHandler(new StreamHandler('app.log', Logger::INFO));

    // Create and use our User class
    $user = new User('Dale');
    $logger->info("A new user was created: " . $user->name);

    echo $user->greet() . PHP_EOL;
    ```

6.  **Run and Verify**:
    Execute the updated script:

    ```bash
    # Run the script
    php index.php
    ```

    **Expected output:**

    ```
    Hello, my name is Dale!
    ```

    Perfect! Composer now knows how to find and load both third-party packages and your own application's classes, as long as you follow the PSR-4 namespace-to-directory structure.

> **Tip: Git Best Practices**
>
> If you're using Git for version control, create a `.gitignore` file in your project root with this content:
>
> ```
> /vendor/
> /app.log
> ```
>
> This ensures you don't commit the `vendor/` directory or log files. Other developers will run `composer install` to get their own `vendor/` folder with the exact same versions specified in `composer.lock`.

### Troubleshooting

**Problem**: `Fatal error: Uncaught Error: Class "App\Models\User" not found`

- **Solution 1**: Make sure you ran `composer dump-autoload` after adding the `autoload` section to `composer.json`.
- **Solution 2**: Verify the file path exactly matches the namespace. `App\Models\User` must be in `src/Models/User.php` (case-sensitive on Linux/macOS).
- **Solution 3**: Check that the `namespace` declaration in `User.php` exactly matches: `namespace App\Models;`

**Problem**: Autoloader works for third-party packages but not my own classes

- **Solution**: The `autoload` section in `composer.json` is separate from `require`. Make sure you added it correctly and ran `composer dump-autoload`.

## Step 5: Development Dependencies (~3 min)

Not all packages are needed in production. Testing frameworks, code quality tools, and debugging utilities are only needed during development. Composer separates these into a `require-dev` section.

1.  **Install a Development Dependency**:
    Let's add PHP_CodeSniffer, a tool that checks your code against coding standards like PSR-12.

    ```bash
    # Install as a dev dependency with the --dev flag
    composer require --dev squizlabs/php_codesniffer
    ```

    **Expected output:**

    ```
    Using version ^3.8 for squizlabs/php_codesniffer
    ./composer.json has been updated
    Running composer update squizlabs/php_codesniffer
    Loading composer repositories with package information
    Updating dependencies
    Lock file operations: 1 install, 0 updates, 0 removals
      - Locking squizlabs/php_codesniffer (3.8.1)
    Writing lock file
    Installing dependencies from lock file (including require-dev)
    Package operations: 1 install, 0 updates, 0 removals
      - Installing squizlabs/php_codesniffer (3.8.1): Extracting archive
    Generating autoload files
    ```

2.  **Check Your `composer.json`**:
    View the file to see the new section:

    ```bash
    # Display composer.json
    cat composer.json
    ```

    **Expected output:**

    ```json
    {
      "name": "your-name/simple-blog",
      "require": {
        "monolog/monolog": "^3.5"
      },
      "require-dev": {
        "squizlabs/php_codesniffer": "^3.8"
      },
      "autoload": {
        "psr-4": {
          "App\\": "src/"
        }
      }
    }
    ```

3.  **Use the Development Tool**:
    Development dependencies install executables into `vendor/bin/`. Let's use PHP_CodeSniffer to check our code:

    ```bash
    # Check code style against PSR-12 standard
    ./vendor/bin/phpcs --standard=PSR12 src/Models/User.php
    ```

    If your code follows PSR-12, you'll see no output (which means success!). Otherwise, you'll see specific style violations to fix.

> **Why This Matters:**
>
> When deploying to production, you can run `composer install --no-dev` to skip development dependencies, making your production `vendor/` folder smaller and faster. This separation keeps your production environment lean.

### Troubleshooting

**Problem**: `composer require --dev` adds to `require` instead of `require-dev`

- **Solution**: Make sure you're using a recent version of Composer (2.x). Older versions had different syntax. Run `composer --version` to check.

**Problem**: Can't execute `./vendor/bin/phpcs`

- **Solution 1**: On Windows, use `.\vendor\bin\phpcs.bat` instead.
- **Solution 2**: If you get a permission error on Linux/macOS, run `chmod +x vendor/bin/phpcs` to make it executable.

## Step 6: Working With Existing Projects (~4 min)

One of Composer's biggest benefits is ensuring everyone on a team uses the exact same dependency versions. Let's simulate joining an existing project.

1.  **Simulate a Fresh Clone**:
    Let's pretend you're a new developer joining the team. You've cloned the repository (which includes `composer.json` and `composer.lock`) but not the `vendor/` directory.

    ```bash
    # Delete the vendor directory to simulate a fresh clone
    rm -rf vendor/

    # Verify it's gone
    ls -la
    ```

    You should see `composer.json` and `composer.lock` but no `vendor/` folder.

2.  **Install Dependencies**:
    When you have a `composer.lock` file, use `composer install` (not `require`). This installs the **exact** versions recorded in the lock file:

    ```bash
    # Install exact versions from composer.lock
    composer install
    ```

    **Expected output:**

    ```
    Installing dependencies from lock file (including require-dev)
    Verifying lock file contents can be installed on current platform.
    Package operations: 3 installs, 0 updates, 0 removals
      - Downloading psr/log (3.0.0)
      - Downloading monolog/monolog (3.5.0)
      - Downloading squizlabs/php_codesniffer (3.8.1)
      - Installing psr/log (3.0.0): Extracting archive
      - Installing monolog/monolog (3.5.0): Extracting archive
      - Installing squizlabs/php_codesniffer (3.8.1): Extracting archive
    Generating autoload files
    ```

3.  **Verify Everything Works**:
    Run your application to confirm the dependencies were installed correctly:

    ```bash
    # Test the application
    php index.php
    ```

    **Expected output:**

    ```
    Hello, my name is Dale!
    ```

    Perfect! Your environment now matches exactly what every other developer on the team has.

> **The Workflow:**
>
> - **Adding a new dependency**: Use `composer require <package>` — this updates both `composer.json` and `composer.lock`. Commit both files.
> - **Joining a project or pulling changes**: Use `composer install` — this reads `composer.lock` to install exact versions.
> - **Updating dependencies**: Use `composer update` — this updates to newer versions within constraints and updates `composer.lock`. Commit the updated lock file.

### Understanding the Lock File

The `composer.lock` file is crucial for reproducibility:

```bash
# View part of composer.lock
head -n 30 composer.lock
```

You'll see JSON with exact package versions, including sub-dependencies you didn't directly install. This ensures:

- ✅ Everyone on your team has identical dependencies
- ✅ Your production server matches your development environment
- ✅ Package updates are intentional, not accidental

### Troubleshooting

**Problem**: `composer install` says "Your lock file is out of date"

- **Solution**: Someone updated `composer.json` without running `composer update` to regenerate the lock file. Run `composer update` to sync them, then commit the updated `composer.lock`.

**Problem**: `composer install` gives platform requirement errors (wrong PHP version)

- **Solution**: The project requires a different PHP version than you have. Check `composer.json` for the `"php"` constraint in the `require` section. Install the required PHP version using [Chapter 0](/series/php-basics/chapters/00-setting-up-your-development-environment).

**Problem**: After running `composer install`, tests/scripts fail

- **Solution**: You might have skipped dev dependencies. Make sure you ran `composer install` (which includes dev dependencies) not `composer install --no-dev` (which is only for production).

## Key Concepts Summary

Let's recap what you've learned:

- **Composer** is PHP's dependency manager, allowing you to install and manage third-party libraries.
- **Packagist** is the main repository where Composer finds packages.
- **`composer.json`** describes your project and its dependencies.
- **`composer.lock`** records exact versions for reproducible installs (commit this file!).
- **`vendor/`** contains downloaded packages (don't commit this directory).
- **PSR-4** is the standard for autoloading classes based on namespace-to-directory mapping.
- **`require`** section lists production dependencies your app needs to run.
- **`require-dev`** section lists development dependencies (testing tools, code quality, etc.).
- **`composer require <package>`** adds a new dependency and installs it.
- **`composer require --dev <package>`** adds a development-only dependency.
- **`composer install`** installs exact versions from `composer.lock` (use after cloning a repo).
- **`composer update`** updates dependencies to newer versions within constraints and updates `composer.lock`.
- **`composer dump-autoload`** regenerates the autoloader after changing `composer.json`.

### Understanding Composer Commands

| Command                            | When to Use                    | What It Does                                                              |
| ---------------------------------- | ------------------------------ | ------------------------------------------------------------------------- |
| `composer require <package>`       | Adding a new dependency        | Adds package to `composer.json`, installs it, and updates `composer.lock` |
| `composer require --dev <package>` | Adding a dev dependency        | Adds package to `require-dev` section for development-only use            |
| `composer install`                 | After cloning a project        | Installs exact versions from `composer.lock` (reproducible builds)        |
| `composer install --no-dev`        | Deploying to production        | Installs only production dependencies, skipping `require-dev`             |
| `composer update`                  | Updating dependencies          | Updates packages to newer versions within version constraints             |
| `composer update <package>`        | Updating one package           | Updates only the specified package                                        |
| `composer dump-autoload`           | After changing autoload config | Regenerates autoloader without touching dependencies                      |
| `composer remove <package>`        | Removing a dependency          | Removes package from `composer.json` and uninstalls it                    |

## Exercises

Try these to reinforce your understanding:

1.  **Add another package**: Install the `vlucas/phpdotenv` package (used for managing environment variables). Add it with `composer require vlucas/phpdotenv` and read its documentation on Packagist to create a simple `.env` file and load it.

2.  **Create more classes**: Add a `Post` class in `App\Models\Post` with properties for `title`, `content`, and `author`. Use it in `index.php`.

3.  **Organize further**: Create a `src/Services/` directory and add a `LoggerService` class that wraps Monolog setup. Use the namespace `App\Services\LoggerService` and instantiate it in your `index.php`.

4.  **Explore Packagist**: Visit [packagist.org](https://packagist.org/) and browse popular packages. Check out `guzzlehttp/guzzle` (HTTP client), `faker/faker` (fake data generator), or `symfony/console` (CLI tool builder).

5.  **Version constraints**: In your `composer.json`, change the Monolog constraint from `^3.5` to `~3.5.0` and run `composer update`. Research what `^`, `~`, and exact versions mean in semantic versioning.

## Wrap-up

This was a huge leap into the world of modern PHP development. You now understand how to use Composer, the cornerstone of the entire ecosystem. You can initialize a project, pull in powerful third-party libraries, and set up a professional, PSR-4 compliant autoloader for your own code. The days of manual `require` statements are over!

**What you accomplished:**

- ✅ Installed Composer globally
- ✅ Created a new Composer project with `composer.json`
- ✅ Installed and used a third-party package (Monolog)
- ✅ Configured PSR-4 autoloading for your own classes
- ✅ Separated development and production dependencies with `require-dev`
- ✅ Learned the `composer install` workflow for team collaboration
- ✅ Built a working project structure ready for expansion

In the next chapter, we'll put this to use and start interacting with the filesystem in a more structured way using Composer-managed libraries.

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- `example-project/` - Complete Composer project with dependencies
- `solutions/` - Solutions to chapter exercises

The example project includes a working `composer.json`, autoloading setup, and demonstrates using third-party packages.
:::

## Further Reading

- [Composer Official Documentation](https://getcomposer.org/doc/) — Comprehensive guide to all Composer features
- [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/) — The official PSR-4 specification
- [Packagist](https://packagist.org/) — Browse thousands of PHP packages
- [Semantic Versioning](https://semver.org/) — Understanding version numbers (MAJOR.MINOR.PATCH)
- [Composer Version Constraints](https://getcomposer.org/doc/articles/versions.md) — Master `^`, `~`, `*` and exact versions
