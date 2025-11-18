<?php

declare(strict_types=1);

/**
 * Data Extraction Example
 *
 * This example demonstrates using Claude to extract structured data from:
 * - Unstructured text
 * - Email content
 * - Web content
 * - Documents
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\QuickStart\ClaudeClient;
use ClaudePhp\QuickStart\CostCalculator;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not found in .env file\n");
}

echo "=== Claude Data Extraction Examples ===\n\n";

// Initialize client and cost calculator
$client = new ClaudeClient($apiKey);
$costCalc = new CostCalculator();

try {
    // Example 1: Extract Contact Information
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Contact Information Extraction\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $emailText = <<<'TEXT'
Hi there,

Thanks for reaching out! I'm Sarah Johnson, the Head of Engineering at TechCorp Inc.
You can reach me at sarah.johnson@techcorp.com or call my office at (555) 123-4567.
Our headquarters is located at 123 Innovation Drive, San Francisco, CA 94102.

Looking forward to our meeting next Tuesday at 2 PM PST.

Best regards,
Sarah
TEXT;

    $response = $client->sendMessage(
        "Extract the following information from this email and return it as JSON:\n" .
        "- Full name\n" .
        "- Job title\n" .
        "- Company\n" .
        "- Email address\n" .
        "- Phone number\n" .
        "- Full address\n" .
        "- Meeting date and time\n\n" .
        "Email:\n{$emailText}\n\n" .
        "Return ONLY valid JSON, no other text."
    );

    echo "Input Text:\n{$emailText}\n\n";
    echo "Extracted Data (JSON):\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 2: Extract Product Information
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Product Specification Extraction\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $productDesc = <<<'TEXT'
Introducing the UltraBook Pro 15 - a powerful laptop designed for professionals.
Features include an Intel Core i9 processor running at 3.2GHz, 32GB DDR5 RAM,
and a massive 2TB NVMe SSD. The stunning 15.6-inch 4K OLED display delivers
incredible visuals. Battery life lasts up to 12 hours on a single charge.
Weighing just 3.5 pounds, it's incredibly portable. All this for just $2,499.99.
Available in Space Gray, Silver, and Midnight Blue. Ships within 2-3 business days.
TEXT;

    $response = $client->sendMessage(
        "Extract product details from this description as JSON with these fields:\n" .
        "- name\n" .
        "- processor\n" .
        "- ram\n" .
        "- storage\n" .
        "- display\n" .
        "- battery_life\n" .
        "- weight\n" .
        "- price\n" .
        "- colors (array)\n" .
        "- shipping_time\n\n" .
        "Description:\n{$productDesc}\n\n" .
        "Return ONLY valid JSON."
    );

    echo "Extracted Product Data:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 3: Extract Events from Text
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "3. Event Information Extraction\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $eventText = <<<'TEXT'
Join us for the Annual Developer Conference 2025!

Date: March 15-17, 2025
Location: Convention Center, Austin, Texas
Time: 9:00 AM - 6:00 PM daily

Day 1: Keynote speeches and workshop sessions
Day 2: Technical deep dives and networking
Day 3: Hackathon and closing ceremony

Registration: $299 (Early bird: $199 until Feb 1)
Capacity: 500 attendees
Contact: events@devconf.com
TEXT;

    $response = $client->sendMessage(
        "Extract event details as JSON:\n" .
        "- event_name\n" .
        "- start_date\n" .
        "- end_date\n" .
        "- location\n" .
        "- daily_schedule (object with day1, day2, day3)\n" .
        "- pricing (object with regular and early_bird)\n" .
        "- capacity\n" .
        "- contact_email\n\n" .
        "Text:\n{$eventText}\n\n" .
        "Return ONLY valid JSON."
    );

    echo "Extracted Event Data:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 4: Extract Key-Value Pairs
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "4. Configuration Extraction\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $configText = <<<'TEXT'
Server Configuration Notes:

The database host is set to db.production.example.com running on port 3306.
Maximum connections allowed: 100
Timeout is configured for 30 seconds
SSL is enabled with cert path /etc/ssl/certs/db.pem
The backup schedule runs daily at 2 AM UTC
Retention policy keeps backups for 30 days
TEXT;

    $response = $client->sendMessage(
        "Extract configuration settings as JSON:\n" .
        "- database_host\n" .
        "- database_port\n" .
        "- max_connections\n" .
        "- timeout_seconds\n" .
        "- ssl_enabled (boolean)\n" .
        "- ssl_cert_path\n" .
        "- backup_schedule\n" .
        "- backup_retention_days\n\n" .
        "Text:\n{$configText}\n\n" .
        "Return ONLY valid JSON."
    );

    echo "Extracted Configuration:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Example 5: Extract and Categorize Items
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "5. Categorized Data Extraction\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $feedbackText = <<<'TEXT'
Customer Feedback Summary:

"The new interface is amazing! Much faster than before." - John D.
"I'm having trouble finding the export button." - Sarah M.
"Love the dark mode feature!" - Mike R.
"The mobile app keeps crashing on Android 13." - Lisa K.
"Great update overall, but please add keyboard shortcuts." - Tom B.
"Can't import CSV files anymore - this is critical for us." - Admin Team
TEXT;

    $response = $client->sendMessage(
        "Categorize this customer feedback into JSON format:\n" .
        "- positive (array of feedback items)\n" .
        "- negative (array of feedback items)\n" .
        "- feature_requests (array of requests)\n" .
        "- bugs (array of bug reports)\n\n" .
        "Each item should include: comment and author.\n\n" .
        "Feedback:\n{$feedbackText}\n\n" .
        "Return ONLY valid JSON."
    );

    echo "Categorized Feedback:\n";
    echo $client->extractText($response) . "\n\n";
    $costCalc->addUsageFromResponse($response);

    // Display summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Session Summary\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo $costCalc->getSummary() . "\n\n";

    echo "✓ All data extraction examples completed successfully!\n";

} catch (\ClaudePhp\Exceptions\APIError $e) {
    echo "✗ API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
