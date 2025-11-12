<?php

declare(strict_types=1);

/**
 * CSRF Protected Form Example
 * 
 * Demonstrates a complete form with CSRF protection,
 * validation, and security best practices.
 * 
 * Run with: php -S localhost:8000 protected-form.php
 */

require_once 'CsrfProtection.php';

// Secure session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0'); // Set to 1 in production with HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');

session_start();
CsrfProtection::init();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Validate CSRF token
        CsrfProtection::validateOrFail();

        // 2. Validate and sanitize input
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($email === false || $email === null) {
            $error = 'Invalid email address';
        } elseif (empty($name)) {
            $error = 'Name is required';
        } else {
            // 3. Process form (save to database, send email, etc.)
            $message = "✓ Form submitted successfully!<br>";
            $message .= "Name: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "<br>";
            $message .= "Email: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

            // 4. Regenerate token after successful submission
            CsrfProtection::regenerateToken();
        }
    } catch (RuntimeException $e) {
        $error = '❌ Security error: ' . htmlspecialchars($e->getMessage());
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Protected Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            line-height: 1.6;
            background: #f5f5f5;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .success {
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #155724;
        }

        .error {
            padding: 15px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #721c24;
        }

        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #007bff;
        }

        button {
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .info {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .info h3 {
            margin-bottom: 10px;
            color: #0056b3;
        }

        .info ul {
            margin-left: 20px;
        }

        .info li {
            margin-bottom: 5px;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <h1>🔒 CSRF Protected Form</h1>
    <p class="subtitle">Secure form submission with Cross-Site Request Forgery protection</p>

    <?php if ($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                placeholder="John Doe"
                value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                placeholder="john@example.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <?= CsrfProtection::getTokenField() ?>

        <button type="submit">Submit Securely</button>
    </form>

    <div class="info">
        <h3>Security Features</h3>
        <ul>
            <li>✓ CSRF token validation on every submission</li>
            <li>✓ Timing-attack-safe token comparison</li>
            <li>✓ Token regeneration after successful submission</li>
            <li>✓ XSS protection via htmlspecialchars()</li>
            <li>✓ Input validation and sanitization</li>
            <li>✓ Secure session configuration</li>
        </ul>

        <h3 style="margin-top: 15px;">Try Testing It</h3>
        <ul>
            <li>Open browser DevTools (F12)</li>
            <li>Inspect the form and modify the <code>csrf_token</code> value</li>
            <li>Submit the form - it should be rejected!</li>
            <li>Remove the token field entirely - also rejected!</li>
        </ul>
    </div>
</body>

</html>