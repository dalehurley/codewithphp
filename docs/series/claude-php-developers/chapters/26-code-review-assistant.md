---
title: "26: Building a Code Review Assistant"
description: "Build an intelligent code review assistant that analyzes pull requests, detects bugs, suggests improvements, and integrates with GitHub/GitLab webhooks for automated code quality analysis."
series: "claude-php-developers"
chapter: 26
order: 26
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15"
  - "Understanding of Git and version control"
  - "Experience with GitHub/GitLab APIs"
  - "Knowledge of code quality principles"
---

![26: Building a Code Review Assistant](/images/claude-php/chapter-26-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 26</span>
</div>

# Chapter 26: Building a Code Review Assistant

## Overview

Code reviews are critical for maintaining code quality, but they're time-consuming and require significant expertise. In this chapter, you'll build an intelligent code review assistant powered by Claude that automatically analyzes pull requests, detects potential bugs, suggests improvements, and identifies security vulnerabilities.

Your assistant will integrate with GitHub and GitLab webhooks to provide automated reviews, generate detailed feedback, and help teams maintain high code quality standards. You'll implement static analysis, pattern detection, best practice enforcement, and intelligent commenting systems.

**What You'll Build**: A production-ready code review system that analyzes PRs, posts intelligent comments, tracks technical debt, and integrates seamlessly with your development workflow.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use and structured outputs)
- ✓ **Git knowledge** for analyzing diffs and commits
- ✓ **GitHub/GitLab API experience** for webhook integration
- ✓ **Code quality understanding** (SOLID, security, performance)

**Estimated Time**: 90-120 minutes

## Architecture Overview

```php
<?php
# filename: src/CodeReview/ReviewSystem.php
declare(strict_types=1);

namespace App\CodeReview;

use Anthropic\Anthropic;

class ReviewSystem
{
    public function __construct(
        private Anthropic $claude,
        private GitHubClient $github,
        private ReviewConfig $config
    ) {}

    /**
     * Review a pull request
     */
    public function reviewPullRequest(int $prNumber): ReviewResult
    {
        // Fetch PR data
        $pr = $this->github->getPullRequest($prNumber);
        $diff = $this->github->getDiff($prNumber);
        $files = $this->github->getChangedFiles($prNumber);

        // Analyze the changes
        $analysis = $this->analyzeChanges($diff, $files);

        // Generate review comments
        $comments = $this->generateComments($analysis, $files);

        // Post review to GitHub
        if ($this->config->autoPost) {
            $this->postReview($prNumber, $comments, $analysis);
        }

        return new ReviewResult(
            analysis: $analysis,
            comments: $comments,
            summary: $analysis['summary']
        );
    }

    private function analyzeChanges(string $diff, array $files): array
    {
        $prompt = $this->buildAnalysisPrompt($diff, $files);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 8192,
            'temperature' => 0.3,
            'system' => $this->getSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return json_decode($response->content[0]->text, true);
    }

    private function getSystemPrompt(): string
    {
        return <<<SYSTEM
You are an expert code reviewer specializing in PHP, with deep knowledge of:
- Security best practices (OWASP, input validation, SQL injection, XSS)
- Performance optimization
- Code quality (SOLID, DRY, design patterns)
- Laravel and modern PHP frameworks
- Testing and test coverage
- Documentation standards

Your reviews are:
- Constructive and educational
- Specific with actionable suggestions
- Balanced between nitpicks and critical issues
- Focused on maintainability and scalability

Severity levels:
- CRITICAL: Security vulnerabilities, data loss risks
- HIGH: Bugs, performance issues, broken functionality
- MEDIUM: Code quality, maintainability concerns
- LOW: Style, minor optimizations, suggestions

Always provide code examples for suggested improvements.
SYSTEM;
    }
}
```

## PR Analysis Engine

### Diff Parser and Analyzer

```php
<?php
# filename: src/CodeReview/DiffAnalyzer.php
declare(strict_types=1);

namespace App\CodeReview;

class DiffAnalyzer
{
    /**
     * Parse unified diff format
     */
    public function parseDiff(string $diff): array
    {
        $files = [];
        $currentFile = null;
        $currentHunk = null;

        $lines = explode("\n", $diff);

        foreach ($lines as $line) {
            // New file
            if (str_starts_with($line, 'diff --git')) {
                if ($currentFile) {
                    $files[] = $currentFile;
                }
                preg_match('/b\/(.+)$/', $line, $matches);
                $currentFile = [
                    'path' => $matches[1] ?? '',
                    'hunks' => [],
                    'additions' => 0,
                    'deletions' => 0
                ];
                continue;
            }

            // File metadata
            if (str_starts_with($line, '+++') || str_starts_with($line, '---')) {
                continue;
            }

            // Hunk header
            if (str_starts_with($line, '@@')) {
                if ($currentHunk) {
                    $currentFile['hunks'][] = $currentHunk;
                }
                preg_match('/@@ -(\d+),?(\d+)? \+(\d+),?(\d+)? @@(.*)/', $line, $matches);
                $currentHunk = [
                    'old_start' => (int)$matches[1],
                    'old_count' => (int)($matches[2] ?? 1),
                    'new_start' => (int)$matches[3],
                    'new_count' => (int)($matches[4] ?? 1),
                    'context' => trim($matches[5] ?? ''),
                    'lines' => []
                ];
                continue;
            }

            // Hunk content
            if ($currentHunk !== null) {
                $type = match($line[0] ?? '') {
                    '+' => 'addition',
                    '-' => 'deletion',
                    default => 'context'
                };

                $currentHunk['lines'][] = [
                    'type' => $type,
                    'content' => substr($line, 1)
                ];

                if ($type === 'addition') {
                    $currentFile['additions']++;
                } elseif ($type === 'deletion') {
                    $currentFile['deletions']++;
                }
            }
        }

        if ($currentHunk) {
            $currentFile['hunks'][] = $currentHunk;
        }
        if ($currentFile) {
            $files[] = $currentFile;
        }

        return $files;
    }

    /**
     * Extract added/modified code only
     */
    public function getChangedCode(array $parsedDiff): array
    {
        $changes = [];

        foreach ($parsedDiff as $file) {
            $fileChanges = [
                'path' => $file['path'],
                'additions' => [],
                'modifications' => []
            ];

            foreach ($file['hunks'] as $hunk) {
                $context = [];
                foreach ($hunk['lines'] as $line) {
                    if ($line['type'] === 'addition') {
                        $fileChanges['additions'][] = [
                            'line' => $hunk['new_start'],
                            'code' => $line['content'],
                            'context' => $context
                        ];
                    } elseif ($line['type'] === 'context') {
                        $context[] = $line['content'];
                        if (count($context) > 3) {
                            array_shift($context);
                        }
                    }
                }
            }

            if (!empty($fileChanges['additions'])) {
                $changes[] = $fileChanges;
            }
        }

        return $changes;
    }
}
```

## Security Vulnerability Detection

```php
<?php
# filename: src/CodeReview/SecurityScanner.php
declare(strict_types=1);

namespace App\CodeReview;

use Anthropic\Anthropic;

class SecurityScanner
{
    private array $vulnerabilityPatterns = [
        'sql_injection' => [
            'pattern' => '/\$.*query.*\$.*[^prepare]/i',
            'description' => 'Possible SQL injection vulnerability'
        ],
        'xss' => [
            'pattern' => '/echo\s+\$_(GET|POST|REQUEST)/i',
            'description' => 'Possible XSS vulnerability'
        ],
        'command_injection' => [
            'pattern' => '/(exec|system|passthru|shell_exec)\s*\(\s*\$/',
            'description' => 'Possible command injection'
        ],
        'file_inclusion' => [
            'pattern' => '/(include|require).*\$_(GET|POST|REQUEST)/i',
            'description' => 'Possible file inclusion vulnerability'
        ]
    ];

    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Scan code for security vulnerabilities
     */
    public function scanCode(string $code, string $filename): array
    {
        $issues = [];

        // Pattern-based detection
        $issues = array_merge($issues, $this->patternScan($code));

        // AI-powered deep analysis
        $aiIssues = $this->deepSecurityAnalysis($code, $filename);
        $issues = array_merge($issues, $aiIssues);

        return $issues;
    }

    private function patternScan(string $code): array
    {
        $issues = [];
        $lines = explode("\n", $code);

        foreach ($lines as $lineNum => $line) {
            foreach ($this->vulnerabilityPatterns as $type => $pattern) {
                if (preg_match($pattern['pattern'], $line)) {
                    $issues[] = [
                        'type' => 'security',
                        'severity' => 'CRITICAL',
                        'category' => $type,
                        'line' => $lineNum + 1,
                        'message' => $pattern['description'],
                        'code' => trim($line)
                    ];
                }
            }
        }

        return $issues;
    }

    private function deepSecurityAnalysis(string $code, string $filename): array
    {
        $prompt = <<<PROMPT
Analyze this PHP code for security vulnerabilities. Return a JSON array of issues found.

File: {$filename}

Code:
```php
{$code}
```

Look for:
1. SQL injection vulnerabilities
2. XSS (Cross-Site Scripting)
3. CSRF (Cross-Site Request Forgery)
4. Authentication/authorization issues
5. Insecure cryptography
6. Information disclosure
7. Insecure deserialization
8. File upload vulnerabilities
9. Path traversal
10. Weak password policies

For each issue found, return:
{
  "type": "security",
  "severity": "CRITICAL|HIGH|MEDIUM|LOW",
  "category": "vulnerability type",
  "line": line_number,
  "message": "description of the issue",
  "suggestion": "how to fix it",
  "code_example": "fixed code example"
}

Return ONLY valid JSON array, no explanation.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;

        // Extract JSON from response
        if (preg_match('/```json\s*(\[.*?\])\s*```/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\[.*\])/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        }

        return json_decode($jsonText, true) ?? [];
    }
}
```

## Bug Detection and Code Quality Analysis

```php
<?php
# filename: src/CodeReview/BugDetector.php
declare(strict_types=1);

namespace App\CodeReview;

use Anthropic\Anthropic;

class BugDetector
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Detect potential bugs in code
     */
    public function detectBugs(array $files): array
    {
        $bugs = [];

        foreach ($files as $file) {
            $fileContent = file_get_contents($file['path']);

            $analysis = $this->analyzeFile($fileContent, $file['path']);

            if (!empty($analysis)) {
                $bugs[$file['path']] = $analysis;
            }
        }

        return $bugs;
    }

    private function analyzeFile(string $code, string $filename): array
    {
        $prompt = <<<PROMPT
Analyze this PHP code for potential bugs and code quality issues.

File: {$filename}

```php
{$code}
```

Check for:
1. Logic errors and edge cases
2. Null pointer exceptions
3. Type errors
4. Resource leaks (unclosed files, connections)
5. Race conditions
6. Off-by-one errors
7. Incorrect error handling
8. Missing validation
9. Dead code
10. Code duplication
11. Performance issues (N+1 queries, inefficient loops)
12. Missing return statements
13. Incorrect comparisons (== vs ===)
14. Uninitialized variables

Return JSON array with format:
[
  {
    "severity": "CRITICAL|HIGH|MEDIUM|LOW",
    "line": line_number,
    "type": "bug|performance|quality",
    "category": "specific issue type",
    "message": "clear description",
    "suggestion": "how to fix",
    "code_before": "problematic code",
    "code_after": "suggested fix"
  }
]

Return ONLY valid JSON, no explanation.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 6144,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;

        // Extract and parse JSON
        if (preg_match('/```json\s*(\[.*?\])\s*```/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\[.*\])/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        }

        return json_decode($jsonText, true) ?? [];
    }
}
```

## GitHub/GitLab Integration

### GitHub Webhook Handler

```php
<?php
# filename: src/CodeReview/GitHub/WebhookHandler.php
declare(strict_types=1);

namespace App\CodeReview\GitHub;

class WebhookHandler
{
    public function __construct(
        private GitHubClient $github,
        private ReviewSystem $reviewSystem,
        private string $webhookSecret
    ) {}

    /**
     * Handle incoming webhook
     */
    public function handle(string $payload, string $signature): ?array
    {
        // Verify signature
        if (!$this->verifySignature($payload, $signature)) {
            throw new \RuntimeException('Invalid webhook signature');
        }

        $data = json_decode($payload, true);

        // Handle different events
        return match($data['action'] ?? '') {
            'opened', 'synchronize' => $this->handlePullRequest($data),
            'created' => $this->handleComment($data),
            default => null
        };
    }

    private function handlePullRequest(array $data): array
    {
        $prNumber = $data['pull_request']['number'];
        $repository = $data['repository']['full_name'];

        // Trigger review
        $result = $this->reviewSystem->reviewPullRequest($prNumber);

        return [
            'pr' => $prNumber,
            'repository' => $repository,
            'review_posted' => true,
            'issues_found' => count($result->comments)
        ];
    }

    private function handleComment(array $data): ?array
    {
        // Handle review comments and questions
        if (!isset($data['comment']['body'])) {
            return null;
        }

        $comment = $data['comment']['body'];

        // Check if comment mentions the bot
        if (str_contains($comment, '@code-reviewer')) {
            // Respond to specific questions
            return $this->respondToComment($data);
        }

        return null;
    }

    private function verifySignature(string $payload, string $signature): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    private function respondToComment(array $data): array
    {
        // Extract question from comment
        $comment = $data['comment']['body'];
        $prNumber = $data['issue']['number'];

        // Use Claude to generate response
        // Implementation details...

        return [
            'responded' => true,
            'pr' => $prNumber
        ];
    }
}
```

### GitHub Client

```php
<?php
# filename: src/CodeReview/GitHub/GitHubClient.php
declare(strict_types=1);

namespace App\CodeReview\GitHub;

class GitHubClient
{
    public function __construct(
        private string $token,
        private string $repository
    ) {}

    /**
     * Get pull request data
     */
    public function getPullRequest(int $prNumber): array
    {
        return $this->request("GET", "/repos/{$this->repository}/pulls/{$prNumber}");
    }

    /**
     * Get PR diff
     */
    public function getDiff(int $prNumber): string
    {
        return $this->request(
            "GET",
            "/repos/{$this->repository}/pulls/{$prNumber}",
            accept: 'application/vnd.github.v3.diff'
        );
    }

    /**
     * Get changed files
     */
    public function getChangedFiles(int $prNumber): array
    {
        return $this->request("GET", "/repos/{$this->repository}/pulls/{$prNumber}/files");
    }

    /**
     * Post review comment
     */
    public function postReview(int $prNumber, array $comments, string $body, string $event = 'COMMENT'): array
    {
        return $this->request("POST", "/repos/{$this->repository}/pulls/{$prNumber}/reviews", [
            'body' => $body,
            'event' => $event, // APPROVE, REQUEST_CHANGES, COMMENT
            'comments' => $comments
        ]);
    }

    /**
     * Post single comment
     */
    public function postComment(int $prNumber, string $body, string $path, int $line): array
    {
        return $this->request("POST", "/repos/{$this->repository}/pulls/{$prNumber}/comments", [
            'body' => $body,
            'path' => $path,
            'line' => $line
        ]);
    }

    /**
     * Get file contents
     */
    public function getFileContents(string $path, string $ref = 'main'): string
    {
        $data = $this->request("GET", "/repos/{$this->repository}/contents/{$path}?ref={$ref}");
        return base64_decode($data['content']);
    }

    private function request(string $method, string $endpoint, ?array $body = null, string $accept = 'application/vnd.github.v3+json'): mixed
    {
        $ch = curl_init("https://api.github.com{$endpoint}");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->token}",
                "Accept: {$accept}",
                'User-Agent: Code-Review-Assistant'
            ]
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new \RuntimeException("GitHub API error: {$httpCode}");
        }

        // For diff format, return as string
        if ($accept === 'application/vnd.github.v3.diff') {
            return $response;
        }

        return json_decode($response, true);
    }
}
```

## Review Comment Generator

```php
<?php
# filename: src/CodeReview/CommentGenerator.php
declare(strict_types=1);

namespace App\CodeReview;

use Anthropic\Anthropic;

class CommentGenerator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Generate helpful review comments
     */
    public function generateComments(array $issues, array $files): array
    {
        $comments = [];

        foreach ($issues as $filePath => $fileIssues) {
            foreach ($fileIssues as $issue) {
                $comment = $this->formatComment($issue);

                $comments[] = [
                    'path' => $filePath,
                    'line' => $issue['line'],
                    'body' => $comment,
                    'severity' => $issue['severity']
                ];
            }
        }

        return $this->prioritizeComments($comments);
    }

    private function formatComment(array $issue): string
    {
        $emoji = match($issue['severity']) {
            'CRITICAL' => '🚨',
            'HIGH' => '⚠️',
            'MEDIUM' => '💡',
            'LOW' => 'ℹ️',
            default => '📝'
        };

        $comment = "{$emoji} **{$issue['severity']}**: {$issue['message']}\n\n";

        if (isset($issue['code_before'])) {
            $comment .= "**Current code:**\n```php\n{$issue['code_before']}\n```\n\n";
        }

        if (isset($issue['suggestion'])) {
            $comment .= "**Suggestion:**\n{$issue['suggestion']}\n\n";
        }

        if (isset($issue['code_after'])) {
            $comment .= "**Improved version:**\n```php\n{$issue['code_after']}\n```\n\n";
        }

        if (isset($issue['references'])) {
            $comment .= "**References:**\n";
            foreach ($issue['references'] as $ref) {
                $comment .= "- {$ref}\n";
            }
        }

        return $comment;
    }

    private function prioritizeComments(array $comments): array
    {
        // Sort by severity
        usort($comments, function($a, $b) {
            $priority = ['CRITICAL' => 0, 'HIGH' => 1, 'MEDIUM' => 2, 'LOW' => 3];
            return ($priority[$a['severity']] ?? 99) <=> ($priority[$b['severity']] ?? 99);
        });

        return $comments;
    }

    /**
     * Generate review summary
     */
    public function generateSummary(array $analysis, array $comments): string
    {
        $criticalCount = count(array_filter($comments, fn($c) => $c['severity'] === 'CRITICAL'));
        $highCount = count(array_filter($comments, fn($c) => $c['severity'] === 'HIGH'));
        $mediumCount = count(array_filter($comments, fn($c) => $c['severity'] === 'MEDIUM'));
        $lowCount = count(array_filter($comments, fn($c) => $c['severity'] === 'LOW'));

        $summary = "## 🤖 Automated Code Review\n\n";
        $summary .= "### Summary\n\n";
        $summary .= "Found **" . count($comments) . " issues**:\n\n";

        if ($criticalCount > 0) {
            $summary .= "- 🚨 **{$criticalCount} Critical** - Requires immediate attention\n";
        }
        if ($highCount > 0) {
            $summary .= "- ⚠️ **{$highCount} High** - Should be addressed\n";
        }
        if ($mediumCount > 0) {
            $summary .= "- 💡 **{$mediumCount} Medium** - Improvements suggested\n";
        }
        if ($lowCount > 0) {
            $summary .= "- ℹ️ **{$lowCount} Low** - Minor suggestions\n";
        }

        $summary .= "\n### Files Analyzed\n\n";
        $summary .= "- **" . count($analysis['files']) . " files** changed\n";
        $summary .= "- **" . ($analysis['additions'] ?? 0) . " additions**, ";
        $summary .= "**" . ($analysis['deletions'] ?? 0) . " deletions**\n\n";

        if ($criticalCount > 0) {
            $summary .= "⚠️ **This PR has critical issues that must be addressed before merging.**\n\n";
        } elseif ($highCount === 0 && $mediumCount === 0 && $lowCount === 0) {
            $summary .= "✅ **Great work! No issues found.**\n\n";
        }

        $summary .= "---\n\n";
        $summary .= "*Reviewed by Claude Code Review Assistant*";

        return $summary;
    }
}
```

## Complete Integration Example

```php
<?php
# filename: webhook.php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Anthropic\Anthropic;
use App\CodeReview\ReviewSystem;
use App\CodeReview\GitHub\GitHubClient;
use App\CodeReview\GitHub\WebhookHandler;
use App\CodeReview\ReviewConfig;
use App\CodeReview\SecurityScanner;
use App\CodeReview\BugDetector;
use App\CodeReview\CommentGenerator;

// Initialize Claude
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Initialize GitHub client
$github = new GitHubClient(
    token: getenv('GITHUB_TOKEN'),
    repository: getenv('GITHUB_REPOSITORY') // e.g., "owner/repo"
);

// Configure review system
$config = new ReviewConfig(
    autoPost: true,
    minSeverity: 'MEDIUM',
    enableSecurityScan: true,
    enableBugDetection: true,
    maxComments: 50
);

// Initialize components
$securityScanner = new SecurityScanner($claude);
$bugDetector = new BugDetector($claude);
$commentGenerator = new CommentGenerator($claude);

// Initialize review system
$reviewSystem = new ReviewSystem(
    claude: $claude,
    github: $github,
    config: $config,
    securityScanner: $securityScanner,
    bugDetector: $bugDetector,
    commentGenerator: $commentGenerator
);

// Initialize webhook handler
$webhookHandler = new WebhookHandler(
    github: $github,
    reviewSystem: $reviewSystem,
    webhookSecret: getenv('GITHUB_WEBHOOK_SECRET')
);

// Handle incoming webhook
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

try {
    $result = $webhookHandler->handle($payload, $signature);

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($result ?? ['status' => 'ok']);

} catch (\Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
```

## CLI Tool for Manual Reviews

```php
<?php
# filename: bin/review-pr.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\CodeReview\ReviewSystem;

if ($argc < 2) {
    echo "Usage: php review-pr.php <pr-number>\n";
    exit(1);
}

$prNumber = (int)$argv[1];

// Initialize system (same as webhook.php)
// ...

echo "🔍 Reviewing PR #{$prNumber}...\n\n";

try {
    $result = $reviewSystem->reviewPullRequest($prNumber);

    echo "✅ Review complete!\n\n";
    echo "Summary:\n";
    echo $result->summary . "\n\n";

    echo "Issues found: " . count($result->comments) . "\n";

    foreach ($result->comments as $comment) {
        echo "\n---\n";
        echo "File: {$comment['path']}:{$comment['line']}\n";
        echo "Severity: {$comment['severity']}\n";
        echo $comment['body'] . "\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

## Testing the Review System

```php
<?php
# filename: tests/CodeReview/SecurityScannerTest.php
declare(strict_types=1);

namespace Tests\CodeReview;

use PHPUnit\Framework\TestCase;
use App\CodeReview\SecurityScanner;

class SecurityScannerTest extends TestCase
{
    public function testDetectsSqlInjection(): void
    {
        $scanner = new SecurityScanner($this->getMockClaude());

        $code = <<<'PHP'
$userId = $_GET['id'];
$query = "SELECT * FROM users WHERE id = " . $userId;
$result = $db->query($query);
PHP;

        $issues = $scanner->scanCode($code, 'test.php');

        $this->assertNotEmpty($issues);
        $this->assertStringContainsString('injection', strtolower($issues[0]['message']));
    }

    public function testDetectsXss(): void
    {
        $scanner = new SecurityScanner($this->getMockClaude());

        $code = <<<'PHP'
echo $_GET['name'];
PHP;

        $issues = $scanner->scanCode($code, 'test.php');

        $this->assertNotEmpty($issues);
        $this->assertEquals('CRITICAL', $issues[0]['severity']);
    }
}
```

## Key Takeaways

- ✓ Code review automation saves time and improves code quality consistently
- ✓ Claude excels at understanding code context and suggesting improvements
- ✓ Security scanning should combine pattern matching with AI analysis
- ✓ GitHub/GitLab webhooks enable seamless integration into development workflow
- ✓ Structured output ensures consistent, actionable review comments
- ✓ Severity levels help prioritize which issues to address first
- ✓ Automated reviews augment, not replace, human code reviews
- ✓ Comments should be educational and provide code examples
- ✓ Rate limiting and error handling are critical for production use
- ✓ Testing your review system prevents false positives

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="26"
  label="You've built an intelligent code review assistant!"
/>

---

Continue to [Chapter 27: Documentation Generator](/series/claude-php-developers/chapters/27-documentation-generator) to automate documentation creation.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 26 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-26)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-26
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
export GITHUB_TOKEN="ghp_your-token"
php bin/review-pr.php 123
```
