<?php

declare(strict_types=1);

// PHP 8.0+ named arguments
// Similar to Python's keyword arguments

function createUser(
    string $name,
    string $email,
    int $age = 0,
    bool $active = true
): array {
    return [
        'name' => $name,
        'email' => $email,
        'age' => $age,
        'active' => $active,
    ];
}

// Traditional positional arguments
$user1 = createUser('John Doe', 'john@example.com', 30, true);
print_r($user1);

// Using named arguments (PHP 8.0+)
$user2 = createUser(
    name: 'Jane Smith',
    email: 'jane@example.com',
    active: false
);
print_r($user2);

// Named arguments allow skipping optional parameters
$user3 = createUser(
    email: 'bob@example.com',
    name: 'Bob Wilson'
);
print_r($user3);

// Named arguments make code more readable
function sendEmail(
    string $to,
    string $subject,
    string $body,
    ?string $from = null,
    array $attachments = []
): bool {
    // Simulate email sending
    echo "Sending email to: {$to}\n";
    echo "Subject: {$subject}\n";
    echo "Body: {$body}\n";
    if ($from !== null) {
        echo "From: {$from}\n";
    }
    if (!empty($attachments)) {
        echo "Attachments: " . count($attachments) . "\n";
    }
    return true;
}

// Using named arguments makes the call self-documenting
sendEmail(
    to: 'user@example.com',
    subject: 'Welcome!',
    body: 'Thank you for joining us.',
    from: 'noreply@example.com',
    attachments: ['file1.pdf', 'file2.jpg']
);

