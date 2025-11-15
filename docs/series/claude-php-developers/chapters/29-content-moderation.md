---
title: "29: Content Moderation System"
description: "Build a comprehensive content moderation system that detects toxic language, spam, PII, policy violations, and manages moderation queues with Claude's intelligent analysis."
series: "claude-php-developers"
chapter: 29
order: 29
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15"
  - "Understanding of content policies and safety"
  - "Experience with moderation workflows"
  - "Knowledge of queue systems and async processing"
---

![29: Content Moderation System](/images/claude-php/chapter-29-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 29</span>
</div>

# Chapter 29: Content Moderation System

## Overview

User-generated content requires careful moderation to maintain community standards and legal compliance. In this chapter, you'll build an intelligent moderation system that automatically detects toxic language, spam, personally identifiable information (PII), policy violations, and inappropriate content.

Claude excels at understanding context and nuance in content moderation—distinguishing between legitimate discussion and harmful content, identifying subtle violations, and explaining moderation decisions. Your system will handle real-time moderation, queue management, and human review workflows.

**What You'll Build**: A production-ready content moderation platform that analyzes text, images, and user behavior, enforces community guidelines, manages moderation queues, and provides detailed violation reports.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use and structured outputs)
- ✓ **Content policy knowledge** for moderation standards
- ✓ **Queue system experience** for async processing
- ✓ **Database skills** for audit trails and appeals

**Estimated Time**: 90-120 minutes

## Architecture Overview

```php
<?php
# filename: src/Moderation/ModerationSystem.php
declare(strict_types=1);

namespace App\Moderation;

use Anthropic\Anthropic;

class ModerationSystem
{
    public function __construct(
        private Anthropic $claude,
        private ContentAnalyzer $analyzer,
        private PolicyEngine $policyEngine,
        private ModerationQueue $queue,
        private AuditLogger $auditLogger
    ) {}

    /**
     * Moderate content in real-time
     */
    public function moderateContent(
        string $content,
        string $contentType = 'text',
        array $context = []
    ): ModerationResult {
        // Analyze content
        $analysis = $this->analyzer->analyze($content, $contentType);

        // Check against policies
        $violations = $this->policyEngine->checkViolations($analysis, $context);

        // Determine action
        $action = $this->determineAction($violations, $analysis);

        // Create result
        $result = new ModerationResult(
            approved: $action->approved,
            violations: $violations,
            severity: $action->severity,
            action: $action->type,
            explanation: $action->explanation,
            confidence: $analysis->confidence
        );

        // Queue for human review if needed
        if ($action->requiresHumanReview) {
            $this->queue->add($content, $result, $context);
        }

        // Log moderation decision
        $this->auditLogger->log($content, $result, $action);

        return $result;
    }

    /**
     * Batch moderate multiple items
     */
    public function moderateBatch(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $results[$item['id']] = $this->moderateContent(
                content: $item['content'],
                contentType: $item['type'] ?? 'text',
                context: $item['context'] ?? []
            );
        }

        return $results;
    }

    private function determineAction(
        array $violations,
        ContentAnalysis $analysis
    ): ModerationAction {
        if (empty($violations)) {
            return new ModerationAction(
                approved: true,
                type: 'approve',
                severity: 'none',
                requiresHumanReview: false,
                explanation: 'Content meets all guidelines'
            );
        }

        // Calculate overall severity
        $maxSeverity = max(array_map(fn($v) => $v->severityScore, $violations));

        // Critical violations = immediate block
        if ($maxSeverity >= 0.9) {
            return new ModerationAction(
                approved: false,
                type: 'block',
                severity: 'critical',
                requiresHumanReview: false,
                explanation: $this->buildExplanation($violations)
            );
        }

        // High severity = block + human review
        if ($maxSeverity >= 0.7) {
            return new ModerationAction(
                approved: false,
                type: 'block',
                severity: 'high',
                requiresHumanReview: true,
                explanation: $this->buildExplanation($violations)
            );
        }

        // Medium severity = flag for review
        if ($maxSeverity >= 0.4) {
            return new ModerationAction(
                approved: true,
                type: 'flag',
                severity: 'medium',
                requiresHumanReview: true,
                explanation: $this->buildExplanation($violations)
            );
        }

        // Low severity = approve with warning
        return new ModerationAction(
            approved: true,
            type: 'warn',
            severity: 'low',
            requiresHumanReview: false,
            explanation: $this->buildExplanation($violations)
        );
    }

    private function buildExplanation(array $violations): string
    {
        $reasons = array_map(
            fn($v) => "{$v->category}: {$v->reason}",
            $violations
        );
        return implode('; ', $reasons);
    }
}
```

## Content Analyzer

```php
<?php
# filename: src/Moderation/ContentAnalyzer.php
declare(strict_types=1);

namespace App\Moderation;

use Anthropic\Anthropic;

class ContentAnalyzer
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Analyze content for policy violations
     */
    public function analyze(string $content, string $contentType = 'text'): ContentAnalysis
    {
        $prompt = $this->buildAnalysisPrompt($content, $contentType);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'temperature' => 0.2,
            'system' => $this->getAnalysisSystemPrompt(),
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $this->parseAnalysis($response->content[0]->text);
    }

    private function buildAnalysisPrompt(string $content, string $contentType): string
    {
        return <<<PROMPT
Analyze this content for moderation purposes.

Content Type: {$contentType}

Content:
{$content}

Analyze for:

1. **Toxic Language**
   - Hate speech
   - Harassment or bullying
   - Threats or violence
   - Discriminatory language

2. **Spam & Manipulation**
   - Spam or repetitive content
   - Phishing or scams
   - Malicious links
   - Vote manipulation

3. **Personal Information**
   - PII (names, addresses, SSN, etc.)
   - Phone numbers
   - Email addresses
   - Credit card information

4. **Inappropriate Content**
   - Sexual content
   - Graphic violence
   - Self-harm content
   - Illegal activities

5. **Misinformation**
   - False medical claims
   - Conspiracy theories
   - Misleading information

6. **Copyright & IP**
   - Copyrighted material
   - Trademark violations

Return JSON:
{
  "violations": [
    {
      "category": "category name",
      "type": "specific violation type",
      "severity": 0.0 to 1.0,
      "confidence": 0.0 to 1.0,
      "reason": "explanation",
      "evidence": "specific text excerpt",
      "recommendation": "approve|flag|block|review"
    }
  ],
  "pii_detected": [
    {
      "type": "email|phone|ssn|address|etc",
      "value": "detected value (redacted)",
      "location": "position in text"
    }
  ],
  "overall_safety_score": 0.0 to 1.0,
  "requires_human_review": boolean,
  "suggested_action": "approve|flag|block",
  "context_notes": "relevant context or nuances"
}

Return ONLY valid JSON.
PROMPT;
    }

    private function getAnalysisSystemPrompt(): string
    {
        return <<<SYSTEM
You are a content moderation expert analyzing user-generated content.

Your analysis must be:
- Objective and unbiased
- Context-aware (distinguish satire, quotes, educational content)
- Culturally sensitive
- Consistent with platform policies
- Detailed with specific examples

Severity Scoring:
- 0.0-0.3: Minor issues, likely acceptable
- 0.4-0.6: Moderate concerns, flag for review
- 0.7-0.8: Serious violations, likely block
- 0.9-1.0: Severe violations, immediate block

Consider:
1. Intent and context
2. Target audience
3. Potential harm
4. Legal implications
5. Community standards

Be especially careful with:
- False positives on legitimate discussion
- Cultural and linguistic nuances
- Satire and sarcasm
- Educational or news content
- Quotes or references

Always err on the side of caution for:
- Child safety
- Violence or threats
- Illegal activity
- PII exposure
SYSTEM;
    }

    private function parseAnalysis(string $jsonText): ContentAnalysis
    {
        // Extract JSON from response
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            $data = json_decode($matches[0], true);
            return new ContentAnalysis($data);
        }

        throw new \RuntimeException('Failed to parse analysis');
    }
}
```

## Toxic Language Detection

```php
<?php
# filename: src/Moderation/ToxicityDetector.php
declare(strict_types=1);

namespace App\Moderation;

use Anthropic\Anthropic;

class ToxicityDetector
{
    private array $toxicPatterns = [
        'profanity' => [
            'severity' => 0.4,
            'patterns' => ['fuck', 'shit', 'damn'] // Simplified example
        ],
        'slurs' => [
            'severity' => 0.9,
            'patterns' => ['<racial_slur>', '<homophobic_slur>'] // Use actual detection
        ],
        'threats' => [
            'severity' => 0.95,
            'patterns' => ['kill you', 'hurt you', 'find you']
        ]
    ];

    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Detect toxic language with context awareness
     */
    public function detect(string $text): ToxicityReport
    {
        // Quick pattern check first
        $patternMatches = $this->patternCheck($text);

        // Deep analysis with Claude for context
        $aiAnalysis = $this->analyzeContext($text, $patternMatches);

        return new ToxicityReport(
            isToxic: $aiAnalysis['is_toxic'] ?? false,
            toxicityScore: $aiAnalysis['toxicity_score'] ?? 0.0,
            categories: $aiAnalysis['categories'] ?? [],
            targetedGroups: $aiAnalysis['targeted_groups'] ?? [],
            contextualFactors: $aiAnalysis['context'] ?? [],
            recommendation: $aiAnalysis['recommendation'] ?? 'approve'
        );
    }

    private function patternCheck(string $text): array
    {
        $matches = [];
        $lowerText = strtolower($text);

        foreach ($this->toxicPatterns as $category => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (str_contains($lowerText, strtolower($pattern))) {
                    $matches[] = [
                        'category' => $category,
                        'pattern' => $pattern,
                        'severity' => $data['severity']
                    ];
                }
            }
        }

        return $matches;
    }

    private function analyzeContext(string $text, array $patternMatches): array
    {
        $patternsText = empty($patternMatches)
            ? 'None detected'
            : json_encode($patternMatches);

        $prompt = <<<PROMPT
Analyze this text for toxic language with full context awareness.

Text: {$text}

Pattern matches found: {$patternsText}

Consider:
1. Is this actually toxic or is it:
   - A quote or reference?
   - Educational discussion?
   - Reclaimed language by the target group?
   - Satire or criticism of toxicity?
   - Song lyrics or artistic expression?

2. If toxic, identify:
   - Type (harassment, hate speech, threats, etc.)
   - Targeted groups
   - Severity (0.0 to 1.0)
   - Intent

Return JSON:
{
  "is_toxic": boolean,
  "toxicity_score": 0.0 to 1.0,
  "categories": ["harassment", "hate_speech", "threats", etc.],
  "targeted_groups": ["group1", "group2"],
  "context": "explanation of context",
  "is_false_positive": boolean,
  "false_positive_reason": "if applicable",
  "recommendation": "approve|flag|block"
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }
}
```

## PII Detection and Redaction

```php
<?php
# filename: src/Moderation/PIIDetector.php
declare(strict_types=1);

namespace App\Moderation;

use Anthropic\Anthropic;

class PIIDetector
{
    private array $patterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'credit_card' => '/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/',
        'ip_address' => '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/'
    ];

    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Detect and categorize PII
     */
    public function detect(string $text): PIIReport
    {
        // Pattern-based detection
        $patternMatches = $this->patternDetect($text);

        // AI-enhanced detection for complex cases
        $aiMatches = $this->aiDetect($text);

        // Merge results
        $allMatches = array_merge($patternMatches, $aiMatches);

        return new PIIReport(
            hasPII: !empty($allMatches),
            items: $allMatches,
            riskLevel: $this->calculateRiskLevel($allMatches)
        );
    }

    /**
     * Redact PII from text
     */
    public function redact(string $text, PIIReport $report): string
    {
        $redacted = $text;

        foreach ($report->items as $item) {
            $replacement = match($item['type']) {
                'email' => '[EMAIL REDACTED]',
                'phone' => '[PHONE REDACTED]',
                'ssn' => '[SSN REDACTED]',
                'credit_card' => '[CARD REDACTED]',
                'address' => '[ADDRESS REDACTED]',
                default => '[PII REDACTED]'
            };

            $redacted = str_replace($item['value'], $replacement, $redacted);
        }

        return $redacted;
    }

    private function patternDetect(string $text): array
    {
        $matches = [];

        foreach ($this->patterns as $type => $pattern) {
            if (preg_match_all($pattern, $text, $found)) {
                foreach ($found[0] as $value) {
                    $matches[] = [
                        'type' => $type,
                        'value' => $value,
                        'detection_method' => 'pattern',
                        'confidence' => 0.9
                    ];
                }
            }
        }

        return $matches;
    }

    private function aiDetect(string $text): array
    {
        $prompt = <<<PROMPT
Detect personally identifiable information (PII) in this text.

Text: {$text}

Identify:
- Full names (with context that makes them identifiable)
- Home addresses
- Government ID numbers
- Financial information
- Medical information
- Login credentials
- Any other PII

Return JSON array:
[
  {
    "type": "name|address|ssn|medical|financial|etc",
    "value": "the PII value (can be partial for demonstration)",
    "confidence": 0.0 to 1.0,
    "context": "why this is PII"
  }
]

Return ONLY valid JSON array.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-20250514',
            'max_tokens' => 1024,
            'temperature' => 0.1,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\[.*\]/s', $jsonText, $matches)) {
            $items = json_decode($matches[0], true) ?? [];
            foreach ($items as &$item) {
                $item['detection_method'] = 'ai';
            }
            return $items;
        }

        return [];
    }

    private function calculateRiskLevel(array $matches): string
    {
        if (empty($matches)) {
            return 'none';
        }

        $highRiskTypes = ['ssn', 'credit_card', 'medical', 'financial'];
        $hasHighRisk = false;

        foreach ($matches as $match) {
            if (in_array($match['type'], $highRiskTypes)) {
                $hasHighRisk = true;
                break;
            }
        }

        if ($hasHighRisk) {
            return 'critical';
        }

        if (count($matches) > 3) {
            return 'high';
        }

        if (count($matches) > 1) {
            return 'medium';
        }

        return 'low';
    }
}
```

## Spam Detection

```php
<?php
# filename: src/Moderation/SpamDetector.php
declare(strict_types=1);

namespace App\Moderation;

use Anthropic\Anthropic;

class SpamDetector
{
    public function __construct(
        private Anthropic $claude,
        private \PDO $db
    ) {}

    /**
     * Detect spam with behavioral analysis
     */
    public function detect(
        string $content,
        string $userId,
        array $context = []
    ): SpamReport {
        // Check user behavior
        $userBehavior = $this->analyzeUserBehavior($userId);

        // Analyze content
        $contentAnalysis = $this->analyzeContent($content);

        // Calculate spam score
        $spamScore = $this->calculateSpamScore($contentAnalysis, $userBehavior, $context);

        return new SpamReport(
            isSpam: $spamScore > 0.7,
            spamScore: $spamScore,
            indicators: array_merge($contentAnalysis['indicators'], $userBehavior['indicators']),
            type: $contentAnalysis['spam_type'] ?? 'unknown',
            recommendation: $spamScore > 0.7 ? 'block' : ($spamScore > 0.4 ? 'flag' : 'approve')
        );
    }

    private function analyzeContent(string $content): array
    {
        $prompt = <<<PROMPT
Analyze this content for spam characteristics.

Content: {$content}

Check for:
1. Excessive links or URLs
2. Repetitive text
3. Keywords associated with spam (crypto, pills, "click here", etc.)
4. Suspicious formatting (all caps, excessive punctuation)
5. Promotional language
6. Phishing attempts
7. Malicious links

Return JSON:
{
  "is_likely_spam": boolean,
  "spam_score": 0.0 to 1.0,
  "spam_type": "promotional|phishing|link_spam|repetitive|malicious|legitimate",
  "indicators": ["indicator1", "indicator2"],
  "reasoning": "explanation"
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-20250514',
            'max_tokens' => 512,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return ['is_likely_spam' => false, 'spam_score' => 0.0, 'indicators' => []];
    }

    private function analyzeUserBehavior(string $userId): array
    {
        $indicators = [];

        // Check posting frequency
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count
             FROM user_content
             WHERE user_id = :user_id
             AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
        $stmt->execute([':user_id' => $userId]);
        $recentPosts = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        if ($recentPosts > 10) {
            $indicators[] = 'high_posting_frequency';
        }

        // Check account age
        $stmt = $this->db->prepare(
            "SELECT DATEDIFF(NOW(), created_at) as age_days
             FROM users
             WHERE id = :user_id"
        );
        $stmt->execute([':user_id' => $userId]);
        $accountAge = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['age_days'];

        if ($accountAge < 1) {
            $indicators[] = 'new_account';
        }

        // Check for similar content
        $stmt = $this->db->prepare(
            "SELECT content
             FROM user_content
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT 5"
        );
        $stmt->execute([':user_id' => $userId]);
        $recentContent = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if ($this->hasRepetitiveContent($recentContent)) {
            $indicators[] = 'repetitive_content';
        }

        return [
            'indicators' => $indicators,
            'recent_posts' => $recentPosts,
            'account_age' => $accountAge
        ];
    }

    private function calculateSpamScore(
        array $contentAnalysis,
        array $userBehavior,
        array $context
    ): float {
        $score = $contentAnalysis['spam_score'] ?? 0.0;

        // Boost score based on user behavior
        if (count($userBehavior['indicators']) > 2) {
            $score += 0.3;
        } elseif (count($userBehavior['indicators']) > 0) {
            $score += 0.15;
        }

        // Consider context
        if (isset($context['reported_by_users']) && $context['reported_by_users'] > 2) {
            $score += 0.2;
        }

        return min($score, 1.0);
    }

    private function hasRepetitiveContent(array $contents): bool
    {
        if (count($contents) < 2) {
            return false;
        }

        for ($i = 0; $i < count($contents) - 1; $i++) {
            for ($j = $i + 1; $j < count($contents); $j++) {
                similar_text($contents[$i], $contents[$j], $percent);
                if ($percent > 80) {
                    return true;
                }
            }
        }

        return false;
    }
}
```

## Moderation Queue System

```php
<?php
# filename: src/Moderation/ModerationQueue.php
declare(strict_types=1);

namespace App\Moderation;

class ModerationQueue
{
    public function __construct(
        private \PDO $db,
        private \Redis $redis
    ) {}

    /**
     * Add item to moderation queue
     */
    public function add(
        string $content,
        ModerationResult $result,
        array $context = []
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO moderation_queue
             (content, content_type, user_id, violations, severity, context, created_at, status)
             VALUES (:content, :type, :user_id, :violations, :severity, :context, NOW(), 'pending')"
        );

        $stmt->execute([
            ':content' => $content,
            ':type' => $context['content_type'] ?? 'text',
            ':user_id' => $context['user_id'] ?? null,
            ':violations' => json_encode($result->violations),
            ':severity' => $result->severity,
            ':context' => json_encode($context)
        ]);

        $queueId = (int)$this->db->lastInsertId();

        // Add to Redis for real-time processing
        $this->redis->zadd(
            'moderation:queue',
            $this->getSeverityScore($result->severity),
            (string)$queueId
        );

        return $queueId;
    }

    /**
     * Get next item from queue
     */
    public function getNext(string $moderatorId): ?array
    {
        // Get highest priority item
        $items = $this->redis->zrevrange('moderation:queue', 0, 0);

        if (empty($items)) {
            return null;
        }

        $queueId = (int)$items[0];

        // Claim the item
        if ($this->claimItem($queueId, $moderatorId)) {
            return $this->getItem($queueId);
        }

        return null;
    }

    /**
     * Resolve moderation item
     */
    public function resolve(
        int $queueId,
        string $moderatorId,
        string $decision,
        string $notes = ''
    ): void {
        $stmt = $this->db->prepare(
            "UPDATE moderation_queue
             SET status = :status,
                 moderator_id = :moderator_id,
                 decision = :decision,
                 moderator_notes = :notes,
                 resolved_at = NOW()
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $queueId,
            ':status' => 'resolved',
            ':moderator_id' => $moderatorId,
            ':decision' => $decision,
            ':notes' => $notes
        ]);

        // Remove from Redis queue
        $this->redis->zrem('moderation:queue', (string)$queueId);
    }

    /**
     * Get queue statistics
     */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_review' THEN 1 ELSE 0 END) as in_review,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_resolution_minutes
             FROM moderation_queue
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function claimItem(int $queueId, string $moderatorId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE moderation_queue
             SET status = 'in_review',
                 moderator_id = :moderator_id,
                 claimed_at = NOW()
             WHERE id = :id
             AND status = 'pending'"
        );

        $stmt->execute([
            ':id' => $queueId,
            ':moderator_id' => $moderatorId
        ]);

        return $stmt->rowCount() > 0;
    }

    private function getItem(int $queueId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_queue WHERE id = :id"
        );
        $stmt->execute([':id' => $queueId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function getSeverityScore(string $severity): float
    {
        return match($severity) {
            'critical' => 1000,
            'high' => 750,
            'medium' => 500,
            'low' => 250,
            default => 0
        };
    }
}
```

## Policy Engine

```php
<?php
# filename: src/Moderation/PolicyEngine.php
declare(strict_types=1);

namespace App\Moderation;

class PolicyEngine
{
    private array $policies;

    public function __construct(array $policies = [])
    {
        $this->policies = $policies ?: $this->getDefaultPolicies();
    }

    /**
     * Check content against policies
     */
    public function checkViolations(
        ContentAnalysis $analysis,
        array $context = []
    ): array {
        $violations = [];

        foreach ($analysis->violations as $violation) {
            $policy = $this->findMatchingPolicy($violation);

            if ($policy && $this->violatesPolicy($violation, $policy, $context)) {
                $violations[] = new PolicyViolation(
                    policy: $policy['name'],
                    category: $violation['category'],
                    type: $violation['type'],
                    severity: $violation['severity'],
                    severityScore: $this->calculateSeverityScore($violation, $policy),
                    reason: $violation['reason'],
                    evidence: $violation['evidence'] ?? null,
                    action: $policy['action']
                );
            }
        }

        return $violations;
    }

    private function findMatchingPolicy(array $violation): ?array
    {
        foreach ($this->policies as $policy) {
            if ($this->matchesPolicy($violation, $policy)) {
                return $policy;
            }
        }
        return null;
    }

    private function matchesPolicy(array $violation, array $policy): bool
    {
        // Check if violation category matches policy
        if (isset($policy['categories']) &&
            !in_array($violation['category'], $policy['categories'])) {
            return false;
        }

        // Check minimum severity threshold
        if (isset($policy['min_severity']) &&
            $violation['severity'] < $policy['min_severity']) {
            return false;
        }

        return true;
    }

    private function violatesPolicy(
        array $violation,
        array $policy,
        array $context
    ): bool {
        // Apply policy-specific rules
        if (isset($policy['rules'])) {
            foreach ($policy['rules'] as $rule) {
                if (!$this->checkRule($rule, $violation, $context)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function checkRule(string $rule, array $violation, array $context): bool
    {
        // Implement custom rule logic
        return match($rule) {
            'requires_high_confidence' => ($violation['confidence'] ?? 0) > 0.8,
            'context_aware' => $this->isContextAppropriate($violation, $context),
            default => true
        };
    }

    private function isContextAppropriate(array $violation, array $context): bool
    {
        // Check if content is appropriate for context
        // E.g., medical discussion in health forum, etc.
        return true; // Simplified
    }

    private function calculateSeverityScore(array $violation, array $policy): float
    {
        $baseScore = $violation['severity'];

        // Apply policy modifiers
        if (isset($policy['severity_multiplier'])) {
            $baseScore *= $policy['severity_multiplier'];
        }

        return min($baseScore, 1.0);
    }

    private function getDefaultPolicies(): array
    {
        return [
            [
                'name' => 'hate_speech',
                'categories' => ['hate_speech', 'discrimination'],
                'min_severity' => 0.7,
                'action' => 'block',
                'severity_multiplier' => 1.2
            ],
            [
                'name' => 'threats',
                'categories' => ['threats', 'violence'],
                'min_severity' => 0.6,
                'action' => 'block',
                'severity_multiplier' => 1.5
            ],
            [
                'name' => 'spam',
                'categories' => ['spam'],
                'min_severity' => 0.7,
                'action' => 'block'
            ],
            [
                'name' => 'pii',
                'categories' => ['pii'],
                'min_severity' => 0.5,
                'action' => 'flag'
            ]
        ];
    }
}
```

## Complete Moderation API

```php
<?php
# filename: api/moderate.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Moderation\ModerationSystem;
use App\Moderation\ContentAnalyzer;
use App\Moderation\PolicyEngine;
use App\Moderation\ModerationQueue;
use App\Moderation\AuditLogger;

header('Content-Type: application/json');

// Initialize
$db = new PDO(getenv('DATABASE_DSN'));
$redis = new Redis();
$redis->connect('localhost', 6379);

$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$analyzer = new ContentAnalyzer($claude);
$policyEngine = new PolicyEngine();
$queue = new ModerationQueue($db, $redis);
$auditLogger = new AuditLogger($db);

$moderationSystem = new ModerationSystem(
    claude: $claude,
    analyzer: $analyzer,
    policyEngine: $policyEngine,
    queue: $queue,
    auditLogger: $auditLogger
);

// Handle request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['content'])) {
    http_response_code(400);
    echo json_encode(['error' => 'content required']);
    exit;
}

try {
    $result = $moderationSystem->moderateContent(
        content: $input['content'],
        contentType: $input['type'] ?? 'text',
        context: $input['context'] ?? []
    );

    echo json_encode([
        'approved' => $result->approved,
        'action' => $result->action,
        'severity' => $result->severity,
        'violations' => array_map(fn($v) => [
            'category' => $v->category,
            'type' => $v->type,
            'reason' => $v->reason
        ], $result->violations),
        'explanation' => $result->explanation,
        'confidence' => $result->confidence
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

## Data Structures

```php
<?php
# filename: src/Moderation/DataStructures.php
declare(strict_types=1);

namespace App\Moderation;

readonly class ModerationResult
{
    public function __construct(
        public bool $approved,
        public array $violations,
        public string $severity,
        public string $action,
        public string $explanation,
        public float $confidence
    ) {}
}

readonly class ModerationAction
{
    public function __construct(
        public bool $approved,
        public string $type,
        public string $severity,
        public bool $requiresHumanReview,
        public string $explanation
    ) {}
}

class ContentAnalysis
{
    public array $violations;
    public array $piiDetected;
    public float $safetyScore;
    public bool $requiresReview;
    public string $suggestedAction;
    public float $confidence;

    public function __construct(array $data)
    {
        $this->violations = $data['violations'] ?? [];
        $this->piiDetected = $data['pii_detected'] ?? [];
        $this->safetyScore = $data['overall_safety_score'] ?? 1.0;
        $this->requiresReview = $data['requires_human_review'] ?? false;
        $this->suggestedAction = $data['suggested_action'] ?? 'approve';
        $this->confidence = $this->calculateConfidence($data);
    }

    private function calculateConfidence(array $data): float
    {
        if (empty($data['violations'])) {
            return 0.95;
        }

        $confidences = array_column($data['violations'], 'confidence');
        return !empty($confidences) ? array_sum($confidences) / count($confidences) : 0.5;
    }
}

readonly class PolicyViolation
{
    public function __construct(
        public string $policy,
        public string $category,
        public string $type,
        public float $severity,
        public float $severityScore,
        public string $reason,
        public ?string $evidence,
        public string $action
    ) {}
}

readonly class ToxicityReport
{
    public function __construct(
        public bool $isToxic,
        public float $toxicityScore,
        public array $categories,
        public array $targetedGroups,
        public array $contextualFactors,
        public string $recommendation
    ) {}
}

readonly class PIIReport
{
    public function __construct(
        public bool $hasPII,
        public array $items,
        public string $riskLevel
    ) {}
}

readonly class SpamReport
{
    public function __construct(
        public bool $isSpam,
        public float $spamScore,
        public array $indicators,
        public string $type,
        public string $recommendation
    ) {}
}
```

## Key Takeaways

- ✓ AI-powered moderation handles scale and context better than pure pattern matching
- ✓ Multi-layered approach (patterns + AI) provides accuracy and speed
- ✓ PII detection and redaction protect user privacy and legal compliance
- ✓ Sentiment and context analysis reduce false positives
- ✓ Human review for edge cases ensures fairness and accuracy
- ✓ Audit trails provide accountability and appeal processes
- ✓ Priority queues ensure critical content gets immediate attention
- ✓ Policy engines allow flexible, customizable rules
- ✓ Real-time and batch processing support different use cases
- ✓ Continuous learning from moderator decisions improves accuracy

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="29"
  label="You've built a comprehensive content moderation system!"
/>

---

Continue to [Chapter 30: Data Extraction and Analysis](/series/claude-php-developers/chapters/30-data-extraction) to build intelligent data processing pipelines.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 29 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-29)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-29
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php -S localhost:8000 api/moderate.php
```
