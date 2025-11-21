<?php

declare(strict_types=1);

require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/CsrfProtection.php';

use App\Validation\Validator;
use App\Security\CsrfProtection;

session_start();

$errors = [];
$success = false;
$name = '';
$email = '';
$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token first
    if (!CsrfProtection::verify()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data with null coalescing operator
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Use Validator class
        $validator = new Validator($_POST);
        $validator
            ->required('name', 'Name is required')
            ->min('name', 2, 'Name must be at least 2 characters')
            ->max('name', 50, 'Name must not exceed 50 characters')
            
            ->required('email', 'Email is required')
            ->email('email', 'Please enter a valid email address')
            
            ->required('message', 'Message is required')
            ->min('message', 10, 'Message must be at least 10 characters')
            ->max('message', 1000, 'Message must not exceed 1000 characters');

        if ($validator->fails()) {
            $errors = $validator->errors();
        } else {
            // In production, save to database or send email
            $success = true;
            
            // Clear form data
            $name = '';
            $email = '';
            $message = '';
            
            // Regenerate CSRF token
            CsrfProtection::regenerateToken();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            background: #4caf50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background: #2196f3;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #1976d2;
        }
    </style>
</head>
<body>
    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <div class="success">
            Thank you! Your message has been sent successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error" style="background: #ffebee; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="">
        <?= CsrfProtection::field() ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message *</label>
            <textarea 
                id="message" 
                name="message" 
                required
            ><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (isset($errors['message'])): ?>
                <span class="error"><?= htmlspecialchars($errors['message']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Send Message</button>
    </form>
</body>
</html>





