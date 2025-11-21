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

## Objectives

By the end of this chapter, you will:

- **Understand** how to build a comprehensive content moderation system using Claude's context-aware analysis
- **Implement** multi-layered content analysis that detects toxic language, spam, PII, and policy violations
- **Create** a policy engine that enforces customizable community guidelines with severity scoring
- **Build** PII detection and redaction capabilities to protect user privacy and ensure legal compliance
- **Design** a moderation queue system with priority-based processing and human review workflows
- **Develop** spam detection that combines content analysis with behavioral pattern recognition
- **Integrate** audit logging and analytics for accountability and continuous improvement

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
            $action->approved,
            $violations,
            $action->severity,
            $action->type,
            $action->explanation,
            $analysis->confidence
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
                $item['content'],
                $item['type'] ?? 'text',
                $item['context'] ?? []
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
            'model' => 'claude-sonnet-4-5-20250929',
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
            'model' => 'claude-sonnet-4-5-20250929',
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
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'temperature' => 0.1,
            'system' => 'You are a PII detection expert. Identify all personally identifiable information accurately.',
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
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 512,
            'temperature' => 0.2,
            'system' => 'You are a spam detection expert. Analyze content for spam characteristics and behavioral patterns.',
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
                    $policy['name'],
                    $violation['category'],
                    $violation['type'],
                    $violation['severity'],
                    $this->calculateSeverityScore($violation, $policy),
                    $violation['reason'],
                    $violation['evidence'] ?? null,
                    $policy['action']
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

## Audit Logger

```php
<?php
# filename: src/Moderation/AuditLogger.php
declare(strict_types=1);

namespace App\Moderation;

class AuditLogger
{
    public function __construct(
        private \PDO $db
    ) {}

    /**
     * Log moderation decision for audit trail
     */
    public function log(
        string $content,
        ModerationResult $result,
        ModerationAction $action,
        array $context = []
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO moderation_audit_log
             (content_hash, content_preview, approved, action, severity, violations,
              explanation, confidence, context, user_id, ip_address, created_at)
             VALUES (:hash, :preview, :approved, :action, :severity, :violations,
                     :explanation, :confidence, :context, :user_id, :ip, NOW())"
        );

        $contentHash = hash('sha256', $content);
        $contentPreview = mb_substr($content, 0, 200);

        $stmt->execute([
            ':hash' => $contentHash,
            ':preview' => $contentPreview,
            ':approved' => $result->approved ? 1 : 0,
            ':action' => $action->type,
            ':severity' => $result->severity,
            ':violations' => json_encode($result->violations),
            ':explanation' => $result->explanation,
            ':confidence' => $result->confidence,
            ':context' => json_encode($context),
            ':user_id' => $context['user_id'] ?? null,
            ':ip' => $context['ip_address'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get audit log entries for a user
     */
    public function getUserLog(string $userId, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_audit_log
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get audit statistics
     */
    public function getStats(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (isset($filters['user_id'])) {
            $where[] = "user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (isset($filters['date_from'])) {
            $where[] = "created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $where[] = "created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_decisions,
                SUM(CASE WHEN approved = 1 THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN approved = 0 THEN 1 ELSE 0 END) as blocked_count,
                AVG(confidence) as avg_confidence,
                COUNT(DISTINCT user_id) as unique_users
             FROM moderation_audit_log
             {$whereClause}"
        );

        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Search audit log by content hash or preview
     */
    public function search(string $query, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_audit_log
             WHERE content_preview LIKE :query
             OR content_hash = :hash
             ORDER BY created_at DESC
             LIMIT :limit"
        );

        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':hash', hash('sha256', $query));
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

## Moderator Workflow System

```php
<?php
# filename: src/Moderation/ModeratorWorkflow.php
declare(strict_types=1);

namespace App\Moderation;

class ModeratorWorkflow
{
    public function __construct(
        private \PDO $db,
        private ModerationQueue $queue,
        private AuditLogger $auditLogger
    ) {}

    /**
     * Get moderator dashboard with queued items
     */
    public function getDashboard(string $moderatorId, array $filters = []): array
    {
        return [
            'statistics' => $this->getStatistics($moderatorId),
            'pending_queue' => $this->getPendingQueue($filters),
            'recent_decisions' => $this->getRecentDecisions($moderatorId),
            'performance_metrics' => $this->getPerformanceMetrics($moderatorId)
        ];
    }

    /**
     * Get moderator statistics
     */
    private function getStatistics(string $moderatorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_decisions,
                AVG(TIMESTAMPDIFF(MINUTE, claimed_at, resolved_at)) as avg_resolution_minutes,
                SUM(CASE WHEN decision = 'approve' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN decision = 'reject' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN decision = 'flag' THEN 1 ELSE 0 END) as flagged
             FROM moderation_queue
             WHERE moderator_id = :moderator_id
             AND status = 'resolved'
             AND resolved_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute([':moderator_id' => $moderatorId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get pending items for moderator review
     */
    private function getPendingQueue(array $filters = []): array
    {
        $where = ['status = ?'];
        $params = ['in_review'];

        if (isset($filters['severity'])) {
            $where[] = 'severity = ?';
            $params[] = $filters['severity'];
        }

        if (isset($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_queue
             WHERE {$whereClause}
             ORDER BY severity DESC, created_at ASC
             LIMIT 50"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get moderator's recent decisions
     */
    private function getRecentDecisions(string $moderatorId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM moderation_queue
             WHERE moderator_id = :moderator_id
             AND status = 'resolved'
             ORDER BY resolved_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':moderator_id', $moderatorId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get moderator performance metrics
     */
    private function getPerformanceMetrics(string $moderatorId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_items_reviewed,
                COUNT(DISTINCT user_id) as unique_users_reviewed,
                COUNT(DISTINCT DATE(resolved_at)) as days_active,
                MIN(TIMESTAMPDIFF(MINUTE, claimed_at, resolved_at)) as min_review_time,
                MAX(TIMESTAMPDIFF(MINUTE, claimed_at, resolved_at)) as max_review_time
             FROM moderation_queue
             WHERE moderator_id = :moderator_id
             AND status = 'resolved'"
        );
        $stmt->execute([':moderator_id' => $moderatorId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Submit moderator decision
     */
    public function submitDecision(
        int $queueId,
        string $moderatorId,
        string $decision,
        string $notes = '',
        array $reasoning = []
    ): bool {
        try {
            $this->db->beginTransaction();

            // Update queue item
            $stmt = $this->db->prepare(
                "UPDATE moderation_queue
                 SET decision = :decision,
                     status = 'resolved',
                     moderator_id = :moderator_id,
                     moderator_notes = :notes,
                     resolved_at = NOW()
                 WHERE id = :id"
            );

            $stmt->execute([
                ':decision' => $decision,
                ':moderator_id' => $moderatorId,
                ':notes' => $notes,
                ':id' => $queueId
            ]);

            // Log in audit trail
            $this->auditLogger->log(
                $this->getContentPreview($queueId),
                new ModerationResult(
                    $decision === 'approve',
                    [],
                    'none',
                    $decision,
                    "Moderator decision: {$notes}",
                    1.0
                ),
                new ModerationAction(
                    $decision === 'approve',
                    $decision,
                    'none',
                    false,
                    "Moderated by {$moderatorId}"
                ),
                context: ['moderator_reasoning' => $reasoning]
            );

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Decision submission error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Appeal moderation decision
     */
    public function createAppeal(
        int $queueId,
        string $userId,
        string $reason,
        string $evidence = ''
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO moderation_appeals
             (queue_id, user_id, reason, evidence, status, created_at)
             VALUES (:queue_id, :user_id, :reason, :evidence, 'pending', NOW())"
        );

        $stmt->execute([
            ':queue_id' => $queueId,
            ':user_id' => $userId,
            ':reason' => $reason,
            ':evidence' => $evidence
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get appeal details
     */
    public function getAppeal(int $appealId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT mq.*, ma.reason, ma.evidence, ma.status as appeal_status
             FROM moderation_appeals ma
             JOIN moderation_queue mq ON ma.queue_id = mq.id
             WHERE ma.id = :id"
        );
        $stmt->execute([':id' => $appealId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getContentPreview(int $queueId): string
    {
        $stmt = $this->db->prepare(
            "SELECT content FROM moderation_queue WHERE id = :id"
        );
        $stmt->execute([':id' => $queueId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['content'] ?? '';
    }
}
```

## Complete Moderation API

```php
<?php
# filename: api/moderate.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
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

$claude = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$analyzer = new ContentAnalyzer($claude);
$policyEngine = new PolicyEngine();
$queue = new ModerationQueue($db, $redis);
$auditLogger = new AuditLogger($db);

$moderationSystem = new ModerationSystem(
    $claude,
    $analyzer,
    $policyEngine,
    $queue,
    $auditLogger
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
        'content' => $input['content'],
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

## Database Schema

```sql
-- Moderation queue table
CREATE TABLE moderation_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    content_type VARCHAR(50) DEFAULT 'text',
    user_id VARCHAR(255) NULL,
    violations JSON NULL,
    severity ENUM('none', 'low', 'medium', 'high', 'critical') DEFAULT 'none',
    context JSON NULL,
    status ENUM('pending', 'in_review', 'resolved') DEFAULT 'pending',
    moderator_id VARCHAR(255) NULL,
    decision ENUM('approve', 'reject', 'flag') NULL,
    moderator_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    claimed_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table
CREATE TABLE moderation_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_hash VARCHAR(64) NOT NULL,
    content_preview VARCHAR(200) NOT NULL,
    approved BOOLEAN NOT NULL,
    action VARCHAR(50) NOT NULL,
    severity ENUM('none', 'low', 'medium', 'high', 'critical') NOT NULL,
    violations JSON NULL,
    explanation TEXT NULL,
    confidence DECIMAL(3,2) DEFAULT 0.00,
    context JSON NULL,
    user_id VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_content_hash (content_hash),
    INDEX idx_user_id (user_id),
    INDEX idx_approved (approved),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User content table (for spam detection)
CREATE TABLE user_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    content_type VARCHAR(50) DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table (for account age tracking)
CREATE TABLE users (
    id VARCHAR(255) PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Moderation appeals table
CREATE TABLE moderation_appeals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue_id INT NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    evidence TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by VARCHAR(255) NULL,
    reviewed_at TIMESTAMP NULL,
    reviewer_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (queue_id) REFERENCES moderation_queue(id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Analytics and Metrics

```php
<?php
# filename: src/Moderation/ModerationAnalytics.php
declare(strict_types=1);

namespace App\Moderation;

class ModerationAnalytics
{
    public function __construct(
        private \PDO $db
    ) {}

    /**
     * Get comprehensive moderation metrics
     */
    public function getMetrics(\DateTime $startDate, \DateTime $endDate): array
    {
        return [
            'total_moderated' => $this->getTotalModerated($startDate, $endDate),
            'approval_rate' => $this->getApprovalRate($startDate, $endDate),
            'block_rate' => $this->getBlockRate($startDate, $endDate),
            'average_confidence' => $this->getAverageConfidence($startDate, $endDate),
            'violation_distribution' => $this->getViolationDistribution($startDate, $endDate),
            'severity_distribution' => $this->getSeverityDistribution($startDate, $endDate),
            'queue_stats' => $this->getQueueStats($startDate, $endDate),
            'top_violations' => $this->getTopViolations($startDate, $endDate),
            'pii_detections' => $this->getPIIDetections($startDate, $endDate),
            'spam_detections' => $this->getSpamDetections($startDate, $endDate)
        ];
    }

    private function getTotalModerated(\DateTime $start, \DateTime $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    private function getApprovalRate(\DateTime $start, \DateTime $end): float
    {
        $total = $this->getTotalModerated($start, $end);
        if ($total === 0) {
            return 0.0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             AND approved = 1"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        $approved = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        return ($approved / $total) * 100;
    }

    private function getBlockRate(\DateTime $start, \DateTime $end): float
    {
        $total = $this->getTotalModerated($start, $end);
        if ($total === 0) {
            return 0.0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             AND approved = 0"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        $blocked = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        return ($blocked / $total) * 100;
    }

    private function getAverageConfidence(\DateTime $start, \DateTime $end): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(confidence) as avg_confidence FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($result['avg_confidence'] ?? 0);
    }

    private function getViolationDistribution(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT violations FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             AND violations IS NOT NULL"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);

        $distribution = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $violations = json_decode($row['violations'], true) ?? [];
            foreach ($violations as $violation) {
                $category = $violation['category'] ?? 'unknown';
                $distribution[$category] = ($distribution[$category] ?? 0) + 1;
            }
        }

        arsort($distribution);
        return $distribution;
    }

    private function getSeverityDistribution(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT severity, COUNT(*) as count
             FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             GROUP BY severity"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);

        $distribution = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $distribution[$row['severity']] = (int)$row['count'];
        }

        return $distribution;
    }

    private function getQueueStats(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_review' THEN 1 ELSE 0 END) as in_review,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_resolution_minutes
             FROM moderation_queue
             WHERE created_at BETWEEN :start AND :end"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    private function getTopViolations(\DateTime $start, \DateTime $end, int $limit = 10): array
    {
        $distribution = $this->getViolationDistribution($start, $end);
        return array_slice($distribution, 0, $limit, true);
    }

    private function getPIIDetections(\DateTime $start, \DateTime $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             AND violations LIKE '%pii%'"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    private function getSpamDetections(\DateTime $start, \DateTime $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM moderation_audit_log
             WHERE created_at BETWEEN :start AND :end
             AND violations LIKE '%spam%'"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ]);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }
}
```

## Usage Examples

```php
<?php
# filename: examples/moderate-content.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Moderation\ModerationSystem;
use App\Moderation\ContentAnalyzer;
use App\Moderation\PolicyEngine;
use App\Moderation\ModerationQueue;
use App\Moderation\AuditLogger;
use App\Moderation\ToxicityDetector;
use App\Moderation\PIIDetector;
use App\Moderation\SpamDetector;

// Initialize components
$db = new PDO(getenv('DATABASE_DSN'));
$redis = new Redis();
$redis->connect('localhost', 6379);

$claude = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$analyzer = new ContentAnalyzer($claude);
$policyEngine = new PolicyEngine();
$queue = new ModerationQueue($db, $redis);
$auditLogger = new AuditLogger($db);

$moderationSystem = new ModerationSystem(
    $claude,
    $analyzer,
    $policyEngine,
    $queue,
    $auditLogger
);

// Example 1: Moderate text content
echo "Example 1: Moderate Text Content\n";
echo str_repeat('=', 50) . "\n";

$result = $moderationSystem->moderateContent(
    'content' => "This is a great product! I love it.",
    contentType: 'text',
    context: ['user_id' => 'user123', 'ip_address' => '192.168.1.1']
);

echo "Approved: " . ($result->approved ? 'Yes' : 'No') . "\n";
echo "Action: {$result->action}\n";
echo "Severity: {$result->severity}\n";
echo "Confidence: " . round($result->confidence * 100, 2) . "%\n";
echo "Explanation: {$result->explanation}\n";
echo "\n";

// Example 2: Detect toxic language
echo "Example 2: Detect Toxic Language\n";
echo str_repeat('=', 50) . "\n";

$toxicityDetector = new ToxicityDetector($claude);
$toxicityReport = $toxicityDetector->detect(
    "This is educational content discussing hate speech patterns."
);

echo "Is Toxic: " . ($toxicityReport->isToxic ? 'Yes' : 'No') . "\n";
echo "Toxicity Score: " . round($toxicityReport->toxicityScore * 100, 2) . "%\n";
echo "Categories: " . implode(', ', $toxicityReport->categories) . "\n";
echo "Recommendation: {$toxicityReport->recommendation}\n";
echo "\n";

// Example 3: Detect and redact PII
echo "Example 3: Detect and Redact PII\n";
echo str_repeat('=', 50) . "\n";

$piiDetector = new PIIDetector($claude);
$textWithPII = "Contact me at john.doe@example.com or call 555-123-4567";
$piiReport = $piiDetector->detect($textWithPII);

echo "Has PII: " . ($piiReport->hasPII ? 'Yes' : 'No') . "\n";
echo "Risk Level: {$piiReport->riskLevel}\n";
echo "Original: {$textWithPII}\n";

if ($piiReport->hasPII) {
    $redacted = $piiDetector->redact($textWithPII, $piiReport);
    echo "Redacted: {$redacted}\n";
}
echo "\n";

// Example 4: Detect spam
echo "Example 4: Detect Spam\n";
echo str_repeat('=', 50) . "\n";

$spamDetector = new SpamDetector($claude, $db);
$spamReport = $spamDetector->detect(
    'content' => "Click here now! Amazing deals! Buy now!",
    userId: 'user456',
    context: []
);

echo "Is Spam: " . ($spamReport->isSpam ? 'Yes' : 'No') . "\n";
echo "Spam Score: " . round($spamReport->spamScore * 100, 2) . "%\n";
echo "Type: {$spamReport->type}\n";
echo "Indicators: " . implode(', ', $spamReport->indicators) . "\n";
echo "\n";

// Example 5: Batch moderation
echo "Example 5: Batch Moderation\n";
echo str_repeat('=', 50) . "\n";

$items = [
    ['id' => '1', 'content' => 'Great post!', 'type' => 'text'],
    ['id' => '2', 'content' => 'Spam content here', 'type' => 'text'],
    ['id' => '3', 'content' => 'Normal discussion', 'type' => 'text']
];

$batchResults = $moderationSystem->moderateBatch($items);

foreach ($batchResults as $itemId => $result) {
    echo "Item {$itemId}: " . ($result->approved ? 'Approved' : 'Blocked') . "\n";
    echo "  Action: {$result->action}, Severity: {$result->severity}\n";
}
echo "\n";

// Example 6: Get queue statistics
echo "Example 6: Queue Statistics\n";
echo str_repeat('=', 50) . "\n";

$stats = $queue->getStats();
echo "Total Items: {$stats['total']}\n";
echo "Pending: {$stats['pending']}\n";
echo "In Review: {$stats['in_review']}\n";
echo "Resolved: {$stats['resolved']}\n";
echo "Avg Resolution Time: " . round($stats['avg_resolution_minutes'] ?? 0, 2) . " minutes\n";
```

## Data Structures

```php
<?php
# filename: src/Moderation/DataStructures.php
declare(strict_types=1);

namespace App\Moderation;

// Note: These classes would typically be in separate files in a real application

class ModerationResult
{
    public readonly bool $approved;
    public readonly array $violations;
    public readonly string $severity;
    public readonly string $action;
    public readonly string $explanation;
    public readonly float $confidence;

    public function __construct(
        bool $approved,
        array $violations,
        string $severity,
        string $action,
        string $explanation,
        float $confidence
    ) {
        $this->approved = $approved;
        $this->violations = $violations;
        $this->severity = $severity;
        $this->action = $action;
        $this->explanation = $explanation;
        $this->confidence = $confidence;
    }
}

class ModerationAction
{
    public readonly bool $approved;
    public readonly string $type;
    public readonly string $severity;
    public readonly bool $requiresHumanReview;
    public readonly string $explanation;

    public function __construct(
        bool $approved,
        string $type,
        string $severity,
        bool $requiresHumanReview,
        string $explanation
    ) {
        $this->approved = $approved;
        $this->type = $type;
        $this->severity = $severity;
        $this->requiresHumanReview = $requiresHumanReview;
        $this->explanation = $explanation;
    }
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
}

class PolicyViolation
{
    public readonly string $policy;
    public readonly string $category;
    public readonly string $type;
    public readonly float $severity;
    public readonly float $severityScore;
    public readonly string $reason;
    public readonly ?string $evidence;
    public readonly string $action;

    public function __construct(
        string $policy,
        string $category,
        string $type,
        float $severity,
        float $severityScore,
        string $reason,
        ?string $evidence,
        string $action
    ) {
        $this->policy = $policy;
        $this->category = $category;
        $this->type = $type;
        $this->severity = $severity;
        $this->severityScore = $severityScore;
        $this->reason = $reason;
        $this->evidence = $evidence;
        $this->action = $action;
    }
}

class ToxicityReport
{
    public readonly bool $isToxic;
    public readonly float $toxicityScore;
    public readonly array $categories;
    public readonly array $targetedGroups;
    public readonly array $contextualFactors;
    public readonly string $recommendation;

    public function __construct(
        bool $isToxic,
        float $toxicityScore,
        array $categories,
        array $targetedGroups,
        array $contextualFactors,
        string $recommendation
    ) {
        $this->isToxic = $isToxic;
        $this->toxicityScore = $toxicityScore;
        $this->categories = $categories;
        $this->targetedGroups = $targetedGroups;
        $this->contextualFactors = $contextualFactors;
        $this->recommendation = $recommendation;
    }
}

class PIIReport
{
    public readonly bool $hasPII;
    public readonly array $items;
    public readonly string $riskLevel;

    public function __construct(
        bool $hasPII,
        array $items,
        string $riskLevel
    ) {
        $this->hasPII = $hasPII;
        $this->items = $items;
        $this->riskLevel = $riskLevel;
    }
}

class SpamReport
{
    public readonly bool $isSpam;
    public readonly float $spamScore;
    public readonly array $indicators;
    public readonly string $type;
    public readonly string $recommendation;

    public function __construct(
        bool $isSpam,
        float $spamScore,
        array $indicators,
        string $type,
        string $recommendation
    ) {
        $this->isSpam = $isSpam;
        $this->spamScore = $spamScore;
        $this->indicators = $indicators;
        $this->type = $type;
        $this->recommendation = $recommendation;
    }
}
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've built a comprehensive content moderation system. Here's what you've accomplished:

- ✓ **Moderation System Core**: Created an intelligent moderation platform that analyzes content with context awareness
- ✓ **Content Analyzer**: Implemented multi-category violation detection (toxic language, spam, PII, inappropriate content, misinformation, copyright)
- ✓ **Toxicity Detection**: Built context-aware toxic language detection that distinguishes legitimate discussion from harmful content
- ✓ **PII Detection**: Developed pattern-based and AI-enhanced PII detection with automatic redaction capabilities
- ✓ **Spam Detection**: Created behavioral analysis that combines content patterns with user behavior indicators
- ✓ **Moderation Queue**: Implemented priority-based queue system with Redis for real-time processing
- ✓ **Policy Engine**: Designed flexible policy enforcement with customizable rules and severity scoring
- ✓ **Audit Logging**: Built comprehensive audit trails for accountability and appeal processes
- ✓ **Database Schema**: Designed normalized database tables for queue management, audit logging, and appeals
- ✓ **Analytics Dashboard**: Created metrics tracking for moderation performance, violation distribution, and queue statistics
- ✓ **Moderator Workflow**: Implemented moderator dashboards, decision submission, and performance metrics
- ✓ **Appeal System**: Developed user appeal workflows with moderator review process
- ✓ **Usage Examples**: Provided practical examples demonstrating all moderation features

### Key Concepts Learned

- **Context-Aware Analysis**: Claude's understanding of nuance reduces false positives by distinguishing satire, quotes, and educational content from actual violations
- **Multi-Layered Detection**: Combining pattern matching (fast) with AI analysis (accurate) provides both speed and precision
- **Severity Scoring**: Quantitative severity scores enable automated decision-making while maintaining human oversight for edge cases
- **Priority Queues**: Redis sorted sets enable efficient priority-based processing of moderation items
- **Policy Flexibility**: Configurable policy engine allows different rules for different content types and contexts
- **Privacy Protection**: PII detection and redaction protect user privacy and ensure GDPR/CCPA compliance
- **Behavioral Analysis**: User behavior patterns (posting frequency, account age, repetitive content) enhance spam detection accuracy

### Next Steps

Your moderation system is production-ready, but consider these enhancements:

- Add image moderation using vision models for inappropriate visual content
- Implement machine learning to improve detection accuracy from moderator feedback
- Build a moderator dashboard UI for reviewing flagged content
- Add appeal workflows allowing users to contest moderation decisions
- Integrate with user reputation systems to adjust moderation thresholds
- Create automated reporting for compliance and legal requirements
- Add multi-language support with language-specific policy rules
- Implement rate limiting and throttling to prevent abuse

### Unique Features of Content Moderation

Unlike customer support (Chapter 28) or data extraction (Chapter 30), content moderation includes:

- **Policy Enforcement**: Customizable policy engines with severity scoring
- **PII Detection & Redaction**: Privacy-first approach protecting user data
- **Behavioral Analysis**: User behavior pattern detection for spam (account age, posting frequency)
- **Multi-Layered Detection**: Combining pattern matching + AI for accuracy and speed
- **Human Review Workflows**: Queue prioritization for moderator review
- **Audit Trails**: Complete logging for compliance, appeals, and accountability
- **Severity Scoring**: Quantitative metrics for automated decision-making
- **False Positive Reduction**: Context-aware analysis reducing legitimate content flagging

## Troubleshooting

### Issue: Content Analysis Returns No Violations When Violations Exist

**Symptom**: `analyze()` method returns empty violations array even when content clearly violates policies

**Cause**: JSON parsing failing or Claude response format changed

**Solution**: Add better error handling and logging:

```php
private function parseAnalysis(string $jsonText): ContentAnalysis
{
    // Extract JSON from response
    if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
        $data = json_decode($matches[0], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            error_log("Response text: " . $jsonText);
            throw new \RuntimeException('Failed to parse analysis: ' . json_last_error_msg());
        }
        return new ContentAnalysis($data);
    }

    error_log("No JSON found in response: " . $jsonText);
    throw new \RuntimeException('Failed to parse analysis: No JSON found');
}
```

### Issue: PII Detection Misses Complex Cases

**Symptom**: Pattern-based detection works but AI detection returns empty results

**Cause**: AI detection prompt not specific enough or model selection incorrect

**Solution**: Use more specific prompts and verify model selection:

```php
private function aiDetect(string $text): array
{
    $prompt = <<<PROMPT
Detect personally identifiable information (PII) in this text.

Text: {$text}

Identify ALL instances of:
- Full names (first + last name together, especially with context like "my name is" or "contact me at")
- Home addresses (street addresses with city/state)
- Government ID numbers (SSN, passport, driver's license)
- Financial information (credit card numbers, bank account numbers)
- Medical information (patient IDs, medical record numbers)
- Login credentials (usernames with passwords)
- Phone numbers (in any format)
- Email addresses (if not already detected by patterns)

Return JSON array with ALL matches found. Be thorough.
PROMPT;

    $response = $this->claude->messages()->create([
        'model' => 'claude-sonnet-4-5', // Use Sonnet for better detection
        'max_tokens' => 2048, // Increase for more matches
        'temperature' => 0.1, // Lower for consistency
        'messages' => [[
            'role' => 'user',
            'content' => $prompt
        ]]
    ]);
    // ... rest of parsing
}
```

### Issue: Moderation Queue Items Not Processing

**Symptom**: Items added to queue but `getNext()` returns null

**Cause**: Redis connection failing or queue key mismatch

**Solution**: Add connection checks and verify Redis operations:

```php
public function getNext(string $moderatorId): ?array
{
    try {
        // Verify Redis connection
        if (!$this->redis->ping()) {
            error_log("Redis connection failed");
            // Fallback to database-only queue
            return $this->getNextFromDatabase($moderatorId);
        }

        // Get highest priority item
        $items = $this->redis->zrevrange('moderation:queue', 0, 0);

        if (empty($items)) {
            return null;
        }

        $queueId = (int)$items[0];

        // Verify item exists in database
        $item = $this->getItem($queueId);
        if (!$item) {
            // Clean up orphaned Redis entry
            $this->redis->zrem('moderation:queue', (string)$queueId);
            return null;
        }

        // Claim the item
        if ($this->claimItem($queueId, $moderatorId)) {
            return $item;
        }

        return null;
    } catch (\Exception $e) {
        error_log("Queue error: " . $e->getMessage());
        return $this->getNextFromDatabase($moderatorId);
    }
}
```

### Issue: False Positives on Legitimate Content

**Symptom**: Educational content, quotes, or satire being flagged as violations

**Cause**: System prompt not emphasizing context awareness enough

**Solution**: Strengthen system prompt with more examples:

```php
private function getAnalysisSystemPrompt(): string
{
    return <<<SYSTEM
You are a content moderation expert analyzing user-generated content.

CRITICAL: Context is everything. Always consider:

1. **Quotes and References**: Content quoting violations is NOT a violation itself
   - "He said 'kill you'" → NOT a threat (it's a quote)
   - Educational discussion of hate speech → NOT hate speech

2. **Satire and Criticism**: Criticizing toxicity is NOT toxic
   - "This is what hate speech looks like: [example]" → Educational, NOT violation
   - Satirical content mocking harmful ideas → NOT a violation

3. **Cultural Context**: Understand cultural and linguistic nuances
   - Reclaimed language by marginalized groups → Context-dependent
   - Medical/health discussions → Appropriate in health forums

4. **Intent Matters**: Distinguish between:
   - Actual threats vs. hypothetical discussion
   - Real harassment vs. friendly banter (in appropriate contexts)
   - Spam vs. legitimate promotion (in appropriate channels)

When in doubt, flag for human review rather than blocking.

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

Always err on the side of caution for:
- Child safety
- Violence or threats
- Illegal activity
- PII exposure
SYSTEM;
}
```

## Further Reading

- [Anthropic Claude API Documentation](https://docs.claude.com) — Official Claude API reference and moderation best practices
- [Content Moderation Best Practices](https://www.cloudflare.com/learning/bots/what-is-content-moderation/) — Industry standards for moderation workflows
- [GDPR Compliance Guide](https://gdpr.eu/what-is-gdpr/) — Understanding PII protection requirements
- [Redis Sorted Sets](https://redis.io/docs/data-types/sorted-sets/) — Priority queue implementation patterns
- [False Positive Reduction Strategies](https://www.contentmoderation.com/blog/reducing-false-positives) — Techniques for improving moderation accuracy
- [Chapter 19: Queue Processing with Laravel](/series/claude-php-developers/chapters/19-queue-processing-laravel) — Related chapter on async processing
- [Chapter 18: Caching Strategies](/series/claude-php-developers/chapters/18-caching-strategies) — Performance optimization for moderation systems

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
