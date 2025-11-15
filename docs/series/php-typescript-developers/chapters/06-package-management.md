---
title: "06: Package Management - npm vs Composer"
description: "Transition from npm to Composer seamlessly. Learn package management in PHP with familiar npm concepts - dependencies, scripts, versioning, and publishing."
series: "php-typescript-developers"
chapter: 6
order: 6
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/05-error-handling"
---

# Package Management: npm vs Composer

## Overview

If you're comfortable with npm, you'll feel right at home with Composer. Both are package managers that handle dependencies, version constraints, and scripts. This chapter maps your npm knowledge directly to Composer.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Understand Composer's role (PHP's equivalent to npm)
- ✅ Navigate composer.json vs package.json
- ✅ Install and manage dependencies
- ✅ Use semantic versioning constraints
- ✅ Run scripts via Composer
- ✅ Understand autoloading (PHP's module system)
- ✅ Publish packages to Packagist
- ✅ Use popular PHP packages

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-06)**

This chapter includes package management examples:
- Sample composer.json configurations
- PSR-4 autoloading setup
- Package creation examples
- CLI tool development

Explore the examples:
```bash
cd code/php-typescript-developers/chapter-06
cat composer.json
composer install
```

## Quick Comparison

| Feature | npm | Composer |
|---------|-----|----------|
| **Config File** | `package.json` | `composer.json` |
| **Lock File** | `package-lock.json` | `composer.lock` |
| **Install Command** | `npm install` | `composer install` |
| **Add Package** | `npm install lodash` | `composer require vendor/package` |
| **Dev Dependencies** | `npm install -D` | `composer require --dev` |
| **Registry** | npmjs.com | packagist.org |
| **Scripts** | `npm run script` | `composer run script` |
| **Global Install** | `npm install -g` | `composer global require` |
| **Dependencies Dir** | `node_modules/` | `vendor/` |
| **Autoloading** | `import`/`require()` | PSR-4 autoloading |

## Installation

### npm Installation

```bash
# Install Node.js (includes npm)
# macOS
brew install node

# Verify
node --version
npm --version
```

### Composer Installation

```bash
# macOS
brew install composer

# Linux/macOS (alternative)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows (Chocolatey)
choco install composer

# Verify
composer --version
```

## Configuration Files

### package.json (npm)

```json
{
  "name": "my-app",
  "version": "1.0.0",
  "description": "My TypeScript app",
  "main": "dist/index.js",
  "scripts": {
    "dev": "ts-node src/index.ts",
    "build": "tsc",
    "test": "jest",
    "lint": "eslint src/"
  },
  "dependencies": {
    "express": "^4.18.0",
    "lodash": "^4.17.21"
  },
  "devDependencies": {
    "typescript": "^5.0.0",
    "@types/node": "^20.0.0",
    "jest": "^29.0.0",
    "eslint": "^8.0.0"
  },
  "engines": {
    "node": ">=18.0.0"
  }
}
```

### composer.json (Composer)

```json
{
  "name": "vendor/my-app",
  "description": "My PHP app",
  "type": "project",
  "require": {
    "php": "^8.1",
    "guzzlehttp/guzzle": "^7.5",
    "monolog/monolog": "^3.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "phpstan/phpstan": "^1.10",
    "squizlabs/php_codesniffer": "^3.7"
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
  },
  "scripts": {
    "test": "phpunit",
    "lint": "phpcs src/",
    "analyse": "phpstan analyse"
  },
  "config": {
    "optimize-autoloader": true,
    "sort-packages": true
  }
}
```

**Key Differences:**

1. **Package naming:** npm uses simple names (`lodash`), Composer requires vendor prefix (`vendor/package`)
2. **PHP version:** Specified as a dependency in `require`
3. **Autoloading:** Explicit autoload configuration (PSR-4)
4. **Main entry:** No `main` field (PHP doesn't bundle like Node.js)

## Installing Dependencies

### npm Commands

```bash
# Install all dependencies
npm install
npm i

# Install specific package
npm install express
npm install lodash@4.17.21

# Install as dev dependency
npm install -D typescript
npm install --save-dev jest

# Install globally
npm install -g typescript

# Remove package
npm uninstall express

# Update packages
npm update
npm update express

# Audit security
npm audit
npm audit fix
```

### Composer Commands

```bash
# Install all dependencies
composer install

# Install specific package
composer require guzzlehttp/guzzle
composer require monolog/monolog:^3.0

# Install as dev dependency
composer require --dev phpunit/phpunit
composer require --dev phpstan/phpstan

# Install globally
composer global require laravel/installer

# Remove package
composer remove guzzlehttp/guzzle

# Update packages
composer update
composer update monolog/monolog

# Audit security
composer audit

# Show installed packages
composer show
composer show --tree
```

**Cheat Sheet:**

| npm | Composer |
|-----|----------|
| `npm install` | `composer install` |
| `npm install pkg` | `composer require pkg` |
| `npm install -D pkg` | `composer require --dev pkg` |
| `npm uninstall pkg` | `composer remove pkg` |
| `npm update` | `composer update` |
| `npm list` | `composer show` |

## Version Constraints

Both npm and Composer use semantic versioning (semver).

### npm Versioning

```json
{
  "dependencies": {
    "express": "4.18.0",        // Exact version
    "lodash": "^4.17.21",       // Compatible: 4.17.21 to <5.0.0
    "axios": "~1.4.0",          // Approximately: 1.4.0 to <1.5.0
    "moment": "*",              // Any version (not recommended)
    "react": ">=18.0.0 <19.0.0" // Range
  }
}
```

### Composer Versioning

```json
{
  "require": {
    "monolog/monolog": "3.0.0",      // Exact version
    "guzzlehttp/guzzle": "^7.5",     // Compatible: 7.5.0 to <8.0.0
    "symfony/console": "~6.2.0",     // Approximately: 6.2.0 to <6.3.0
    "psr/log": "*",                  // Any version (not recommended)
    "doctrine/orm": ">=2.14 <3.0"    // Range
  }
}
```

**Identical Behavior!** `^` and `~` work the same way.

### Semver Quick Reference

| Constraint | npm | Composer | Meaning |
|------------|-----|----------|---------|
| **Exact** | `1.2.3` | `1.2.3` | Exactly 1.2.3 |
| **Caret** | `^1.2.3` | `^1.2.3` | 1.2.3 to <2.0.0 |
| **Tilde** | `~1.2.3` | `~1.2.3` | 1.2.3 to <1.3.0 |
| **Wildcard** | `1.2.*` | `1.2.*` | Any patch version |
| **Range** | `>=1.2.3 <2.0` | `>=1.2.3 <2.0` | Between versions |

## Lock Files

### package-lock.json (npm)

```json
{
  "name": "my-app",
  "version": "1.0.0",
  "lockfileVersion": 3,
  "packages": {
    "node_modules/express": {
      "version": "4.18.2",
      "resolved": "https://registry.npmjs.org/express/-/express-4.18.2.tgz",
      "integrity": "sha512-...",
      "dependencies": {
        "body-parser": "1.20.1"
      }
    }
  }
}
```

### composer.lock (Composer)

```json
{
  "packages": [
    {
      "name": "guzzlehttp/guzzle",
      "version": "7.5.0",
      "source": {
        "type": "git",
        "url": "https://github.com/guzzle/guzzle.git",
        "reference": "abc123..."
      },
      "require": {
        "php": "^7.2.5 || ^8.0"
      }
    }
  ]
}
```

**Purpose (Identical):**
- ✅ Lock exact versions of all dependencies
- ✅ Ensure consistent installs across environments
- ✅ Committed to version control
- ✅ Generated automatically

**Commands:**

| npm | Composer |
|-----|----------|
| `npm ci` (clean install from lock) | `composer install` (uses lock if present) |
| `npm install` (updates lock) | `composer update` (updates lock) |

## Scripts

### npm Scripts

```json
{
  "scripts": {
    "dev": "ts-node src/index.ts",
    "build": "tsc",
    "test": "jest",
    "test:watch": "jest --watch",
    "lint": "eslint src/",
    "format": "prettier --write src/",
    "start": "node dist/index.js",
    "pretest": "echo 'Running before test'",
    "posttest": "echo 'Running after test'"
  }
}
```

Run with: `npm run dev`, `npm test`

### Composer Scripts

```json
{
  "scripts": {
    "dev": "php -S localhost:8000 -t public/",
    "test": "phpunit",
    "test:coverage": "phpunit --coverage-html coverage/",
    "lint": "phpcs src/",
    "fix": "phpcbf src/",
    "analyse": "phpstan analyse",
    "pre-test": "echo 'Running before test'",
    "post-test": "echo 'Running after test'"
  }
}
```

Run with: `composer run dev`, `composer test`

**Script Hooks:**

| npm | Composer |
|-----|----------|
| `pretest` | `pre-test` |
| `posttest` | `post-test` |
| `prepublish` | `pre-install-cmd` |

## Autoloading (Module System)

### TypeScript/JavaScript Modules

```typescript
// src/utils/helper.ts
export function greet(name: string): string {
  return `Hello, ${name}!`;
}

export class User {
  constructor(public name: string) {}
}

// src/index.ts
import { greet, User } from './utils/helper';

const user = new User("Alice");
console.log(greet(user.name));
```

### PHP PSR-4 Autoloading

**composer.json:**
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

Run `composer dump-autoload` to generate autoloader.

**PHP Files:**
```php
<?php
// src/Utils/Helper.php
namespace App\Utils;

function greet(string $name): string {
    return "Hello, {$name}!";
}

class User {
    public function __construct(
        public string $name
    ) {}
}
```

```php
<?php
// index.php
require __DIR__ . '/vendor/autoload.php';

use App\Utils\User;
use function App\Utils\greet;

$user = new User("Alice");
echo greet($user->name);
```

**PSR-4 Mapping:**

| Namespace | Directory |
|-----------|-----------|
| `App\` | `src/` |
| `App\Utils\User` | `src/Utils/User.php` |
| `App\Http\Controllers\UserController` | `src/Http/Controllers/UserController.php` |

**Key Points:**
- Namespace must match directory structure
- Class name must match filename
- One class per file
- Always `require __DIR__ . '/vendor/autoload.php';` at entry point

## Popular Packages

### npm Ecosystem

```bash
npm install express          # Web framework
npm install lodash           # Utility library
npm install axios            # HTTP client
npm install dotenv           # Environment variables
npm install winston          # Logging
npm install joi              # Validation
npm install jest             # Testing
```

### Composer Ecosystem

```bash
composer require guzzlehttp/guzzle    # HTTP client (like axios)
composer require monolog/monolog      # Logging (like winston)
composer require vlucas/phpdotenv     # Environment variables (like dotenv)
composer require symfony/console      # CLI framework
composer require doctrine/orm         # ORM
composer require twig/twig            # Templating
composer require phpunit/phpunit --dev  # Testing (like jest)
```

**Framework Ecosystems:**

| TypeScript | PHP |
|------------|-----|
| Express.js | Laravel, Symfony |
| Nest.js | Laravel (similar architecture) |
| Next.js | Laravel + Inertia.js |
| Prisma | Eloquent (Laravel), Doctrine |

## Package Discovery

### npm Registry

**Search:** https://www.npmjs.com/

```bash
npm search express
npm view express
npm view express versions
```

### Packagist (Composer Registry)

**Search:** https://packagist.org/

```bash
composer search guzzle
composer show guzzlehttp/guzzle
composer show guzzlehttp/guzzle --all
```

**Popular Packages Sites:**
- npm: https://www.npmjs.com/
- Packagist: https://packagist.org/
- PHP packages: https://packagist.org/explore/popular

## Creating a Package

### npm Package

**package.json:**
```json
{
  "name": "@username/my-package",
  "version": "1.0.0",
  "main": "dist/index.js",
  "types": "dist/index.d.ts",
  "files": ["dist"],
  "scripts": {
    "build": "tsc",
    "prepublishOnly": "npm run build"
  }
}
```

**Publish:**
```bash
npm login
npm publish --access public
```

### Composer Package

**composer.json:**
```json
{
  "name": "username/my-package",
  "description": "My awesome PHP package",
  "type": "library",
  "license": "MIT",
  "authors": [
    {
      "name": "Your Name",
      "email": "you@example.com"
    }
  ],
  "require": {
    "php": "^8.1"
  },
  "autoload": {
    "psr-4": {
      "Username\\MyPackage\\": "src/"
    }
  }
}
```

**Publish:**
1. Push to GitHub
2. Submit to Packagist: https://packagist.org/packages/submit
3. Packagist auto-updates from your repository

**No manual publish command needed!** Packagist pulls from your repo.

## Private Packages

### npm Private Packages

```json
{
  "name": "@myorg/private-package",
  "private": true
}
```

**Registry options:**
- npm private packages (paid)
- GitHub Packages
- Verdaccio (self-hosted)

### Composer Private Packages

**composer.json:**
```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/myorg/private-package.git"
    }
  ],
  "require": {
    "myorg/private-package": "^1.0"
  }
}
```

**Options:**
- Private GitHub/GitLab repos
- Satis (self-hosted Packagist)
- Private Packagist (paid)

## Workspace/Monorepo

### npm Workspaces

**package.json:**
```json
{
  "workspaces": [
    "packages/*"
  ]
}
```

### Composer (Monorepo)

**composer.json:**
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "./packages/*"
    }
  ],
  "require": {
    "myorg/package-a": "@dev",
    "myorg/package-b": "@dev"
  }
}
```

## Practical Example: Building a CLI Tool

### npm/TypeScript CLI

**package.json:**
```json
{
  "name": "my-cli",
  "version": "1.0.0",
  "bin": {
    "my-cli": "./dist/cli.js"
  },
  "dependencies": {
    "commander": "^11.0.0",
    "chalk": "^5.0.0"
  }
}
```

**src/cli.ts:**
```typescript
#!/usr/bin/env node
import { Command } from 'commander';
import chalk from 'chalk';

const program = new Command();

program
  .name('my-cli')
  .description('My awesome CLI tool')
  .version('1.0.0');

program
  .command('greet <name>')
  .description('Greet someone')
  .action((name: string) => {
    console.log(chalk.green(`Hello, ${name}!`));
  });

program.parse();
```

**Install:**
```bash
npm install -g .
my-cli greet Alice
```

### Composer/PHP CLI

**composer.json:**
```json
{
  "name": "vendor/my-cli",
  "bin": ["bin/my-cli"],
  "require": {
    "php": "^8.1",
    "symfony/console": "^6.3"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

**bin/my-cli:**
```php
#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Commands\GreetCommand;

$app = new Application('my-cli', '1.0.0');
$app->add(new GreetCommand());
$app->run();
```

**src/Commands/GreetCommand.php:**
```php
<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command {
    protected function configure(): void {
        $this->setName('greet')
            ->setDescription('Greet someone')
            ->addArgument('name', InputArgument::REQUIRED, 'Name to greet');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $name = $input->getArgument('name');
        $output->writeln("<info>Hello, {$name}!</info>");
        return Command::SUCCESS;
    }
}
```

**Install:**
```bash
composer global require vendor/my-cli
my-cli greet Alice
```

## Hands-On Exercise

### Task 1: Initialize a PHP Project

Create a new PHP project with Composer and add dependencies:

```bash
# Create project directory
mkdir my-php-app && cd my-php-app

# Initialize composer.json interactively
composer init

# Add dependencies
composer require guzzlehttp/guzzle
composer require --dev phpunit/phpunit

# Configure autoloading
# Edit composer.json to add autoload section

# Generate autoloader
composer dump-autoload
```

### Task 2: Create a Simple Package

Create a simple math utilities package:

<details>
<summary>Solution</summary>

**composer.json:**
```json
{
  "name": "username/math-utils",
  "description": "Simple math utilities",
  "type": "library",
  "license": "MIT",
  "autoload": {
    "psr-4": {
      "MathUtils\\": "src/"
    }
  },
  "require": {
    "php": "^8.1"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0"
  }
}
```

**src/Calculator.php:**
```php
<?php
namespace MathUtils;

class Calculator {
    public function add(float $a, float $b): float {
        return $a + $b;
    }

    public function multiply(float $a, float $b): float {
        return $a * $b;
    }
}
```

**example.php:**
```php
<?php
require 'vendor/autoload.php';

use MathUtils\Calculator;

$calc = new Calculator();
echo $calc->add(5, 3) . PHP_EOL;      // 8
echo $calc->multiply(4, 7) . PHP_EOL; // 28
```
</details>

## Key Takeaways

1. **Composer is PHP's npm** - Nearly identical workflow and concepts
2. **composer.json = package.json** with minor syntax differences (uses `require` not `dependencies`)
3. **PSR-4 autoloading** replaces import/require statements - maps namespaces to directories
4. **Packagist.org** is PHP's npm registry (default, no configuration needed)
5. **composer.lock = package-lock.json** for reproducible builds - commit this file!
6. **Semantic versioning** works identically in both (^, ~, *, exact versions)
7. **Scripts** work similarly with minor naming differences (use `composer run script-name`)
8. **No build step** required (PHP is interpreted, not compiled)
9. **`vendor/` = `node_modules/`** - add to `.gitignore`
10. **Global packages** installed with `composer global require` (like `npm install -g`)
11. **Platform requirements** ensure PHP version/extensions via `require.platform`
12. **`composer dump-autoload`** regenerates autoloader (like rebuilding imports)

## Command Cheat Sheet

| Task | npm | Composer |
|------|-----|----------|
| **Initialize** | `npm init` | `composer init` |
| **Install all** | `npm install` | `composer install` |
| **Add package** | `npm install pkg` | `composer require pkg` |
| **Add dev package** | `npm install -D pkg` | `composer require --dev pkg` |
| **Remove package** | `npm uninstall pkg` | `composer remove pkg` |
| **Update all** | `npm update` | `composer update` |
| **Update one** | `npm update pkg` | `composer update pkg` |
| **List packages** | `npm list` | `composer show` |
| **Run script** | `npm run script` | `composer run script` |
| **Audit security** | `npm audit` | `composer audit` |
| **Clean install** | `npm ci` | `composer install` |

## Next Steps

Now that you understand package management, let's explore testing with PHPUnit.

**Next Chapter:** [07: Testing: Jest Patterns in PHPUnit](/series/php-typescript-developers/chapters/07-testing)

## Resources

- [Composer Documentation](https://getcomposer.org/doc/)
- [Packagist](https://packagist.org/)
- [PSR-4 Autoloading Standard](https://www.php-fig.org/psr/psr-4/)
- [Semantic Versioning](https://semver.org/)
- [Composer vs npm Comparison](https://getcomposer.org/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
