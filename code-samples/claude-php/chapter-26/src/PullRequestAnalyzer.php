<?php

declare(strict_types=1);

namespace CodeReview;

use ClaudePhp\ClaudePhp;

/**
 * AI-Powered Pull Request Analyzer
 */
class PullRequestAnalyzer
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);
    }

    /**
     * Analyze pull request changes
     */
    public function analyzePullRequest(array $diff, array $context = []): array
    {
        $prompt = $this->buildAnalysisPrompt($diff, $context);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt,
            ]],
        ]);

        return $this->parseReview($response->content[0]['text']);
    }

    /**
     * Review specific file
     */
    public function reviewFile(string $filename, string $content, string $diff): array
    {
        $prompt = <<<PROMPT
Review this code file for:
- Code quality and best practices
- Potential bugs or errors
- Security vulnerabilities
- Performance issues
- Maintainability concerns

Filename: {$filename}

Changes:
{$diff}

Full content:
{$content}

Provide specific, actionable feedback with line numbers where applicable.
PROMPT;

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt,
            ]],
        ]);

        return [
            'filename' => $filename,
            'review' => $response->content[0]['text'],
            'severity' => $this->detectSeverity($response->content[0]['text']),
        ];
    }

    /**
     * Check for security issues
     */
    public function securityCheck(string $code): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Analyze this code for security vulnerabilities:
- SQL injection
- XSS vulnerabilities
- CSRF issues
- Authentication/authorization flaws
- Data exposure
- Insecure dependencies

Code:
{$code}

List any security issues found with severity (critical, high, medium, low).
PROMPT,
            ]],
        ]);

        return $this->parseSecurityIssues($response->content[0]['text']);
    }

    /**
     * Suggest improvements
     */
    public function suggestImprovements(string $code, string $language = 'php'): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Suggest improvements for this {$language} code:
- Refactoring opportunities
- Design pattern applications
- Performance optimizations
- Code simplification

Code:
{$code}

Provide specific suggestions with code examples where helpful.
PROMPT,
            ]],
        ]);

        return [
            'suggestions' => $response->content[0]['text'],
            'priority' => 'medium',
        ];
    }

    /**
     * Generate review summary
     */
    public function generateSummary(array $fileReviews): string
    {
        $reviewText = json_encode($fileReviews, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Create a concise summary of this code review:

{$reviewText}

Include:
- Overall assessment
- Critical issues (if any)
- Key recommendations
- Approval status (approved/changes requested/needs work)
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    private function buildAnalysisPrompt(array $diff, array $context): string
    {
        $diffText = '';
        foreach ($diff as $file) {
            $diffText .= "File: {$file['filename']}\n";
            $diffText .= $file['patch'] . "\n\n";
        }

        $contextText = '';
        if (!empty($context['description'])) {
            $contextText .= "PR Description: {$context['description']}\n\n";
        }

        return <<<PROMPT
Review this pull request:

{$contextText}
Changes:
{$diffText}

Analyze for:
1. Code quality and style
2. Potential bugs
3. Security issues
4. Performance concerns
5. Test coverage
6. Documentation

Provide structured feedback.
PROMPT;
    }

    private function parseReview(string $reviewText): array
    {
        // Simplified parsing - would use more sophisticated parsing
        return [
            'summary' => $reviewText,
            'issues' => [],
            'suggestions' => [],
            'approval_status' => 'needs_review',
        ];
    }

    private function detectSeverity(string $text): string
    {
        if (stripos($text, 'critical') !== false || stripos($text, 'severe') !== false) {
            return 'critical';
        }
        if (stripos($text, 'important') !== false || stripos($text, 'major') !== false) {
            return 'high';
        }
        if (stripos($text, 'minor') !== false || stripos($text, 'suggestion') !== false) {
            return 'low';
        }
        return 'medium';
    }

    private function parseSecurityIssues(string $text): array
    {
        // Would parse structured output
        return [
            'has_issues' => !empty($text),
            'issues' => [],
            'summary' => $text,
        ];
    }
}
