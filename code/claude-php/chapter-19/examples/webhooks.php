<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Queue Processing - Webhook Handlers\n\n";

/**
 * Webhook handler for async Claude job results
 */
class WebhookHandler
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function handle(array $payload, string $signature): bool
    {
        // Verify signature
        if (!$this->verifySignature($payload, $signature)) {
            echo "✗ Invalid signature\n";
            return false;
        }

        echo "✓ Signature verified\n";

        // Process webhook
        $status = $payload['status'] ?? 'unknown';

        switch ($status) {
            case 'completed':
                $this->handleSuccess($payload);
                break;

            case 'failed':
                $this->handleFailure($payload);
                break;

            default:
                echo "Unknown status: {$status}\n";
                return false;
        }

        return true;
    }

    private function verifySignature(array $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', json_encode($payload), $this->secret);
        return hash_equals($expected, $signature);
    }

    private function handleSuccess(array $payload): void
    {
        echo "\n✓ Job completed successfully\n";
        echo "Prompt: {$payload['prompt']}\n";
        echo "Response: " . substr($payload['response'], 0, 100) . "...\n";

        // Store result, update database, send notification, etc.
    }

    private function handleFailure(array $payload): void
    {
        echo "\n✗ Job failed\n";
        echo "Prompt: {$payload['prompt']}\n";
        echo "Error: {$payload['error']}\n";

        // Log error, retry job, send alert, etc.
    }
}

// Simulate webhook server
class WebhookServer
{
    public function simulateWebhook(WebhookHandler $handler): void
    {
        // Simulate successful completion
        $payload = [
            'status' => 'completed',
            'prompt' => 'What is PHP?',
            'response' => 'PHP is a server-side scripting language...',
            'timestamp' => date('c'),
        ];

        $secret = 'your-webhook-secret';
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        echo "Simulating webhook: completed job\n";
        echo str_repeat("─", 60) . "\n";
        $handler->handle($payload, $signature);

        echo "\n\n";

        // Simulate failure
        $failurePayload = [
            'status' => 'failed',
            'prompt' => 'Invalid request',
            'error' => 'Rate limit exceeded',
            'timestamp' => date('c'),
        ];

        $failureSignature = hash_hmac('sha256', json_encode($failurePayload), $secret);

        echo "Simulating webhook: failed job\n";
        echo str_repeat("─", 60) . "\n";
        $handler->handle($failurePayload, $failureSignature);
    }
}

// Run simulation
$handler = new WebhookHandler('your-webhook-secret');
$server = new WebhookServer();
$server->simulateWebhook($handler);

echo "\n" . str_repeat("=", 60) . "\n";
echo "Webhook Best Practices:\n";
echo str_repeat("=", 60) . "\n";
echo "1. Always verify webhook signatures\n";
echo "2. Use HTTPS for webhook endpoints\n";
echo "3. Implement idempotency (handle duplicate webhooks)\n";
echo "4. Return 200 OK quickly, process async\n";
echo "5. Log all webhook events\n";
echo "6. Implement retry logic for failed webhooks\n";
