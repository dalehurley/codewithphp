---
title: "Appendix B: Common Prompting Patterns"
description: "Proven prompt templates and patterns for PHP developers working with Claude AI. Copy-paste ready prompts for common use cases."
series: "claude-php-developers"
appendix: "B"
order: 101
difficulty: "Reference"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Appendix B: Prompting Patterns</span>
</div>

# Appendix B: Common Prompting Patterns

Production-ready prompt templates for PHP developers. Copy, customize, and use these proven patterns in your applications.

---

## Table of Contents

- [Code Generation](#code-generation)
- [Code Review](#code-review)
- [Documentation](#documentation)
- [Data Extraction](#data-extraction)
- [Content Generation](#content-generation)
- [Analysis and Classification](#analysis-and-classification)
- [Debugging and Problem Solving](#debugging-and-problem-solving)
- [Refactoring](#refactoring)
- [Testing](#testing)
- [System Prompts](#system-prompts)

---

## Code Generation

### Generate PHP Class

```php
# filename: generate-class-prompt.php
$prompt = <<<PROMPT
Create a PHP 8.4+ class with the following requirements:

Class Name: UserRepository
Purpose: Manage user data with database interactions
Framework: Laravel 11

Requirements:
- Implement dependency injection for database
- Include methods: findById(), create(), update(), delete(), search()
- Add type hints for all parameters and return types
- Include PHPDoc comments
- Use modern PHP features (readonly properties, constructor property promotion)
- Follow PSR-12 coding standards
- Include error handling

Return only the class code, properly formatted.
PROMPT;
```

**Usage:**

```php
# filename: use-code-generation-prompt.php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'temperature' => 0.3, // Lower for more consistent code
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ]
]);
```

### Generate API Endpoint

```php
# filename: generate-api-endpoint-prompt.php
$prompt = <<<PROMPT
Generate a Laravel API endpoint with the following specifications:

Endpoint: POST /api/products
Purpose: Create a new product
Framework: Laravel 11 with API resources

Requirements:
1. FormRequest validation class with these rules:
   - name: required, string, max 255
   - price: required, numeric, min 0
   - description: optional, string
   - category_id: required, exists in categories table

2. Controller method with:
   - Dependency injection
   - Proper response codes (201 for success)
   - Error handling
   - API resource transformation

3. Use repository pattern for data access
4. Include type hints and return types
5. Follow RESTful conventions

Provide the FormRequest, Controller method, and API Resource class.
PROMPT;
```

### Generate Database Migration

```php
# filename: generate-migration-prompt.php
$prompt = <<<PROMPT
Create a Laravel migration for the following database schema:

Table: orders
Columns:
- id (primary key, auto-increment)
- user_id (foreign key to users table, cascading delete)
- order_number (unique, string)
- total_amount (decimal, 2 decimal places)
- status (enum: pending, processing, completed, cancelled)
- notes (text, nullable)
- ordered_at (timestamp)
- shipped_at (timestamp, nullable)
- created_at, updated_at (timestamps)

Additional requirements:
- Add indexes on: user_id, order_number, status, ordered_at
- Add foreign key constraint for user_id
- Use modern Laravel migration syntax
- Include down() method for rollback

Return the complete migration file.
PROMPT;
```

---

## Code Review

### Comprehensive Code Review

```php
# filename: code-review-prompt.php
$code = file_get_contents('path/to/code.php');

$prompt = <<<PROMPT
Review the following PHP code and provide detailed feedback:

{$code}

Please analyze:

1. **Code Quality**
   - PSR-12 compliance
   - Naming conventions
   - Code organization

2. **Security Issues**
   - SQL injection vulnerabilities
   - XSS vulnerabilities
   - Authentication/authorization issues
   - Input validation gaps

3. **Performance**
   - N+1 query problems
   - Inefficient algorithms
   - Memory usage concerns

4. **Best Practices**
   - SOLID principles violations
   - Design pattern opportunities
   - Error handling improvements

5. **Bugs and Logic Errors**
   - Potential runtime errors
   - Edge cases not handled
   - Type safety issues

For each issue found:
- Severity (Critical, High, Medium, Low)
- Line number(s)
- Explanation
- Suggested fix with code example

Format as a structured report.
PROMPT;
```

### Security Audit

````php
# filename: security-audit-prompt.php
$prompt = <<<PROMPT
Perform a security audit on this PHP code. Focus exclusively on security vulnerabilities:

```php
{CODE_HERE}
````

Check for:

1. SQL Injection (raw queries, improper parameterization)
2. XSS (unescaped output, unsafe HTML rendering)
3. CSRF vulnerabilities
4. Authentication bypass opportunities
5. Authorization issues
6. Insecure file uploads
7. Path traversal vulnerabilities
8. Insecure cryptography
9. Sensitive data exposure
10. Missing input validation

For each vulnerability:

- OWASP category
- Risk level (Critical/High/Medium/Low)
- Attack vector
- Code example of the fix
- Prevention best practices

Return findings in order of severity.
PROMPT;

````

---

## Documentation

### Generate PHPDoc

```php
# filename: generate-phpdoc-prompt.php
$code = <<<'PHP'
public function processPayment($amount, $currency, $customerId, array $options = [])
{
    // Implementation here
}
PHP;

$prompt = <<<PROMPT
Generate comprehensive PHPDoc documentation for this method:

{$code}

Include:
- Description of what the method does
- @param tags with types and descriptions
- @return tag with type and description
- @throws tags for possible exceptions
- @example with usage example
- Any relevant @see or @link references

Follow PSR-5 PHPDoc standard.
PROMPT;
````

### Generate README

```php
# filename: generate-readme-prompt.php
$prompt = <<<PROMPT
Create a comprehensive README.md for a Laravel package with these details:

Package Name: claude-php-sdk
Purpose: Claude PHP SDK for Anthropic's Claude API
Features:
- Type-safe API client
- Streaming support
- Tool use (function calling)
- Vision API support
- Built-in retry logic
- Laravel integration

Include these sections:
1. Badge row (build, coverage, version, license)
2. Short description
3. Features list
4. Requirements
5. Installation (Composer)
6. Quick Start example
7. Configuration
8. Usage examples (5-6 common scenarios)
9. Advanced usage
10. Testing
11. Contributing guidelines
12. Changelog link
13. License
14. Credits

Use clear formatting, code examples in PHP, and professional tone.
PROMPT;
```

### API Documentation

````php
# filename: generate-api-docs-prompt.php
$prompt = <<<PROMPT
Generate OpenAPI 3.0 documentation for this Laravel API endpoint:

Endpoint: GET /api/v1/products/{id}
Controller method:
```php
public function show(Product $product): JsonResponse
{
    return response()->json([
        'data' => new ProductResource($product)
    ]);
}
````

ProductResource structure:

- id (integer)
- name (string)
- description (string, nullable)
- price (number, format: float)
- category (object with id, name)
- images (array of objects with id, url)
- created_at (string, ISO 8601)
- updated_at (string, ISO 8601)

Include:

- Path definition
- Parameters (path, query)
- Response schemas (200, 404)
- Example responses
- Security requirements (Bearer token)

Return valid OpenAPI YAML.
PROMPT;

````

---

## Data Extraction

### Extract Structured Data

```php
# filename: extract-structured-data-prompt.php
$text = "John Smith ordered 3 MacBook Pros for $6,000 on March 15, 2024...";

$prompt = <<<PROMPT
Extract structured data from this text and return as JSON:

"{$text}"

Extract:
- customer_name (string)
- product (string)
- quantity (integer)
- total_amount (float)
- order_date (ISO 8601 date)

Rules:
- Return ONLY valid JSON
- Use null for missing values
- Ensure data types are correct
- Parse dates to ISO 8601 format

Example output format:
{
    "customer_name": "John Smith",
    "product": "MacBook Pro",
    "quantity": 3,
    "total_amount": 6000.00,
    "order_date": "2024-03-15"
}
PROMPT;
````

### Parse Email Content

```php
# filename: parse-email-prompt.php
$prompt = <<<PROMPT
Parse this email and extract key information as JSON:

Email:
"""
{EMAIL_CONTENT}
"""

Extract:
{
    "type": "order|inquiry|complaint|support",
    "priority": "low|medium|high|urgent",
    "customer": {
        "name": "string",
        "email": "string",
        "phone": "string or null"
    },
    "subject": "brief summary",
    "intent": "what the customer wants",
    "products_mentioned": ["array of product names"],
    "order_number": "string or null",
    "requires_response": boolean,
    "sentiment": "positive|neutral|negative",
    "suggested_department": "sales|support|billing|technical"
}

Return ONLY the JSON, no explanation.
PROMPT;
```

### Extract Invoice Data

```php
# filename: extract-invoice-prompt.php
$prompt = <<<PROMPT
Extract all relevant data from this invoice text and structure it as JSON:

Invoice Text:
"""
{INVOICE_TEXT}
"""

Required fields:
{
    "invoice_number": "string",
    "invoice_date": "YYYY-MM-DD",
    "due_date": "YYYY-MM-DD",
    "vendor": {
        "name": "string",
        "address": "string",
        "tax_id": "string or null"
    },
    "customer": {
        "name": "string",
        "address": "string"
    },
    "line_items": [
        {
            "description": "string",
            "quantity": number,
            "unit_price": number,
            "total": number
        }
    ],
    "subtotal": number,
    "tax": number,
    "total": number,
    "currency": "USD|EUR|GBP etc"
}

Validate that line_items sum to subtotal.
Return ONLY valid JSON.
PROMPT;
```

---

## Content Generation

### Product Description

```php
# filename: generate-product-description-prompt.php
$prompt = <<<PROMPT
Generate a compelling product description for an e-commerce website:

Product: {PRODUCT_NAME}
Category: {CATEGORY}
Key Features:
- {FEATURE_1}
- {FEATURE_2}
- {FEATURE_3}

Target Audience: {TARGET_AUDIENCE}
Tone: Professional but friendly
Length: 150-200 words

Include:
1. Attention-grabbing opening
2. Key benefits (not just features)
3. Use case scenarios
4. Call to action

Focus on benefits over features. Use persuasive language.
PROMPT;
```

### Blog Post Outline

```php
# filename: generate-blog-outline-prompt.php
$prompt = <<<PROMPT
Create a detailed blog post outline on this topic:

Topic: {BLOG_TOPIC}
Target Audience: {AUDIENCE}
Goal: {GOAL - educate/convince/entertain}
SEO Keyword: {KEYWORD}
Word Count Target: {WORD_COUNT}

Generate an outline with:
1. Compelling title (include keyword)
2. Meta description (155 characters max)
3. Introduction hook
4. 5-7 main sections with:
   - Section title
   - 3-4 subsection points
   - Key takeaways
5. Conclusion with call-to-action
6. Suggested internal/external links

Make it SEO-optimized and engaging.
PROMPT;
```

### Email Template

```php
# filename: generate-email-template-prompt.php
$prompt = <<<PROMPT
Write a professional email template for:

Purpose: {PURPOSE - welcome, order confirmation, password reset, etc}
Recipient: {RECIPIENT_TYPE}
Tone: {TONE - formal, friendly, urgent}

Include:
- Compelling subject line
- Personalization placeholders (\{\{name\}\}, \{\{order_number\}\}, etc)
- Clear main message
- Action button/CTA
- Footer with contact info
- Compliance text if needed

Format as HTML email template with inline CSS.
Keep it mobile-responsive.
PROMPT;
```

---

## Analysis and Classification

### Sentiment Analysis

```php
# filename: sentiment-analysis-prompt.php
$prompt = <<<PROMPT
Analyze the sentiment of this customer review:

Review: "{REVIEW_TEXT}"

Provide analysis as JSON:
{
    "overall_sentiment": "positive|neutral|negative",
    "confidence": 0.0-1.0,
    "aspects": {
        "product_quality": "positive|neutral|negative",
        "customer_service": "positive|neutral|negative",
        "value_for_money": "positive|neutral|negative",
        "delivery": "positive|neutral|negative"
    },
    "key_phrases": ["array", "of", "important", "phrases"],
    "actionable_issues": ["array of issues to address"],
    "emotion": "happy|satisfied|frustrated|angry|disappointed|neutral",
    "requires_response": boolean
}

Return ONLY the JSON.
PROMPT;
```

### Content Classification

```php
# filename: content-classification-prompt.php
$prompt = <<<PROMPT
Classify this user-generated content:

Content: "{CONTENT}"

Classify as JSON:
{
    "categories": ["primary_category", "secondary_category"],
    "content_type": "question|review|complaint|suggestion|spam|other",
    "language": "en|es|fr|de|etc",
    "toxicity": {
        "is_toxic": boolean,
        "severity": "none|low|medium|high",
        "types": ["profanity|hate_speech|harassment|etc"]
    },
    "topics": ["array", "of", "topics"],
    "requires_moderation": boolean,
    "suggested_action": "approve|flag|reject|escalate"
}

Be conservative with toxicity flagging.
PROMPT;
```

### Topic Extraction

```php
# filename: topic-extraction-prompt.php
$prompt = <<<PROMPT
Extract and categorize topics from this article:

Article:
"""
{ARTICLE_TEXT}
"""

Return JSON:
{
    "main_topic": "string",
    "subtopics": ["array", "of", "subtopics"],
    "keywords": ["ranked", "by", "relevance"],
    "entities": {
        "people": ["person names"],
        "organizations": ["org names"],
        "locations": ["place names"],
        "technologies": ["tech mentioned"]
    },
    "summary": "2-3 sentence summary",
    "recommended_tags": ["seo", "friendly", "tags"]
}
PROMPT;
```

---

## Debugging and Problem Solving

### Debug Error

```php
# filename: debug-error-prompt.php
$error = "Call to undefined method App\Models\User::getFullname()";
$code = file_get_contents('User.php');

$prompt = <<<PROMPT
Debug this PHP error:

Error Message:
{$error}

Code:
{$code}

Stack Trace (if available):
{$stackTrace}

Provide:
1. Root cause explanation
2. Why this error occurred
3. Step-by-step fix
4. Code example of the solution
5. How to prevent similar errors
6. Related best practices

Be specific and actionable.
PROMPT;
```

### Performance Optimization

````php
# filename: performance-optimization-prompt.php
$prompt = <<<PROMPT
Analyze this code for performance issues and suggest optimizations:

```php
{CODE}
````

Current performance metrics:

- Execution time: {TIME}
- Memory usage: {MEMORY}
- Database queries: {QUERY_COUNT}

Identify:

1. Performance bottlenecks
2. N+1 query problems
3. Inefficient algorithms
4. Memory leaks
5. Missing indexes

For each issue:

- Impact (High/Medium/Low)
- Explanation
- Optimized code example
- Expected improvement

Prioritize by impact.
PROMPT;

````

---

## Refactoring

### Refactor to Design Pattern

```php
# filename: refactor-pattern-prompt.php
$prompt = <<<PROMPT
Refactor this code to use an appropriate design pattern:

Current Code:
```php
{CODE}
````

Issues with current code:

- {ISSUE_1}
- {ISSUE_2}

Requirements:

1. Suggest the most suitable design pattern
2. Explain why this pattern fits
3. Provide refactored code using the pattern
4. Show before/after comparison
5. List benefits of the refactoring
6. Note any trade-offs

Use PHP 8.4+ features where appropriate.
PROMPT;

````

### Extract Service Class

```php
# filename: extract-service-prompt.php
$prompt = <<<PROMPT
This controller has too much business logic. Extract it into a service class:

Current Controller:
```php
{CONTROLLER_CODE}
````

Create:

1. A service class with appropriate methods
2. Interface for the service (dependency inversion)
3. Updated controller using the service
4. Unit test example for the service

Follow:

- Single Responsibility Principle
- Dependency Injection
- Type hints and return types
- PSR-12 standards
  PROMPT;

````

---

## Testing

### Generate Unit Test

```php
# filename: generate-unit-test-prompt.php
$prompt = <<<PROMPT
Generate comprehensive PHPUnit tests for this class:

Class to Test:
```php
{CLASS_CODE}
````

Create tests that:

1. Cover all public methods
2. Test happy paths and edge cases
3. Test error conditions
4. Use data providers for multiple scenarios
5. Mock dependencies appropriately
6. Include setUp and tearDown if needed
7. Follow PHPUnit best practices
8. Aim for 100% code coverage

Use PHPUnit 10 syntax.
Include descriptive test method names.
PROMPT;

````

### Generate Test Cases

```php
# filename: generate-test-cases-prompt.php
$prompt = <<<PROMPT
Generate test cases for this user registration feature:

Feature: User Registration
Requirements:
- Email must be unique and valid
- Password must be 8+ characters with 1 number and 1 special char
- Username must be 3-20 alphanumeric characters
- Terms acceptance is required

Generate test cases covering:
1. Valid registration (happy path)
2. Invalid email formats
3. Duplicate email
4. Weak passwords
5. Invalid usernames
6. Missing required fields
7. SQL injection attempts
8. XSS attempts

Format as a test case table with:
- Test ID
- Description
- Input data
- Expected result
- Priority (P0/P1/P2)
PROMPT;
````

---

## System Prompts

System prompts define Claude's role and behavior. Use these in the `system` parameter.

### Code Review Assistant

```php
# filename: code-review-system-prompt.php
$systemPrompt = <<<'SYSTEM'
You are an expert PHP code reviewer with 10+ years of experience.

Your expertise includes:
- PHP 8.4+ features and best practices
- Laravel and Symfony frameworks
- PSR standards (PSR-1, PSR-12, PSR-4)
- SOLID principles and design patterns
- Security best practices (OWASP Top 10)
- Performance optimization
- Database optimization

When reviewing code:
1. Be constructive and educational
2. Explain WHY something is an issue, not just WHAT
3. Provide code examples for fixes
4. Prioritize issues by severity
5. Acknowledge good practices too
6. Consider context and trade-offs
7. Focus on actionable feedback

Format reviews clearly with:
- Severity levels (Critical, High, Medium, Low)
- Line numbers
- Explanations
- Code examples

Be thorough but concise.
SYSTEM;
```

### API Documentation Writer

```php
# filename: api-docs-system-prompt.php
$systemPrompt = <<<'SYSTEM'
You are a technical documentation specialist for API documentation.

Your responsibilities:
- Write clear, accurate API documentation
- Follow OpenAPI 3.0 specification
- Provide realistic code examples
- Document all parameters, responses, errors
- Include authentication requirements
- Show cURL and PHP examples
- Write for developers of all skill levels

Documentation standards:
- Use consistent terminology
- Explain complex concepts simply
- Provide complete, working examples
- Document edge cases and errors
- Include rate limiting information
- Show best practices

Format:
- Use markdown for readability
- Include code syntax highlighting
- Organize logically
- Add links to related endpoints
SYSTEM;
```

### Data Extraction Specialist

```php
# filename: data-extraction-system-prompt.php
$systemPrompt = <<<'SYSTEM'
You are a data extraction and parsing specialist.

Rules:
1. Always return valid JSON unless otherwise specified
2. Preserve data types (string, number, boolean, null)
3. Use null for missing/unavailable data
4. Parse dates to ISO 8601 format (YYYY-MM-DD)
5. Normalize data (trim whitespace, consistent casing)
6. Validate extracted data for consistency
7. Never make up data - use null if uncertain
8. Handle currency correctly (float with 2 decimals)

For ambiguous data:
- Make reasonable inferences based on context
- Flag low-confidence extractions
- Provide alternative interpretations if needed

Always prioritize accuracy over completeness.
SYSTEM;
```

### Security Auditor

```php
# filename: security-auditor-system-prompt.php
$systemPrompt = <<<'SYSTEM'
You are a security expert specializing in PHP application security.

Focus areas:
- OWASP Top 10 vulnerabilities
- PHP-specific security issues
- Laravel security best practices
- Authentication and authorization flaws
- Input validation and output escaping
- SQL injection, XSS, CSRF
- Cryptography and data protection
- API security
- Dependency vulnerabilities

When auditing code:
1. Identify all security risks, even potential ones
2. Rate severity: Critical, High, Medium, Low
3. Explain the attack vector
4. Provide proof-of-concept if relevant
5. Suggest specific remediation
6. Reference OWASP/CVE when applicable

Be thorough and paranoid - security is critical.
Assume all input is malicious until proven otherwise.
SYSTEM;
```

---

## Advanced Patterns

### Prompt Caching

Prompt caching allows you to cache frequently used system prompts or message prefixes, reducing costs and latency for repeated content.

```php
# filename: prompt-caching-example.php
// Cache a system prompt for 5 minutes
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'system' => 'You are an expert PHP code reviewer. Review code for security, performance, and best practices.',
    'messages' => [
        ['role' => 'user', 'content' => 'Review this code: ' . $code]
    ],
    'metadata' => [
        'cache_control' => [
            'type' => 'ephemeral',
            'ttl_seconds' => 300  // 5 minutes
        ]
    ]
]);

// For 1-hour cache (important but less frequent)
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'system' => 'You are an expert PHP code reviewer...',
    'messages' => [
        ['role' => 'user', 'content' => 'Review this code: ' . $code]
    ],
    'metadata' => [
        'cache_control' => [
            'type' => 'ephemeral',
            'ttl_seconds' => 3600  // 1 hour
        ]
    ]
]);
```

**Benefits:**

- Reduced costs for repeated context
- Lower latency for cached prompts
- Best for system prompts or long prefixes that don't change

**When to Use:**

- System prompts that define behavior
- Long document prefixes in RAG applications
- Frequently reused prompt templates

### Chain of Thought Prompting

For complex reasoning tasks:

```php
# filename: chain-of-thought-prompt.php
$prompt = <<<PROMPT
Solve this problem step by step:

Problem: {PROBLEM_DESCRIPTION}

Please:
1. First, analyze what the problem is asking
2. Break down the problem into smaller steps
3. Solve each step, showing your reasoning
4. Verify your solution
5. Provide the final answer

Think through each step carefully before moving to the next.
PROMPT;
```

### Few-Shot Learning

Provide examples to establish patterns:

```php
# filename: few-shot-learning-prompt.php
$prompt = <<<PROMPT
Extract product information from these listings and format as JSON.

Example 1:
Input: "iPhone 14 Pro - $999 - 256GB - Space Black"
Output: {"product": "iPhone 14 Pro", "price": 999.00, "variant": "256GB Space Black"}

Example 2:
Input: "Samsung Galaxy S23 Ultra (512GB, Phantom Black) - $1,199.99"
Output: {"product": "Samsung Galaxy S23 Ultra", "price": 1199.99, "variant": "512GB Phantom Black"}

Now extract from:
Input: "{NEW_LISTING}"
Output:
PROMPT;
```

### Role-Based Prompting

```php
# filename: role-based-prompt.php
$prompt = <<<PROMPT
You are a senior Laravel developer with 8 years of experience.

A junior developer asks:
"{QUESTION}"

Provide a comprehensive answer that:
- Explains the concept clearly
- Shows a code example
- Mentions best practices
- Points out common pitfalls
- Suggests further reading

Be encouraging and educational.
PROMPT;
```

---

## Tips for Effective Prompting

### Be Specific

❌ Bad: "Review this code"
✅ Good: "Review this PHP code for security vulnerabilities, focusing on SQL injection and XSS. Provide severity ratings and specific fixes."

### Provide Context

````php
# filename: context-prompt-example.php
$prompt = "
Context: This is a payment processing service for an e-commerce platform handling $1M monthly.

Requirements: The code must be PCI-DSS compliant and handle failures gracefully.

Task: Review this payment processing code for security and reliability issues.

Code:
```php
{$code}
````

";

````

### Use Delimiters

```php
# filename: delimiters-prompt-example.php
$prompt = <<<PROMPT
Analyze the code between the XML tags:

<code>
{$phpCode}
</code>

Provide refactoring suggestions.
PROMPT;
````

### Specify Output Format

```php
# filename: output-format-prompt.php
$prompt = <<<PROMPT
Extract user data and return ONLY valid JSON with no explanation.

Required format:
{"name": "string", "email": "string", "age": number}

Text: "{$text}"
PROMPT;
```

### Set Temperature Appropriately

```php
# filename: temperature-settings.php
// For consistent, deterministic outputs (code generation, data extraction)
'temperature' => 0.0

// For balanced outputs (most use cases)
'temperature' => 0.7

// For creative outputs (content writing, brainstorming)
'temperature' => 1.0
```

---

## Prompt Template Library (PHP)

Helper function to use templates:

```php
# filename: PromptTemplate.php
class PromptTemplate
{
    private string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function render(array $variables): string
    {
        $prompt = $this->template;

        foreach ($variables as $key => $value) {
            $placeholder = '{' . strtoupper($key) . '}';
            $prompt = str_replace($placeholder, $value, $prompt);
        }

        return $prompt;
    }
}

// Usage
$template = new PromptTemplate(<<<PROMPT
Generate a {TYPE} for:

Name: {NAME}
Description: {DESCRIPTION}

Requirements:
{REQUIREMENTS}
PROMPT);

$prompt = $template->render([
    'type' => PHP class',
    'name' => 'ProductRepository',
    'description' => 'Manages product data',
    'requirements' => "- Use Laravel\n- Include CRUD methods\n- Add type hints"
]);
```

---

## Additional Resources

- **[Anthropic Prompt Engineering Guide](https://docs.claude.com/en/docs/prompt-engineering)** - Official guide
- **[Prompt Library](https://docs.claude.com/en/prompt-library)** - More examples
- **[Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics)** - Full chapter on prompting

---

::: tip Quick Navigation

- **[← Appendix A: API Reference](/series/claude-php-developers/appendices/a-api-reference)** - Complete API reference
- **[← Appendix B: Prompting Patterns](/series/claude-php-developers/appendices/b-prompting-patterns)** - Prompt templates
- **[Appendix C: Error Codes →](/series/claude-php-developers/appendices/c-error-codes)** - Troubleshooting guide
- **[Appendix D: Resources →](/series/claude-php-developers/appendices/d-resources)** - Tools and resources
- **[Back to Series](/series/claude-php-developers)** - Return to main series
  :::

_Last updated: November 2024_
