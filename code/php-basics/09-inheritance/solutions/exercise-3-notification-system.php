<?php

declare(strict_types=1);

/**
 * Exercise 3: Notification System with Interfaces
 * 
 * Build a flexible notification system:
 * 
 * Requirements:
 * - Notifiable interface with send() method
 * - EmailNotification implementation
 * - SmsNotification implementation
 * - PushNotification implementation
 * - notifyUser() function that accepts Notifiable interface
 * - Array of different channels to loop through
 * - Challenge: Logger class that also implements Notifiable
 */

interface Notifiable
{
    public function send(string $message): void;
}

class EmailNotification implements Notifiable
{
    public function __construct(
        private string $emailAddress
    ) {}

    public function send(string $message): void
    {
        echo "Email to {$this->emailAddress}: {$message}" . PHP_EOL;
    }
}

class SmsNotification implements Notifiable
{
    public function __construct(
        private string $phoneNumber
    ) {}

    public function send(string $message): void
    {
        echo "SMS to {$this->phoneNumber}: {$message}" . PHP_EOL;
    }
}

class PushNotification implements Notifiable
{
    public function __construct(
        private string $deviceId
    ) {}

    public function send(string $message): void
    {
        echo "Push to device {$this->deviceId}: {$message}" . PHP_EOL;
    }
}

/**
 * Challenge: Logger class that implements Notifiable
 * This demonstrates how unrelated classes can share an interface
 */
class NotificationLogger implements Notifiable
{
    private array $logs = [];

    public function send(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] LOG: {$message}";
        $this->logs[] = $logEntry;
        echo $logEntry . PHP_EOL;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}

/**
 * Generic notification function that works with any Notifiable implementation
 */
function notifyUser(Notifiable $channel, string $message): void
{
    $channel->send($message);
}

// Test the notification system
echo "=== Notification System Demo ===" . PHP_EOL . PHP_EOL;

$email = new EmailNotification('user@example.com');
$sms = new SmsNotification('+1-555-0123');
$push = new PushNotification('device-abc-123');
$logger = new NotificationLogger();

echo "--- Individual notifications ---" . PHP_EOL;
notifyUser($email, 'Your order has been shipped!');
notifyUser($sms, 'Your verification code is: 123456');
notifyUser($push, 'You have a new message');
notifyUser($logger, 'System notification sent');

echo PHP_EOL . "--- Batch notifications ---" . PHP_EOL;
$channels = [
    $email,
    $sms,
    $push,
    $logger
];

$broadcastMessage = 'Important: Scheduled maintenance tonight at 2 AM';
echo "Broadcasting to all channels: '{$broadcastMessage}'" . PHP_EOL . PHP_EOL;

foreach ($channels as $channel) {
    notifyUser($channel, $broadcastMessage);
}

echo PHP_EOL . "--- Logger history ---" . PHP_EOL;
$logs = $logger->getLogs();
echo "Total logged notifications: " . count($logs) . PHP_EOL;
foreach ($logs as $log) {
    echo "  {$log}" . PHP_EOL;
}
