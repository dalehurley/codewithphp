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

Claude's natural language understanding makes it perfect for support scenarios—it can interpret customer intent, maintain conversation context, access documentation, and provide helpful, empathetic responses while maintaining your brand voice.

**What You'll Build**: A production-ready support system that handles multi-channel conversations, integrates with knowledge bases, classifies and routes tickets, analyzes sentiment, and seamlessly hands off to human agents when needed.

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

use Anthropic\Anthropic;

class SupportBot
{
    public function __construct(
        private Anthropic $claude,
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

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.7,
            'system' => $systemPrompt,
            'messages' => $messages
        ]);

        return new BotResponse(
            text: $response->content[0]->text,
            confidence: $this->calculateConfidence($response),
            suggestedActions: $this->extractActions($response),
            escalationNeeded: $this->detectEscalationIntent($response)
        );
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

use Anthropic\Anthropic;

class KnowledgeBase
{
    public function __construct(
        private Anthropic $claude,
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
            'model' => 'claude-haiku-4-20250514',
            'max_tokens' => 512,
            'temperature' => 0.3,
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
        ]);

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
        $stmt->execute([':id' => $id]);

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

use Anthropic\Anthropic;

class TicketClassifier
{
    public function __construct(
        private Anthropic $claude
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
            'model' => 'claude-haiku-4-20250514',
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

use Anthropic\Anthropic;

class SentimentAnalyzer
{
    private array $cache = [];

    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Analyze message sentiment
     * Returns score from -1 (very negative) to +1 (very positive)
     */
    public function analyze(string $message): float
    {
        // Check cache
        $cacheKey = md5($message);
        if (isset($this->cache[$cacheKey])) {
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
        $messages = $conversation->getMessages();
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
        $stmt->execute([':customer_id' => $customerId]);
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
    public function create(string $customerId, array $metadata = []): Conversation
    {
        $stmt = $this->db->prepare(
            "INSERT INTO conversations (customer_id, status, metadata, created_at, updated_at)
             VALUES (:customer_id, 'active', :metadata, NOW(), NOW())"
        );

        $stmt->execute([
            ':customer_id' => $customerId,
            ':metadata' => json_encode($metadata)
        ]);

        $id = (int)$this->db->lastInsertId();

        $conversation = new Conversation(
            id: $id,
            customerId: $customerId,
            messages: [],
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
        ]);

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
            messages: json_decode($data['messages'] ?? '[]', true),
            metadata: json_decode($data['metadata'] ?? '{}', true),
            status: $data['status'],
            createdAt: new \DateTime($data['created_at'])
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
        $customerId = $this->identifyCustomer($emailData['from']);
        $subject = $emailData['subject'];
        $body = $this->extractTextFromEmail($emailData['body']);

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
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return (string)$result['id'];
        }

        // Create new customer
        $stmt = $this->db->prepare(
            "INSERT INTO customers (email, created_at) VALUES (:email, NOW())"
        );
        $stmt->execute([':email' => $email]);

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
        ]);
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
        ]);
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
        ]));
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
            ]));

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

use Anthropic\Anthropic;
use App\Support\SupportBot;
use App\Support\KnowledgeBase;
use App\Support\TicketSystem;
use App\Support\ConversationManager;
use App\Support\SentimentAnalyzer;
use App\Support\EscalationEngine;

header('Content-Type: application/json');

// Initialize components
$db = new PDO(getenv('DATABASE_DSN'));
$redis = new Redis();
$redis->connect('localhost', 6379);

$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$knowledgeBase = new KnowledgeBase($claude, $db, new VectorStore());
$ticketSystem = new TicketSystem($db);
$conversations = new ConversationManager($db, $redis);
$sentiment = new SentimentAnalyzer($claude);
$escalation = new EscalationEngine($db, $redis);

$bot = new SupportBot(
    claude: $claude,
    knowledgeBase: $knowledgeBase,
    ticketSystem: $ticketSystem,
    conversations: $conversations,
    sentiment: $sentiment,
    escalation: $escalation
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
        customerId: $input['customer_id'],
        message: $input['message'],
        context: $input['context'] ?? []
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
        ]);
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
        ]);
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
        ]);
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
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        $contents = array_map(fn($m) => strtolower($m['content']), $userMessages);

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
