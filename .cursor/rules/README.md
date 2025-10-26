# Cursor Rules for Code with PHP

This directory contains Cursor Rules (`.mdc` files) that guide AI-assisted development and content creation for the Code with PHP tutorial platform.

## Overview

Cursor Rules are markdown files with special frontmatter that tell the AI:

- When to apply the rule (always, for specific files, or on request)
- What patterns and conventions to follow
- How to structure content and code

## Rule Files

### Core Documentation Rules

#### `authoring-guidelines.mdc`

**Type:** Applied to all markdown files in `docs/**/*.md`  
**Purpose:** Comprehensive chapter authoring guidelines

Defines:

- Frontmatter requirements for chapters
- Chapter structure template (Overview → Prerequisites → Steps → Exercises → Wrap-up)
- Code block conventions and formatting
- Step structure format (Goal → Actions → Expected Result → Why It Works → Troubleshooting)
- Exercise and troubleshooting patterns
- Writing style and tone guidelines
- Validation checklist

**Use when:** Writing or editing tutorial chapters

---

#### `php-basics-patterns.mdc`

**Type:** Reference (fetch manually)  
**Purpose:** Specific patterns extracted from the completed php-basics series

Defines:

- Chapter progression philosophy (foundations → OOP → professional tools → projects → frameworks)
- Step structure pattern with exact format
- Code example patterns (PHP scripts, commands, output)
- "What You'll Build" pattern
- Exercise patterns (basic vs challenge)
- Troubleshooting section patterns
- Mermaid diagram patterns
- Quick Start pattern
- VitePress component usage
- Modern vs traditional code comparison pattern
- Code directory structure
- Encouragement and tone examples

**Use when:** Creating new series or chapters that should match php-basics quality

---

#### `tutorials-global.mdc`

**Type:** Applied to all markdown files in `docs/**/*.md`  
**Purpose:** Global tutorial writing standards

Defines:

- Role (senior educator-engineer)
- Audience (beginner to advanced developers)
- Primary goals (build real things, minimize confusion, teach theory through practice)
- Mandatory structure for all tutorials
- Tone and voice guidelines
- Formatting and conventions
- PHP 8.4 standards
- Reproducibility rules
- Safety and quality standards
- Comprehensive validation checklist

**Use when:** Writing any tutorial content

---

#### `tutorials-task-template.mdc`

**Type:** Reference (fetch manually)  
**Purpose:** Template for creating new tutorial chapters

Provides:

- Pre-writing checklist
- Metadata template with examples
- Complete chapter structure template (copy-paste ready)
- Planning checklist
- Writing tips (do's and don'ts)
- Code directory setup instructions
- README template for code directories
- Final review checklist
- Common patterns to follow
- Encouragement and transition phrases

**Use when:** Starting a new chapter from scratch

---

### Project Configuration Rules

#### `linking-and-sources.mdc`

**Type:** Reference (fetch manually)  
**Purpose:** Comprehensive linking conventions and attribution standards

Defines:

- Internal linking (absolute paths from doc root)
- Code sample linking (relative paths from chapters)
- External linking (descriptive anchors with em dashes)
- Cursor rule references (MDC format)
- GitHub edit links configuration
- Code directory structure and README templates
- License and attribution requirements
- Quick reference table for all link types

**Use when:** Adding links, referencing code samples, or setting up new series

---

#### `php-version.mdc`

**Type:** Always applied  
**Purpose:** PHP version requirements

Defines:

- PHP 8.4 as the standard version
- Code requirements for PHP 8.4 compatibility
- Documentation standards
- Modern features to use (property hooks, asymmetric visibility, etc.)

**Use when:** Writing any PHP code or documentation

---

#### `project-structure.mdc`

**Type:** Always applied  
**Purpose:** Project directory structure and organization

Defines:

- VitePress content root structure (`docs/`)
- Series organization (`series/<slug>/`)
- Code sample location (`series/<slug>/code/`)
- Configuration files
- GitHub workflow structure

**Use when:** Creating new series, organizing content, or setting up project structure

---

#### `vitepress-usage.mdc`

**Type:** Reference (fetch manually)  
**Purpose:** VitePress-specific usage and configuration

Defines:

- Development commands (dev, build, preview)
- Project structure
- Adding new series (step-by-step)
- Adding chapters
- VitePress components (callouts, code groups, mermaid)
- Frontmatter configuration
- Sidebar configuration patterns
- Link conventions
- Deployment process
- Troubleshooting

**Use when:** Working with VitePress, adding series/chapters, or configuring site

---

### Series-Specific Rules

#### `ai-ml-series.mdc`

**Type:** Applied to `docs/series/ai-ml-php-developers/**/*.md`  
**Purpose:** Guidelines specific to the AI/ML for PHP Developers series

Defines:

- Series overview and target audience
- Core technologies (PHP-ML, Rubix ML, Python integration)
- Chapter progression (Foundations → Basic ML → Advanced ML → NLP → Vision → Production)
- Content guidelines (theory, code examples, data handling)
- Code sample structure for ML projects
- Python integration patterns (CLI, REST API, message queue)
- External API usage (OpenAI, TensorFlow) with caching and error handling
- Model evaluation and testing patterns
- Terminology and conventions specific to AI/ML
- Common pitfalls to address
- File organization with links to actual code samples
- Environment setup requirements
- Deployment and scaling considerations
- Resources and references for AI/ML development

Includes direct references to code samples:

- [verify-installation.php](mdc:docs/series/ai-ml-php-developers/code/chapter-02/verify-installation.php)
- [test-phpml.php](mdc:docs/series/ai-ml-php-developers/code/chapter-02/test-phpml.php)
- [test-rubixml.php](mdc:docs/series/ai-ml-php-developers/code/chapter-02/test-rubixml.php)
- [create-products-db.php](mdc:docs/series/ai-ml-php-developers/code/chapter-04/create-products-db.php)

**Use when:** Writing or editing AI/ML series chapters, implementing ML examples, or referencing ML libraries

---

## Rule Types

### Always Applied

Rules with `alwaysApply: true` in frontmatter:

- `php-version.mdc`
- `project-structure.mdc`

These rules are automatically considered for every AI interaction.

### File Pattern Applied

Rules with `globs: pattern` in frontmatter:

- `authoring-guidelines.mdc` → `docs/**/*.md`
- `tutorials-global.mdc` → `docs/**/*.md`
- `ai-ml-series.mdc` → `docs/series/ai-ml-php-developers/**/*.md`

These rules apply when working with files matching the pattern.

### Reference Only

Rules with `description:` only:

- `php-basics-patterns.mdc`
- `tutorials-task-template.mdc`
- `linking-and-sources.mdc`
- `vitepress-usage.mdc`

These must be explicitly fetched using the `@rule-name` syntax when needed.

## Using the Rules

### As a Developer

When writing a new chapter:

1. **Start with:** `@tutorials-task-template.mdc` for the template
2. **Reference:** `@authoring-guidelines.mdc` for structure details
3. **Check:** `@php-basics-patterns.mdc` for examples and patterns
4. **Ensure:** PHP 8.4 compliance per `php-version.mdc` (auto-applied)

### With AI Assistant

The AI automatically applies rules based on context. You can:

```
# Explicitly request a rule
@tutorials-task-template Create a new chapter about async PHP

# Reference multiple rules
@authoring-guidelines @php-basics-patterns
Review this chapter for consistency

# Use with specific files
@vitepress-usage Add this series to the sidebar
```

## Rule Hierarchy

When rules conflict (rare), follow this priority:

1. **Always Applied Rules** (php-version, project-structure)
2. **File Pattern Rules** (authoring-guidelines, tutorials-global)
3. **Explicitly Requested Rules** (@rule-name)
4. **Reference Rules** (as needed)

## Quick Reference

### Writing a New Chapter?

→ `@tutorials-task-template.mdc`

### Need Structure Details?

→ `@authoring-guidelines.mdc`

### Want to Match php-basics Quality?

→ `@php-basics-patterns.mdc`

### Working with VitePress?

→ `@vitepress-usage.mdc`

### Creating a New Series?

→ `@vitepress-usage.mdc` + `@project-structure.mdc`

### Adding Links or Code References?

→ `@linking-and-sources.mdc`

### Working on AI/ML Series?

→ `ai-ml-series.mdc` (auto-applied) + check code samples

### PHP Code Guidelines?

→ `php-version.mdc` (auto-applied)

## Updating Rules

When updating rules:

1. **Test** the rule with AI to ensure it works as expected
2. **Document** any new patterns observed in existing content
3. **Update this README** if adding new rules or changing structure
4. **Validate** that changes don't conflict with other rules

## Best Practices

1. **Keep rules focused** - Each rule should have a clear, specific purpose
2. **Extract patterns** - Document observed patterns from successful content
3. **Provide examples** - Include real examples from the codebase
4. **Link rules** - Reference related rules when relevant
5. **Update regularly** - As content evolves, update rules to match
6. **Test with AI** - Ensure rules produce the desired AI behavior

## Rule Structure

Good rules include:

```yaml
---
# How the rule is applied
alwaysApply: true # OR
globs: path/pattern # OR
description: What it does # (reference only)
---
# Clear title
# Purpose statement
# Organized sections
# Specific examples
# Do's and don'ts
# Checklists when applicable
```

## Contributing

When adding new rules:

1. Choose appropriate frontmatter (alwaysApply, globs, or description)
2. Follow the structure of existing rules
3. Include practical examples
4. Add an entry to this README
5. Test with AI to verify behavior

## Resources

- [Cursor Rules Documentation](https://docs.cursor.com/)
- [VitePress Documentation](https://vitepress.dev/)
- [PHP 8.4 Documentation](https://www.php.net/releases/8.4/)
- [PSR Standards](https://www.php-fig.org/psr/)

## Questions?

If you're unsure which rule to use or how to structure something:

1. Look at existing chapters in `docs/series/php-basics/` for examples
2. Check `@php-basics-patterns.mdc` for documented patterns
3. Refer to the specific rule for that content type
4. When in doubt, ask the AI to `@tutorials-global` explain the approach

---

**Last Updated:** Based on php-basics series (25 chapters completed)  
**Next Review:** After completing next full series
