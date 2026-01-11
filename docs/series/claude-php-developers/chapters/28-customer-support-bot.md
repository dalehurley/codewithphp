---
title: "28: Customer Support Bot"
description: "Build an intelligent customer support bot with knowledge base integration, ticket classification, sentiment analysis, and seamless human handoff capabilities using Claude."
series: "claude-php-developers"
chapter: 28
order: 28
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15"
  - "Understanding of customer support workflows"
  - "Experience with databases and queues"
  - "Knowledge of webhooks and real-time systems"
teaches:
  - 'Understand how to build an intelligent support bot with Claude that maintains conversation context'
  - 'Implement knowledge base integration with semantic search and re-ranking'
  - 'Create a ticket classification system that automatically routes issues to appropriate teams'
  - 'Build sentiment analysis capabilities to detect customer frustration and urgency'
  - 'Design an escalation engine that seamlessly hands off complex issues to human agents'
---
![28: Customer Support Bot](/images/claude-php/chapter-28-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 28</span>
</div>

# Chapter 28: Customer Support Bot

## Overview

Customer support is essential but resource-intensive. In this chapter, you'll build an intelligent support bot that handles common inquiries, classifies tickets, analyzes sentiment, and knows when to escalate to human agents. Your bot will integrate with knowledge bases, CRM systems, and ticketing platforms.

Claude's natural language understanding makes it perfect for support scenarios - it can interpret customer intent, maintain conversation context, access documentation, and provide helpful, empathetic responses while maintaining your brand voice.

**What You'll Build**: A production-ready support system that handles multi-channel conversations, integrates with knowledge bases, classifies and routes tickets, analyzes sentiment, and seamlessly hands off to human agents when needed.

## Objectives

By the end of this chapter, you will:

- **Understand** how to build an intelligent support bot with Claude that maintains conversation context
- **Implement** knowledge base integration with semantic search and re-ranking
- **Create** a ticket classification system that automatically routes issues to appropriate teams
- **Build** sentiment analysis capabilities to detect customer frustration and urgency
- **Design** an escalation engine that seamlessly hands off complex issues to human agents
- **Integrate** multi-channel support (email, chat) with unified conversation management
- **Develop** analytics and metrics tracking for bot performance monitoring

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use and structured outputs)
- ✓ **Support workflow knowledge** for ticket classification and routing
- ✓ **Database experience** for conversation persistence
- ✓ **Queue familiarity** for async processing

**Estimated Time**: 90-120 minutes

## Architecture Overview

```php
<?php
# filename: src/Support/SupportBot.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class SupportBot
{
    public function __construct(
        private ClaudePhp $claude,
        private KnowledgeBase $knowledgeBase,
        private TicketSystem $ticketSystem,
        private ConversationManager $conversations,
        private SentimentAnalyzer $sentiment,
        private EscalationEngine $escalation
    ) {}

    /**
     * Handle incoming customer message
     */
    public function handleMessage(
        string $customerId,
        string $message,
        array $context = []
    ): BotResponse {
        // Get or create conversation
        $conversation = $this->conversations->get($customerId)
            ?? $this->conversations->create($customerId);

        // Analyze sentiment
        $sentimentScore = $this->sentiment->analyze($message);

        // Check if escalation needed
        if ($this->shouldEscalate($conversation, $sentimentScore, $message)) {
            return $this->escalateToHuman($conversation, $message);
        }

        // Search knowledge base
        $relevantArticles = $this->knowledgeBase->search($message);

        // Generate response
        $response = $this->generateResponse(
            conversation: $conversation,
            message: $message,
            articles: $relevantArticles,
            context: $context
        );

        // Save conversation turn
        $conversation->addTurn($message, $response->text);
        $this->conversations->save($conversation);

        return $response;
    }

    private function generateResponse(
        Conversation $conversation,
        string $message,
        array $articles,
        array $context
    ): BotResponse {
        $systemPrompt = $this->buildSystemPrompt($articles, $context);
        $messages = $this->formatConversationHistory($conversation);


        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'temperature' => 0.7,
            'system' => $systemPrompt,
            'messages' => array_map(
                fn($msg) => [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ),
                $messages
            )
        );

        return new BotResponse(
            text: $response->content[0]->text,
            confidence: $this->calculateConfidence($response),
            suggestedActions: $this->extractActions($response),
            escalationNeeded: $this->detectEscalationIntent($response)
        );
    }

    private function formatConversationHistory(Conversation $conversation): array
    {
        $messages = [];
        foreach ($conversation->messages as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        return $messages;
    }

    private function formatArticles(array $articles): string
    {
        if (empty($articles)) {
            return 'No relevant articles found.';
        }

        $formatted = [];
        foreach ($articles as $article) {
            $formatted[] = "Title: {$article['title']}\nContent: {$article['content']}";
        }

        return implode("\n\n---\n\n", $formatted);
    }

    private function formatCustomerContext(array $context): string
    {
        $info = [];
        if (isset($context['customer_name'])) {
            $info[] = "Name: {$context['customer_name']}";
        }
        if (isset($context['account_type']) {
            $info[] = "Account Type: {$context['account_type']}";
        }
        if (isset($context['previous_tickets']) {
            $info[] = "Previous Tickets: {$context['previous_tickets']}";
        }

        return !empty($info) ? implode("\n", $info) : 'No additional customer information available.';
    }

    private function calculateConfidence($response): float
    {
        // Simple confidence calculation based on response length and structure
        $text = $response->content[0]->text;
        $length = strlen($text);

        // Longer, structured responses tend to be more confident
        if ($length > 200 && str_contains($text, '.')) {
            return 0.8;
        }
        if ($length > 100) {
            return 0.6;
        }
        return 0.4;
    }

    private function extractActions($response): array
    {
        // Extract suggested actions from response text
        $text = $response->content[0]->text;
        $actions = [];

        // Look for action patterns
        if (preg_match_all('/you can (.+?)(?:\.|$)/i', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $actions[] = trim($match);
            }
        }

        return array_slice($actions, 0, 3); // Limit to 3 actions
    }

    private function detectEscalationIntent($response): bool
    {
        $text = strtolower($response->content[0]->text);
        $escalationPhrases = [
            'connect you with',
            'transfer you to',
            'escalate',
            'specialist',
            'human agent'
        ];

        foreach ($escalationPhrases as $phrase) {
            if (str_contains($text, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function detectHumanRequest(string $message): bool
    {
        $lowerMessage = strtolower($message);
        $humanRequestPhrases = [
            'speak to a person',
            'talk to someone',
            'human agent',
            'real person',
            'customer service',
            'support agent',
            'can\'t help',
            'not helpful'
        ];

        foreach ($humanRequestPhrases as $phrase) {
            if (str_contains($lowerMessage, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function extractSubject(Conversation $conversation): string
    {
        if (empty($conversation->messages)) {
            return 'Support Request';
        }

        $firstMessage = $conversation->messages[0]['content'] ?? '';
        return substr($firstMessage, 0, 100);
    }

    private function calculatePriority(Conversation $conversation): string
    {
        // Simple priority calculation based on conversation length and sentiment
        if ($conversation->turnCount > 8) {
            return 'high';
        }
        if ($conversation->turnCount > 5) {
            return 'medium';
        }
        return 'low';
    }

    private function buildSystemPrompt(array $articles, array $context): string
    {
        $articleText = $this->formatArticles($articles);
        $customerInfo = $this->formatCustomerContext($context);

        return <<<SYSTEM
You are a helpful, professional customer support assistant for {$context['company_name'] ?? 'our company'}.

Your role:
- Provide accurate, helpful information
- Be friendly, empathetic, and professional
- Use the knowledge base to answer questions
- Admit when you don't know something
- Offer to escalate complex issues to human agents
- Never make promises you can't keep
- Maintain a positive, solution-focused tone

Knowledge Base Articles:
{$articleText}

Customer Information:
{$customerInfo}

Guidelines:
1. Listen actively to understand the customer's needs
2. Provide clear, step-by-step solutions
3. Use simple language, avoid jargon
4. Confirm understanding before closing
5. Suggest escalation if issue is too complex
6. Always be respectful and patient

If you cannot help, say: "I'd like to connect you with one of our support specialists who can better assist you with this."
SYSTEM;
    }

    private function shouldEscalate(
        Conversation $conversation,
        float $sentimentScore,
        string $message
    ): bool {
        // Escalate if:
        // 1. Very negative sentiment
        if ($sentimentScore < -0.7) {
            return true;
        }

        // 2. Customer explicitly requests human
        if ($this->detectHumanRequest($message)) {
            return true;
        }

        // 3. Conversation too long without resolution
        if ($conversation->turnCount > 10) {
            return true;
        }

        // 4. Customer frustrated (repeated issue)
        if ($conversation->getRepetitionCount() > 3) {
            return true;
        }

        return false;
    }

    private function escalateToHuman(
        Conversation $conversation,
        string $message
    ): BotResponse {
        // Create ticket
        $ticket = $this->ticketSystem->createTicket([
            'customer_id' => $conversation->customerId,
            'subject' => $this->extractSubject($conversation),
            'description' => $conversation->getSummary(),
            'priority' => $this->calculatePriority($conversation),
            'conversation_history' => $conversation->getHistory()
        ]);

        // Notify available agents
        $this->escalation->notifyAgents($ticket);

        return new BotResponse(
            text: "I understand this requires special attention. I've created a ticket and connected you with one of our support specialists who will help you shortly. Your ticket number is #{$ticket->id}.",
            escalationNeeded: true,
            ticketId: $ticket->id
        );
    }
}
```

## Knowledge Base Integration

```php
<?php
# filename: src/Support/KnowledgeBase.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class KnowledgeBase
{
    public function __construct(
        private ClaudePhp $claude,
        private \PDO $db,
        private VectorStore $vectorStore
    ) {}

    /**
     * Search knowledge base using semantic search
     */
    public function search(string $query, int $limit = 5): array
    {
        // First, use Claude to understand the query intent
        $analyzedQuery = $this->analyzeQuery($query);

        // Semantic search using embeddings
        $results = $this->vectorStore->search($query, $limit);

        // Re-rank results based on relevance
        $ranked = $this->reRankResults($results, $analyzedQuery);

        return array_slice($ranked, 0, $limit);
    }

    private function analyzeQuery(string $query): array
    {
        $prompt = <<<PROMPT
Analyze this customer support query and extract key information.

Query: {$query}

Return JSON with:
{
  "intent": "what the customer wants to do",
  "category": "account|billing|technical|product|shipping|other",
  "urgency": "low|medium|high|critical",
  "keywords": ["key", "terms"],
  "question_type": "how-to|troubleshooting|information|complaint"
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 512,
            'temperature' => 0.3,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    private function reRankResults(array $results, array $queryAnalysis): array
    {
        // Score each result
        foreach ($results as &$result) {
            $score = $result['similarity_score'] ?? 0;

            // Boost if categories match
            if (isset($result['category']) &&
                $result['category'] === ($queryAnalysis['category'] ?? '')) {
                $score += 0.2;
            }

            // Boost recent articles
            $daysOld = (time() - strtotime($result['updated_at'])) / 86400;
            if ($daysOld < 30) {
                $score += 0.1;
            }

            // Boost popular articles
            if (isset($result['view_count']) && $result['view_count'] > 100) {
                $score += 0.05;
            }

            $result['final_score'] = $score;
        }

        // Sort by final score
        usort($results, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

        return $results;
    }

    /**
     * Add article to knowledge base
     */
    public function addArticle(
        string $title,
        string $content,
        string $category,
        array $tags = []
    ): int {
        // Insert article
        $stmt = $this->db->prepare(
            "INSERT INTO kb_articles (title, content, category, tags, created_at, updated_at)
             VALUES (:title, :content, :category, :tags, NOW(), NOW())"
        );

        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':category' => $category,
            ':tags' => json_encode($tags)
        ];

        $articleId = (int)$this->db->lastInsertId();

        // Generate and store embedding
        $this->vectorStore->addDocument($articleId, $title . "\n\n" . $content);

        return $articleId;
    }

    /**
     * Get article by ID
     */
    public function getArticle(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM kb_articles WHERE id = :id"
        );
        $stmt->execute([':id' => $id];

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
```

## Ticket Classification System

```php
<?php
# filename: src/Support/TicketClassifier.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class TicketClassifier
{
    public function __construct(
        private ClaudePhp $claude
    ) {}

    /**
     * Classify and route ticket
     */
    public function classify(string $subject, string $description): TicketClassification
    {
        $prompt = <<<PROMPT
Classify this customer support ticket.

Subject: {$subject}

Description: {$description}

Analyze and return JSON:
{
  "category": "billing|technical|account|product|shipping|sales|other",
  "priority": "low|medium|high|critical",
  "urgency": "not_urgent|soon|urgent|immediate",
  "department": "support|engineering|billing|sales|management",
  "estimated_complexity": "simple|moderate|complex|very_complex",
  "suggested_assignee_skills": ["skill1", "skill2"],
  "tags": ["relevant", "tags"],
  "requires_technical_knowledge": boolean,
  "requires_manager_approval": boolean,
  "sentiment": "positive|neutral|negative|very_negative"
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
            $data = json_decode($matches[0], true);
            return new TicketClassification($data);
        }

        throw new \RuntimeException('Failed to classify ticket');
    }

    /**
     * Generate ticket summary
     */
    public function generateSummary(array $conversation): string
    {
        $conversationText = $this->formatConversation($conversation);

        $prompt = <<<PROMPT
Create a concise summary of this customer support conversation.

Conversation:
{$conversationText}

Generate a summary that includes:
1. Main issue/question
2. Steps taken to resolve
3. Current status
4. Next actions needed

Keep it brief (2-3 sentences) but informative for the next agent.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 512,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        return $response->content[0]->text;
    }

    private function formatConversation(array $conversation): string
    {
        $formatted = [];
        foreach ($conversation as $turn) {
            $role = $turn['role'] === 'user' ? 'Customer' : 'Bot';
            $formatted[] = "{$role}: {$turn['content']}";
        }
        return implode("\n\n", $formatted);
    }
}
```

## Sentiment Analysis

```php
<?php
# filename: src/Support/SentimentAnalyzer.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class SentimentAnalyzer
{
    private array $cache = [];

    public function __construct(
        private ClaudePhp $claude
    ) {}

    /**
     * Analyze message sentiment
     * Returns score from -1 (very negative) to +1 (very positive)
     */
    public function analyze(string $message): float
    {
        // Check cache
        $cacheKey = md5($message);
        if (isset($this->cache[$cacheKey]) {
            return $this->cache[$cacheKey];
        }

        $prompt = <<<PROMPT
Analyze the sentiment and emotional tone of this customer message.

Message: {$message}

Return JSON:
{
  "sentiment_score": float between -1 (very negative) and +1 (very positive),
  "emotion": "angry|frustrated|confused|neutral|satisfied|happy",
  "urgency_detected": boolean,
  "frustration_level": "none|low|medium|high|extreme",
  "indicators": ["specific phrases or words indicating sentiment"]
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 512,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            $data = json_decode($matches[0], true);
            $score = $data['sentiment_score'] ?? 0.0;

            // Cache result
            $this->cache[$cacheKey] = $score;

            return $score;
        }

        return 0.0;
    }

    /**
     * Analyze conversation trend
     */
    public function analyzeConversationTrend(Conversation $conversation): array
    {
        $messages = $conversation->messages;
        $scores = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'user') {
                $scores[] = $this->analyze($msg['content']);
            }
        }

        return [
            'average_sentiment' => !empty($scores) ? array_sum($scores) / count($scores) : 0,
            'trend' => $this->calculateTrend($scores),
            'is_improving' => $this->isImproving($scores),
            'lowest_point' => !empty($scores) ? min($scores) : 0
        ];
    }

    private function calculateTrend(array $scores): string
    {
        if (empty($scores) || count($scores) < 2) {
            return 'stable';
        }

        $first = array_slice($scores, 0, ceil(count($scores) / 2));
        $second = array_slice($scores, ceil(count($scores) / 2));

        $firstAvg = array_sum($first) / count($first);
        $secondAvg = array_sum($second) / count($second);

        $diff = $secondAvg - $firstAvg;

        if ($diff > 0.2) return 'improving';
        if ($diff < -0.2) return 'declining';
        return 'stable';
    }

    private function isImproving(array $scores): bool
    {
        return $this->calculateTrend($scores) === 'improving';
    }
}
```

## Conversation Manager

```php
<?php
# filename: src/Support/ConversationManager.php
declare(strict_types=1);

namespace App\Support;

class ConversationManager
{
    public function __construct(
        private \PDO $db,
        private \Redis $redis
    ) {}

    /**
     * Get conversation by customer ID
     */
    public function get(string $customerId): ?Conversation
    {
        // Try cache first
        $cached = $this->redis->get("conversation:{$customerId}");
        if ($cached) {
            return unserialize($cached);
        }

        // Load from database
        $stmt = $this->db->prepare(
            "SELECT * FROM conversations
             WHERE customer_id = :customer_id
             AND status = 'active'
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([':customer_id' => $customerId];
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $conversation = $this->hydrate($data);

        // Cache it
        $this->redis->setex(
            "conversation:{$customerId}",
            3600,
            serialize($conversation)
        );

        return $conversation;
    }

    /**
     * Create new conversation
     */
    public function create(string $customerId, array $metadata = []: Conversation
    {
        $stmt = $this->db->prepare(
            "INSERT INTO conversations (customer_id, status, metadata, created_at, updated_at)
             VALUES (:customer_id, 'active', :metadata, NOW(), NOW())"
        );

        $stmt->execute([
            ':customer_id' => $customerId,
            ':metadata' => json_encode($metadata)
        ];

        $id = (int)$this->db->lastInsertId();

        $conversation = new Conversation(
            id: $id,
            customerId: $customerId,
            'messages' => [],
            metadata: $metadata,
            status: 'active',
            createdAt: new \DateTime()
        );

        return $conversation;
    }

    /**
     * Save conversation
     */
    public function save(Conversation $conversation): void
    {
        $stmt = $this->db->prepare(
            "UPDATE conversations
             SET messages = :messages,
                 metadata = :metadata,
                 status = :status,
                 turn_count = :turn_count,
                 updated_at = NOW()
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $conversation->id,
            ':messages' => json_encode($conversation->messages),
            ':metadata' => json_encode($conversation->metadata),
            ':status' => $conversation->status,
            ':turn_count' => count($conversation->messages)
        ];

        // Update cache
        $this->redis->setex(
            "conversation:{$conversation->customerId}",
            3600,
            serialize($conversation)
        );
    }

    /**
     * Close conversation
     */
    public function close(Conversation $conversation, string $reason = 'resolved'): void
    {
        $conversation->status = 'closed';
        $conversation->metadata['closed_reason'] = $reason;
        $conversation->metadata['closed_at'] = date('Y-m-d H:i:s');

        $this->save($conversation);

        // Remove from cache
        $this->redis->del("conversation:{$conversation->customerId}");
    }

    private function hydrate(array $data): Conversation
    {
        return new Conversation(
            id: (int)$data['id'],
            customerId: $data['customer_id'],
            'messages' => json_decode($data['messages'] ?? '[]', true),
            metadata: json_decode($data['metadata'] ?? '{}', true),
            status: $data['status'],
            createdAt: new \DateTime($data['created_at']
        );
    }
}
```

## Multi-Channel Integration

### Email Support Handler

```php
<?php
# filename: src/Support/Channels/EmailHandler.php
declare(strict_types=1);

namespace App\Support\Channels;

use App\Support\SupportBot;

class EmailHandler
{
    public function __construct(
        private SupportBot $bot,
        private \PDO $db
    ) {}

    /**
     * Process incoming support email
     */
    public function handleIncomingEmail(array $emailData): void
    {
        $customerId = $this->identifyCustomer($emailData['from'];
        $subject = $emailData['subject'];
        $body = $this->extractTextFromEmail($emailData['body'];

        // Process with bot
        $response = $this->bot->handleMessage(
            customerId: $customerId,
            message: $body,
            context: [
                'channel' => 'email',
                'subject' => $subject,
                'from' => $emailData['from']
            ]
        );

        // Send reply
        if (!$response->escalationNeeded) {
            $this->sendEmail(
                to: $emailData['from'],
                subject: "Re: {$subject}",
                body: $response->text
            );
        }

        // Log interaction
        $this->logInteraction($customerId, 'email', $body, $response);
    }

    private function identifyCustomer(string $email): string
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM customers WHERE email = :email"
        );
        $stmt->execute([':email' => $email];
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return (string)$result['id'];
        }

        // Create new customer
        $stmt = $this->db->prepare(
            "INSERT INTO customers (email, created_at) VALUES (:email, NOW())"
        );
        $stmt->execute([':email' => $email];

        return (string)$this->db->lastInsertId();
    }

    private function extractTextFromEmail(string $body): string
    {
        // Remove HTML tags
        $text = strip_tags($body);

        // Remove quoted text (lines starting with >)
        $lines = explode("\n", $text);
        $filtered = array_filter($lines, fn($line) => !str_starts_with(trim($line), '>'));

        return trim(implode("\n", $filtered));
    }

    private function sendEmail(string $to, string $subject, string $body): void
    {
        // Use your email service (SendGrid, Mailgun, etc.)
        mail($to, $subject, $body, [
            'From' => 'support@example.com',
            'Reply-To' => 'support@example.com'
        ];
    }

    private function logInteraction(
        string $customerId,
        string $channel,
        string $message,
        $response
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO support_interactions
             (customer_id, channel, message, response, escalated, created_at)
             VALUES (:customer_id, :channel, :message, :response, :escalated, NOW())"
        );

        $stmt->execute([
            ':customer_id' => $customerId,
            ':channel' => $channel,
            ':message' => $message,
            ':response' => $response->text,
            ':escalated' => $response->escalationNeeded ? 1 : 0
        ];
    }
}
```

### Live Chat Handler

```php
<?php
# filename: src/Support/Channels/ChatHandler.php
declare(strict_types=1);

namespace App\Support\Channels;

use App\Support\SupportBot;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatHandler implements MessageComponentInterface
{
    private \SplObjectStorage $clients;

    public function __construct(
        private SupportBot $bot
    ) {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);

        // Send welcome message
        $conn->send(json_encode([
            'type' => 'welcome',
            'message' => 'Hello! How can I help you today?'
        ]);
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $data = json_decode($msg, true);

        if ($data['type'] === 'message') {
            $customerId = $data['customer_id'] ?? 'anonymous_' . spl_object_id($from);

            // Process with bot
            $response = $this->bot->handleMessage(
                customerId: $customerId,
                message: $data['message'],
                context: ['channel' => 'chat']
            );

            // Send response
            $from->send(json_encode([
                'type' => 'response',
                'message' => $response->text,
                'escalated' => $response->escalationNeeded,
                'ticket_id' => $response->ticketId ?? null
            ]);

            // If escalated, notify agents
            if ($response->escalationNeeded) {
                $this->notifyAgents($response);
            }
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        error_log("Chat error: " . $e->getMessage());
        $conn->close();
    }

    private function notifyAgents($response): void
    {
        // Notify available agents via your notification system
        // This could be WebSocket, email, Slack, etc.
    }
}
```

## Complete Support Bot API

```php
<?php
# filename: api/support-bot.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Support\SupportBot;
use App\Support\KnowledgeBase;
use App\Support\TicketSystem;
use App\Support\ConversationManager;
use App\Support\SentimentAnalyzer;
use App\Support\EscalationEngine;
use ClaudePhp\ClaudePhp;

header('Content-Type: application/json');

// Initialize components
$db = new PDO(getenv('DATABASE_DSN'));
$redis = new Redis();
$redis->connect('localhost', 6379);

$claude = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$knowledgeBase = new KnowledgeBase($claude, $db, new VectorStore());
$ticketSystem = new TicketSystem($db);
$conversations = new ConversationManager($db, $redis);
$sentiment = new SentimentAnalyzer($claude);
$escalation = new EscalationEngine($db, $redis);

$bot = new SupportBot(
    $claude,
    $knowledgeBase,
    $ticketSystem,
    $conversations,
    $sentiment,
    $escalation
);

// Handle request
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['customer_id']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'customer_id and message required']);
    exit;
}

try {
    $response = $bot->handleMessage(
        $input['customer_id'],
        $input['message'],
        $input['context'] ?? []
    );

    echo json_encode([
        'success' => true,
        'response' => $response->text,
        'escalated' => $response->escalationNeeded,
        'ticket_id' => $response->ticketId ?? null,
        'suggested_actions' => $response->suggestedActions
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

## Analytics Dashboard

```php
<?php
# filename: src/Support/Analytics.php
declare(strict_types=1);

namespace App\Support;

class SupportAnalytics
{
    public function __construct(
        private \PDO $db
    ) {}

    /**
     * Get support metrics
     */
    public function getMetrics(\DateTime $startDate, \DateTime $endDate): array
    {
        return [
            'total_conversations' => $this->getTotalConversations($startDate, $endDate),
            'resolved_by_bot' => $this->getResolvedByBot($startDate, $endDate),
            'escalation_rate' => $this->getEscalationRate($startDate, $endDate),
            'average_resolution_time' => $this->getAverageResolutionTime($startDate, $endDate),
            'customer_satisfaction' => $this->getCustomerSatisfaction($startDate, $endDate),
            'top_issues' => $this->getTopIssues($startDate, $endDate),
            'sentiment_distribution' => $this->getSentimentDistribution($startDate, $endDate)
        ];
    }

    private function getTotalConversations(\DateTime $start, \DateTime $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM conversations
             WHERE created_at BETWEEN :start AND :end"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    private function getResolvedByBot(\DateTime $start, \DateTime $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM conversations
             WHERE created_at BETWEEN :start AND :end
             AND status = 'closed'
             AND escalated = 0"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    private function getEscalationRate(\DateTime $start, \DateTime $end): float
    {
        $total = $this->getTotalConversations($start, $end);
        if ($total === 0) {
            return 0.0;
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM conversations
             WHERE created_at BETWEEN :start AND :end
             AND escalated = 1"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        $escalated = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['count'];

        return ($escalated / $total) * 100;
    }

    private function getTopIssues(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT category, COUNT(*) as count
             FROM tickets
             WHERE created_at BETWEEN :start AND :end
             GROUP BY category
             ORDER BY count DESC
             LIMIT 10"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAverageResolutionTime(\DateTime $start, \DateTime $end): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes
             FROM conversations
             WHERE created_at BETWEEN :start AND :end
             AND status = 'closed'"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($result['avg_minutes'] ?? 0);
    }

    private function getCustomerSatisfaction(\DateTime $start, \DateTime $end): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(satisfaction_score) as avg_score
             FROM support_interactions
             WHERE created_at BETWEEN :start AND :end
             AND satisfaction_score IS NOT NULL"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($result['avg_score'] ?? 0);
    }

    private function getSentimentDistribution(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN sentiment_score < -0.5 THEN 1 ELSE 0 END) as negative,
                SUM(CASE WHEN sentiment_score BETWEEN -0.5 AND 0.5 THEN 1 ELSE 0 END) as neutral,
                SUM(CASE WHEN sentiment_score > 0.5 THEN 1 ELSE 0 END) as positive
             FROM support_interactions
             WHERE created_at BETWEEN :start AND :end
             AND sentiment_score IS NOT NULL"
        );
        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['negative' => 0, 'neutral' => 0, 'positive' => 0];
    }
}
```

## Data Structures

```php
<?php
# filename: src/Support/DataStructures.php
declare(strict_types=1);

namespace App\Support;

readonly class BotResponse
{
    public function __construct(
        public string $text,
        public float $confidence = 0.0,
        public array $suggestedActions = [],
        public bool $escalationNeeded = false,
        public ?int $ticketId = null
    ) {}
}

class Conversation
{
    public int $turnCount = 0;

    public function __construct(
        public int $id,
        public string $customerId,
        public array $messages = [],
        public array $metadata = [],
        public string $status = 'active',
        public \DateTime $createdAt = new \DateTime()
    ) {
        $this->turnCount = count($messages);
    }

    public function addTurn(string $userMessage, string $botResponse): void
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => time()
        ];
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $botResponse,
            'timestamp' => time()
        ];
        $this->turnCount = count($this->messages);
    }

    public function getHistory(): array
    {
        return $this->messages;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getSummary(): string
    {
        $summary = '';
        foreach ($this->messages as $msg) {
            $role = $msg['role'] === 'user' ? 'Customer' : 'Bot';
            $summary .= "{$role}: " . substr($msg['content'], 0, 100) . "...\n";
        }
        return $summary;
    }

    public function getRepetitionCount(): int
    {
        // Count how many times similar messages appear
        $userMessages = array_filter($this->messages, fn($m) => $m['role'] === 'user');
        $contents = array_map(fn($m) => strtolower($m['content'], $userMessages);

        $repetitions = 0;
        foreach ($contents as $i => $content) {
            foreach (array_slice($contents, $i + 1) as $other) {
                similar_text($content, $other, $percent);
                if ($percent > 70) {
                    $repetitions++;
                }
            }
        }

        return $repetitions;
    }
}

readonly class TicketClassification
{
    public function __construct(
        public array $data
    ) {}

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}
```

## Quality Assurance & Evaluation

### Bot Response Quality Scoring

```php
<?php
# filename: src/Support/QualityAssurance.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class QualityAssurance
{
    public function __construct(
        private ClaudePhp $claude,
        private \PDO $db
    ) {}

    /**
     * Score bot response quality
     */
    public function scoreResponse(string $botResponse, string $customerMessage): array
    {
        $prompt = <<<PROMPT
Evaluate this customer support bot response on multiple quality dimensions.

Customer Message: {$customerMessage}

Bot Response: {$botResponse}

Rate on a scale of 0-10 and return JSON:
{
  "clarity_score": number (0-10, is response clear and understandable?),
  "helpfulness_score": number (0-10, does it answer the customer's question?),
  "tone_score": number (0-10, is tone professional and empathetic?),
  "accuracy_score": number (0-10, is information accurate?),
  "actionability_score": number (0-10, can customer act on this advice?),
  "overall_score": number (0-10, overall quality rating),
  "issues": ["list of identified problems"],
  "recommendations": ["suggestions for improvement"],
  "requires_review": boolean (flag for human review if problematic)
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
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

    /**
     * Track satisfaction scores
     */
    public function recordSatisfactionScore(int $conversationId, int $score, ?string $feedback = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO conversation_satisfaction (conversation_id, score, feedback, created_at)
             VALUES (:conversation_id, :score, :feedback, NOW())"
        );

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':score' => $score,
            ':feedback' => $feedback
        ];
    }

    /**
     * Calculate satisfaction metrics
     */
    public function getSatisfactionMetrics(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_ratings,
                AVG(score) as average_score,
                SUM(CASE WHEN score >= 4 THEN 1 ELSE 0 END) as satisfied,
                SUM(CASE WHEN score <= 2 THEN 1 ELSE 0 END) as unsatisfied,
                MIN(score) as lowest_score,
                MAX(score) as highest_score
             FROM conversation_satisfaction
             WHERE created_at BETWEEN :start AND :end"
        );

        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result['total_ratings'] > 0) {
            $result['satisfaction_rate'] = ($result['satisfied'] / $result['total_ratings'] * 100;
        }

        return $result ?: [];
    }

    /**
     * Get responses flagged for review
     */
    public function getFlaggedResponses(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT cr.*, c.customer_id, c.messages
             FROM conversation_responses cr
             JOIN conversations c ON cr.conversation_id = c.id
             WHERE cr.quality_score < 5 OR cr.requires_review = 1
             ORDER BY cr.created_at DESC
             LIMIT :limit"
        );

        $stmt->execute([':limit' => $limit];
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### Conversation Quality Tracking

```php
<?php
# filename: src/Support/ConversationQuality.php
declare(strict_types=1);

namespace App\Support;

class ConversationQuality
{
    public function __construct(
        private \PDO $db,
        private QualityAssurance $qa
    ) {}

    /**
     * Track resolution metrics
     */
    public function recordResolution(int $conversationId, string $resolutionType, bool $resolved): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO resolution_tracking (conversation_id, type, resolved, created_at)
             VALUES (:conversation_id, :type, :resolved, NOW())"
        );

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':type' => $resolutionType,
            ':resolved' => $resolved ? 1 : 0
        ];
    }

    /**
     * Get resolution effectiveness
     */
    public function getResolutionEffectiveness(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                type,
                COUNT(*) as total,
                SUM(CASE WHEN resolved = 1 THEN 1 ELSE 0 END) as successful,
                (SUM(CASE WHEN resolved = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate
             FROM resolution_tracking
             WHERE created_at BETWEEN :start AND :end
             GROUP BY type
             ORDER BY success_rate DESC"
        );

        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get average resolution time by type
     */
    public function getAverageResolutionTime(string $type, \DateTime $start, \DateTime $end): float
    {
        $stmt = $this->db->prepare(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, c.created_at, c.updated_at)) as avg_minutes
             FROM resolution_tracking rt
             JOIN conversations c ON rt.conversation_id = c.id
             WHERE rt.type = :type
             AND rt.resolved = 1
             AND rt.created_at BETWEEN :start AND :end"
        );

        $stmt->execute([
            ':type' => $type,
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($result['avg_minutes'] ?? 0);
    }
}
```

## Prompt Caching for Cost Optimization

```php
<?php
# filename: src/Support/CachingStrategy.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class CachingStrategy
{
    public function __construct(
        private ClaudePhp $claude,
        private \Redis $redis
    ) {}

    /**
     * Generate response with cached system prompt
     */
    public function generateWithCachedPrompt(
        string $systemPrompt,
        array $articles,
        array $messages
    ): string {
        // Cache key for this specific knowledge base + articles
        $cacheKey = 'kb_cache_' . md5($systemPrompt . json_encode(array_keys($articles)));

        // Check if cache exists
        $cached = $this->redis->get($cacheKey);
        if ($cached) {
            return $this->usesCachedContext($cached, $messages);
        }

        // Use with prompt caching to save tokens on repeated queries
        $articleText = $this->formatArticles($articles);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'temperature' => 0.7,
            'system' => $systemPrompt . "\n\nKnowledge Base:\n" . $articleText,
            'messages' => $messages
        ]);

        // Store indication that cache is available
        $this->redis->setex($cacheKey, 300, json_encode([
            'cached_at' => time(),
            'article_count' => count($articles)
        ]);

        return $response->content[0]->text;
    }

    /**
     * Cache knowledge base articles in Redis
     */
    public function cacheKnowledgeBase(array $articles, int $ttl = 86400): void
    {
        $cacheKey = 'kb_articles_' . md5(json_encode(array_column($articles, 'id')));

        $this->redis->setex(
            $cacheKey,
            $ttl,
            json_encode($articles)
        );
    }

    /**
     * Get cached knowledge base
     */
    public function getCachedKnowledgeBase(array $articleIds): ?array
    {
        $cacheKey = 'kb_articles_' . md5(json_encode($articleIds));

        $cached = $this->redis->get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }

        return null;
    }

    private function usesCachedContext(string $cacheInfo, array $messages): string
    {
        $cached = json_decode($cacheInfo, true);
        // Logic to use cached context for faster responses
        return "Response using cached KB (cached at: {$cached['cached_at']})";
    }

    private function formatArticles(array $articles): string
    {
        $formatted = [];
        foreach ($articles as $article) {
            $formatted[] = "Title: {$article['title']}\nContent: {$article['content']}";
        }
        return implode("\n\n---\n\n", $formatted);
    }
}
```

## Memory Tool for Persistent Customer Context

```php
<?php
# filename: src/Support/CustomerMemory.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class CustomerMemory
{
    public function __construct(
        private ClaudePhp $claude,
        private \PDO $db
    ) {}

    /**
     * Extract and store customer context using Memory Tool
     */
    public function updateCustomerMemory(string $customerId, string $conversationText): void
    {
        $prompt = <<<PROMPT
Extract important customer information from this conversation that should be remembered for future interactions.

Conversation:
{$conversationText}

Extract and return JSON:
{
  "preferences": ["customer preferences mentioned"],
  "issues_history": ["past issues mentioned"],
  "customer_name": "if mentioned",
  "account_type": "subscription level or tier",
  "vip_status": boolean,
  "special_notes": "any special handling requests",
  "communication_style": "formal/casual/technical",
  "preferred_solutions": ["solutions this customer prefers"]
}

Return ONLY valid JSON.
PROMPT;

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            $memory = json_decode($matches[0], true);
            $this->storeCustomerMemory($customerId, $memory);
        }
    }

    /**
     * Store customer memory for future use
     */
    private function storeCustomerMemory(string $customerId, array $memory): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO customer_memory (customer_id, memory_data, updated_at)
             VALUES (:customer_id, :memory_data, NOW())
             ON DUPLICATE KEY UPDATE
                memory_data = :memory_data,
                updated_at = NOW()"
        );

        $stmt->execute([
            ':customer_id' => $customerId,
            ':memory_data' => json_encode($memory)
        ];
    }

    /**
     * Retrieve customer memory for context
     */
    public function getCustomerMemory(string $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT memory_data FROM customer_memory WHERE customer_id = :customer_id"
        );

        $stmt->execute([':customer_id' => $customerId];
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return json_decode($result['memory_data'], true);
        }

        return null;
    }

    /**
     * Use customer memory in system prompt
     */
    public function buildContextAwareSystemPrompt(string $basePompt, ?array $memory): string
    {
        if (!$memory) {
            return $basePompt;
        }

        $memoryContext = "## Customer Profile\n";
        if ($memory['customer_name'] ?? null) {
            $memoryContext .= "Name: {$memory['customer_name']}\n";
        }
        if ($memory['vip_status'] ?? false) {
            $memoryContext .= "VIP Customer - Provide priority support\n";
        }
        if ($memory['preferences'] ?? null) {
            $memoryContext .= "Preferences: " . implode(", ", $memory['preferences']) . "\n";
        }
        if ($memory['communication_style'] ?? null) {
            $memoryContext .= "Communication Style: {$memory['communication_style']}\n";
        }

        return $basePompt . "\n\n" . $memoryContext;
    }
}
```

## Files API for Attachment Handling

```php
<?php
# filename: src/Support/AttachmentHandler.php
declare(strict_types=1);

namespace App\Support;

use ClaudePhp\ClaudePhp;

class AttachmentHandler
{
    public function __construct(
        private ClaudePhp $claude,
        private \PDO $db,
        private string $uploadDir
    ) {}

    /**
     * Process customer attachment/file
     */
    public function processAttachment(string $conversationId, string $filePath): array
    {
        $fileName = basename($filePath);
        $fileType = mime_content_type($filePath);

        // Handle different file types
        $analysis = match (true) {
            str_contains($fileType, 'image') => $this->analyzeImage($filePath),
            str_contains($fileType, 'pdf') => $this->analyzePdf($filePath),
            str_contains($fileType, 'text') => $this->analyzeText($filePath),
            default => $this->analyzeGeneric($filePath, $fileType)
        };

        // Store attachment record
        $this->storeAttachment($conversationId, $fileName, $fileType, $analysis);

        return $analysis;
    }

    /**
     * Analyze image attachment
     */
    private function analyzeImage(string $filePath): array
    {
        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType = mime_content_type($filePath);

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $imageData
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Analyze this support ticket attachment. What issue does it show? Return JSON: {"description": "...", "severity": "low|medium|high", "suggested_resolution": "..."}'
                    ]
                ]
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    /**
     * Analyze PDF attachment
     */
    private function analyzePdf(string $filePath): array
    {
        $pdfData = base64_encode(file_get_contents($filePath));

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'document',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => 'application/pdf',
                            'data' => $pdfData
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Extract and summarize key information from this document. Return JSON: {"summary": "...", "key_points": [...], "action_items": [...]}'
                    ]
                ]
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    /**
     * Analyze text attachment
     */
    private function analyzeText(string $filePath): array
    {
        $content = file_get_contents($filePath);

        $response = $this->claude->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'temperature' => 0.3,
            'messages' => [[
                'role' => 'user',
                'content' => "Analyze this support document:\n\n{$content}\n\nReturn JSON: {\"analysis\": \"...\", \"key_issues\": [...], \"recommendations\": [...]}"
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            return json_decode($matches[0], true) ?? [];
        }

        return [];
    }

    /**
     * Generic file analysis
     */
    private function analyzeGeneric(string $filePath, string $fileType): array
    {
        return [
            'file_type' => $fileType,
            'size' => filesize($filePath),
            'note' => 'File type requires manual review'
        ];
    }

    /**
     * Store attachment metadata
     */
    private function storeAttachment(string $conversationId, string $fileName, string $fileType, array $analysis): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO conversation_attachments (conversation_id, file_name, file_type, analysis, created_at)
             VALUES (:conversation_id, :file_name, :file_type, :analysis, NOW())"
        );

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':file_name' => $fileName,
            ':file_type' => $fileType,
            ':analysis' => json_encode($analysis)
        ];
    }
}
```

## Testing & Mocking

```php
<?php
# filename: tests/Support/SupportBotTest.php
declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use App\Support\SupportBot;
use App\Support\BotResponse;

class SupportBotTest extends TestCase
{
    private Anthropic $claudeMock;
    private SupportBot $bot;

    protected function setUp(): void
    {
        $this->claudeMock = $this->createMock(Anthropic::class);
        // Initialize bot with mocks
    }

    public function testBotRespondsToSimpleQuery(): void
    {
        $response = new BotResponse(
            text: 'Here is how to solve your problem...',
            confidence: 0.85,
            escalationNeeded: false
        );

        $this->assertEquals('Here is how to solve your problem...', $response->text);
        $this->assertFalse($response->escalationNeeded);
    }

    public function testBotEscalatesOnComplexIssue(): void
    {
        $response = new BotResponse(
            text: 'I will connect you with a specialist...',
            escalationNeeded: true,
            ticketId: 12345
        );

        $this->assertTrue($response->escalationNeeded);
        $this->assertEquals(12345, $response->ticketId);
    }

    public function testSentimentAnalysisDetectsFrustration(): void
    {
        // Mock sentiment analyzer
        $sentiment = -0.8; // Very negative

        $this->assertLessThan(-0.7, $sentiment);
    }

    public function testConversationMemoryPersists(): void
    {
        // Mock conversation storage
        $conversationId = 123;
        $messages = [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!']
        ];

        $this->assertCount(2, $messages);
    }
}
```

## CRM Integration Example

```php
<?php
# filename: src/Support/CrmIntegration.php
declare(strict_types=1);

namespace App\Support;

class CrmIntegration
{
    public function __construct(
        private string $crmApiKey,
        private string $crmBaseUrl
    ) {}

    /**
     * Fetch customer data from CRM (Salesforce example)
     */
    public function getCustomerFromCrm(string $email): ?array
    {
        $query = urlencode("SELECT Id, Name, Email, Phone, Account_Type__c FROM Contact WHERE Email = '{$email}'");

        $response = $this->makeApiCall(
            'GET',
            "/services/data/v59.0/query?q={$query}"
        );

        if ($response['records'] ?? null) {
            return $response['records'][0];
        }

        return null;
    }

    /**
     * Log support interaction to CRM
     */
    public function logSupportInteraction(string $customerId, array $interaction): bool
    {
        $payload = [
            'Subject' => 'Support Bot Interaction',
            'Description' => $interaction['summary'],
            'Type' => 'Support',
            'Status' => 'Logged',
            'WhoId' => $customerId,
            'Priority' => $this->mapPriority($interaction['priority'] ?? 'medium')
        ];

        $response = $this->makeApiCall(
            'POST',
            '/services/data/v59.0/sobjects/Task',
            $payload
        );

        return $response['id'] ?? null;
    }

    /**
     * Create case in CRM
     */
    public function createCrmCase(string $customerId, array $ticketData): ?string
    {
        $payload = [
            'AccountId' => $customerId,
            'Subject' => $ticketData['subject'],
            'Description' => $ticketData['description'],
            'Priority' => $this->mapPriority($ticketData['priority'],
            'Origin' => 'Support Bot',
            'Status' => 'New'
        ];

        $response = $this->makeApiCall(
            'POST',
            '/services/data/v59.0/sobjects/Case',
            $payload
        );

        return $response['id'] ?? null;
    }

    /**
     * Update case with bot analysis
     */
    public function updateCaseWithAnalysis(string $caseId, array $analysis): bool
    {
        $payload = [
            'Bot_Analysis__c' => json_encode($analysis),
            'Sentiment__c' => $analysis['sentiment'] ?? 'neutral',
            'Confidence__c' => $analysis['confidence'] ?? 0.5
        ];

        $response = $this->makeApiCall(
            'PATCH',
            "/services/data/v59.0/sobjects/Case/{$caseId}",
            $payload
        );

        return (bool)$response;
    }

    /**
     * Make authenticated API call to CRM
     */
    private function makeApiCall(string $method, string $endpoint, ?array $data = null): array
    {
        $url = $this->crmBaseUrl . $endpoint;

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => [
                    "Authorization: Bearer {$this->crmApiKey}",
                    'Content-Type: application/json'
                ],
                'content' => $data ? json_encode($data) : null
            ]
        ];

        $response = file_get_contents($url, false, $context);
        return json_decode($response, true) ?? [];
    }

    private function mapPriority(string $priority): string
    {
        return match ($priority) {
            'high', 'critical' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => 'Medium'
        };
    }
}
```

## Monitoring & Observability

```php
<?php
# filename: src/Support/SupportMonitoring.php
declare(strict_types=1);

namespace App\Support;

class SupportMonitoring
{
    public function __construct(
        private \PDO $db,
        private string $sentryDsn = ''
    ) {}

    /**
     * Log bot metrics
     */
    public function logBotMetric(string $conversationId, array $metric): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO bot_metrics (conversation_id, response_time, tokens_used, cost_estimate, quality_score, created_at)
             VALUES (:conversation_id, :response_time, :tokens_used, :cost_estimate, :quality_score, NOW())"
        );

        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':response_time' => $metric['response_time'] ?? 0,
            ':tokens_used' => $metric['tokens_used'] ?? 0,
            ':cost_estimate' => $metric['cost_estimate'] ?? 0,
            ':quality_score' => $metric['quality_score'] ?? 0
        ];
    }

    /**
     * Track API performance
     */
    public function getPerformanceMetrics(\DateTime $start, \DateTime $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_requests,
                AVG(response_time) as avg_response_time,
                MAX(response_time) as max_response_time,
                MIN(response_time) as min_response_time,
                SUM(tokens_used) as total_tokens,
                SUM(cost_estimate) as total_cost,
                AVG(quality_score) as avg_quality
             FROM bot_metrics
             WHERE created_at BETWEEN :start AND :end"
        );

        $stmt->execute([
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Alert on performance degradation
     */
    public function checkAlerts(): void
    {
        $oneHourAgo = new \DateTime('-1 hour');

        $stmt = $this->db->prepare(
            "SELECT AVG(response_time) as avg_time
             FROM bot_metrics
             WHERE created_at > :time"
        );

        $stmt->execute([':time' => $oneHourAgo->format('Y-m-d H:i:s')];
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Alert if response time exceeds 5 seconds
        if ($result['avg_time'] > 5000) {
            $this->triggerAlert('High response time detected: ' . $result['avg_time'] . 'ms');
        }
    }

    private function triggerAlert(string $message): void
    {
        // Send to Sentry, Slack, or monitoring service
        error_log("ALERT: {$message}");
    }
}
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've built a comprehensive customer support bot system. Here's what you've accomplished:

- ✓ **Support Bot Core**: Created an intelligent bot that handles customer inquiries with context awareness
- ✓ **Knowledge Base Integration**: Implemented semantic search with query analysis and result re-ranking
- ✓ **Ticket Classification**: Built an automated system that classifies and routes tickets to appropriate departments
- ✓ **Sentiment Analysis**: Developed sentiment tracking to identify frustrated customers and prioritize escalations
- ✓ **Conversation Management**: Implemented persistent conversation storage with Redis caching for performance
- ✓ **Multi-Channel Support**: Created handlers for email and live chat with unified bot processing
- ✓ **Escalation Engine**: Designed seamless handoff to human agents when needed
- ✓ **Analytics Dashboard**: Built metrics tracking for bot performance, resolution rates, and customer satisfaction

### Key Concepts Learned

- **Context Management**: Maintaining conversation history across multiple turns enables natural, coherent interactions
- **Semantic Search**: Using embeddings and vector stores provides more relevant knowledge base results than keyword matching
- **Sentiment Detection**: Analyzing emotional tone helps prioritize urgent issues and improve customer experience
- **Multi-Channel Architecture**: Unified bot processing across channels maintains consistency while allowing channel-specific optimizations
- **Intelligent Escalation**: Automated detection of when to escalate ensures complex issues reach human experts

### Next Steps

Your support bot is production-ready, but consider these enhancements:

- Add more channels (SMS, social media, phone)
- Implement A/B testing for different response strategies
- Add machine learning to improve classification accuracy over time
- Integrate with CRM systems for richer customer context
- Build a dashboard UI for monitoring bot performance in real-time

## Troubleshooting

### Issue: Knowledge Base Returns No Results

**Symptom**: `search()` method returns empty array even with relevant articles

**Cause**: Vector store not properly initialized or embeddings not generated

**Solution**: Ensure vector store is set up and articles have embeddings:

```php
// After adding article, verify embedding was created
$articleId = $knowledgeBase->addArticle($title, $content, $category);
$embedding = $vectorStore->getEmbedding($articleId);
if (!$embedding) {
    // Regenerate embedding
    $vectorStore->addDocument($articleId, $title . "\n\n" . $content);
}
```

### Issue: Conversations Not Persisting

**Symptom**: Conversation history lost on page refresh

**Cause**: Redis connection failing or cache expiration too short

**Solution**: Add error handling and fallback to database:

```php
try {
    $cached = $this->redis->get("conversation:{$customerId}");
} catch (\Exception $e) {
    error_log("Redis error: " . $e->getMessage());
    // Fallback to database only
    return $this->loadFromDatabase($customerId);
}
```

### Issue: Sentiment Analysis Always Returns 0.0

**Symptom**: `analyze()` method returns neutral score regardless of message tone

**Cause**: JSON parsing failing or Claude response format changed

**Solution**: Add better error handling and logging:

```php
$jsonText = $response->content[0]->text;
if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
    $data = json_decode($matches[0], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        error_log("Response text: " . $jsonText);
    }
    $score = $data['sentiment_score'] ?? 0.0;
    // ...
}
```

### Issue: Escalation Not Triggering

**Symptom**: Bot doesn't escalate even when customer requests human agent

**Cause**: `detectHumanRequest()` method not matching customer phrases

**Solution**: Expand phrase matching and add logging:

```php
private function detectHumanRequest(string $message): bool
{
    $lowerMessage = strtolower($message);
    // Add more variations
    $humanRequestPhrases = [
        'speak to a person', 'talk to someone', 'human agent',
        'real person', 'customer service', 'support agent',
        'can\'t help', 'not helpful', 'need help', 'want to talk',
        'speak with someone', 'get a person'
    ];

    foreach ($humanRequestPhrases as $phrase) {
        if (str_contains($lowerMessage, $phrase)) {
            error_log("Human request detected: {$message}");
            return true;
        }
    }

    return false;
}
```

## Further Reading

- [Anthropic Claude API Documentation](https://docs.claude.com) — Official Claude API reference and best practices
- [Customer Support Best Practices](https://www.zendesk.com/blog/customer-support-best-practices/) — Industry standards for support workflows
- [Semantic Search with Embeddings](https://platform.openai.com/docs/guides/embeddings) — Understanding vector embeddings for search
- [Redis Caching Strategies](https://redis.io/docs/manual/patterns/) — Performance optimization patterns
- [WebSocket Real-time Communication](https://www.php.net/manual/en/book.websockets.php) — PHP WebSocket implementation
- [Chapter 20: Real-time Chat with WebSockets](/series/claude-php-developers/chapters/20-realtime-chat-websockets) — Related chapter on real-time systems
- [Chapter 18: Caching Strategies](/series/claude-php-developers/chapters/18-caching-strategies) — Advanced caching techniques

## Key Takeaways

- ✓ AI support bots can handle 60-80% of common inquiries automatically
- ✓ Knowledge base integration provides accurate, consistent answers
- ✓ Sentiment analysis helps identify frustrated customers needing immediate attention
- ✓ Seamless escalation to humans ensures complex issues get expert help
- ✓ Multi-channel support (email, chat, phone) provides flexibility
- ✓ Conversation context enables natural, coherent interactions
- ✓ Ticket classification automates routing to appropriate teams
- ✓ Analytics track bot performance and identify improvement opportunities
- ✓ Empathetic, professional tone maintains brand voice
- ✓ Real-time responses improve customer satisfaction significantly

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="28"
  label="You've built an intelligent customer support bot!"
/>

---

Continue to [Chapter 29: Content Moderation System](/series/claude-php-developers/chapters/29-content-moderation) to build automated content moderation.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 28 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-28)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-28
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php -S localhost:8000 api/support-bot.php
```
