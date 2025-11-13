# PHP for TypeScript Developers - Code Examples

Complete code examples for the "PHP for TypeScript Developers" series.

## 📚 Series Overview

This directory contains hands-on code examples for TypeScript developers learning PHP. Each chapter folder includes working examples with setup instructions.

## 🚀 Quick Start

```bash
# Navigate to any chapter
cd chapter-01

# Install dependencies (if composer.json exists)
composer install

# Run examples
php example-file.php
```

## 📂 Chapter Structure

### Phase 1: Foundations (Chapters 01-05)

**✅ Chapter 01:** Type Systems Compared
- Basic type annotations
- Nullable and union types
- Type safety demonstrations

**✅ Chapter 02:** Modern PHP Syntax
- Arrow functions (`fn`)
- Match expressions
- Spread operators
- Named arguments

**✅ Chapter 03:** Functions & Closures
- Closure variable capture
- Higher-order functions
- Generators and lazy evaluation

**✅ Chapter 04:** OOP - Classes, Interfaces & Generics
- Traits for code reuse
- Abstract classes and interfaces
- Property promotion
- Magic methods

**✅ Chapter 05:** Error Handling
- Custom exceptions
- Exception chaining
- Result type pattern
- Validation examples

### Phase 2: Ecosystem (Chapters 06-10)

**✅ Chapter 06:** Package Management (npm vs Composer)
- composer.json examples
- PSR-4 autoloading
- Creating packages
- CLI tool examples

**✅ Chapter 07:** Testing (Jest vs PHPUnit)
- PHPUnit setup and configuration
- Unit testing examples
- Mocking and assertions
- Data providers
- Integration tests

**✅ Chapter 08:** Code Quality
- PHP_CodeSniffer configuration
- PHPStan/Psalm examples
- PHP-CS-Fixer setup
- Quality check scripts

**✅ Chapter 09:** Build Tools
- Asset bundling with Vite
- PHAR creation
- OPcache configuration
- Docker examples

**✅ Chapter 10:** Debugging
- Xdebug configuration
- VS Code launch.json examples
- Debugging scenarios
- Logging examples

### Phase 3: Advanced (Chapters 11-15)

*Coming soon*

## 🎯 Prerequisites

- **PHP 8.1+** (8.2+ recommended)
- **Composer** (PHP package manager)
- **Node.js/npm** (for asset examples)
- **Xdebug** (optional, for debugging/coverage)

## 📦 Installation

### Install PHP

**macOS:**
```bash
brew install php
```

**Windows:**
```bash
choco install php
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php8.2-cli php8.2-mbstring php8.2-xml
```

### Install Composer

```bash
# Download and install
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

### Verify Setup

```bash
php -v     # Should show PHP 8.1+
composer -v  # Should show Composer version
```

## 🏃 Running Examples

### Basic PHP Scripts

```bash
# Navigate to chapter directory
cd chapter-01

# Run PHP file
php basic-types.php
```

### Projects with Dependencies

```bash
# Navigate to chapter
cd chapter-07

# Install dependencies
composer install

# Run tests
composer test

# Or run PHPUnit directly
vendor/bin/phpunit
```

### Development Server

```bash
# Start built-in server
php -S localhost:8000

# With custom document root
php -S localhost:8000 -t public/
```

## 📖 Learning Path

### Recommended Order

1. **Start with Foundations** (Chapters 01-05)
   - Understand type systems and syntax differences
   - Learn OOP patterns and error handling

2. **Explore Ecosystem** (Chapters 06-10)
   - Set up tooling and testing
   - Learn debugging techniques

3. **Master Advanced Topics** (Chapters 11-15)
   - Build real applications
   - Understand Laravel ecosystem

### Hands-On Approach

Each chapter includes:
- ✅ **Working examples** - Run immediately
- ✅ **README files** - Setup and usage instructions
- ✅ **Comments** - Explaining key concepts
- ✅ **Comparisons** - Side-by-side with TypeScript

## 🛠️ Development Tools

### Recommended VS Code Extensions

- **PHP Intelephense** - IntelliSense and code navigation
- **PHP Debug** - Xdebug integration
- **PHP CS Fixer** - Code formatting
- **PHPStan** - Static analysis

### Alternative IDEs

- **PHPStorm** - Full-featured PHP IDE (commercial)
- **Sublime Text** - Lightweight with PHP plugins
- **Vim/Neovim** - With PHP language server

## 📚 Additional Resources

### Official Documentation

- [PHP Manual](https://www.php.net/manual/en/)
- [Composer Docs](https://getcomposer.org/doc/)
- [PHPUnit Docs](https://phpunit.de/documentation.html)
- [PSR Standards](https://www.php-fig.org/psr/)

### Community

- [PHP Reddit](https://www.reddit.com/r/PHP/)
- [PHP Discord](https://discord.gg/php)
- [Packagist](https://packagist.org/) - Package repository

### Learning Resources

- [PHP: The Right Way](https://phptherightway.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Symfony Documentation](https://symfony.com/doc)

## 🤝 Contributing

Found an issue or want to improve examples?

1. Fork the repository
2. Create your feature branch
3. Make your changes
4. Submit a pull request

## 📝 License

These examples are part of the Code with PHP educational series.

## 💬 Questions?

- Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
- Check the main documentation for detailed explanations

---

**Happy Learning!** 🚀

*From TypeScript to PHP - You've got this!*
