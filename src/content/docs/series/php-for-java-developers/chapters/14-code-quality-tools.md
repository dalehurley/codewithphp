---
title: "14: Code Quality Tools"
description: "PHPStan, PHP_CodeSniffer, PHP CS Fixer, Git hooks"
sidebar:
  label: "14: Code Quality Tools"
  order: 14
  badge:
    text: Intermediate
    variant: caution
---

![Code Quality Tools](/images/php-for-java-developers/chapter-14-code-quality-tools-hero-full.webp)

# Chapter 14: Code Quality Tools

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Code quality tools help maintain consistent code standards, catch bugs early, and ensure your codebase remains maintainable. If you're coming from Java, you're likely familiar with tools like Checkstyle, PMD, SpotBugs, and SonarQube. PHP has equivalent tools that provide static analysis, code formatting, and quality metrics.

This chapter covers the essential code quality tools for PHP development, from static analysis with PHPStan and Psalm to automated code formatting with PHP CS Fixer. You'll learn how to set up these tools, configure them for your project, integrate them into your development workflow, and automate quality checks through Git hooks and CI/CD pipelines.

By the end of this chapter, you'll have a complete code quality toolchain that catches bugs before they reach production, enforces consistent coding standards across your team, and helps maintain a high-quality codebase as your project grows.

**What You'll Learn:**
- Static analysis with PHPStan and Psalm
- Code style checking with PHP_CodeSniffer
- Automatic code formatting with PHP CS Fixer
- Custom coding standards (PSR-12, PSR-1)
- Security-focused tools (Rector, Security Checker)
- Advanced analysis tools (Deptrac, PHP Insights, Infection, PHPBench)
- Git hooks for automated checks (pre-commit, pre-push, commit-msg)
- Integrating quality tools into CI/CD
- Mess detection with PHPMD
- Copy-paste detection with PHPCPD
- Documentation and API quality tools
- Dependency management quality checks
- Advanced configuration and optimization
- Metrics, reporting, and trend analysis
- IDE integration

## What You'll Build

In this chapter, you'll set up:

- A complete PHPStan configuration with level 8 static analysis
- PHP_CodeSniffer rules enforcing PSR-12 coding standards
- PHP CS Fixer configuration for automatic code formatting
- PHPMD ruleset for detecting code smells and complexity issues
- Security scanning with Composer Security Checker and Rector
- Architecture testing with Deptrac
- Mutation testing setup with Infection PHP
- Performance benchmarking with PHPBench
- Git hooks (pre-commit, pre-push, commit-msg) for automated checks
- A comprehensive quality check script that runs all tools in sequence
- CI/CD pipeline integration with GitHub Actions including matrix builds
- Documentation quality tools and PHPDoc standards
- Dependency management quality checks (unused, outdated, licenses)
- Advanced configuration with baselines and custom rules
- Quality metrics dashboard and trend analysis
- IDE configuration for real-time quality feedback
- A production-ready code quality toolchain covering all aspects

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP development basics — [/series/php-for-java-developers/chapters/01-types-variables-and-operators](/series/php-for-java-developers/chapters/01-types-variables-and-operators)
- Composer for dependency management — [/series/php-for-java-developers/chapters/08-composer-and-dependencies](/series/php-for-java-developers/chapters/08-composer-and-dependencies)
- Command-line usage and Git version control
- PHP namespaces and autoloading — [/series/php-for-java-developers/chapters/06-namespaces-and-autoloading](/series/php-for-java-developers/chapters/06-namespaces-and-autoloading)
- Unit testing with PHPUnit — [/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit](/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit) (helpful but not required)

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Configure PHPStan** for static analysis
2. **Use PHP_CodeSniffer** to enforce coding standards
3. **Format code automatically** with PHP CS Fixer
4. **Set up security scanning** with Rector and Security Checker
5. **Test architecture** with Deptrac
6. **Assess test quality** with Infection PHP mutation testing
7. **Benchmark performance** with PHPBench
8. **Set up Git hooks** for pre-commit, pre-push, and commit-msg checks
9. **Detect code smells** with PHPMD
10. **Find duplicate code** with PHPCPD
11. **Integrate tools** into CI/CD pipelines with matrix builds
12. **Manage documentation quality** with PHPDoc and API tools
13. **Check dependency quality** (unused, outdated, licenses)
14. **Customize coding standards** with baselines and custom rules
15. **Configure IDE integration** for real-time feedback
16. **Measure and track** code quality metrics with dashboards

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

```yaml
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

```yaml
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

## Section 3: Security-Focused Tools

Security is a critical aspect of code quality. These tools help identify vulnerabilities and security issues.

### Rector - Automated Refactoring

Rector automates PHP upgrades and code modernization.

```bash
# Install Rector
composer require --dev rector/rector

# Initialize configuration
vendor/bin/rector init

# Run refactoring
vendor/bin/rector process src --dry-run
vendor/bin/rector process src
```

### Rector Configuration

```php
<?php
// rector.php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(
        php84: true
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ])
    ->withSkip([
        __DIR__ . '/vendor',
    ]);
```

### Composer Security Checker

Check for known vulnerabilities in dependencies.

```bash
# Install Composer Security Checker
composer require --dev sensiolabs/security-checker

# Check dependencies
vendor/bin/security-checker security:check

# Or use online service
composer require --dev symfony/security-checker
vendor/bin/security-checker security:check
```

### PHP Security Checker Integration

```bash
#!/bin/bash
# Check for security vulnerabilities

echo "🔒 Checking for security vulnerabilities..."

# Check Composer dependencies
vendor/bin/security-checker security:check

if [ $? -ne 0 ]; then
    echo "❌ Security vulnerabilities found!"
    exit 1
fi

echo "✅ No known security vulnerabilities"
```

### Psalm Security Plugin

Psalm has security-focused analysis.

```bash
# Install Psalm security plugin
composer require --dev psalm/plugin-security-checker

# Run security analysis
vendor/bin/psalm --plugin=vendor/psalm/plugin-security-checker/psalm-plugin.php
```

### Security-Focused PHPStan Rules

```bash
# Install PHPStan security rules
composer require --dev phpstan/phpstan-security-rules
```

```yaml
# phpstan.neon
includes:
    - vendor/phpstan/phpstan-security-rules/rules.neon

parameters:
    level: 8
    paths:
        - src
```

---

## Section 4: PHP_CodeSniffer - Coding Standards

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

## Section 5: PHP CS Fixer - Automatic Formatting

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

## Section 6: PHPMD - Mess Detection

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

## Section 7: PHPCPD - Copy-Paste Detection

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

## Section 8: Advanced Static Analysis Tools

Beyond PHPStan and Psalm, there are specialized tools for architecture, insights, and testing quality.

### Deptrac - Architecture Testing

Deptrac enforces architectural boundaries and dependency rules.

```bash
# Install Deptrac
composer require --dev qossmic/deptrac-shim

# Initialize configuration
vendor/bin/deptrac init

# Analyze dependencies
vendor/bin/deptrac analyse
```

### Deptrac Configuration

```yaml
# deptrac.yaml
deptrac:
  paths:
    - ./src
  exclude_files:
    - '#.*test.*#'
  layers:
    - name: Controller
      collectors:
        - type: className
          regex: .*Controller.*
    - name: Service
      collectors:
        - type: className
          regex: .*Service.*
    - name: Repository
      collectors:
        - type: className
          regex: .*Repository.*
  ruleset:
    Controller:
      - Service
      - Repository
    Service:
      - Repository
    Repository: []
```

### PHP Insights - All-in-One Quality Tool

PHP Insights combines multiple analyzers in one tool.

```bash
# Install PHP Insights
composer require --dev nunomaduro/phpinsights

# Analyze code
vendor/bin/phpinsights analyse src

# Fix issues automatically
vendor/bin/phpinsights fix src
```

### PHP Insights Configuration

```php
<?php
// phpinsights.php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineGlobalConstants;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Classes;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\LowerCaseConstantSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;

return [
    'preset' => 'laravel',
    'ide' => 'vscode',
    'exclude' => [
        'vendor',
        'storage',
        'bootstrap/cache',
    ],
    'add' => [
        Classes::class => [
            ForbiddenNormalClasses::class,
        ],
    ],
    'remove' => [
        ForbiddenTraits::class,
        ForbiddenDefineGlobalConstants::class,
    ],
    'config' => [
        ReturnTypeHintSniff::class => [
            'enableObjectTypeHint' => false,
        ],
        LowerCaseConstantSniff::class => [
            'exclude' => ['T_STRING'],
        ],
    ],
    'requirements' => [
        'min-quality' => 80,
        'min-complexity' => 80,
        'min-architecture' => 80,
        'min-style' => 80,
    ],
];
```

### Infection PHP - Mutation Testing

Infection tests the quality of your test suite by introducing mutations.

```bash
# Install Infection
composer require --dev infection/infection

# Run mutation testing
vendor/bin/infection

# With coverage
vendor/bin/infection --coverage=coverage
```

### Infection Configuration

```json
{
    "timeout": 10,
    "source": {
        "directories": [
            "src"
        ],
        "excludes": [
            "tests",
            "vendor"
        ]
    },
    "logs": {
        "text": "infection.log",
        "html": "infection.html",
        "summary": "infection-summary.log"
    },
    "mutators": {
        "@default": true,
        "TrueValue": {
            "ignore": [
                "App\\Service\\*"
            ]
        }
    },
    "testFramework": "phpunit",
    "phpUnit": {
        "configDir": "."
    },
    "minMsi": 60,
    "minCoveredMsi": 80
}
```

### PHPBench - Performance Benchmarking

PHPBench measures and compares performance of your code.

```bash
# Install PHPBench
composer require --dev phpbench/phpbench

# Run benchmarks
vendor/bin/phpbench run

# Compare results
vendor/bin/phpbench report --report=default
```

### PHPBench Example

```php
<?php

declare(strict_types=1);

namespace Benchmarks;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Benchmark array operations
 */
class ArrayBench
{
    private array $data;

    #[BeforeMethods('setUp')]
    #[Warmup(2)]
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchArrayMap(): void
    {
        array_map(fn($x) => $x * 2, $this->data);
    }

    #[BeforeMethods('setUp')]
    #[Warmup(2)]
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchForeach(): void
    {
        $result = [];
        foreach ($this->data as $value) {
            $result[] = $value * 2;
        }
    }

    public function setUp(): void
    {
        $this->data = range(1, 1000);
    }
}
```

---

## Section 9: Git Hooks

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

## Section 10: Advanced Git Hooks

Beyond pre-commit, there are other useful Git hooks for quality checks.

### Pre-Push Hook

Run comprehensive checks before pushing to remote.

```bash
#!/bin/bash
# .git/hooks/pre-push

echo "Running pre-push quality checks..."

# Run full test suite
echo "Running tests..."
vendor/bin/phpunit

if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Push aborted."
    exit 1
fi

# Run all quality checks
echo "Running quality checks..."
./scripts/check-quality.sh

if [ $? -ne 0 ]; then
    echo "❌ Quality checks failed. Push aborted."
    exit 1
fi

echo "✅ All checks passed!"
exit 0
```

### Commit Message Hook

Enforce commit message standards.

```bash
#!/bin/bash
# .git/hooks/commit-msg

COMMIT_MSG_FILE=$1
COMMIT_MSG=$(cat "$COMMIT_MSG_FILE")

# Check format: type(scope): subject
if ! echo "$COMMIT_MSG" | grep -qE "^(feat|fix|docs|style|refactor|test|chore)(\(.+\))?: .+"; then
    echo "❌ Invalid commit message format."
    echo "Format: type(scope): subject"
    echo "Types: feat, fix, docs, style, refactor, test, chore"
    exit 1
fi

exit 0
```

### Post-Merge Hook

Update dependencies after merge.

```bash
#!/bin/bash
# .git/hooks/post-merge

echo "Running post-merge tasks..."

# Update Composer dependencies
if [ -f "composer.json" ]; then
    echo "Updating Composer dependencies..."
    composer install --no-interaction
fi

# Clear caches
if [ -d "var/cache" ]; then
    echo "Clearing cache..."
    rm -rf var/cache/*
fi

echo "✅ Post-merge tasks completed"
```

---

## Section 11: CI/CD Integration

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
          php-version: '8.4'
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

      - name: Run Security Check
        run: vendor/bin/security-checker security:check

      - name: Run Deptrac
        run: vendor/bin/deptrac analyse
        continue-on-error: true

      - name: Run Infection
        run: vendor/bin/infection --coverage=coverage --min-msi=60
        continue-on-error: true

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
echo ""

ERRORS=0

# Static analysis
echo "📊 PHPStan..."
if vendor/bin/phpstan analyse src tests --level=8 --no-progress; then
    echo "✅ PHPStan passed"
else
    echo "❌ PHPStan failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Code style
echo "🎨 PHP_CodeSniffer..."
if vendor/bin/phpcs src tests --standard=PSR12; then
    echo "✅ PHP_CodeSniffer passed"
else
    echo "❌ PHP_CodeSniffer failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Mess detection
echo "🔨 PHPMD..."
if vendor/bin/phpmd src text cleancode,codesize,design,naming,unusedcode; then
    echo "✅ PHPMD passed"
else
    echo "❌ PHPMD failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Copy-paste detection
echo "📋 PHPCPD..."
if vendor/bin/phpcpd src --min-lines=5; then
    echo "✅ PHPCPD passed"
else
    echo "❌ PHPCPD failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Security check
echo "🔒 Security Check..."
if vendor/bin/security-checker security:check; then
    echo "✅ Security check passed"
else
    echo "❌ Security vulnerabilities found"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Architecture check
echo "🏗️ Deptrac..."
if vendor/bin/deptrac analyse --no-progress; then
    echo "✅ Deptrac passed"
else
    echo "⚠️ Architecture violations found (non-blocking)"
fi
echo ""

# Summary
if [ $ERRORS -eq 0 ]; then
    echo "✅ All quality checks passed!"
    exit 0
else
    echo "❌ $ERRORS check(s) failed"
    exit 1
fi
```

### Matrix Builds for Multiple PHP Versions

```yaml
# .github/workflows/quality-matrix.yml
name: Quality Matrix

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['8.1', '8.2', '8.3', '8.4']
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP ${{ matrix.php-version }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          tools: composer
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse src --level=8
      
      - name: Run Tests
        run: vendor/bin/phpunit
```

---

## Section 12: Documentation & API Quality

Maintaining high-quality documentation is part of code quality.

### PHPDoc Standards

Enforce proper PHPDoc comments.

```bash
# Install PHPDoc Checker
composer require --dev phpstan/phpstan-strict-rules

# PHPStan checks PHPDoc completeness
vendor/bin/phpstan analyse src --level=8
```

### PHPDoc Example

```php
<?php

declare(strict_types=1);

namespace App\Services;

/**
 * User service for managing user operations
 *
 * @package App\Services
 */
class UserService
{
    /**
     * Create a new user
     *
     * @param array{name: string, email: string, age: int} $data User data
     * @return User Created user instance
     * @throws \InvalidArgumentException If data is invalid
     * @throws \RuntimeException If user creation fails
     */
    public function createUser(array $data): User
    {
        // Implementation
    }

    /**
     * Find user by ID
     *
     * @param int $id User ID
     * @return User|null User instance or null if not found
     */
    public function findById(int $id): ?User
    {
        // Implementation
    }
}
```

### API Documentation Tools

Generate API documentation from code.

```bash
# Install phpDocumentor
composer require --dev phpdocumentor/phpdocumentor

# Generate documentation
vendor/bin/phpdoc -d src -t docs/api
```

### OpenAPI/Swagger Generation

```bash
# Install Swagger PHP
composer require --dev zircote/swagger-php

# Generate OpenAPI spec
vendor/bin/openapi src -o openapi.yaml
```

### README Quality Checks

```bash
#!/bin/bash
# Check README completeness

README="README.md"

if [ ! -f "$README" ]; then
    echo "❌ README.md not found"
    exit 1
fi

# Check for required sections
REQUIRED_SECTIONS=("Installation" "Usage" "Requirements" "License")

for section in "${REQUIRED_SECTIONS[@]}"; do
    if ! grep -q "$section" "$README"; then
        echo "⚠️ README missing section: $section"
    fi
done

echo "✅ README check completed"
```

---

## Section 13: Dependency Management Quality

Ensure your dependencies are well-managed and secure.

### Composer Unused Dependencies

Find unused Composer packages.

```bash
# Install Composer Unused
composer require --dev icanhazstring/composer-unused

# Check for unused dependencies
vendor/bin/composer-unused
```

### Composer Normalize

Standardize composer.json format.

```bash
# Install Composer Normalize
composer require --dev ergebnis/composer-normalize

# Normalize composer.json
vendor/bin/composer-normalize

# Check without modifying
vendor/bin/composer-normalize --dry-run
```

### License Compatibility Checker

```bash
# Install License Checker
composer require --dev composer/composer

# Check licenses
composer licenses
```

### Dependency Update Checker

```bash
# Install Composer Outdated
composer require --dev composer/composer

# Check for outdated packages
composer outdated

# Show security advisories
composer audit
```

### Composer Scripts for Quality

```json
{
    "scripts": {
        "quality": [
            "@phpstan",
            "@phpcs",
            "@security-check",
            "@unused-check"
        ],
        "phpstan": "phpstan analyse src --level=8",
        "phpcs": "phpcs src --standard=PSR12",
        "security-check": "security-checker security:check",
        "unused-check": "composer-unused",
        "normalize": "composer-normalize",
        "audit": "composer audit"
    }
}
```

---

## Section 14: Advanced Configuration

Optimize and customize your quality toolchain.

### Baseline Files

Manage existing technical debt with baselines.

```bash
# Generate PHPStan baseline
vendor/bin/phpstan analyse src --level=8 --generate-baseline phpstan-baseline.neon
```

```yaml
# phpstan-baseline.neon
parameters:
    ignoreErrors:
        - '#Call to an undefined method App\\Service\\.*#'
        - '#Access to an undefined property App\\Model\\.*#'
```

### Custom Rule Development

Create project-specific rules.

```php
<?php
// src/Rules/CustomSniff.php

declare(strict_types=1);

namespace App\Rules;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ServiceNamingSniff implements Sniff
{
    public function register(): array
    {
        return [T_CLASS];
    }

    public function process(File $phpcsFile, $stackPtr): void
    {
        $className = $phpcsFile->getDeclarationName($stackPtr);
        $namespace = $phpcsFile->getNamespace($stackPtr);

        if (str_contains($namespace, 'Service') && !str_ends_with($className, 'Service')) {
            $phpcsFile->addError(
                'Service classes must end with "Service"',
                $stackPtr,
                'InvalidServiceName'
            );
        }
    }
}
```

### Team Configuration Sharing

Share quality configurations across teams.

```bash
# Create quality-config package
mkdir quality-config
cd quality-config

# Include configurations
# - phpstan.neon
# - phpcs.xml
# - .php-cs-fixer.php
# - deptrac.yaml
# - infection.json
```

### Performance Optimization

Speed up analysis for large codebases.

```yaml
# phpstan.neon - Optimized for performance
parameters:
    level: 8
    paths:
        - src
    parallel:
        jobSize: 20
        maximumNumberOfProcesses: 4
    tmpDir: build/phpstan
    resultCachePath: build/phpstan-result-cache.php
```

```bash
# Use result cache
vendor/bin/phpstan analyse --memory-limit=2G
```

### Quality Gates

Set up pass/fail thresholds.

```php
<?php
// scripts/quality-gate.php

declare(strict_types=1);

$gates = [
    'phpstan' => ['command' => 'vendor/bin/phpstan analyse src --level=8', 'required' => true],
    'phpcs' => ['command' => 'vendor/bin/phpcs src --standard=PSR12', 'required' => true],
    'coverage' => ['command' => 'vendor/bin/phpunit --coverage-text', 'threshold' => 80, 'required' => false],
    'infection' => ['command' => 'vendor/bin/infection --min-msi=60', 'required' => false],
];

$failed = [];

foreach ($gates as $name => $config) {
    echo "Checking {$name}...\n";
    
    exec($config['command'], $output, $code);
    
    if ($code !== 0) {
        if ($config['required'] ?? true) {
            $failed[] = $name;
            echo "❌ {$name} failed (required)\n";
        } else {
            echo "⚠️ {$name} failed (optional)\n";
        }
    } else {
        echo "✅ {$name} passed\n";
    }
}

if (!empty($failed)) {
    echo "\n❌ Quality gate failed. Required checks: " . implode(', ', $failed) . "\n";
    exit(1);
}

echo "\n✅ Quality gate passed!\n";
exit(0);
```

---

## Section 15: IDE Integration

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

## Section 16: Metrics and Reporting

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

### HTML Reports

Generate visual quality dashboards.

```bash
# PHPStan HTML report
vendor/bin/phpstan analyse src --level=8 --error-format=table > phpstan-report.html

# PHPMetrics HTML report
vendor/bin/phpmetrics --report-html=build/metrics src

# PHP Insights HTML report
vendor/bin/phpinsights analyse src --format=html > build/insights.html
```

### Trend Analysis

Track quality metrics over time.

```php
<?php
// scripts/quality-trend.php

declare(strict_types=1);

/**
 * Track quality metrics over time
 */

$metricsFile = 'build/quality-metrics.json';
$date = date('Y-m-d');

// Run tools and collect metrics
$metrics = [
    'date' => $date,
    'phpstan_errors' => getPHPStanErrors(),
    'phpcs_errors' => getPHPCSErrors(),
    'coverage' => getCoverage(),
    'complexity' => getComplexity(),
    'duplication' => getDuplication(),
];

// Load historical data
$history = file_exists($metricsFile) 
    ? json_decode(file_get_contents($metricsFile), true) 
    : [];

$history[] = $metrics;

// Save updated history
file_put_contents($metricsFile, json_encode($history, JSON_PRETTY_PRINT));

// Generate trend report
generateTrendReport($history);

function getPHPStanErrors(): int
{
    exec('vendor/bin/phpstan analyse src --level=8 --no-progress 2>&1', $output);
    return count(array_filter($output, fn($line) => str_contains($line, 'Error')));
}

function getPHPCSErrors(): int
{
    exec('vendor/bin/phpcs src --standard=PSR12 --report=json 2>&1', $output);
    $json = json_decode(implode("\n", $output), true);
    return $json['totals']['errors'] ?? 0;
}

function getCoverage(): float
{
    exec('vendor/bin/phpunit --coverage-text --coverage-filter=src 2>&1', $output);
    foreach ($output as $line) {
        if (preg_match('/Lines:\s+(\d+\.\d+)%/', $line, $matches)) {
            return (float)$matches[1];
        }
    }
    return 0.0;
}

function getComplexity(): float
{
    exec('vendor/bin/phpmd src json codesize 2>&1', $output);
    $json = json_decode(implode("\n", $output), true);
    // Calculate average complexity
    return 0.0; // Simplified
}

function getDuplication(): float
{
    exec('vendor/bin/phpcpd src --min-lines=5 2>&1', $output);
    foreach ($output as $line) {
        if (preg_match('/(\d+\.\d+)% duplicated/', $line, $matches)) {
            return (float)$matches[1];
        }
    }
    return 0.0;
}

function generateTrendReport(array $history): void
{
    echo "Quality Metrics Trend Report\n";
    echo str_repeat("=", 50) . "\n\n";
    
    foreach ($history as $entry) {
        echo "Date: {$entry['date']}\n";
        echo "  PHPStan Errors: {$entry['phpstan_errors']}\n";
        echo "  PHPCS Errors: {$entry['phpcs_errors']}\n";
        echo "  Coverage: {$entry['coverage']}%\n";
        echo "  Duplication: {$entry['duplication']}%\n";
        echo "\n";
    }
}
```

### Team Dashboards

Create shared quality dashboards.

```bash
#!/bin/bash
# scripts/generate-dashboard.sh

# Generate all reports
vendor/bin/phpmetrics --report-html=build/dashboard/metrics src
vendor/bin/phpinsights analyse src --format=html > build/dashboard/insights.html
vendor/bin/phpstan analyse src --level=8 --error-format=table > build/dashboard/phpstan.html

# Create index page
cat > build/dashboard/index.html <<EOF
<!DOCTYPE html>
<html>
<head>
    <title>Code Quality Dashboard</title>
</head>
<body>
    <h1>Code Quality Dashboard</h1>
    <ul>
        <li><a href="metrics/index.html">PHPMetrics</a></li>
        <li><a href="insights.html">PHP Insights</a></li>
        <li><a href="phpstan.html">PHPStan</a></li>
    </ul>
</body>
</html>
EOF

echo "✅ Dashboard generated at build/dashboard/index.html"
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
    'security' => checkSecurity(),
    'duplication' => checkDuplication(),
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

function checkSecurity(): bool
{
    exec('vendor/bin/security-checker security:check', $output, $code);
    return $code === 0;
}

function checkDuplication(): bool
{
    exec('vendor/bin/phpcpd src --min-lines=5', $output, $code);
    // Check if duplication is below threshold
    foreach ($output as $line) {
        if (preg_match('/(\d+\.\d+)% duplicated/', $line, $matches)) {
            return (float)$matches[1] < 5.0; // Less than 5% duplication
        }
    }
    return true;
}
```

---

## Exercises

Practice code quality tool configuration with these hands-on exercises:

### Exercise 1: Set Up Quality Tools

**Goal**: Install and configure all essential code quality tools for a new project.

Create a new directory called `quality-tools-exercise` and set up:

1. **Initialize a Composer project**:
```bash
mkdir quality-tools-exercise
cd quality-tools-exercise
composer init --no-interaction --name="myproject/quality-demo"
```

2. **Install PHPStan** with level 8:
```bash
composer require --dev phpstan/phpstan
vendor/bin/phpstan --init
# Edit phpstan.neon to set level: 8
```

3. **Install PHP_CodeSniffer** with PSR-12:
```bash
composer require --dev squizlabs/php_codesniffer
vendor/bin/phpcs --config-set default_standard PSR12
```

4. **Install PHP CS Fixer**:
```bash
composer require --dev friendsofphp/php-cs-fixer
vendor/bin/php-cs-fixer init
```

5. **Install PHPMD**:
```bash
composer require --dev phpmd/phpmd
```

**Validation**: Run each tool to verify installation:
```bash
vendor/bin/phpstan --version
vendor/bin/phpcs --version
vendor/bin/php-cs-fixer --version
vendor/bin/phpmd --version
```

Expected output: Version numbers for each tool.

### Exercise 2: Create Quality Check Script

**Goal**: Build a script that runs all quality checks and provides a summary.

Create `scripts/check-quality.sh`:

```bash
#!/bin/bash
# scripts/check-quality.sh

set -e

echo "🔍 Running code quality checks..."
echo ""

ERRORS=0

# PHPStan
echo "📊 Running PHPStan..."
if vendor/bin/phpstan analyse src --level=8 --no-progress; then
    echo "✅ PHPStan passed"
else
    echo "❌ PHPStan failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# PHP_CodeSniffer
echo "🎨 Running PHP_CodeSniffer..."
if vendor/bin/phpcs src --standard=PSR12; then
    echo "✅ PHP_CodeSniffer passed"
else
    echo "❌ PHP_CodeSniffer failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# PHPMD
echo "🔨 Running PHPMD..."
if vendor/bin/phpmd src text cleancode,codesize,design,naming,unusedcode; then
    echo "✅ PHPMD passed"
else
    echo "❌ PHPMD failed"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Summary
if [ $ERRORS -eq 0 ]; then
    echo "✅ All quality checks passed!"
    exit 0
else
    echo "❌ $ERRORS check(s) failed"
    exit 1
fi
```

Make it executable:
```bash
chmod +x scripts/check-quality.sh
```

**Validation**: Run the script:
```bash
./scripts/check-quality.sh
```

Expected output: Summary of all checks with pass/fail status.

### Exercise 3: Set Up Git Pre-commit Hook

**Goal**: Create a Git hook that prevents commits if quality checks fail.

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
# .git/hooks/pre-commit

echo "Running pre-commit quality checks..."

# Get staged PHP files
STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep "\.php$")

if [ -z "$STAGED_FILES" ]; then
    echo "No PHP files to check"
    exit 0
fi

# Run PHPStan on staged files
echo "Running PHPStan on staged files..."
vendor/bin/phpstan analyse $STAGED_FILES --level=8 --no-progress
if [ $? -ne 0 ]; then
    echo "❌ PHPStan failed. Fix errors before committing."
    exit 1
fi

# Run PHP_CodeSniffer on staged files
echo "Running PHP_CodeSniffer on staged files..."
vendor/bin/phpcs $STAGED_FILES --standard=PSR12
if [ $? -ne 0 ]; then
    echo "❌ Code style issues found. Run 'vendor/bin/phpcbf' to auto-fix."
    exit 1
fi

echo "✅ All pre-commit checks passed!"
exit 0
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

**Validation**: Try committing a PHP file with style issues:
```bash
# Create a file with style issues
echo '<?php class Test{public function test(){}}' > test.php
git add test.php
git commit -m "Test commit"
```

Expected output: Hook should prevent commit and show PHP_CodeSniffer errors.

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

```yaml
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
- [PHP_CodeSniffer Documentation](https://github.com/PHPCSStandards/PHP_CodeSniffer)
- [PHP CS Fixer Documentation](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [Rector Documentation](https://getrector.org/)
- [Deptrac Documentation](https://qossmic.github.io/deptrac/)
- [PHP Insights Documentation](https://phpinsights.com/)
- [Infection PHP Documentation](https://infection.github.io/)
- [PHPBench Documentation](https://phpbench.readthedocs.io/)
- [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Composer Security Checker](https://github.com/sensiolabs/security-checker)
- [PHPMetrics Documentation](https://www.phpmetrics.org/)

---

## Wrap-up

Congratulations! You've completed Chapter 14 on Code Quality Tools. In this chapter, you've learned how to:

- ✅ **Set up static analysis** with PHPStan and Psalm to catch bugs before runtime
- ✅ **Enforce coding standards** with PHP_CodeSniffer and PSR-12
- ✅ **Automatically format code** with PHP CS Fixer for consistent style
- ✅ **Scan for security vulnerabilities** with Rector and Security Checker
- ✅ **Test architecture boundaries** with Deptrac to enforce design rules
- ✅ **Assess test quality** with Infection PHP mutation testing
- ✅ **Benchmark performance** with PHPBench to identify regressions
- ✅ **Detect code smells** with PHPMD to identify maintainability issues
- ✅ **Find duplicate code** with PHPCPD to improve code reuse
- ✅ **Automate quality checks** with comprehensive Git hooks (pre-commit, pre-push, commit-msg)
- ✅ **Integrate into CI/CD** with matrix builds across PHP versions
- ✅ **Maintain documentation quality** with PHPDoc and API documentation tools
- ✅ **Manage dependencies** by finding unused packages and checking licenses
- ✅ **Configure advanced settings** with baselines, custom rules, and performance optimization
- ✅ **Configure IDE integration** for real-time feedback while coding
- ✅ **Measure and track quality** with dashboards, trend analysis, and quality gates

These tools form the foundation of a professional PHP development workflow. They help maintain code quality as your project grows, catch bugs early in development, and ensure consistency across team members. In the next chapter, you'll learn about HTTP and request/response handling in PHP.

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Configure and run PHPStan for static analysis
- [ ] Use PHP_CodeSniffer to enforce coding standards
- [ ] Automatically format code with PHP CS Fixer
- [ ] Set up security scanning with Rector and Security Checker
- [ ] Test architecture with Deptrac
- [ ] Run mutation testing with Infection PHP
- [ ] Benchmark performance with PHPBench
- [ ] Set up Git hooks (pre-commit, pre-push, commit-msg) for automated checks
- [ ] Detect code smells with PHPMD
- [ ] Find duplicate code with PHPCPD
- [ ] Integrate tools into CI/CD pipelines with matrix builds
- [ ] Maintain documentation quality with PHPDoc standards
- [ ] Check dependency quality (unused, outdated, licenses)
- [ ] Customize coding standards with baselines and custom rules
- [ ] Configure IDE integration for real-time feedback
- [ ] Measure and track code quality metrics with dashboards


---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/13-integration-testing">← Chapter 13</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/15-http-and-request-response">Chapter 15 →</a></div>
</div>
