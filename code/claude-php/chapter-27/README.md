# Chapter 27: Documentation Generator

AI-powered documentation generator that automatically creates comprehensive documentation from PHP code, including API docs, README files, and changelogs.

## Features

- Parse PHP code structure
- Generate class documentation
- Create API endpoint docs
- Generate README files
- Create changelogs
- Improve existing docs
- Markdown output
- Table of contents generation

## Installation

```bash
composer install
cp .env.example .env
```

## Usage

### Parse and Document a Class

```php
$parser = new CodeParser();
$generator = new ApiDocsGenerator($apiKey);
$markdown = new MarkdownGenerator();

// Parse file
$classInfo = $parser->parseFile('src/MyClass.php');

// Generate AI documentation
$aiDocs = $generator->generateClassDocs($classInfo['classes'][0]);

// Create markdown
$md = $markdown->generateClassMarkdown($classInfo['classes'][0], $aiDocs);

file_put_contents('docs/MyClass.md', $md);
```

### Generate README

```php
$structure = $parser->parseDirectory('src/');
$readme = $generator->generateReadme($structure);
file_put_contents('README.md', $readme);
```

### Generate API Docs

```php
$docs = $generator->generateEndpointDocs(
    'POST',
    '/api/users',
    ['class' => 'UserController', 'method' => 'store']
);
```

## Command Line Tool

```bash
php bin/generate-docs src/ --output=docs/
php bin/generate-readme src/ --output=README.md
```

## Output Examples

Generated documentation includes:
- Class overviews
- Method descriptions
- Parameter documentation
- Return value documentation
- Usage examples
- Code samples
- Best practices

## Next Steps

- Support bot (Chapter 28)
- Content moderation (Chapter 29)
## 📚 Resources

- **[Official Anthropic Documentation](https://docs.anthropic.com/)** — Complete API reference
- **[Official PHP SDK on GitHub](https://github.com/anthropics/anthropic-sdk-php)** — Anthropic's official PHP implementation
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples
- **[PHP SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package
- **[Community Discord](https://discord.gg/anthropic)** — Get help and discuss
