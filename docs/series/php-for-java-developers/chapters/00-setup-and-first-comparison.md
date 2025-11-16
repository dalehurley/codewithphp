---
title: "00: Setup & First Comparison"
description: "Set up your PHP development environment and write your first PHP script alongside Java equivalents"
series: "php-for-java-developers"
chapter: 0
order: 0
difficulty: "Beginner"
prerequisites: []
---

![PHP Setup Hero](/images/php-for-java-developers/chapter-00-setup-hero-full.webp)

# Chapter 0: Setup & First Comparison

## Overview

Welcome to PHP! As a Java developer, you're already familiar with setting up development environments, compilers, and IDEs. PHP's setup is refreshingly simple compared to Java - no need for complex build tools or JVM configuration. In this chapter, we'll get you up and running with PHP and write your first script, comparing it side-by-side with Java code you already know.

By the end of this chapter, you'll have a working PHP environment and understand the fundamental differences between PHP and Java execution models.

## Prerequisites

Before starting this chapter, you should have:

- A computer running Windows, macOS, or Linux
- Administrator/sudo access to install software
- Your favorite text editor or IDE (we'll show you how to configure it)
- Basic command line familiarity (you already have this!)

**Estimated Time**: ~45-60 minutes

## What You'll Build

In this chapter, you'll:
- Install PHP 8.4 (the latest stable version)
- Configure your IDE for PHP development
- Write a "Hello, World!" program in both Java and PHP
- Create a simple REST API endpoint in both languages
- Run PHP scripts from command line and web server

## Quick Start

If you're already familiar with command-line tools and want to get up and running quickly:

```bash
# macOS (with Homebrew)
brew install php@8.4

# Verify installation
php --version

# Create your first PHP script
echo '<?php echo "Hello, PHP!";' > hello.php

# Run it
php hello.php
```

For detailed setup instructions and comparisons with Java, continue reading below.

## Objectives

By the end of this chapter, you'll be able to:

- **Install and configure PHP** on your development machine
- **Run PHP scripts** from the command line and browser
- **Compare Java and PHP** execution models
- **Understand key differences** in syntax and structure
- **Set up your IDE** for productive PHP development

---

## Step 1: Understanding PHP's Execution Model (~10 min)

### Goal

Understand how PHP execution differs from Java's compiled, JVM-based approach.

### Java vs PHP: Key Differences

**Java's Execution Model:**
```
.java file → javac → .class file → JVM → Execution
(Source)   (Compile) (Bytecode)  (Runtime) (Output)
```

**PHP's Execution Model:**
```
.php file → PHP Interpreter → Execution
(Source)   (Parse + Execute)  (Output)
```

### Key Differences to Know

| Aspect | Java | PHP |
|--------|------|-----|
| **Compilation** | Compiled to bytecode | Interpreted (with opcache) |
| **Execution** | Runs in JVM | Native execution via PHP runtime |
| **Deployment** | JAR/WAR files | Source files directly |
| **Startup** | Application server (long-running) | Request-based (typically) |
| **Reload** | Restart required | Changes reflected immediately |
| **Performance** | JIT compilation, warm-up | Opcache + JIT (PHP 8+) |

::: tip Quick Comparison
Think of PHP more like interpreted Python or Ruby, but with optional static typing (as of PHP 7.4+). Modern PHP has JIT compilation, making it surprisingly fast for most web applications.
:::

### Why It Matters

This execution model difference affects:
- **Development workflow**: PHP changes appear immediately (no compilation step)
- **Deployment**: You typically deploy source code, not compiled artifacts
- **Memory management**: PHP has request-based lifecycle, garbage collection per request
- **State management**: PHP is typically stateless between requests (unlike Spring Boot applications)

---

## Step 2: Installing PHP (~15 min)

### Goal

Install PHP 8.4 on your development machine.

### Actions

::: code-group

```bash [macOS (Homebrew)]
# Install Homebrew if you don't have it
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP 8.4
brew install php@8.4

# Verify installation
php --version

# Should output: PHP 8.4.x (cli)...
```

```bash [Ubuntu/Debian]
# Update package lists
sudo apt update

# Add PHP repository
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php

# Install PHP 8.4
sudo apt install php8.4 php8.4-cli php8.4-common php8.4-mbstring php8.4-xml php8.4-curl

# Verify installation
php --version
```

```bash [Windows]
# Option 1: Using Chocolatey
choco install php --version=8.4

# Option 2: Download from windows.php.net
# 1. Visit https://windows.php.net/download/
# 2. Download "Thread Safe" ZIP for PHP 8.4
# 3. Extract to C:\php
# 4. Add C:\php to your PATH

# Verify installation
php --version
```

```bash [Fedora/RHEL]
# Enable REMI repository
sudo dnf install https://rpms.remirepo.net/fedora/remi-release-XX.rpm

# Install PHP 8.4
sudo dnf module reset php
sudo dnf module install php:remi-8.4

# Verify installation
php --version
```

:::

### Expected Result

Running `php --version` should display:

```
PHP 8.4.x (cli) (built: ...)
Copyright (c) The PHP Group
Zend Engine v4.4.x, Copyright (c) Zend Technologies
    with Zend OPcache v8.4.x, Copyright (c), by Zend Technologies
```

::: tip OPcache
If you see "with Zend OPcache" in the output, great! OPcache is PHP's bytecode cache, similar to JVM's JIT compilation. It significantly improves performance.
:::

### Why It Works

PHP's installation includes:
- **PHP CLI**: Command-line interpreter (like `java` command)
- **PHP Libraries**: Core extensions (similar to Java's standard library)
- **OPcache**: Bytecode cache for performance
- **Built-in web server**: For local development (like Spring Boot's embedded server)

### Troubleshooting

**Problem: `php: command not found`**
- **Solution**: PHP isn't in your PATH. Find where PHP was installed and add it to your PATH.
  ```bash
  # Find PHP location
  which php  # macOS/Linux
  where php  # Windows

  # Add to PATH (add to ~/.bashrc or ~/.zshrc)
  export PATH="/usr/local/bin/php:$PATH"
  ```

**Problem: Wrong PHP version**
- **Solution**: Multiple PHP versions installed. Specify the version:
  ```bash
  # macOS with Homebrew
  brew unlink php@8.3 && brew link php@8.4
  ```

**Problem: Missing extensions**
- **Solution**: Install required extensions:
  ```bash
  # Ubuntu/Debian
  sudo apt install php8.4-<extension-name>

  # macOS
  pecl install <extension-name>
  ```

---

## Step 2.5: Docker Installation (Alternative) (~5 min)

### Goal

Use Docker for a consistent PHP environment across all platforms.

### Why Docker?

As a Java developer, you might already be familiar with Docker. Using Docker for PHP provides:
- **Consistency**: Same environment on all machines
- **Isolation**: No conflicts with system PHP
- **Easy version switching**: Run multiple PHP versions
- **Pre-configured**: Many images include common extensions

### Quick Docker Setup

```bash
# Pull official PHP image
docker pull php:8.4-cli

# Run PHP in Docker
docker run --rm php:8.4-cli php --version

# Run a PHP script
docker run --rm -v $(pwd):/app php:8.4-cli php /app/hello.php

# Interactive PHP shell
docker run --rm -it php:8.4-cli php -a
```

### Docker Compose for Development

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  php:
    image: php:8.4-apache
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www/html
    environment:
      - PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d
```

Run with:
```bash
docker-compose up
# Access at http://localhost:8000
```

::: tip Docker vs Native
**Use Docker if:**
- You want isolated environments
- You switch between PHP versions often
- You work on multiple machines

**Use Native if:**
- You prefer direct system integration
- You want IDE autocomplete to work seamlessly
- You're doing performance-critical development
:::

---

## Step 2.6: Installing Composer (~5 min)

### Goal

Install Composer, PHP's dependency manager (like Maven/Gradle).

### Installation

::: code-group

```bash [macOS/Linux]
# Download and install globally
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

```bash [Windows]
# Download and run Composer-Setup.exe from:
# https://getcomposer.org/Composer-Setup.exe

# After installation, verify:
composer --version
```

```bash [Docker]
# Use Composer Docker image
docker run --rm -v $(pwd):/app composer:latest --version

# Alias for convenience (add to ~/.bashrc or ~/.zshrc)
alias composer='docker run --rm -v $(pwd):/app composer:latest'
```

:::

### Why Composer?

| Java Tool | PHP Equivalent | Purpose |
|-----------|----------------|---------|
| Maven | Composer | Dependency management |
| pom.xml | composer.json | Project configuration |
| mvn install | composer install | Install dependencies |
| .m2/repository | vendor/ | Dependencies location |

### Test Composer

```bash
# Create a test project
composer init --name=myapp --no-interaction

# Install a package
composer require monolog/monolog

# Verify
ls vendor/  # Should see monolog and other dependencies
```

---

## Step 2.7: PHP Extensions Overview (~5 min)

### Goal

Understand essential PHP extensions for development.

### Core Extensions (Usually Pre-installed)

These come with PHP but you should verify:

```bash
# Check installed extensions
php -m

# Common extensions you'll need:
```

| Extension | Purpose | Java Equivalent |
|-----------|---------|-----------------|
| **json** | JSON encoding/decoding | Jackson, Gson |
| **mbstring** | Multi-byte string handling | Built into Java |
| **xml** | XML processing | JAXB, DOM Parser |
| **curl** | HTTP client | HttpClient, OkHttp |
| **pdo** | Database abstraction | JDBC |
| **zip** | ZIP file handling | java.util.zip |
| **openssl** | Cryptography | java.security |

### Installing Additional Extensions

::: code-group

```bash [Ubuntu/Debian]
# Database drivers
sudo apt install php8.4-mysql php8.4-pgsql php8.4-sqlite3

# Web development
sudo apt install php8.4-curl php8.4-gd php8.4-xml

# Performance
sudo apt install php8.4-opcache php8.4-apcu

# Verify
php -m | grep -i mysql
```

```bash [macOS]
# Most extensions included with Homebrew PHP
# For additional ones:
pecl install apcu
pecl install redis
```

```bash [Windows]
# Edit php.ini and uncomment extensions:
extension=curl
extension=mbstring
extension=pdo_mysql

# Restart web server after changes
```

:::

### Verification Script

Create `check-extensions.php`:

```php
# filename: check-extensions.php
<?php

$required = ['json', 'mbstring', 'xml', 'curl', 'pdo', 'openssl'];
$missing = [];

foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
    }
}

if (empty($missing)) {
    echo "✅ All required extensions are installed!\n";
    echo "\nInstalled extensions:\n";
    foreach ($required as $ext) {
        echo "  - $ext\n";
    }
} else {
    echo "❌ Missing extensions:\n";
    foreach ($missing as $ext) {
        echo "  - $ext\n";
    }
    exit(1);
}
```

Run it:
```bash
php check-extensions.php
```

---

## Step 2.8: PHP Configuration Basics (~5 min)

### Goal

Understand PHP's configuration system and how it compares to Java's configuration approach.

### Finding Your php.ini File

Unlike Java's `application.properties` or `application.yml`, PHP uses a single `php.ini` file for configuration. However, PHP can have multiple `php.ini` files (one for CLI, one for web server).

```bash
# Find which php.ini file PHP is using
php --ini

# Output example:
# Configuration File (php.ini) Path: /usr/local/etc/php/8.4
# Loaded Configuration File: /usr/local/etc/php/8.4/php.ini
# Scan for additional .ini files in: /usr/local/etc/php/8.4/conf.d
```

### Key Configuration Settings for Development

For Java developers, here's how PHP configuration compares:

::: code-group

```ini [php.ini (PHP)]
; Error reporting (similar to Java logging levels)
error_reporting = E_ALL
display_errors = On          ; Show errors in development
display_startup_errors = On  ; Show startup errors
log_errors = On              ; Log errors to file
error_log = /var/log/php_errors.log

; Memory and execution limits
memory_limit = 256M          ; Like -Xmx in Java
max_execution_time = 30      ; Script timeout (seconds)

; File uploads
upload_max_filesize = 10M
post_max_size = 20M

; Date and timezone (important!)
date.timezone = America/New_York
```

```properties [application.properties (Java/Spring Boot)]
# Error handling
logging.level.root=INFO
logging.level.com.example=DEBUG

# Server configuration
server.port=8080
spring.servlet.multipart.max-file-size=10MB
spring.servlet.multipart.max-request-size=20MB

# Memory (JVM arguments)
# -Xmx256m
```

:::

### Setting Configuration Programmatically

Unlike Java where you typically configure via properties files or annotations, PHP allows runtime configuration:

```php
# filename: config-example.php
<?php

// Set configuration at runtime (like System.setProperty in Java)
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '512M');

// Get current configuration
$memoryLimit = ini_get('memory_limit');
echo "Memory limit: $memoryLimit\n";

// Check if a setting is changeable
if (ini_get('display_errors') === false) {
    echo "display_errors cannot be changed\n";
}
```

**Java Comparison:**
```java
// Java uses System properties or application config
System.setProperty("log.level", "DEBUG");
String logLevel = System.getProperty("log.level");
```

### Development vs Production Settings

**Development (php.ini):**
```ini
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
```

**Production (php.ini):**
```ini
display_errors = Off          ; Never show errors to users
display_startup_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
log_errors = On               ; Always log, but don't display
```

::: warning Security Note
Never set `display_errors = On` in production! This exposes sensitive information. Always log errors instead of displaying them.
:::

### Why It Works

PHP's configuration system differs from Java:

| Aspect | Java | PHP |
|--------|------|-----|
| **Configuration Files** | Multiple (properties, YAML, XML) | Single `php.ini` (with includes) |
| **Runtime Changes** | Limited (System properties) | Extensive (`ini_set()`) |
| **Per-Request Config** | Application-level | Can change per script |
| **Environment-Specific** | Spring profiles, Maven profiles | Separate `php.ini` files or `ini_set()` |
| **CLI vs Web** | Same JVM settings | Different `php.ini` files possible |

### Troubleshooting

**Problem: Changes to php.ini not taking effect**
- **Solution**: Restart your web server (Apache/Nginx) or use `php --ini` to verify which file is loaded
  ```bash
  # Find loaded config
  php --ini
  
  # For web server, restart:
  sudo systemctl restart apache2  # or nginx
  ```

**Problem: Need different settings for CLI vs web**
- **Solution**: PHP can use different `php.ini` files. Check with `php --ini` for CLI and `phpinfo()` for web server.

---

## Step 3: Hello World Comparison (~10 min)

### Goal

Write and run your first PHP script, comparing it to Java.

### Actions

**Step 1: Create your first PHP script**

Create a file named `hello.php`:

::: code-group

```php [hello.php]
# filename: hello.php
<?php

// This is a comment in PHP
echo "Hello, World!\n";

// More complex example
$name = "Java Developer";
echo "Welcome to PHP, $name!\n";
```

```java [Hello.java (for comparison)]
public class Hello {
    // This is a comment in Java
    public static void main(String[] args) {
        System.out.println("Hello, World!");

        // More complex example
        String name = "Java Developer";
        System.out.println("Welcome to Java, " + name + "!");
    }
}
```

:::

**Step 2: Run the scripts**

::: code-group

```bash [Running PHP]
# Just run it directly
php hello.php

# Output:
# Hello, World!
# Welcome to PHP, Java Developer!
```

```bash [Running Java (for comparison)]
# Compile first
javac Hello.java

# Then run
java Hello

# Output:
# Hello, World!
# Welcome to Java, Java Developer!
```

:::

### Expected Result

You should see:
```
Hello, World!
Welcome to PHP, Java Developer!
```

### Why It Works

Let's break down the key differences:

**1. Entry Point**
- **Java**: Requires `public static void main(String[] args)` method in a class
- **PHP**: Top-level code execution—no class or method required (though you can use them)

**2. Syntax**
- **Java**: Statements end with `;`, variables are typed (`String name`)
- **PHP**: Statements end with `;`, variables start with `$` and are dynamically typed (or optionally typed)

**3. Output**
- **Java**: `System.out.println()`
- **PHP**: `echo` or `print` (simpler, built-in language constructs)

**4. String Interpolation**
- **Java**: Concatenation with `+` or `String.format()`
- **PHP**: Direct variable interpolation in double-quoted strings: `"$variable"`

**5. Execution**
- **Java**: Compile, then run
- **PHP**: Run directly (interpretation + opcache)

::: tip No Boilerplate
PHP's lack of required boilerplate (no classes, no main method) makes it great for scripting and rapid development. You can add structure as needed for larger applications.
:::

---

## Step 3.5: PHP Tags and Syntax Basics (~5 min)

### Goal

Understand PHP's tag syntax and how it differs from Java's file structure.

### PHP Opening and Closing Tags

PHP code must be enclosed in special tags. Unlike Java where the entire file is code, PHP can mix with HTML:

```php
# filename: tags-example.php
<?php
// Pure PHP file - all code here
echo "Hello, World!\n";
```

```php
# filename: mixed-html.php
<!DOCTYPE html>
<html>
<head>
    <title>PHP Example</title>
</head>
<body>
    <?php
    // PHP code embedded in HTML
    $name = "Java Developer";
    echo "<h1>Welcome, $name!</h1>";
    ?>
</body>
</html>
```

### Closing Tags: When to Omit

**PSR-12 Standard (Recommended):**

For pure PHP files (no HTML), **omit the closing tag** `?>`:

```php
# filename: person.php
<?php

declare(strict_types=1);

class Person
{
    // Class code
}
// No closing tag - this is correct!
```

**Why omit the closing tag?**
- Prevents accidental whitespace/newlines after `?>` from being sent to output
- Avoids "headers already sent" errors
- Follows PSR-12 coding standard

**When to include closing tag:**
- Only when mixing PHP with HTML/other content
- In template files (`.phtml` files)

### Short Echo Syntax

PHP provides a shorthand for `echo`:

```php
# filename: short-echo.php
<?php
$name = "Alice";
?>

<!-- Long form -->
<?php echo $name; ?>

<!-- Short form (recommended in templates) -->
<?= $name ?>

<!-- Both output: Alice -->
```

**Java Comparison:**
```java
// Java doesn't have template syntax built-in
// You'd use JSP, Thymeleaf, or similar:
// <c:out value="${name}" />
```

### PHP Tags Comparison

| Tag Type | Syntax | Use Case |
|----------|--------|----------|
| **Standard** | `<?php ... ?>` | Always works, recommended |
| **Short Echo** | `<?= ... ?>` | Templates only (must enable `short_open_tag`) |
| **Short Tags** | `<? ... ?>` | Deprecated, don't use |

::: warning Short Tags
Avoid `<? ... ?>` (short tags). They're deprecated and may not work on all servers. Always use `<?php ... ?>` or `<?= ... ?>` for echo.
:::

### File Organization: No Classpath

Unlike Java where classes are automatically found via the classpath, PHP requires explicit file inclusion:

**Java:**
```java
// Java automatically finds classes via classpath
import com.example.User;
User user = new User();  // ClassLoader finds it
```

**PHP (without autoloading):**
```php
# filename: main.php
<?php

// Must explicitly include/require the file
require_once 'Person.php';  // Like import, but loads the file

$person = new Person("Alice", 30);
```

**PHP (with autoloading - covered in Chapter 6):**
```php
<?php

// With Composer autoloader (like Java's classpath)
require 'vendor/autoload.php';

use App\Models\Person;  // Now works like Java import
$person = new Person("Alice", 30);
```

### Why It Works

PHP's tag system allows:
- **Pure PHP files**: Just code, no HTML
- **Mixed files**: PHP embedded in HTML (like JSP)
- **Templates**: PHP generating HTML dynamically

This flexibility is different from Java where:
- `.java` files are always pure Java code
- Templates use separate technologies (JSP, Thymeleaf, etc.)
- No mixing of code and markup in source files

### Troubleshooting

**Problem: "Parse error: syntax error, unexpected '?'"**
- **Cause**: Short tags `<?` not enabled or using wrong syntax
- **Solution**: Use `<?php` instead of `<?`

**Problem: "Headers already sent" error**
- **Cause**: Whitespace or output before `<?php` tag or after `?>` tag
- **Solution**: Remove closing `?>` tag in pure PHP files, check for whitespace before opening tag

**Problem: Short echo `<?=` not working**
- **Cause**: `short_open_tag` disabled in php.ini
- **Solution**: Enable it or use `<?php echo` instead

---

## Step 4: Creating a Simple Class (~10 min)

### Goal

Compare object-oriented programming basics between Java and PHP.

### Actions

Create a `Person` class in both languages:

::: code-group

```php [Person.php]
# filename: Person.php
<?php

declare(strict_types=1);  // Enable strict type checking (optional but recommended)

class Person
{
    private string $name;
    private int $age;

    // Constructor
    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    // Getter methods
    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    // Method
    public function introduce(): string
    {
        return "Hi, I'm {$this->name} and I'm {$this->age} years old.";
    }
}

// Usage (in the same file or another file)
$person = new Person("Alice", 30);
echo $person->introduce() . "\n";
echo "Name: " . $person->getName() . "\n";
```

```java [Person.java (for comparison)]
public class Person {
    private String name;
    private int age;

    // Constructor
    public Person(String name, int age) {
        this.name = name;
        this.age = age;
    }

    // Getter methods
    public String getName() {
        return name;
    }

    public int getAge() {
        return age;
    }

    // Method
    public String introduce() {
        return String.format("Hi, I'm %s and I'm %d years old.", name, age);
    }

    // Usage
    public static void main(String[] args) {
        Person person = new Person("Alice", 30);
        System.out.println(person.introduce());
        System.out.println("Name: " + person.getName());
    }
}
```

:::

### Expected Result

```
Hi, I'm Alice and I'm 30 years old.
Name: Alice
```

### Key Differences Explained

| Feature | Java | PHP |
|---------|------|-----|
| **Class Declaration** | `public class Person` | `class Person` (public by default) |
| **Properties** | `private String name;` | `private string $name;` (note: lowercase `string`) |
| **Constructor** | `public Person(...)` | `public function __construct(...)` |
| **Method Declaration** | `public String getName()` | `public function getName(): string` (return type after `:`) |
| **This Reference** | `this.name` | `$this->name` (uses `->` operator) |
| **Type Hints** | Required | Optional (but recommended with `declare(strict_types=1)`) |
| **Instantiation** | `new Person(...)` | `new Person(...)` (same!) |

::: warning Important Differences
1. **Property access**: PHP uses `->` instead of `.`
2. **Variables**: Always start with `$` in PHP
3. **Constructor name**: PHP uses `__construct()` instead of class name
4. **Type declarations**: PHP uses lowercase type names (`string`, `int`, `bool`)
5. **Strict types**: PHP needs `declare(strict_types=1);` for type safety similar to Java
:::

### Why It Works

Modern PHP (7.4+) supports:
- **Type hints** for parameters and return types
- **Property types** (PHP 7.4+)
- **Visibility modifiers** (public, private, protected)
- **Interfaces and abstract classes**
- **Traits** (similar to Java's interface default methods)

This makes PHP's OOP model very similar to Java's, with the main difference being syntax.

---

## Step 5: Setting Up Your IDE (~10 min)

### Goal

Configure your IDE for productive PHP development.

### Actions

Choose your preferred IDE:

::: code-group

```text [PhpStorm (Recommended)]
1. Download PhpStorm from jetbrains.com/phpstorm
2. Install and launch
3. Configure PHP Interpreter:
   - Go to Settings/Preferences → PHP
   - Click "..." next to CLI Interpreter
   - Click "+" and select your PHP installation
   - Verify version shows PHP 8.4
4. Enable PHP 8.4 language level:
   - Settings → PHP → PHP Language Level → 8.4

PhpStorm Features for Java Developers:
✅ Familiar JetBrains interface
✅ Excellent autocomplete and refactoring
✅ Built-in debugger (like IntelliJ's debugger)
✅ Integrated Composer support (like Maven/Gradle)
✅ Database tools built-in
```

```text [VS Code (Free)]
1. Install VS Code from code.visualstudio.com
2. Install PHP extensions:
   - PHP Intelephense (best PHP autocomplete)
   - PHP Debug
   - PHP Namespace Resolver
   - PHPDoc Comment

3. Configure PHP executable:
   - Open Settings (Cmd/Ctrl + ,)
   - Search for "php.validate.executablePath"
   - Set to your PHP installation path

4. Create .vscode/settings.json in your project:
{
    "php.validate.executablePath": "/usr/local/bin/php",
    "php.suggest.basic": false,
    "intelephense.diagnostics.undefinedTypes": true,
    "intelephense.diagnostics.undefinedFunctions": true
}
```

```text [IntelliJ IDEA Ultimate]
1. IntelliJ IDEA Ultimate includes PHP support
2. Enable PHP plugin:
   - Settings → Plugins → Search "PHP"
   - Enable "PHP" plugin
3. Configure PHP interpreter:
   - Settings → PHP → CLI Interpreter
   - Add your PHP 8.4 installation
4. Set language level to PHP 8.4

Benefits for Java developers:
✅ Same IDE for Java and PHP
✅ Familiar interface and shortcuts
✅ Unified project view
✅ Database tools work the same
```

:::

### Expected Result

After setup, you should have:
- **Syntax highlighting** for PHP code
- **Autocomplete** for PHP functions and classes
- **Error detection** for type mismatches and undefined variables
- **Go to definition** (Cmd/Ctrl + Click)
- **Integrated terminal** for running PHP commands

### Testing Your IDE Setup

Create a new PHP file and test autocomplete:

```php
# filename: test-ide.php
<?php

// Type this and test autocomplete:
$text = "hello";
$text->  // IDE should show string methods

// Test type checking:
function add(int $a, int $b): int {
    return $a + $b;
}

add("hello", "world");  // IDE should show error
```

::: tip Keyboard Shortcuts
Most shortcuts you know from Java development work in PHP:
- **Cmd/Ctrl + Click**: Go to definition
- **Cmd/Ctrl + Space**: Autocomplete
- **Cmd/Ctrl + B**: Go to declaration
- **Alt + Enter**: Quick fixes
- **Shift + Shift**: Search everywhere (PhpStorm/IntelliJ)
:::

---

## Step 6: Your First Web Response (~10 min)

### Goal

Create a simple web endpoint in PHP and compare it to a Java servlet.

### Actions

**Step 1: Create a simple PHP web response**

Create `index.php`:

```php
# filename: index.php
<?php

declare(strict_types=1);

// Set response headers
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Simple routing
if ($method === 'GET') {
    $response = [
        'message' => 'Hello from PHP!',
        'timestamp' => time(),
        'version' => phpversion()
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
```

**Step 2: Run PHP's built-in web server**

```bash
# Start the server (similar to Spring Boot's embedded server)
php -S localhost:8000

# You should see:
# PHP 8.4.x Development Server (http://localhost:8000) started
```

**Step 3: Test the endpoint**

```bash
# Test with curl
curl http://localhost:8000/index.php

# Or open in browser:
# http://localhost:8000/index.php
```

**For comparison, here's the Java servlet equivalent:**

```java
@WebServlet("/hello")
public class HelloServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws IOException {
        response.setContentType("application/json");

        JSONObject json = new JSONObject();
        json.put("message", "Hello from Java!");
        json.put("timestamp", System.currentTimeMillis() / 1000);
        json.put("version", System.getProperty("java.version"));

        response.getWriter().write(json.toString());
    }
}
```

### Expected Result

```json
{
    "message": "Hello from PHP!",
    "timestamp": 1701234567,
    "version": "8.4.0"
}
```

### Why It Works

**Key Differences:**

1. **No Application Server Required**
   - Java: Needs Tomcat, Jetty, or embedded server
   - PHP: Built-in development server or Apache/Nginx

2. **Request Handling**
   - Java: Servlet classes with lifecycle methods
   - PHP: Script executes per request, accessing `$_SERVER`, `$_GET`, `$_POST` superglobals

3. **Response Generation**
   - Java: Write to `response` object
   - PHP: `echo` output directly, set headers with `header()`

4. **JSON Handling**
   - Java: Use library like Jackson or GSON
   - PHP: Built-in `json_encode()` and `json_decode()`

::: warning Important PHP Concepts
- **Superglobals**: `$_GET`, `$_POST`, `$_SERVER` are built-in arrays containing request data
- **Output buffering**: Everything you `echo` goes to the response
- **Headers**: Must be set before any output with `header()`
- **Request lifecycle**: Each request runs the script from start to finish (stateless by default)
:::

---

## Step 7: Project Structure Comparison (~5 min)

### Goal

Understand how PHP projects are structured compared to Java projects.

### Typical Structure Comparison

::: code-group

```text [PHP Project]
my-php-project/
├── composer.json          # Dependencies (like pom.xml)
├── composer.lock          # Lock file (like pom.xml.lock)
├── vendor/                # Dependencies (like .m2 or build/libs)
├── public/                # Web root (accessible to browser)
│   ├── index.php          # Entry point
│   └── assets/            # CSS, JS, images
├── src/                   # Application code
│   ├── Controller/
│   ├── Model/
│   └── Service/
├── config/                # Configuration files
│   └── database.php
├── tests/                 # PHPUnit tests
│   └── Unit/
├── .env                   # Environment variables
└── README.md
```

```text [Java Project (Maven)]
my-java-project/
├── pom.xml                # Dependencies
├── target/                # Build output
├── src/
│   ├── main/
│   │   ├── java/          # Source code
│   │   │   └── com/example/
│   │   │       ├── controller/
│   │   │       ├── model/
│   │   │       └── service/
│   │   └── resources/     # Config files
│   │       └── application.properties
│   └── test/              # JUnit tests
│       └── java/
│           └── com/example/
├── .env                   # Environment variables
└── README.md
```

:::

**Key Parallels:**

| PHP | Java | Purpose |
|-----|------|---------|
| `composer.json` | `pom.xml` / `build.gradle` | Dependency management |
| `vendor/` | `target/` / `.m2/` | Dependencies |
| `src/` | `src/main/java/` | Source code |
| `tests/` | `src/test/java/` | Tests |
| `public/index.php` | `@SpringBootApplication` | Entry point |
| `config/` | `src/main/resources/` | Configuration |
| `.env` | `.env` / `application.properties` | Environment config |

---

## Exercises

::: tip Practice Time
Complete these exercises to reinforce your learning:
:::

### Exercise 1: Hello You!

Modify `hello.php` to accept a name as a command-line argument.

**Expected behavior:**
```bash
php hello.php Alice
# Output: Hello, Alice!

php hello.php
# Output: Hello, World!
```

**Hints:**
- In Java: `args[0]` for command-line arguments
- In PHP: `$argv[1]` for command-line arguments
- Check if argument exists before using it

<details>
<summary>Solution</summary>

```php
# filename: hello.php
<?php

// Get name from command line, default to "World"
$name = $argv[1] ?? "World";

echo "Hello, $name!\n";
```

**Comparison with Java:**
```java
public class Hello {
    public static void main(String[] args) {
        String name = args.length > 0 ? args[0] : "World";
        System.out.println("Hello, " + name + "!");
    }
}
```

</details>

### Exercise 2: Calculator Class

Create a `Calculator` class in PHP with methods for basic operations.

**Requirements:**
- Methods: `add()`, `subtract()`, `multiply()`, `divide()`
- All methods should use type hints
- `divide()` should throw an exception for division by zero
- Create a test script to verify all methods work

**Expected usage:**
```php
$calc = new Calculator();
echo $calc->add(5, 3);        // 8
echo $calc->divide(10, 2);    // 5
echo $calc->divide(10, 0);    // Exception thrown
```

<details>
<summary>Solution</summary>

```php
# filename: Calculator.php
<?php

declare(strict_types=1);

class Calculator
{
```
    public function add(int|float $a, int|float $b): int|float
    {
        return $a + $b;
    }

    public function subtract(int|float $a, int|float $b): int|float
    {
        return $a - $b;
    }

    public function multiply(int|float $a, int|float $b): int|float
    {
        return $a * $b;
    }

    public function divide(int|float $a, int|float $b): float
    {
        if ($b === 0) {
            throw new InvalidArgumentException("Division by zero");
        }
        return $a / $b;
    }
}

// Test script
$calc = new Calculator();

try {
    echo "5 + 3 = " . $calc->add(5, 3) . "\n";
    echo "10 - 4 = " . $calc->subtract(10, 4) . "\n";
    echo "6 * 7 = " . $calc->multiply(6, 7) . "\n";
    echo "15 / 3 = " . $calc->divide(15, 3) . "\n";

    // This will throw an exception
    echo $calc->divide(10, 0);
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

**Note:** PHP 8+ supports union types (`int|float`), similar to Java's type system.

</details>

### Exercise 3: JSON API

Create a simple REST API endpoint that returns information about a product.

**Requirements:**
- Endpoint: `/product.php?id=123`
- Return JSON with product details
- Handle missing `id` parameter
- Return proper HTTP status codes

<details>
<summary>Solution</summary>

```php
# filename: product.php
<?php

declare(strict_types=1);

header('Content-Type: application/json');

// Simulated database
$products = [
    123 => ['id' => 123, 'name' => 'Laptop', 'price' => 999.99],
    456 => ['id' => 456, 'name' => 'Mouse', 'price' => 29.99],
];

// Get product ID from query parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

if (!isset($products[$id])) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Return product
echo json_encode($products[$id], JSON_PRETTY_PRINT);
```

**Test it:**
```bash
php -S localhost:8000

# In another terminal:
curl http://localhost:8000/product.php?id=123
curl http://localhost:8000/product.php?id=999  # 404
curl http://localhost:8000/product.php         # 400
```

</details>

---

## Wrap-up Checklist

Before moving to the next chapter, make sure you can:

- [ ] Run `php --version` and see PHP 8.4
- [ ] Execute PHP scripts from the command line
- [ ] Create and instantiate PHP classes with type hints
- [ ] Understand the differences between Java and PHP syntax
- [ ] Start PHP's built-in web server
- [ ] Return JSON responses from PHP scripts
- [ ] Configure your IDE for PHP development
- [ ] Explain PHP's execution model vs Java's

::: tip Ready for More?
In [Chapter 1: Types, Variables & Operators](/series/php-for-java-developers/chapters/01-types-variables-and-operators), we'll dive deeper into PHP's type system and see how it compares to Java's strong typing.
:::

---

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="00"
  label="Setup complete! You're ready to start learning PHP."
/>

## Further Reading

**PHP Documentation:**
- [PHP Manual: Getting Started](https://www.php.net/manual/en/getting-started.php)
- [PHP Types](https://www.php.net/manual/en/language.types.php)
- [Built-in Web Server](https://www.php.net/manual/en/features.commandline.webserver.php)

**For Java Developers:**
- [PHP: The Right Way](https://phptherightway.com/)
- [Migrating from Java to PHP](https://www.php.net/manual/en/migration.php)
- [PSR Standards](https://www.php-fig.org/psr/) - PHP's equivalent to Java conventions

**Tools:**
- [PhpStorm](https://www.jetbrains.com/phpstorm/) - The JetBrains PHP IDE
- [Composer](https://getcomposer.org/) - Dependency manager (we'll cover this in Chapter 8)
- [PHP The Right Way](https://phptherightway.com/) - Best practices guide

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div></div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/01-types-variables-and-operators">Chapter 1: Types, Variables & Operators →</a>
  </div>
</div>
