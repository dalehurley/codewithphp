---
title: "14: Code Quality Tools"
description: "PHPStan, PHP_CodeSniffer, PHP CS Fixer, Git hooks"
series: "php-for-java-developers"
chapter: 14
order: 14
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/13-integration-testing"
---

# Chapter 14: Code Quality Tools

<Badge type="warning">Intermediate</Badge>

## Overview

Code quality tools help maintain consistent code standards, catch bugs early, and ensure your codebase remains maintainable. If you're coming from Java, you're likely familiar with tools like Checkstyle, PMD, SpotBugs, and SonarQube. PHP has equivalent tools that provide static analysis, code formatting, and quality metrics. This chapter covers the essential code quality tools for PHP development.

**What You'll Learn:**
- Static analysis with PHPStan and Psalm
- Code style checking with PHP_CodeSniffer
- Automatic code formatting with PHP CS Fixer
- Custom coding standards (PSR-12, PSR-1)
- Git hooks for automated checks
- Integrating quality tools into CI/CD
- Mess detection with PHPMD
- Copy-paste detection with PHPCPD
- Metrics and complexity analysis
- IDE integration

## Prerequisites

Before starting this chapter, you should be comfortable with:
- PHP development basics
- Composer for dependency management
- Command-line usage
- Git version control

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Configure PHPStan** for static analysis
2. **Use PHP_CodeSniffer** to enforce coding standards
3. **Format code automatically** with PHP CS Fixer
4. **Set up Git hooks** for pre-commit checks
5. **Detect code smells** with PHPMD
6. **Find duplicate code** with PHPCPD
7. **Integrate tools** into CI/CD pipelines
8. **Customize coding standards** for your project
9. **Configure IDE integration** for real-time feedback
10. **Measure code quality metrics**

---

## Section 1: PHPStan - Static Analysis

PHPStan finds bugs without running your code.

### Installation and Basic Usage

```bash
# Install PHPStan
composer require --dev phpstan/phpstan

# Run analysis
vendor/bin/phpstan analyse src tests

# Run with specific level (0-9)
vendor/bin/phpstan analyse src --level=8
```

### PHPStan Levels

::: code-group

```php [Level 0 - Basic]
<?php

declare(strict_types=1);

// Level 0 catches basic errors
class Example
{
    public function process(): void
    {
        // PHPStan finds: Undefined variable $user
        echo $user->name;
    }
}
```

```php [Level 5 - Type Checking]
<?php

declare(strict_types=1);

// Level 5 enforces type hints
class UserService
{
    // PHPStan error: Missing return type
    public function getUser(int $id)
    {
        return $this->repository->find($id);
    }

    // Fixed version
    public function getUserFixed(int $id): ?User
    {
        return $this->repository->find($id);
    }
}
```

```php [Level 8 - Strict]
<?php

declare(strict_types=1);

// Level 8 catches subtle type issues
class Calculator
{
    public function divide(int $a, int $b): float
    {
        // PHPStan error: Division by zero possible
        return $a / $b;
    }

    // Fixed version
    public function divideFixed(int $a, int $b): float
    {
        if ($b === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $a / $b;
    }
}
```

:::

### PHPStan Configuration

```neon
# phpstan.neon
parameters:
    level: 8
    paths:
        - src
        - tests

    excludePaths:
        - tests/bootstrap.php
        - vendor

    # Ignore specific errors
    ignoreErrors:
        - '#Call to an undefined method#'

    # Check for missing typehints
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: true

    # Strict rules
    checkAlwaysTrueCheckTypeFunctionCall: true
    checkAlwaysTrueInstanceof: true
    checkAlwaysTrueStrictComparison: true
    checkExplicitMixedMissingReturn: true
    checkFunctionNameCase: true
    checkInternalClassCaseSensitivity: true

    # Enable bleeding edge
    reportUnmatchedIgnoredErrors: true
```

### PHPStan Extensions

```bash
# Install useful extensions
composer require --dev \
    phpstan/phpstan-phpunit \
    phpstan/phpstan-strict-rules \
    phpstan/phpstan-deprecation-rules
```

```neon
# phpstan.neon with extensions
includes:
    - vendor/phpstan/phpstan-phpunit/extension.neon
    - vendor/phpstan/phpstan-strict-rules/rules.neon
    - vendor/phpstan/phpstan-deprecation-rules/rules.neon

parameters:
    level: 8
    paths:
        - src
        - tests
```

### Real-World Example

```php
<?php

declare(strict_types=1);

namespace App\Services;

class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $payment
    ) {}

    /**
     * PHPStan helps catch type errors
     *
     * @param array<string, mixed> $data
     * @return Order
     */
    public function createOrder(array $data): Order
    {
        // PHPStan ensures all required fields exist
        if (!isset($data['user_id'], $data['items'], $data['total'])) {
            throw new \InvalidArgumentException('Missing required fields');
        }

        // PHPStan verifies types match
        $order = new Order(
            userId: (int) $data['user_id'],
            items: $data['items'], // Array<OrderItem>
            total: (float) $data['total']
        );

        // PHPStan catches if save() doesn't return Order
        return $this->orders->save($order);
    }

    /**
     * PHPStan enforces proper null handling
     */
    public function findOrder(int $id): ?Order
    {
        $order = $this->orders->findById($id);

        // PHPStan error if we don't handle null case
        if ($order === null) {
            return null;
        }

        return $order;
    }
}
```

---

## Section 2: Psalm - Alternative Static Analyzer

Psalm is another powerful static analyzer with different strengths.

### Installation and Configuration

```bash
# Install Psalm
composer require --dev vimeo/psalm

# Initialize configuration
vendor/bin/psalm --init

# Run analysis
vendor/bin/psalm
```

### Psalm Configuration

```xml
<?xml version="1.0"?>
<psalm
    errorLevel="3"
    resolveFromConfigFile="true"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="https://getpsalm.org/schema/config"
    xsi:schemaLocation="https://getpsalm.org/schema/config vendor/vimeo/psalm/config.xsd"
>
    <projectFiles>
        <directory name="src" />
        <directory name="tests" />
        <ignoreFiles>
            <directory name="vendor" />
        </ignoreFiles>
    </projectFiles>

    <issueHandlers>
        <MissingReturnType errorLevel="error" />
        <MissingPropertyType errorLevel="error" />
        <MissingParamType errorLevel="error" />
    </issueHandlers>
</psalm>
```

### Psalm Annotations

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Psalm template annotations for generics
     *
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    public function create(string $class): object
    {
        return new $class();
    }

    /**
     * Psalm array shape annotations
     *
     * @param array{name: string, email: string, age: int} $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return new User(
            $data['name'],
            $data['email'],
            $data['age']
        );
    }

    /**
     * Psalm list vs array distinction
     *
     * @param list<int> $ids List (sequential array)
     * @return array<int, User> Associative array
     */
    public function getUsersByIds(array $ids): array
    {
        $users = [];
        foreach ($ids as $id) {
            $users[$id] = $this->repository->find($id);
        }
        return $users;
    }

    /**
     * @psalm-assert !null $user
     */
    private function ensureUserExists(?User $user): void
    {
        if ($user === null) {
            throw new \RuntimeException('User not found');
        }
    }
}
```

---

## Section 3: PHP_CodeSniffer - Coding Standards

PHP_CodeSniffer enforces coding standards.

### Installation

```bash
# Install PHP_CodeSniffer
composer require --dev squizlabs/php_codesniffer

# Check code style
vendor/bin/phpcs src tests

# Fix automatically (when possible)
vendor/bin/phpcbf src tests
```

### Configuration

```xml
<?xml version="1.0"?>
<ruleset name="Project Coding Standard">
    <description>Custom coding standard for the project</description>

    <!-- Include PSR-12 standard -->
    <rule ref="PSR12"/>

    <!-- Paths to check -->
    <file>src</file>
    <file>tests</file>

    <!-- Exclude patterns -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/cache/*</exclude-pattern>

    <!-- Custom rules -->
    <rule ref="Generic.Arrays.DisallowLongArraySyntax"/>
    <rule ref="Generic.PHP.RequireStrictTypes"/>

    <!-- Complexity rules -->
    <rule ref="Generic.Metrics.CyclomaticComplexity">
        <properties>
            <property name="complexity" value="10"/>
            <property name="absoluteComplexity" value="20"/>
        </properties>
    </rule>

    <rule ref="Generic.Metrics.NestingLevel">
        <properties>
            <property name="nestingLevel" value="5"/>
            <property name="absoluteNestingLevel" value="10"/>
        </properties>
    </rule>

    <!-- Naming conventions -->
    <rule ref="Generic.NamingConventions.CamelCapsFunctionName"/>

    <!-- Documentation -->
    <rule ref="Generic.Commenting.DocComment"/>
    <rule ref="Squiz.Commenting.FunctionComment">
        <exclude name="Squiz.Commenting.FunctionComment.MissingParamTag"/>
    </rule>

    <!-- Code organization -->
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="150"/>
        </properties>
    </rule>

    <!-- Security -->
    <rule ref="Generic.PHP.ForbiddenFunctions">
        <properties>
            <property name="forbiddenFunctions" type="array">
                <element key="eval" value="null"/>
                <element key="exec" value="null"/>
                <element key="system" value="null"/>
                <element key="var_dump" value="null"/>
                <element key="print_r" value="null"/>
            </property>
        </properties>
    </rule>
</ruleset>
```

### PSR Standards Comparison

| Standard | Description | Use Case |
|----------|-------------|----------|
| PSR-1 | Basic Coding Standard | Minimum requirements |
| PSR-12 | Extended Coding Style | Recommended for new projects |
| PSR-2 | Deprecated | Replaced by PSR-12 |

### Custom Sniffs

```php
<?php

declare(strict_types=1);

namespace MyProject\Sniffs\NamingConventions;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Custom sniff to enforce service class naming
 */
class ServiceClassNameSniff implements Sniff
{
    public function register(): array
    {
        return [T_CLASS];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $className = $phpcsFile->getDeclarationName($stackPtr);

        // Check if class is in Services namespace
        $namespace = $phpcsFile->getNamespace($stackPtr);

        if (str_contains($namespace, 'Services')) {
            // Enforce 'Service' suffix
            if (!str_ends_with($className, 'Service')) {
                $phpcsFile->addError(
                    'Service classes must end with "Service" suffix',
                    $stackPtr,
                    'InvalidServiceName'
                );
            }
        }
    }
}
```

---

## Section 4: PHP CS Fixer - Automatic Formatting

PHP CS Fixer automatically fixes code style issues.

### Installation

```bash
# Install PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer

# Run fixer
vendor/bin/php-cs-fixer fix src

# Dry run (see what would change)
vendor/bin/php-cs-fixer fix src --dry-run --diff
```

### Configuration

```php
<?php

// .php-cs-fixer.php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'try', 'throw'],
        ],
        'cast_spaces' => true,
        'class_attributes_separation' => [
            'elements' => ['method' => 'one', 'property' => 'one'],
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'function_typehint_space' => true,
        'lowercase_cast' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
            ],
        ],
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'return_type_declaration' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'single_class_element_per_statement' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
```

### Before and After

::: code-group

```php [Before]
<?php
namespace App\Services;
use App\Models\User;
use App\Repositories\UserRepository;

class UserService{
    private $repository;
    public function __construct(UserRepository $repository){
        $this->repository=$repository;
    }
    public function getUser($id){
        $user=$this->repository->find($id);
        if(!$user){
            throw new \Exception("User not found");
        }
        return $user;
    }
}
```

```php [After]
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function getUser(int $id): User
    {
        $user = $this->repository->find($id);

        if ($user === null) {
            throw new \RuntimeException('User not found');
        }

        return $user;
    }
}
```

:::

---

## Section 5: PHPMD - Mess Detection

PHPMD finds potential problems in your code.

### Installation

```bash
# Install PHPMD
composer require --dev phpmd/phpmd

# Run mess detector
vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
```

### Configuration

```xml
<?xml version="1.0"?>
<ruleset name="Project Mess Detection Rules">
    <description>Custom PHPMD rules</description>

    <!-- Code Size Rules -->
    <rule ref="rulesets/codesize.xml/CyclomaticComplexity">
        <properties>
            <property name="reportLevel" value="10"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/NPathComplexity">
        <properties>
            <property name="minimum" value="200"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/ExcessiveMethodLength">
        <properties>
            <property name="minimum" value="50"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/ExcessiveClassLength">
        <properties>
            <property name="minimum" value="500"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/ExcessiveParameterList">
        <properties>
            <property name="minimum" value="5"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/TooManyFields">
        <properties>
            <property name="maxfields" value="15"/>
        </properties>
    </rule>

    <rule ref="rulesets/codesize.xml/TooManyMethods">
        <properties>
            <property name="maxmethods" value="20"/>
        </properties>
    </rule>

    <!-- Clean Code Rules -->
    <rule ref="rulesets/cleancode.xml">
        <exclude name="StaticAccess"/>
        <exclude name="ElseExpression"/>
    </rule>

    <!-- Design Rules -->
    <rule ref="rulesets/design.xml"/>

    <!-- Naming Rules -->
    <rule ref="rulesets/naming.xml">
        <exclude name="ShortVariable"/>
        <exclude name="LongVariable"/>
    </rule>

    <rule ref="rulesets/naming.xml/ShortVariable">
        <properties>
            <property name="minimum" value="2"/>
            <property name="exceptions" value="id,db,tz"/>
        </properties>
    </rule>

    <!-- Unused Code Rules -->
    <rule ref="rulesets/unusedcode.xml"/>
</ruleset>
```

### Common Issues Detected

```php
<?php

declare(strict_types=1);

// ❌ PHPMD: Too many parameters
class OrderService
{
    public function createOrder(
        int $userId,
        array $items,
        string $shippingAddress,
        string $billingAddress,
        string $paymentMethod,
        ?string $couponCode,
        bool $giftWrap
    ): Order {
        // ...
    }
}

// ✅ Fixed: Use parameter object
class OrderService
{
    public function createOrder(OrderData $orderData): Order
    {
        // ...
    }
}

// ❌ PHPMD: Cyclomatic complexity too high
class PriceCalculator
{
    public function calculate(Order $order): float
    {
        $total = 0;

        if ($order->hasDiscount()) {
            if ($order->discountType === 'percentage') {
                $total -= $total * ($order->discountValue / 100);
            } elseif ($order->discountType === 'fixed') {
                $total -= $order->discountValue;
            }
        }

        if ($order->requiresShipping()) {
            if ($order->shippingMethod === 'express') {
                $total += 20;
            } elseif ($order->shippingMethod === 'standard') {
                $total += 5;
            }
        }

        // ... more nested conditions
    }
}

// ✅ Fixed: Extract methods
class PriceCalculator
{
    public function calculate(Order $order): float
    {
        $total = $order->getSubtotal();
        $total = $this->applyDiscount($total, $order);
        $total = $this->addShipping($total, $order);

        return $total;
    }

    private function applyDiscount(float $total, Order $order): float
    {
        if (!$order->hasDiscount()) {
            return $total;
        }

        return match ($order->discountType) {
            'percentage' => $total * (1 - $order->discountValue / 100),
            'fixed' => $total - $order->discountValue,
            default => $total,
        };
    }

    private function addShipping(float $total, Order $order): float
    {
        if (!$order->requiresShipping()) {
            return $total;
        }

        return $total + match ($order->shippingMethod) {
            'express' => 20,
            'standard' => 5,
            default => 0,
        };
    }
}
```

---

## Section 6: PHPCPD - Copy-Paste Detection

Find duplicate code blocks.

### Installation and Usage

```bash
# Install PHPCPD
composer require --dev sebastian/phpcpd

# Detect duplicates
vendor/bin/phpcpd src

# With minimum lines and tokens
vendor/bin/phpcpd --min-lines 5 --min-tokens 50 src
```

### Example Output

```bash
phpcpd 6.0.3 by Sebastian Bergmann.

Found 2 clones with 45 duplicated lines in 4 files:

  - src/Services/UserService.php:15-30
    src/Services/AdminService.php:20-35

  - src/Repositories/UserRepository.php:40-55
    src/Repositories/PostRepository.php:45-60

2.15% duplicated lines out of 2094 total lines of code.

Average: 22.5 lines per clone
```

### Refactoring Duplicates

```php
<?php

declare(strict_types=1);

// ❌ Before: Duplicated code
class UserRepository
{
    public function findActive(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE status = ? ORDER BY created_at DESC'
        );
        $stmt->execute(['active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class PostRepository
{
    public function findPublished(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM posts WHERE status = ? ORDER BY created_at DESC'
        );
        $stmt->execute(['published']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ✅ After: Extracted to base class
abstract class Repository
{
    protected function findByStatus(string $status): array
    {
        $table = $this->getTable();
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$table} WHERE status = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    abstract protected function getTable(): string;
}

class UserRepository extends Repository
{
    protected function getTable(): string
    {
        return 'users';
    }

    public function findActive(): array
    {
        return $this->findByStatus('active');
    }
}

class PostRepository extends Repository
{
    protected function getTable(): string
    {
        return 'posts';
    }

    public function findPublished(): array
    {
        return $this->findByStatus('published');
    }
}
```

---

## Section 7: Git Hooks

Automate quality checks before commits.

### Pre-commit Hook

```bash
#!/bin/bash
# .git/hooks/pre-commit

echo "Running code quality checks..."

# Get list of staged PHP files
STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep ".php$")

if [ -z "$STAGED_FILES" ]; then
    echo "No PHP files to check"
    exit 0
fi

# Run PHPStan
echo "Running PHPStan..."
vendor/bin/phpstan analyse $STAGED_FILES --level=8 --no-progress
if [ $? -ne 0 ]; then
    echo "❌ PHPStan failed. Please fix errors before committing."
    exit 1
fi

# Run PHP_CodeSniffer
echo "Running PHP_CodeSniffer..."
vendor/bin/phpcs $STAGED_FILES --standard=PSR12
if [ $? -ne 0 ]; then
    echo "❌ Code style issues found. Run 'vendor/bin/phpcbf' to fix."
    exit 1
fi

# Run tests
echo "Running tests..."
vendor/bin/phpunit
if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Please fix before committing."
    exit 1
fi

echo "✅ All checks passed!"
exit 0
```

### Husky-like Setup with Composer

```json
{
    "scripts": {
        "pre-commit": [
            "@php-cs-fixer",
            "@phpstan",
            "@phpunit"
        ],
        "php-cs-fixer": "php-cs-fixer fix --dry-run --diff",
        "phpstan": "phpstan analyse src tests --level=8",
        "phpunit": "phpunit",
        "test": [
            "@phpstan",
            "@phpunit"
        ],
        "fix": "php-cs-fixer fix"
    }
}
```

### Captain Hook Integration

```bash
# Install Captain Hook
composer require --dev captainhook/captainhook

# Initialize hooks
vendor/bin/captainhook install
```

```json
{
    "config": {
        "captainhook": {
            "pre-commit": {
                "enabled": true,
                "actions": [
                    {
                        "action": "vendor/bin/phpstan analyse src --level=8",
                        "options": [],
                        "conditions": []
                    },
                    {
                        "action": "vendor/bin/phpcs src --standard=PSR12",
                        "options": [],
                        "conditions": []
                    }
                ]
            },
            "pre-push": {
                "enabled": true,
                "actions": [
                    {
                        "action": "vendor/bin/phpunit",
                        "options": [],
                        "conditions": []
                    }
                ]
            }
        }
    }
}
```

---

## Section 8: CI/CD Integration

Integrate quality tools into your pipeline.

### GitHub Actions

```yaml
# .github/workflows/code-quality.yml
name: Code Quality

on: [push, pull_request]

jobs:
  code-quality:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          tools: composer, cs2pr

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run PHPStan
        run: vendor/bin/phpstan analyse src tests --level=8 --error-format=github

      - name: Run PHP_CodeSniffer
        run: vendor/bin/phpcs src tests --standard=PSR12 --report=checkstyle | cs2pr

      - name: Run PHPMD
        run: vendor/bin/phpmd src github cleancode,codesize,controversial,design,naming,unusedcode

      - name: Run PHPCPD
        run: vendor/bin/phpcpd src

      - name: Run Tests
        run: vendor/bin/phpunit --coverage-clover=coverage.xml

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

### Combining Tools

```bash
#!/bin/bash
# scripts/check-quality.sh

set -e

echo "🔍 Running code quality checks..."

# Static analysis
echo "📊 PHPStan..."
vendor/bin/phpstan analyse src tests --level=8

# Code style
echo "🎨 PHP_CodeSniffer..."
vendor/bin/phpcs src tests --standard=PSR12

# Mess detection
echo "🔨 PHPMD..."
vendor/bin/phpmd src text cleancode,codesize,design,naming,unusedcode

# Copy-paste detection
echo "📋 PHPCPD..."
vendor/bin/phpcpd src --min-lines=5

# Tests
echo "🧪 PHPUnit..."
vendor/bin/phpunit --coverage-text

echo "✅ All quality checks passed!"
```

---

## Section 9: IDE Integration

Configure your IDE for real-time feedback.

### PHPStorm Configuration

**Enable PHPStan:**

1. Settings → PHP → Quality Tools → PHPStan
2. Configuration: Point to `vendor/bin/phpstan`
3. Configuration file: `phpstan.neon`
4. Enable inspection: Settings → Editor → Inspections → PHPStan validation

**Enable PHP_CodeSniffer:**

1. Settings → PHP → Quality Tools → PHP_CodeSniffer
2. Configuration: Point to `vendor/bin/phpcs`
3. Coding standard: PSR12
4. Enable inspection: Settings → Editor → Inspections → PHP Code Sniffer validation

**Enable PHP CS Fixer:**

1. Settings → PHP → Quality Tools → PHP CS Fixer
2. PHP CS Fixer path: `vendor/bin/php-cs-fixer`
3. Ruleset: `.php-cs-fixer.php`
4. Enable on save: Settings → Tools → Actions on Save → Reformat code

### VS Code Configuration

```json
{
    "php.validate.enable": true,
    "php.validate.run": "onType",

    // PHPStan
    "phpstan.enabled": true,
    "phpstan.level": "8",
    "phpstan.configFile": "phpstan.neon",

    // PHP_CodeSniffer
    "phpcs.enable": true,
    "phpcs.standard": "PSR12",

    // PHP CS Fixer
    "php-cs-fixer.executablePath": "${workspaceFolder}/vendor/bin/php-cs-fixer",
    "php-cs-fixer.onsave": true,
    "php-cs-fixer.rules": "@PSR12",

    // Format on save
    "[php]": {
        "editor.formatOnSave": true,
        "editor.defaultFormatter": "junstyle.php-cs-fixer"
    }
}
```

---

## Section 10: Metrics and Reporting

Measure and track code quality over time.

### PHPMetrics

```bash
# Install PHPMetrics
composer require --dev phpmetrics/phpmetrics

# Generate report
vendor/bin/phpmetrics --report-html=build/metrics src
```

### SonarQube for PHP

```yaml
# sonar-project.properties
sonar.projectKey=my-php-project
sonar.projectName=My PHP Project
sonar.sources=src
sonar.tests=tests

sonar.php.coverage.reportPaths=coverage/clover.xml
sonar.php.tests.reportPath=build/phpunit.xml

sonar.exclusions=vendor/**,tests/**
```

### Quality Gate Script

```php
<?php

declare(strict_types=1);

/**
 * Check if code meets quality standards
 */

$metrics = [
    'phpstan' => checkPHPStan(),
    'phpcs' => checkPHPCS(),
    'coverage' => checkCoverage(),
    'complexity' => checkComplexity(),
];

$passed = array_filter($metrics);

if (count($passed) === count($metrics)) {
    echo "✅ Quality gate passed!\n";
    exit(0);
} else {
    echo "❌ Quality gate failed:\n";
    foreach ($metrics as $check => $result) {
        echo "  " . ($result ? '✅' : '❌') . " {$check}\n";
    }
    exit(1);
}

function checkPHPStan(): bool
{
    exec('vendor/bin/phpstan analyse src --level=8', $output, $code);
    return $code === 0;
}

function checkPHPCS(): bool
{
    exec('vendor/bin/phpcs src --standard=PSR12', $output, $code);
    return $code === 0;
}

function checkCoverage(): bool
{
    $xml = simplexml_load_file('coverage/clover.xml');
    $metrics = $xml->project->metrics;
    $coverage = (int) $metrics['statements'] / (int) $metrics['elements'] * 100;

    return $coverage >= 80; // Require 80% coverage
}

function checkComplexity(): bool
{
    exec('vendor/bin/phpmd src text codesize', $output, $code);
    return $code === 0;
}
```

---

## Exercises

Practice code quality tool configuration:

### Exercise 1: Set Up Quality Tools

```bash
# TODO: Install and configure all quality tools
# - PHPStan with level 8
# - PHP_CodeSniffer with PSR-12
# - PHP CS Fixer with custom rules
# - PHPMD with custom ruleset
```

### Exercise 2: Create Quality Check Script

```bash
#!/bin/bash
# TODO: Create script that runs all checks
# - Exit with error if any check fails
# - Display progress and results
# - Generate summary report
```

### Exercise 3: Git Hooks

```bash
# TODO: Set up pre-commit hook
# - Run PHPStan on staged files
# - Run PHPCS on staged files
# - Prevent commit if checks fail
```

---

## Common Pitfalls

**❌ Running Tools on Vendor Directory**

```bash
# Bad - Checking vendor code
vendor/bin/phpstan analyse . --level=8

# Good - Only check your code
vendor/bin/phpstan analyse src tests --level=8
```

**❌ Ignoring All Warnings**

```neon
# Bad - Hiding all issues
parameters:
    ignoreErrors:
        - '#.*#'

# Good - Specific ignores only
parameters:
    ignoreErrors:
        - '#Call to deprecated method#'
```

**❌ Not Fixing Style Issues**

```bash
# Bad - Just checking without fixing
vendor/bin/phpcs src

# Good - Auto-fix what you can
vendor/bin/phpcbf src
vendor/bin/php-cs-fixer fix src
```

---

## Best Practices Summary

✅ **Start with lower levels** - Gradually increase PHPStan/Psalm levels
✅ **Automate checks** - Use Git hooks and CI/CD
✅ **Fix automatically** - Use PHP CS Fixer when possible
✅ **Track metrics** - Monitor code quality over time
✅ **Configure IDE** - Get instant feedback while coding
✅ **Use standards** - Follow PSR-12 for consistency
✅ **Exclude generated code** - Don't check vendor or build directories
✅ **Document exceptions** - Explain why rules are disabled
✅ **Run locally** - Catch issues before pushing
✅ **Keep tools updated** - Benefit from latest improvements

---

## Further Reading

- [PHPStan Documentation](https://phpstan.org/)
- [Psalm Documentation](https://psalm.dev/)
- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHP CS Fixer Documentation](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/)

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Configure and run PHPStan for static analysis
- [ ] Use PHP_CodeSniffer to enforce coding standards
- [ ] Automatically format code with PHP CS Fixer
- [ ] Set up Git hooks for automated checks
- [ ] Detect code smells with PHPMD
- [ ] Find duplicate code with PHPCPD
- [ ] Integrate tools into CI/CD pipelines
- [ ] Customize coding standards for your project
- [ ] Configure IDE integration for real-time feedback
- [ ] Measure and track code quality metrics

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/13-integration-testing">← Chapter 13</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/15-http-and-request-response">Chapter 15 →</a></div>
</div>
