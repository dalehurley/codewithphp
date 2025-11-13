---
title: "08: Code Quality - ESLint meets PHP_CodeSniffer"
description: "Transition from ESLint and Prettier to PHP code quality tools. Learn linting, static analysis, formatting, and type checking in the PHP ecosystem."
series: "php-typescript-developers"
chapter: 8
order: 8
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/07-testing"
---

# Code Quality: ESLint meets PHP_CodeSniffer

## Overview

The PHP ecosystem has a rich set of code quality tools similar to TypeScript's ESLint, Prettier, and TypeScript compiler. This chapter maps your existing knowledge to PHP's tooling landscape.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Use PHP_CodeSniffer (PHPCS) for code style checking
- ✅ Apply PHPStan for static analysis (like TypeScript compiler)
- ✅ Format code with PHP-CS-Fixer (like Prettier)
- ✅ Configure quality tools for your project
- ✅ Integrate tools with your editor
- ✅ Set up pre-commit hooks
- ✅ Run quality checks in CI/CD

## Tool Ecosystem Comparison

| TypeScript/JavaScript | PHP | Purpose |
|----------------------|-----|---------|
| **ESLint** | **PHP_CodeSniffer (PHPCS)** | Code style linting |
| **Prettier** | **PHP-CS-Fixer / Laravel Pint** | Code formatting |
| **TypeScript Compiler** | **PHPStan / Psalm** | Static analysis |
| **SonarQube** | **SonarQube (same)** | Code quality platform |
| **Husky** | **GrumPHP / Husky** | Git hooks |

## Code Style: ESLint vs PHP_CodeSniffer

### ESLint Setup

```bash
npm install --save-dev eslint @typescript-eslint/parser @typescript-eslint/eslint-plugin
```

**.eslintrc.json:**
```json
{
  "parser": "@typescript-eslint/parser",
  "extends": [
    "eslint:recommended",
    "plugin:@typescript-eslint/recommended"
  ],
  "rules": {
    "semi": ["error", "always"],
    "quotes": ["error", "single"],
    "indent": ["error", 2],
    "no-unused-vars": "error",
    "@typescript-eslint/no-explicit-any": "warn"
  }
}
```

**Run:**
```bash
npx eslint src/
npx eslint src/ --fix
```

### PHP_CodeSniffer Setup

```bash
composer require --dev squizlabs/php_codesniffer
```

**phpcs.xml:**
```xml
<?xml version="1.0"?>
<ruleset name="MyProject">
    <description>Coding standard for my project</description>

    <!-- Check all PHP files in src/ -->
    <file>src</file>

    <!-- Use PSR-12 standard -->
    <rule ref="PSR12"/>

    <!-- Custom rules -->
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="150"/>
        </properties>
    </rule>

    <!-- Ignore specific patterns -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
</ruleset>
```

**Run:**
```bash
vendor/bin/phpcs
vendor/bin/phpcbf  # Automatic fix (like --fix)
```

**Coding Standards:**
- **PSR-1:** Basic coding standard
- **PSR-12:** Extended coding style (successor to PSR-2)
- **PEAR:** PEAR coding standard
- **Zend:** Zend Framework standard
- **Squiz:** Squiz Labs standard

### ESLint vs PHPCS Rules

| ESLint Rule | PHPCS Rule | Description |
|-------------|------------|-------------|
| `indent` | `Generic.WhiteSpace.ScopeIndent` | Indentation |
| `semi` | `Squiz.PHP.DiscouragedFunctions` | Semicolons |
| `quotes` | `Squiz.Strings.DoubleQuoteUsage` | Quote style |
| `no-unused-vars` | `Generic.CodeAnalysis.UnusedFunctionParameter` | Unused variables |
| `max-len` | `Generic.Files.LineLength` | Line length |
| `camelcase` | `Squiz.NamingConventions.ValidVariableName` | Naming conventions |

### Custom PHPCS Rules

**phpcs.xml with custom rules:**
```xml
<?xml version="1.0"?>
<ruleset name="Custom">
    <rule ref="PSR12">
        <!-- Exclude specific rules -->
        <exclude name="PSR2.Methods.FunctionCallSignature.SpaceAfterOpenBracket"/>
    </rule>

    <!-- Arrays must use short syntax -->
    <rule ref="Generic.Arrays.DisallowLongArraySyntax"/>

    <!-- No trailing whitespace -->
    <rule ref="Squiz.WhiteSpace.SuperfluousWhitespace">
        <properties>
            <property name="ignoreBlankLines" value="true"/>
        </properties>
    </rule>

    <!-- Class names must be StudlyCaps -->
    <rule ref="Squiz.Classes.ValidClassName"/>

    <!-- Methods must be camelCase -->
    <rule ref="PSR1.Methods.CamelCapsMethodName"/>
</ruleset>
```

## Code Formatting: Prettier vs PHP-CS-Fixer

### Prettier Setup

```bash
npm install --save-dev prettier
```

**.prettierrc:**
```json
{
  "semi": true,
  "singleQuote": true,
  "tabWidth": 2,
  "trailingComma": "es5",
  "printWidth": 100,
  "arrowParens": "avoid"
}
```

**Run:**
```bash
npx prettier --write src/
```

### PHP-CS-Fixer Setup

```bash
composer require --dev friendsofphp/php-cs-fixer
```

**.php-cs-fixer.php:**
```php
<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
    ])
    ->setFinder($finder);
```

**Run:**
```bash
vendor/bin/php-cs-fixer fix
vendor/bin/php-cs-fixer fix --dry-run  # Preview changes
```

### Laravel Pint (Opinionated Alternative)

Laravel Pint is a zero-config PHP code formatter built on PHP-CS-Fixer:

```bash
composer require --dev laravel/pint
```

**Run:**
```bash
vendor/bin/pint
vendor/bin/pint --test  # Check without modifying
```

**pint.json (optional):**
```json
{
  "preset": "laravel",
  "rules": {
    "array_syntax": {
      "syntax": "short"
    },
    "binary_operator_spaces": {
      "default": "single_space"
    }
  }
}
```

## Static Analysis: TypeScript vs PHPStan/Psalm

### TypeScript Compiler

```bash
npm install --save-dev typescript
```

**tsconfig.json:**
```json
{
  "compilerOptions": {
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true
  }
}
```

**Run:**
```bash
npx tsc --noEmit  # Type check without compilation
```

### PHPStan Setup

PHPStan performs static analysis similar to TypeScript's type checking:

```bash
composer require --dev phpstan/phpstan
```

**phpstan.neon:**
```neon
parameters:
    level: 8  # 0 (loose) to 9 (max strict)
    paths:
        - src
    excludePaths:
        - src/legacy/*
    ignoreErrors:
        - '#Unsafe usage of new static#'
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: true
```

**Run:**
```bash
vendor/bin/phpstan analyse
vendor/bin/phpstan analyse --level=5
```

**PHPStan Levels:**
- **Level 0:** Basic checks
- **Level 4:** Check for unknown methods, properties
- **Level 6:** Check for missing type hints
- **Level 8:** Check for strict types
- **Level 9:** Maximum strictness (mixed disallowed)

### Psalm Setup

Psalm is another powerful static analysis tool (alternative to PHPStan):

```bash
composer require --dev vimeo/psalm
```

**psalm.xml:**
```xml
<?xml version="1.0"?>
<psalm
    errorLevel="4"
    resolveFromConfigFile="true"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="https://getpsalm.org/schema/config"
    xsi:schemaLocation="https://getpsalm.org/schema/config vendor/vimeo/psalm/config.xsd"
>
    <projectFiles>
        <directory name="src" />
        <ignoreFiles>
            <directory name="vendor" />
        </ignoreFiles>
    </projectFiles>
</psalm>
```

**Run:**
```bash
vendor/bin/psalm
vendor/bin/psalm --show-info=true
```

### Generic Type Annotations

Both PHPStan and Psalm support generic type annotations via docblocks:

```php
<?php
/**
 * @template T
 * @param array<T> $items
 * @return T|null
 */
function first(array $items): mixed {
    return $items[0] ?? null;
}

/**
 * @template T of object
 */
class Repository {
    /**
     * @param class-string<T> $className
     * @return T
     */
    public function find(string $className, int $id): object {
        // ...
    }
}

/** @var Repository<User> */
$userRepo = new Repository();
$user = $userRepo->find(User::class, 1); // PHPStan knows this is User
```

## Configuration Examples

### Complete Project Setup

**composer.json:**
```json
{
  "require-dev": {
    "squizlabs/php_codesniffer": "^3.7",
    "friendsofphp/php-cs-fixer": "^3.35",
    "phpstan/phpstan": "^1.10",
    "vimeo/psalm": "^5.15"
  },
  "scripts": {
    "lint": "phpcs",
    "lint:fix": "phpcbf",
    "format": "php-cs-fixer fix",
    "analyse": "phpstan analyse",
    "psalm": "psalm",
    "check": [
      "@lint",
      "@analyse",
      "@test"
    ]
  }
}
```

**Run quality checks:**
```bash
composer lint        # Check code style
composer lint:fix    # Fix code style
composer format      # Format code
composer analyse     # Static analysis
composer check       # Run all checks
```

## Editor Integration

### VS Code

**TypeScript:**
```json
{
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode"
}
```

**PHP:**
```json
{
  "php.validate.enable": true,
  "php.validate.run": "onSave",
  "[php]": {
    "editor.defaultFormatter": "junstyle.php-cs-fixer",
    "editor.formatOnSave": true
  },
  "phpstan.enabled": true,
  "phpstan.level": "8"
}
```

**Extensions:**
- PHP Intelephense
- PHP CS Fixer
- PHPStan
- Psalm

### PHPStorm

PHPStorm has built-in support for all PHP quality tools:

1. **Settings → PHP → Quality Tools**
2. Configure paths to PHPCS, PHP-CS-Fixer, PHPStan
3. Enable inspections
4. Configure code style (PSR-12)
5. Enable "Reformat Code" on save

## Pre-commit Hooks

### Husky (TypeScript)

```bash
npm install --save-dev husky lint-staged
npx husky install
```

**package.json:**
```json
{
  "lint-staged": {
    "*.ts": [
      "eslint --fix",
      "prettier --write"
    ]
  }
}
```

**.husky/pre-commit:**
```bash
#!/bin/sh
npx lint-staged
```

### GrumPHP (PHP)

```bash
composer require --dev phpro/grumphp
```

**grumphp.yml:**
```yaml
grumphp:
  tasks:
    phpcs:
      standard: PSR12
    phpstan:
      level: 8
    phpunit:
      always_execute: false
```

GrumPHP automatically installs git hooks.

### Alternative: PHP-based Husky

```bash
composer require --dev brainmaestro/composer-git-hooks
```

**composer.json:**
```json
{
  "extra": {
    "hooks": {
      "pre-commit": [
        "vendor/bin/phpcs",
        "vendor/bin/phpstan analyse"
      ],
      "pre-push": [
        "vendor/bin/phpunit"
      ]
    }
  },
  "scripts": {
    "post-install-cmd": "vendor/bin/cghooks add --ignore-lock",
    "post-update-cmd": "vendor/bin/cghooks update"
  }
}
```

## CI/CD Integration

### GitHub Actions (TypeScript)

**.github/workflows/lint.yml:**
```yaml
name: Lint

on: [push, pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: npm ci
      - run: npm run lint
      - run: npx tsc --noEmit
      - run: npm test
```

### GitHub Actions (PHP)

**.github/workflows/quality.yml:**
```yaml
name: Code Quality

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Code Style (PHPCS)
        run: vendor/bin/phpcs

      - name: Static Analysis (PHPStan)
        run: vendor/bin/phpstan analyse

      - name: Run Tests
        run: vendor/bin/phpunit --coverage-clover coverage.xml
```

## Practical Example: Full Quality Setup

### Project Structure

```
my-project/
├── src/
├── tests/
├── .php-cs-fixer.php
├── phpcs.xml
├── phpstan.neon
├── phpunit.xml
├── grumphp.yml
└── composer.json
```

### composer.json

```json
{
  "require": {
    "php": "^8.1"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "squizlabs/php_codesniffer": "^3.7",
    "friendsofphp/php-cs-fixer": "^3.35",
    "phpstan/phpstan": "^1.10",
    "phpro/grumphp": "^2.0"
  },
  "scripts": {
    "test": "phpunit",
    "lint": "phpcs",
    "lint:fix": "phpcbf",
    "format": "php-cs-fixer fix",
    "analyse": "phpstan analyse --level=8",
    "quality": [
      "@lint",
      "@analyse",
      "@test"
    ]
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Tests\\": "tests/"
    }
  }
}
```

### Run Quality Checks

```bash
# Check code style
composer lint

# Fix code style automatically
composer lint:fix

# Format code
composer format

# Run static analysis
composer analyse

# Run all quality checks
composer quality
```

## Best Practices

### 1. Start with Presets

**ESLint:**
```json
{
  "extends": ["eslint:recommended", "plugin:@typescript-eslint/recommended"]
}
```

**PHPCS:**
```xml
<rule ref="PSR12"/>
```

**PHPStan:**
```neon
parameters:
    level: 5  # Start with level 5, increase gradually
```

### 2. Enforce in CI

Don't just check locally—enforce in CI/CD:

```yaml
# Fail build if quality checks don't pass
- run: composer lint
- run: composer analyse
```

### 3. Gradual Adoption

If adding to existing project:

**PHPStan with baseline:**
```bash
vendor/bin/phpstan analyse --generate-baseline
```

This creates `phpstan-baseline.neon` with existing errors, allowing you to fix them incrementally.

### 4. Team Consistency

Commit configuration files:
- ✅ `.php-cs-fixer.php`
- ✅ `phpcs.xml`
- ✅ `phpstan.neon`
- ✅ `grumphp.yml`

## Key Takeaways

1. **PHP_CodeSniffer (PHPCS)** = ESLint for code style
2. **PHP-CS-Fixer / Pint** = Prettier for formatting
3. **PHPStan / Psalm** = TypeScript compiler for static analysis
4. **PSR-12** is the standard coding style (like Airbnb style guide)
5. **GrumPHP** provides git hooks (like Husky)
6. **Editor integration** improves development experience
7. **CI/CD integration** enforces quality standards

## Comparison Table

| Feature | TypeScript/JS | PHP |
|---------|--------------|-----|
| **Style Linting** | ESLint | PHP_CodeSniffer |
| **Formatting** | Prettier | PHP-CS-Fixer / Pint |
| **Static Analysis** | TypeScript compiler | PHPStan / Psalm |
| **Git Hooks** | Husky | GrumPHP |
| **CI Integration** | GitHub Actions | GitHub Actions |
| **Editor Support** | VS Code + extensions | VS Code / PHPStorm |
| **Standards** | Airbnb, Standard | PSR-12 |

## Next Steps

Now that you understand code quality tools, let's explore build tools and compilation.

**Next Chapter:** [09: Build Tools: TypeScript Compiler vs PHP](/series/php-typescript-developers/chapters/09-build-tools)

## Resources

- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHP-CS-Fixer](https://cs.symfony.com/)
- [Laravel Pint](https://laravel.com/docs/pint)
- [PHPStan](https://phpstan.org/)
- [Psalm](https://psalm.dev/)
- [PSR-12 Standard](https://www.php-fig.org/psr/psr-12/)
- [GrumPHP](https://github.com/phpro/grumphp)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
