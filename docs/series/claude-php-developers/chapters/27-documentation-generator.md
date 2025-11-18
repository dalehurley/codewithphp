---
title: "27: Documentation Generator"
description: "Build an intelligent documentation generator that parses codebases, generates API documentation, creates user guides, and produces tutorials automatically with Claude."
series: "claude-php-developers"
chapter: 27
order: 27
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15"
  - "Understanding of PHPDoc and documentation standards"
  - "Knowledge of Markdown and static site generators"
  - "Experience with code parsing and AST"
---

![27: Documentation Generator](/images/claude-php/chapter-27-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 27</span>
</div>

# Chapter 27: Documentation Generator

## Overview

Great documentation is essential but often neglected due to the time and effort required. In this chapter, you'll build an intelligent documentation generator that analyzes your codebase, understands context, and produces comprehensive documentation automatically.

Your system will parse PHP code, extract meaningful information, generate API references, create user guides, and even produce step-by-step tutorials. Claude's understanding of code and natural language makes it perfect for creating documentation that's both technically accurate and easy to understand.

## What You'll Build

By the end of this chapter, you will have created:

- **Code Parser**: AST-based parser that extracts classes, methods, properties, and functions from PHP codebases
- **API Documentation Generator**: Produces comprehensive API docs with method signatures, parameters, return types, and examples
- **User Guide Generator**: Creates getting started guides, cookbooks, advanced topics, and migration guides
- **README Generator**: Automatically generates professional README files with installation, usage, and examples
- **Tutorial Generator**: Produces step-by-step tutorials with code examples and explanations
- **Example Generator**: Creates basic, advanced, and pattern-based code examples
- **CLI Tool**: Command-line interface for generating all documentation types
- **Complete Documentation System**: Production-ready system that analyzes codebases and generates multiple documentation formats

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use and structured outputs)
- ✓ **PHPDoc knowledge** for understanding annotations
- ✓ **Markdown experience** for formatting documentation
- ✓ **Static site generator familiarity** (VitePress, MkDocs, etc.)

**Estimated Time**: 90-120 minutes

## Objectives

By completing this chapter, you will:

- **Understand** how to parse PHP code using Abstract Syntax Trees (AST) with PHP-Parser
- **Implement** code structure extraction from PHP files (classes, methods, properties, functions)
- **Create** API documentation generators that produce comprehensive technical documentation
- **Build** user guide generators for different documentation types (getting started, cookbooks, advanced topics)
- **Develop** automated README generation with project metadata extraction
- **Generate** step-by-step tutorials and code examples using Claude's natural language capabilities
- **Master** the integration of code analysis with AI-powered documentation generation

## Architecture Overview

```php
<?php
# filename: src/Documentation/DocumentationGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;

class DocumentationGenerator
{
    public function __construct(
        private Anthropic $claude,
        private CodeParser $parser,
        private TemplateEngine $templates
    ) {}

    /**
     * Generate complete documentation for a project
     */
    public function generateProjectDocs(string $projectPath, array $options = []): DocumentationProject
    {
        // Parse all PHP files
        $files = $this->parser->parseDirectory($projectPath);

        // Extract structure
        $structure = $this->extractStructure($files);

        // Generate different documentation types
        $docs = [
            'api' => $this->generateApiDocs($structure),
            'guides' => $this->generateUserGuides($structure, $options),
            'readme' => $this->generateReadme($structure),
            'examples' => $this->generateExamples($structure),
            'tutorials' => $this->generateTutorials($structure)
        ];

        return new DocumentationProject(
            structure: $structure,
            documentation: $docs,
            metadata: $this->extractMetadata($projectPath)
        );
    }

    /**
     * Generate API documentation
     */
    private function generateApiDocs(CodeStructure $structure): array
    {
        $apiDocs = [];

        foreach ($structure->classes as $class) {
            $apiDocs[$class->name] = $this->generateClassDoc($class);
        }

        return $apiDocs;
    }

    /**
     * Generate class documentation
     */
    private function generateClassDoc(ClassInfo $class): string
    {
        $prompt = $this->buildClassDocPrompt($class);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.3,
            'system' => $this->getDocumentationSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function getDocumentationSystemPrompt(): string
    {
        return <<<SYSTEM
You are a technical documentation expert specializing in PHP documentation.

Your documentation is:
- Clear and concise
- Technically accurate
- Includes practical examples
- Follows industry standards (PHPDoc, Markdown)
- Beginner-friendly but comprehensive
- Well-structured with headings and sections

When documenting:
1. Start with a clear overview
2. Explain the purpose and use cases
3. Document all parameters with types
4. Include return value documentation
5. Provide code examples
6. Note exceptions and edge cases
7. Add related links and references
8. Use proper Markdown formatting

Always generate complete, production-ready documentation.
SYSTEM;
    }

    /**
     * Extract code structure from parsed files
     */
    private function extractStructure(array $files): CodeStructure
    {
        $allClasses = [];
        $allFiles = [];

        foreach ($files as $filepath => $fileStructure) {
            $allFiles[] = $fileStructure;
            $allClasses = array_merge($allClasses, $fileStructure->classes);
        }

        return new CodeStructure(
            files: $allFiles,
            classes: $allClasses
        );
    }

    /**
     * Generate code examples from structure
     */
    private function generateExamples(CodeStructure $structure): array
    {
        $examples = [];
        $exampleGen = new ExampleGenerator($this->claude);

        foreach ($structure->classes as $class) {
            $examples[$class->name] = $exampleGen->generateClassExamples($class);
        }

        return $examples;
    }

    /**
     * Generate tutorials from structure
     */
    private function generateTutorials(CodeStructure $structure): array
    {
        $tutorialGen = new TutorialGenerator($this->claude);

        $topics = [
            'Getting Started',
            'Common Patterns',
            'Advanced Usage'
        ];

        $tutorials = [];
        foreach ($topics as $topic) {
            $tutorials[$topic] = $tutorialGen->generateTutorial($topic, $structure);
        }

        return $tutorials;
    }

    /**
     * Extract project metadata
     */
    private function extractMetadata(string $projectPath): array
    {
        $composerPath = $projectPath . '/composer.json';
        $readmePath = $projectPath . '/README.md';

        $metadata = [
            'package' => basename($projectPath),
            'description' => 'PHP Library',
            'version' => '1.0.0',
            'license' => 'MIT'
        ];

        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            $metadata['package'] = $composer['name'] ?? $metadata['package'];
            $metadata['description'] = $composer['description'] ?? $metadata['description'];
            $metadata['version'] = $composer['version'] ?? $metadata['version'];
            $metadata['license'] = is_array($composer['license'] ?? null)
                ? implode(', ', $composer['license'])
                : ($composer['license'] ?? $metadata['license']);
        }

        return $metadata;
    }

    /**
     * Build prompt for class documentation
     */
    private function buildClassDocPrompt(ClassInfo $class): string
    {
        $methods = array_filter($class->methods, fn($m) => $m->visibility === 'public');
        $methodList = implode(', ', array_map(fn($m) => $m->name, $methods));
        $implements = implode(', ', $class->implements);
        $extends = $class->extends ?? 'None';

        return <<<PROMPT
Generate comprehensive API documentation for this PHP class.

Class: {$class->name}
Extends: {$extends}
Implements: {$implements}
Public Methods: {$methodList}

Create detailed documentation following PHPDoc standards.
PROMPT;
    }
}
```

## Code Parser

```php
<?php
# filename: src/Documentation/CodeParser.php
declare(strict_types=1);

namespace App\Documentation;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class CodeParser
{
    private $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * Parse all PHP files in directory
     */
    public function parseDirectory(string $directory): array
    {
        $files = $this->findPhpFiles($directory);
        $parsed = [];

        foreach ($files as $file) {
            try {
                $parsed[$file] = $this->parseFile($file);
            } catch (\Exception $e) {
                error_log("Failed to parse {$file}: " . $e->getMessage());
            }
        }

        return $parsed;
    }

    /**
     * Parse a single PHP file
     */
    public function parseFile(string $filepath): FileStructure
    {
        $code = file_get_contents($filepath);
        $ast = $this->parser->parse($code);

        $visitor = new StructureVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return new FileStructure(
            path: $filepath,
            namespace: $visitor->namespace,
            classes: $visitor->classes,
            functions: $visitor->functions,
            constants: $visitor->constants,
            uses: $visitor->uses
        );
    }

    private function findPhpFiles(string $directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        $phpFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }

        return $phpFiles;
    }
}

/**
 * AST visitor to extract structure
 */
class StructureVisitor extends NodeVisitorAbstract
{
    public ?string $namespace = null;
    public array $classes = [];
    public array $functions = [];
    public array $constants = [];
    public array $uses = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name?->toString();
        }

        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->uses[] = $use->name->toString();
            }
        }

        if ($node instanceof Node\Stmt\Class_) {
            $this->classes[] = $this->extractClassInfo($node);
        }

        if ($node instanceof Node\Stmt\Function_) {
            $this->functions[] = $this->extractFunctionInfo($node);
        }

        return null;
    }

    private function extractClassInfo(Node\Stmt\Class_ $node): ClassInfo
    {
        return new ClassInfo(
            name: $node->name->toString(),
            extends: $node->extends?->toString(),
            implements: array_map(fn($i) => $i->toString(), $node->implements),
            methods: array_map(
                fn($m) => $this->extractMethodInfo($m),
                array_filter($node->stmts, fn($s) => $s instanceof Node\Stmt\ClassMethod)
            ),
            properties: array_map(
                fn($p) => $this->extractPropertyInfo($p),
                array_filter($node->stmts, fn($s) => $s instanceof Node\Stmt\Property)
            ),
            docComment: $node->getDocComment()?->getText(),
            isAbstract: $node->isAbstract(),
            isFinal: $node->isFinal()
        );
    }

    private function extractMethodInfo(Node\Stmt\ClassMethod $node): MethodInfo
    {
        return new MethodInfo(
            name: $node->name->toString(),
            parameters: array_map(
                fn($p) => $this->extractParameterInfo($p),
                $node->params
            ),
            returnType: $node->returnType?->toString(),
            visibility: $this->getVisibility($node),
            isStatic: $node->isStatic(),
            isAbstract: $node->isAbstract(),
            isFinal: $node->isFinal(),
            docComment: $node->getDocComment()?->getText()
        );
    }

    private function extractParameterInfo(Node\Param $node): ParameterInfo
    {
        return new ParameterInfo(
            name: $node->var->name,
            type: $node->type?->toString(),
            defaultValue: $node->default !== null ? $this->nodeToString($node->default) : null,
            isVariadic: $node->variadic,
            isByRef: $node->byRef
        );
    }

    private function extractPropertyInfo(Node\Stmt\Property $node): PropertyInfo
    {
        $prop = $node->props[0];

        return new PropertyInfo(
            name: $prop->name->toString(),
            type: $node->type?->toString(),
            defaultValue: $prop->default !== null ? $this->nodeToString($prop->default) : null,
            visibility: $this->getVisibility($node),
            isStatic: $node->isStatic(),
            docComment: $node->getDocComment()?->getText()
        );
    }

    private function extractFunctionInfo(Node\Stmt\Function_ $node): FunctionInfo
    {
        return new FunctionInfo(
            name: $node->name->toString(),
            parameters: array_map(
                fn($p) => $this->extractParameterInfo($p),
                $node->params
            ),
            returnType: $node->returnType?->toString(),
            docComment: $node->getDocComment()?->getText()
        );
    }

    private function getVisibility(Node $node): string
    {
        if ($node->isPublic()) return 'public';
        if ($node->isProtected()) return 'protected';
        if ($node->isPrivate()) return 'private';
        return 'public';
    }

    private function nodeToString(Node $node): string
    {
        // Simplified - use php-parser's prettyPrinter in production
        return get_class($node);
    }
}
```

## API Documentation Generator

````php
<?php
# filename: src/Documentation/ApiDocGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;

class ApiDocGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate comprehensive API documentation for a class
     */
    public function generateClassDocumentation(ClassInfo $class): string
    {
        $prompt = $this->buildClassPrompt($class);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 6144,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function buildClassPrompt(ClassInfo $class): string
    {
        $classCode = $this->formatClassSignature($class);
        $methods = $this->formatMethods($class->methods);

        return <<<PROMPT
Generate comprehensive API documentation for this PHP class.

Class:
{$classCode}
`
Methods:
{$methods}

Create documentation in Markdown format with:

1. **Class Overview** - Purpose and use cases
2. **Installation/Setup** - If applicable
3. **Constructor** - Parameters and usage
4. **Public Methods** - For each method:
   - Description
   - Parameters (with types and descriptions)
   - Return value
   - Exceptions
   - Example usage
5. **Code Examples** - Practical usage examples
6. **Related Classes** - If applicable

Use proper Markdown formatting with code blocks, tables, and sections.
PROMPT;
}

    private function formatClassSignature(ClassInfo $class): string
    {
        $signature = '';

        if ($class->isAbstract) {
            $signature .= 'abstract ';
        }
        if ($class->isFinal) {
            $signature .= 'final ';
        }

        $signature .= "class {$class->name}";

        if ($class->extends) {
            $signature .= " extends {$class->extends}";
        }

        if (!empty($class->implements)) {
            $signature .= " implements " . implode(', ', $class->implements);
        }

        return $signature;
    }

    private function formatMethods(array $methods): string
    {
        $formatted = [];

        foreach ($methods as $method) {
            if ($method->visibility !== 'public') {
                continue; // Only document public methods
            }

            $params = array_map(function($p) {
                $str = $p->type ? "{$p->type} " : '';
                $str .= "\${$p->name}";
                if ($p->defaultValue !== null) {
                    $str .= " = {$p->defaultValue}";
                }
                return $str;
            }, $method->parameters);

            $signature = $method->visibility . ' function ' . $method->name;
            $signature .= '(' . implode(', ', $params) . ')';

            if ($method->returnType) {
                $signature .= ": {$method->returnType}";
            }

            $formatted[] = $signature;

            if ($method->docComment) {
                $formatted[] = "DocBlock: {$method->docComment}";
            }
        }

        return implode("\n\n", $formatted);
    }

    /**
     * Generate method documentation
     */
    public function generateMethodDocumentation(MethodInfo $method, string $className): string
    {
        $signature = $this->formatMethodSignature($method);
        $docBlock = $method->docComment ?? 'No documentation available';

        $prompt = <<<PROMPT

Generate detailed documentation for this PHP method.

Class: {$className}
Method: {$signature}
Existing DocBlock: {$docBlock}

Create comprehensive method documentation including:

1. Clear description of what the method does
2. Parameter descriptions (name, type, purpose)
3. Return value description
4. Possible exceptions
5. Usage example
6. Notes about edge cases or important behaviors

Format in Markdown.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function formatMethodSignature(MethodInfo $method): string
    {
        $params = array_map(function($p) {
            $str = $p->type ? "{$p->type} " : '';
            $str .= "\${$p->name}";
            if ($p->defaultValue !== null) {
                $str .= " = {$p->defaultValue}";
            }
            return $str;
        }, $method->parameters);

        $signature = "{$method->visibility} function {$method->name}";
        $signature .= '(' . implode(', ', $params) . ')';

        if ($method->returnType) {
            $signature .= ": {$method->returnType}";
        }

        return $signature;
    }

}

````

## User Guide Generator

```php
<?php
# filename: src/Documentation/UserGuideGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;

class UserGuideGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate user guide from code structure
     */
    public function generateGuide(CodeStructure $structure, string $guideType = 'getting-started'): string
    {
        $prompt = match($guideType) {
            'getting-started' => $this->buildGettingStartedPrompt($structure),
            'advanced' => $this->buildAdvancedGuidePrompt($structure),
            'cookbook' => $this->buildCookbookPrompt($structure),
            'migration' => $this->buildMigrationGuidePrompt($structure),
            default => throw new \InvalidArgumentException("Unknown guide type: {$guideType}")
        };

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 8192,
            'temperature' => 0.4,
            'system' => $this->getUserGuideSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function buildGettingStartedPrompt(CodeStructure $structure): string
    {
        $mainClasses = array_slice($structure->classes, 0, 5);
        $classNames = array_map(fn($c) => $c->name, $mainClasses);

        return <<<PROMPT
Create a comprehensive "Getting Started" guide for this PHP library.

Main classes:
{$this->formatClassList($mainClasses)}

The guide should include:

1. **Introduction**
   - What this library does
   - Key features
   - When to use it

2. **Installation**
   - Composer installation
   - Requirements
   - Configuration

3. **Quick Start**
   - Simplest possible example
   - Common use case example
   - Expected output

4. **Basic Concepts**
   - Core concepts explained
   - Architecture overview
   - Important terminology

5. **Your First Application**
   - Step-by-step tutorial
   - Complete working example
   - Explanation of each step

6. **Next Steps**
   - Links to other guides
   - Common patterns
   - Resources

Use Markdown format with code examples in PHP.
PROMPT;
    }

    private function buildCookbookPrompt(CodeStructure $structure): string
    {
        return <<<PROMPT
Create a "Cookbook" with practical recipes for common tasks using this library.

Classes available:
{$this->formatClassList($structure->classes)}

Create 8-10 recipes, each with:

1. **Recipe Title** - Clear, task-oriented
2. **Problem** - What the user wants to accomplish
3. **Solution** - Complete code example
4. **Explanation** - How it works
5. **Variations** - Alternative approaches
6. **See Also** - Related recipes

Examples of recipes:
- "How to handle errors gracefully"
- "Processing large datasets efficiently"
- "Implementing caching"
- "Testing your code"
- "Deployment best practices"

Format in Markdown with clear sections.
PROMPT;
    }

    private function buildAdvancedGuidePrompt(CodeStructure $structure): string
    {
        return <<<PROMPT
Create an "Advanced Topics" guide for experienced developers.

Library structure:
{$this->formatClassList($structure->classes)}

Cover advanced topics:

1. **Architecture Deep Dive**
   - Design patterns used
   - Internal structure
   - Extension points

2. **Performance Optimization**
   - Benchmarking
   - Caching strategies
   - Memory management

3. **Advanced Features**
   - Power user features
   - Configuration options
   - Customization

4. **Integration Patterns**
   - Framework integration
   - Microservices
   - Event-driven architectures

5. **Extending the Library**
   - Custom implementations
   - Plugin development
   - Contributing

6. **Production Deployment**
   - Scaling considerations
   - Monitoring
   - Troubleshooting

Use technical depth with code examples.
PROMPT;
    }

    private function buildMigrationGuidePrompt(CodeStructure $structure): string
    {
        return <<<PROMPT
Create a migration guide for upgrading to a new version or migrating from another library.

Library structure:
{$this->formatClassList($structure->classes)}

The migration guide should include:

1. **Overview**
   - What changed
   - Why migrate
   - Benefits of upgrading

2. **Breaking Changes**
   - List of breaking changes
   - Impact assessment
   - Migration steps for each

3. **Deprecated Features**
   - Features being removed
   - Alternatives available
   - Timeline for removal

4. **New Features**
   - What's new
   - How to use new features
   - Examples

5. **Step-by-Step Migration**
   - Pre-migration checklist
   - Migration process
   - Testing after migration
   - Rollback plan

6. **Code Examples**
   - Before/after comparisons
   - Common migration patterns
   - Edge cases

7. **Troubleshooting**
   - Common issues
   - Solutions
   - Getting help

Format in Markdown with clear before/after code examples.
PROMPT;
    }

    private function formatClassList(array $classes): string
    {
        $formatted = [];
        foreach ($classes as $class) {
            $formatted[] = "- {$class->name}";
            if (!empty($class->methods)) {
                $publicMethods = array_filter($class->methods, fn($m) => $m->visibility === 'public');
                $methodNames = array_slice(array_map(fn($m) => $m->name, $publicMethods), 0, 3);
                $formatted[] = "  Methods: " . implode(', ', $methodNames);
            }
        }
        return implode("\n", $formatted);
    }

    private function getUserGuideSystemPrompt(): string
    {
        return <<<SYSTEM
You are a technical writer specializing in developer documentation.

Your guides are:
- Beginner-friendly yet comprehensive
- Task-oriented and practical
- Full of working code examples
- Well-structured with clear progression
- Engaging and easy to follow

When writing:
1. Start with the simplest concepts
2. Build complexity gradually
3. Include complete, runnable examples
4. Explain WHY, not just HOW
5. Use analogies when helpful
6. Add tips, warnings, and best practices
7. Link related concepts
8. Test examples mentally for accuracy

Always produce publication-ready documentation.
SYSTEM;
    }
}
````

## README Generator

````php
<?php
# filename: src/Documentation/ReadmeGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;

class ReadmeGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate comprehensive README.md
     */
    public function generateReadme(
        CodeStructure $structure,
        array $metadata = []
    ): string {
        $projectInfo = $this->analyzeProject($structure);

        $prompt = <<<PROMPT
Generate a comprehensive README.md for this PHP project.

Project Analysis:
- Main classes: {$this->listClasses($structure->classes)}
- Package name: {$metadata['package'] ?? 'Unknown'}
- Description: {$metadata['description'] ?? 'PHP Library'}

Create a README with:

## Project Title
- Clear, descriptive title
- Badges (build, coverage, version, license)
- One-line description

## Features
- Bullet list of key features
- What makes this special

## Requirements
- PHP version
- Extensions needed
- Dependencies

## Installation
composer require ...
`
## Quick Start

// Simplest possible example

## Usage

### Basic Usage

- Common use cases with examples

### Advanced Usage

- More complex scenarios

## Configuration

- How to configure
- Available options

## Examples

- Practical examples
- Real-world scenarios

## API Documentation

- Link to full docs
- Quick reference

## Testing

./vendor/bin/phpunit

## Contributing

- How to contribute
- Development setup
- Guidelines

## License

- License type

## Credits

- Authors
- Contributors

Make it engaging, professional, and complete.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.4,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function analyzeProject(CodeStructure $structure): array
    {
        // Analyze project to extract key info
        return [
            'class_count' => count($structure->classes),
            'main_classes' => array_slice($structure->classes, 0, 3),
            'has_tests' => $this->hasTests($structure),
            'namespace' => $this->detectNamespace($structure)
        ];
    }

    private function listClasses(array $classes): string
    {
        return implode(', ', array_map(fn($c) => $c->name, array_slice($classes, 0, 5)));
    }

    private function hasTests(CodeStructure $structure): bool
    {
        foreach ($structure->classes as $class) {
            if (str_contains($class->name, 'Test')) {
                return true;
            }
        }
        return false;
    }

    private function detectNamespace(CodeStructure $structure): ?string
    {
        foreach ($structure->files as $file) {
            if ($file->namespace) {
                return explode('\\', $file->namespace)[0];
            }
        }
        return null;
    }

}

````

## Example Generator

```php
<?php
# filename: src/Documentation/ExampleGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;

class ExampleGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate code examples for a class
     */
    public function generateClassExamples(ClassInfo $class): array
    {
        $examples = [];

        // Generate basic usage example
        $examples['basic'] = $this->generateBasicExample($class);

        // Generate advanced example
        $examples['advanced'] = $this->generateAdvancedExample($class);

        // Generate common patterns
        $examples['patterns'] = $this->generatePatternExamples($class);

        return $examples;
    }

    private function generateBasicExample(ClassInfo $class): string
    {
        $publicMethods = array_filter($class->methods, fn($m) => $m->visibility === 'public');
        $methodNames = implode(', ', array_slice(array_map(fn($m) => $m->name, $publicMethods), 0, 3));

        $prompt = <<<PROMPT
Generate a basic usage example for this PHP class.

Class: {$class->name}
Public Methods: {$methodNames}

Create a simple, beginner-friendly example showing:
1. How to instantiate the class
2. Basic method calls
3. Expected output
4. Brief explanation

Format as Markdown with PHP code blocks.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function generateAdvancedExample(ClassInfo $class): string
    {
        $prompt = <<<PROMPT
Generate an advanced usage example for this PHP class: {$class->name}

Show:
1. Complex use case
2. Integration with other classes
3. Error handling
4. Best practices
5. Performance considerations

Format as Markdown with complete, runnable code.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 3072,
            'temperature' => 0.4,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

    private function generatePatternExamples(ClassInfo $class): string
    {
        $prompt = <<<PROMPT
Generate common usage patterns and recipes for this PHP class: {$class->name}

Include 3-5 common patterns:
1. Pattern name and description
2. When to use it
3. Complete code example
4. Explanation
5. Variations

Format as Markdown with clear sections.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.4,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }
}
````

## Tutorial Generator

````php
<?php
# filename: src/Documentation/TutorialGenerator.php
declare(strict_types=1);

namespace App\Documentation;

use ClaudePhp\ClaudePhp;

class TutorialGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate step-by-step tutorial
     */
    public function generateTutorial(
        string $topic,
        CodeStructure $structure,
        string $difficulty = 'beginner'
    ): string {
        $prompt = <<<PROMPT
Create a comprehensive step-by-step tutorial: "{$topic}"

Difficulty level: {$difficulty}

Available classes/features:
{$this->formatStructure($structure)}

Tutorial format:

# Tutorial: {$topic}

## Introduction
- What you'll build
- What you'll learn
- Prerequisites

## Step 1: [First Step]
**Goal:** What this step accomplishes

// Complete code for this step
`
**Explanation:**

- What this code does
- Why we do it this way
- Key concepts introduced

## Step 2: [Second Step]

... continue ...

## Final Code

// Complete, working final version

## Testing

How to test what you built

## Next Steps

- What to explore next
- Related tutorials
- Advanced topics

## Troubleshooting

Common issues and solutions

Create an engaging, educational tutorial with:

- Clear learning progression
- Complete, runnable code at each step
- Explanations of concepts
- Best practices
- Real-world context
  PROMPT;

          $response = $this->claude->messages()->create([
              'model' => 'claude-sonnet-4-20250514',
              'max_tokens' => 8192,
              'temperature' => 0.5,
              'messages' => [[
                  'role' => 'user',
                  'content' => $prompt
              ]]
          ]);

          return $response->content[0]['text'];
      }

      private function formatStructure(CodeStructure $structure): string
      {
          $output = [];
          foreach ($structure->classes as $class) {
              $output[] = "- {$class->name}";
              $methods = array_filter($class->methods, fn($m) => $m->visibility === 'public');
              foreach (array_slice($methods, 0, 3) as $method) {
                  $output[] = "  - {$method->name}()";
              }
          }
          return implode("\n", $output);
      }

      /**
       * Generate interactive example
       */
      public function generateInteractiveExample(
          string $feature,
          ClassInfo $class
      ): string {
          $prompt = <<<PROMPT

  Create an interactive code example demonstrating: {$feature}

Using class: {$class->name}

Generate:

1. **Introduction**
   What this example demonstrates

2. **The Code**

<?php
// Complete, runnable example
// Include comments explaining each part

3. **How It Works**
   Step-by-step breakdown

4. **Try It Yourself**
   Challenges or modifications to try:

   - Easy variation
   - Medium challenge
   - Advanced modification

5. **Expected Output**
   What should happen when run

6. **Common Mistakes**
   What beginners often get wrong

Make it hands-on and educational.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 3072,
            'temperature' => 0.4,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]['text'];
    }

}

````

## Complete Documentation Generator CLI

```php
<?php
# filename: bin/generate-docs.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Documentation\DocumentationGenerator;
use App\Documentation\CodeParser;
use App\Documentation\ApiDocGenerator;
use App\Documentation\UserGuideGenerator;
use App\Documentation\ReadmeGenerator;
use App\Documentation\TutorialGenerator;

// Parse command line arguments
$options = getopt('', [
    'source:',
    'output:',
    'type:',
    'help'
]);

if (isset($options['help']) || !isset($options['source'])) {
    echo <<<HELP
Documentation Generator

Usage:
  php bin/generate-docs.php --source=./src --output=./docs [--type=all]

Options:
  --source     Source directory to parse (required)
  --output     Output directory for docs (default: ./docs)
  --type       Documentation type: api, guides, readme, tutorials, all (default: all)
  --help       Show this help

Examples:
  php bin/generate-docs.php --source=./src --output=./docs
  php bin/generate-docs.php --source=./src --type=api
  php bin/generate-docs.php --source=./src --type=readme

HELP;
    exit(0);
}

$sourcePath = $options['source'];
$outputPath = $options['output'] ?? './docs';
$type = $options['type'] ?? 'all';

if (!is_dir($sourcePath)) {
    echo "Error: Source directory not found: {$sourcePath}\n";
    exit(1);
}

// Create output directory
if (!is_dir($outputPath)) {
    mkdir($outputPath, 0755, true);
}

// Initialize Claude
$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

// Initialize components
$parser = new CodeParser();
$apiDocGen = new ApiDocGenerator($claude);
$guideGen = new UserGuideGenerator($claude);
$readmeGen = new ReadmeGenerator($claude);
$tutorialGen = new TutorialGenerator($claude);

echo "🔍 Parsing code from {$sourcePath}...\n";
$files = $parser->parseDirectory($sourcePath);

echo "📝 Found " . count($files) . " PHP files\n\n";

// Extract structure
$classes = [];
foreach ($files as $file) {
    $classes = array_merge($classes, $file->classes);
}

$structure = new \App\Documentation\CodeStructure(
    files: $files,
    classes: $classes
);

// Generate documentation based on type
if ($type === 'all' || $type === 'readme') {
    echo "📄 Generating README.md...\n";
    $readme = $readmeGen->generateReadme($structure, [
        'package' => basename(dirname($sourcePath)),
        'description' => 'PHP Library'
    ]);
    file_put_contents("{$outputPath}/README.md", $readme);
    echo "✅ Created {$outputPath}/README.md\n\n";
}

if ($type === 'all' || $type === 'api') {
    echo "📚 Generating API documentation...\n";
    $apiPath = "{$outputPath}/api";
    if (!is_dir($apiPath)) {
        mkdir($apiPath, 0755, true);
    }

    foreach ($classes as $class) {
        echo "  - Documenting {$class->name}...\n";
        $doc = $apiDocGen->generateClassDocumentation($class);
        $filename = strtolower(str_replace('\\', '-', $class->name)) . '.md';
        file_put_contents("{$apiPath}/{$filename}", $doc);
    }
    echo "✅ Created API docs for " . count($classes) . " classes\n\n";
}

if ($type === 'all' || $type === 'guides') {
    echo "📖 Generating user guides...\n";
    $guidePath = "{$outputPath}/guides";
    if (!is_dir($guidePath)) {
        mkdir($guidePath, 0755, true);
    }

    $guides = [
        'getting-started' => 'Getting Started',
        'cookbook' => 'Cookbook',
        'advanced' => 'Advanced Topics'
    ];

    foreach ($guides as $type => $title) {
        echo "  - Generating {$title}...\n";
        $guide = $guideGen->generateGuide($structure, $type);
        file_put_contents("{$guidePath}/{$type}.md", $guide);
    }
    echo "✅ Created " . count($guides) . " user guides\n\n";
}

if ($type === 'all' || $type === 'tutorials') {
    echo "🎓 Generating tutorials...\n";
    $tutorialPath = "{$outputPath}/tutorials";
    if (!is_dir($tutorialPath)) {
        mkdir($tutorialPath, 0755, true);
    }

    $topics = [
        'Quick Start Tutorial',
        'Building Your First Application',
        'Advanced Patterns and Techniques'
    ];

    foreach ($topics as $topic) {
        echo "  - Generating: {$topic}...\n";
        $tutorial = $tutorialGen->generateTutorial($topic, $structure);
        $filename = strtolower(str_replace(' ', '-', $topic)) . '.md';
        file_put_contents("{$tutorialPath}/{$filename}", $tutorial);
    }
    echo "✅ Created " . count($topics) . " tutorials\n\n";
}

echo "🎉 Documentation generation complete!\n";
echo "📁 Output directory: {$outputPath}\n";
````

## Best Practices

### Performance Optimization

**1. Batch Processing for Large Codebases**

Instead of generating docs for each class individually, batch multiple classes in a single API request:

```php
// Instead of this (slower):
foreach ($classes as $class) {
    $doc = $apiDocGen->generateClassDocumentation($class);
    // Save individually
}

// Do this (faster):
$batchSize = 5;
for ($i = 0; $i < count($classes); $i += $batchSize) {
    $batch = array_slice($classes, $i, $batchSize);
    $docs = $this->generateBatchDocs($batch);
    // Save batch
}
```

**2. Caching Generated Documentation**

Cache generated documentation to avoid regenerating unchanged code:

```php
private function generateClassDoc(ClassInfo $class): string
{
    $cacheKey = "doc_{$class->name}_" . md5(json_encode($class));

    // Check cache first
    if ($cached = $this->cache->get($cacheKey)) {
        return $cached;
    }

    // Generate if not cached
    $doc = $this->callClaude($class);

    // Cache for future use
    $this->cache->set($cacheKey, $doc, 3600); // 1 hour TTL

    return $doc;
}
```

**3. Streaming for Real-Time Feedback**

Use streaming for immediate feedback on long documentation generation:

```php
$response = $this->claude->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'stream' => true,  // Enable streaming
    'messages' => [[
        'role' => 'user',
        'content' => $prompt
    ]]
]);

foreach ($response as $chunk) {
    echo $chunk->delta->text ?? '';
    flush(); // Send immediately
}
```

### Cost Optimization

**1. Model Selection**

Choose the right model for each documentation type:

```php
// Use Haiku for simple class documentation
if (count($class->methods) < 5) {
    $model = 'claude-haiku-4-20250514'; // Faster, cheaper
} else {
    $model = 'claude-sonnet-4-20250514'; // More capable
}
```

**2. Token Limiting**

Set appropriate `max_tokens` based on documentation type:

```php
// README - typically shorter
'max_tokens' => 4096,

// API documentation - more detailed
'max_tokens' => 6144,

// Tutorial - comprehensive
'max_tokens' => 8192,
```

**3. Prompt Caching**

Use prompt caching for frequently referenced context:

```php
$systemPrompt = $this->getDocumentationSystemPrompt();
$cachedSystemPrompt = [
    'type' => 'text',
    'text' => $systemPrompt,
    'cache_control' => ['type' => 'ephemeral']
];

$response = $this->claude->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'system' => [$cachedSystemPrompt],
    'messages' => [[
        'role' => 'user',
        'content' => $prompt
    ]]
]);
```

### Quality Assurance

**1. Validate Generated Code Examples**

Always validate code examples are runnable:

```php
private function validateCodeExample(string $code): bool
{
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmpFile, "<?php\n" . $code);

    $output = shell_exec("php -l {$tmpFile} 2>&1");
    unlink($tmpFile);

    return strpos($output, 'No syntax errors') !== false;
}
```

**2. Check Documentation Completeness**

Verify all public methods are documented:

```php
private function checkCompleteness(ClassInfo $class, string $docs): array
{
    $publicMethods = array_filter($class->methods, fn($m) => $m->visibility === 'public');
    $missing = [];

    foreach ($publicMethods as $method) {
        if (stripos($docs, $method->name) === false) {
            $missing[] = $method->name;
        }
    }

    return $missing;
}
```

**3. Version Control for Documentation**

Track documentation changes:

```php
if (file_exists($outputPath)) {
    $previous = file_get_contents($outputPath);
    if ($previous !== $newDoc) {
        // Log changes
        error_log("Documentation changed for {$class->name}");
    }
}
```

### Security Considerations

**1. Sanitize Generated Content**

Sanitize AI-generated documentation before output:

```php
$sanitized = htmlspecialchars($generatedDoc, ENT_QUOTES, 'UTF-8');
$sanitized = strip_tags($sanitized, '<p><code><pre><em><strong><h1><h2><h3><a>');
```

**2. Protect API Keys**

Never hardcode API keys:

```php
// ✗ Bad
$apiKey = 'sk-ant-...';

// ✓ Good
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    throw new Exception('ANTHROPIC_API_KEY environment variable not set');
}
```

**3. Validate Input Paths**

Prevent directory traversal attacks:

```php
$realPath = realpath($sourcePath);
$projectRoot = realpath(dirname(__FILE__) . '/../..');

if (strpos($realPath, $projectRoot) !== 0) {
    throw new Exception('Invalid source path');
}
```

## Data Structures

```php
<?php
# filename: src/Documentation/DataStructures.php
declare(strict_types=1);

namespace App\Documentation;

readonly class FileStructure
{
    public function __construct(
        public string $path,
        public ?string $namespace,
        public array $classes,
        public array $functions,
        public array $constants,
        public array $uses
    ) {}
}

readonly class CodeStructure
{
    public function __construct(
        public array $files,
        public array $classes
    ) {}
}

readonly class ClassInfo
{
    public function __construct(
        public string $name,
        public ?string $extends,
        public array $implements,
        public array $methods,
        public array $properties,
        public ?string $docComment,
        public bool $isAbstract,
        public bool $isFinal
    ) {}
}

readonly class MethodInfo
{
    public function __construct(
        public string $name,
        public array $parameters,
        public ?string $returnType,
        public string $visibility,
        public bool $isStatic,
        public bool $isAbstract,
        public bool $isFinal,
        public ?string $docComment
    ) {}
}

readonly class ParameterInfo
{
    public function __construct(
        public string $name,
        public ?string $type,
        public ?string $defaultValue,
        public bool $isVariadic,
        public bool $isByRef
    ) {}
}

readonly class PropertyInfo
{
    public function __construct(
        public string $name,
        public ?string $type,
        public ?string $defaultValue,
        public string $visibility,
        public bool $isStatic,
        public ?string $docComment
    ) {}
}

readonly class FunctionInfo
{
    public function __construct(
        public string $name,
        public array $parameters,
        public ?string $returnType,
        public ?string $docComment
    ) {}
}

readonly class DocumentationProject
{
    public function __construct(
        public CodeStructure $structure,
        public array $documentation,
        public array $metadata
    ) {}
}
```

## Exercises

### Exercise 1: Custom Documentation Template

**Goal**: Create a custom template system for documentation generation

Create a `TemplateEngine` class that:

- Accepts template files with placeholders (e.g., `\{\{class_name\}\}`, `\{\{methods\}\}`)
- Supports template inheritance and includes
- Allows custom formatting for different output formats (Markdown, HTML, PDF)
- Integrates with the `DocumentationGenerator` class

**Validation**: Generate documentation using your custom templates and verify the output matches your template structure.

```php
# filename: src/Documentation/TemplateEngine.php
<?php
declare(strict_types=1);

namespace App\Documentation;

class TemplateEngine
{
    // Your implementation here
}
```

### Exercise 2: Documentation Quality Scoring

**Goal**: Build a system to evaluate and score generated documentation quality

Implement a `DocumentationQualityScorer` that:

- Analyzes documentation completeness (coverage of methods, parameters, examples)
- Checks for clarity and readability
- Validates code examples are runnable
- Provides improvement suggestions
- Generates quality reports

**Validation**: Score existing documentation and verify the metrics are accurate.

### Exercise 3: Incremental Documentation Updates

**Goal**: Create a system that only regenerates changed documentation

Build an `IncrementalDocGenerator` that:

- Tracks file modification times
- Compares current code structure with previous version
- Only regenerates docs for changed classes/methods
- Maintains a cache of generated documentation
- Supports diff view of documentation changes

**Validation**: Make changes to a class, regenerate docs, and verify only changed classes are updated.

## Troubleshooting

### Error: "Failed to parse file"

**Symptom**: `Failed to parse {filepath}: Syntax error`

**Cause**: The PHP file contains syntax errors or uses features not supported by PHP-Parser

**Solution**:

1. Validate PHP syntax first:

```bash
php -l filename.php
```

2. Update PHP-Parser version if using newer PHP features:

```bash
composer require nikic/php-parser:^5.0
```

3. Add error handling to skip problematic files:

```php
try {
    $parsed[$file] = $this->parseFile($file);
} catch (\Exception $e) {
    error_log("Skipping {$file}: " . $e->getMessage());
    continue;
}
```

### Problem: Generated Documentation is Too Generic

**Symptom**: Claude generates generic documentation that doesn't reflect your specific codebase

**Cause**: Insufficient context provided in prompts

**Solution**:

1. Include more code context in prompts:

````php
$prompt = <<<PROMPT
Generate documentation for this class:

Full source code:
{$fullSourceCode}
`
Existing PHPDoc comments:
{$existingDocComments}

Create documentation that reflects the actual implementation.
PROMPT;

````

2. Use more specific system prompts tailored to your domain
3. Provide examples of desired documentation style

### Problem: Documentation Generation is Slow

**Symptom**: Generating docs for large codebases takes too long

**Cause**: Processing all classes sequentially, making individual API calls

**Solution**:

1. Batch multiple classes in a single request:
```php
$prompt = "Generate docs for these classes:\n";
foreach ($classes as $class) {
    $prompt .= $this->formatClass($class) . "\n\n";
}
````

2. Use parallel processing:

```php
$promises = [];
foreach ($classes as $class) {
    $promises[] = $this->claude->messages()->createAsync([...]);
}
$results = Promise\all($promises)->wait();
```

3. Cache generated documentation and only regenerate when code changes

## Wrap-up

Congratulations! You've built a comprehensive documentation generation system. Here's what you've accomplished:

- ✓ **Code Parsing**: Implemented AST-based code parsing with PHP-Parser to extract structural information
- ✓ **API Documentation**: Created a generator that produces comprehensive API docs with examples
- ✓ **User Guides**: Built generators for getting started guides, cookbooks, and advanced topics
- ✓ **README Generation**: Automated README creation with project analysis
- ✓ **Tutorial Generation**: Created a system that generates step-by-step tutorials
- ✓ **Example Generation**: Implemented basic, advanced, and pattern-based example generation
- ✓ **CLI Tool**: Built a command-line interface for easy documentation generation
- ✓ **Metadata Extraction**: Automatically extracts project information from composer.json

**Key Concepts Learned**:

- Code parsing with Abstract Syntax Trees (AST) provides accurate structural analysis
- Claude's natural language understanding excels at generating educational documentation
- Different documentation types serve different audiences (developers, users, beginners)
- Automated documentation keeps code and docs in sync
- Template systems allow customization while maintaining consistency

**Real-World Applications**:

- Generate API documentation for libraries and frameworks
- Create user guides for applications and tools
- Maintain README files automatically
- Produce tutorials for onboarding new developers
- Keep documentation up-to-date with code changes

**Next Steps**:

- Integrate documentation generation into your CI/CD pipeline
- Add support for multiple output formats (HTML, PDF, EPUB)
- Implement documentation versioning and change tracking
- Create custom templates for your organization's style guide
- Add interactive documentation features (search, navigation)

## Further Reading

- [PHP-Parser Documentation](https://github.com/nikic/PHP-Parser) - Complete guide to parsing PHP code with AST
- [PHPDoc Standards](https://docs.phpdoc.org/) - Official PHPDoc documentation standards
- [Markdown Guide](https://www.markdownguide.org/) - Comprehensive Markdown syntax reference
- [Documentation Best Practices](https://www.writethedocs.org/guide/) - Write the Docs community resources
- [Claude API Documentation](https://docs.claude.com/en/api/overview) - Official Claude API reference
- [Chapter 11: Tool Use](/series/claude-php-developers/chapters/11-tool-use) - Learn about Claude's tool use capabilities
- [Chapter 15: Structured Outputs](/series/claude-php-developers/chapters/15-structured-outputs) - Generate structured documentation formats

## Key Takeaways

- ✓ Automated documentation saves time and ensures consistency
- ✓ Code parsing with PHP-Parser extracts accurate structural information
- ✓ Claude excels at generating clear, educational documentation
- ✓ Different documentation types serve different audiences and purposes
- ✓ API docs should be technical and comprehensive
- ✓ User guides should be task-oriented and beginner-friendly
- ✓ Tutorials should teach concepts through practical examples
- ✓ README files are crucial for first impressions
- ✓ Generated docs can be refined and customized as needed
- ✓ Regular regeneration keeps documentation in sync with code

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="27"
  label="You've built an intelligent documentation generator!"
/>

---

Continue to [Chapter 28: Customer Support Bot](/series/claude-php-developers/chapters/28-customer-support-bot) to build an AI-powered support system.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 27 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-27)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-27
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php bin/generate-docs.php --source=./src --output=./docs
```
