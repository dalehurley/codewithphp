<?php

declare(strict_types=1);

/**
 * Input Sanitization and XSS Prevention
 * 
 * Demonstrates why sanitization is critical and how to do it properly.
 * Shows the difference between sanitized and unsanitized output.
 */

$userInput = '';
$sanitized = '';
$showDemo = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['input'] ?? '';
    $sanitized = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
    $showDemo = true;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sanitization Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }

        .danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
            border-radius: 4px;
        }

        .safe {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
            border-radius: 4px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: monospace;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .demo-box {
            border: 2px solid #dee2e6;
            padding: 15px;
            margin: 15px 0;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }

        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <h1>Input Sanitization Demo</h1>

    <div class="danger">
        <strong>⚠️ Security Warning:</strong> Never display user input directly without sanitization.
        This can lead to XSS (Cross-Site Scripting) attacks!
    </div>

    <form method="POST" action="sanitization-demo.php">
        <p>
            <label for="input"><strong>Enter some text (try adding HTML tags):</strong></label>
        </p>
        <textarea id="input" name="input" rows="4" placeholder="Try entering: <script>alert('XSS')</script>"><?= htmlspecialchars($userInput) ?></textarea>
        <br>
        <button type="submit">Test Sanitization</button>
    </form>

    <?php if ($showDemo): ?>
        <h2>Results</h2>

        <div class="danger">
            <h3>❌ WITHOUT Sanitization (DANGEROUS)</h3>
            <p><strong>What attackers could inject:</strong></p>
            <div class="demo-box">
                <!-- This would be dangerous in production - DO NOT DO THIS -->
                <!-- Output: <?= $userInput ?> -->
                <em>[Dangerous output intentionally disabled - imagine malicious scripts running here]</em>
            </div>
            <p><strong>Raw HTML source:</strong></p>
            <pre><code><?= htmlspecialchars($userInput) ?></code></pre>
        </div>

        <div class="safe">
            <h3>✅ WITH Sanitization (SAFE)</h3>
            <p><strong>Safely escaped output:</strong></p>
            <div class="demo-box">
                <?= $sanitized ?>
            </div>
            <p><strong>What the browser receives:</strong></p>
            <pre><code><?= htmlspecialchars($sanitized) ?></code></pre>
        </div>

        <h3>How It Works</h3>
        <p>The <code>htmlspecialchars()</code> function converts special characters:</p>
        <ul>
            <li><code>&lt;</code> becomes <code>&amp;lt;</code></li>
            <li><code>&gt;</code> becomes <code>&amp;gt;</code></li>
            <li><code>&quot;</code> becomes <code>&amp;quot;</code></li>
            <li><code>&apos;</code> becomes <code>&amp;#039;</code> (with ENT_QUOTES)</li>
            <li><code>&amp;</code> becomes <code>&amp;amp;</code></li>
        </ul>
    <?php endif; ?>

    <h2>Common Attack Examples</h2>

    <div class="demo-box">
        <h3>Try these inputs to see how sanitization protects you:</h3>
        <ol>
            <li><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code> - JavaScript injection</li>
            <li><code>&lt;img src=x onerror=alert('XSS')&gt;</code> - Malicious image tag</li>
            <li><code>&lt;a href="javascript:alert('XSS')"&gt;Click me&lt;/a&gt;</code> - Malicious link</li>
            <li><code>&lt;iframe src="http://evil.com"&gt;&lt;/iframe&gt;</code> - Hidden iframe</li>
        </ol>
    </div>

    <h2>Best Practices</h2>

    <div class="safe">
        <h3>Always Sanitize:</h3>
        <pre><code>&lt;?php
// ✅ GOOD - Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// ✅ GOOD - Short syntax in templates
&lt;?= htmlspecialchars($userInput) ?&gt;

// ❌ BAD - Never output directly
echo $userInput;  // DANGEROUS!
?&gt;</code></pre>
    </div>

    <div class="demo-box">
        <h3>Filter Functions for Different Contexts:</h3>
        <pre><code>&lt;?php
// For HTML output
$safe = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// For URLs
$safe = urlencode($input);

// For email validation
$email = filter_var($input, FILTER_VALIDATE_EMAIL);

// For integer validation
$id = filter_var($input, FILTER_VALIDATE_INT);

// Remove all HTML tags
$clean = strip_tags($input);

// Remove specific HTML tags, keep others
$clean = strip_tags($input, '&lt;p&gt;&lt;br&gt;');
?&gt;</code></pre>
    </div>

    <h2>Security Checklist</h2>
    <ul>
        <li>✓ Always use <code>htmlspecialchars()</code> when displaying user input</li>
        <li>✓ Validate input on the server side (never trust the client)</li>
        <li>✓ Use prepared statements for database queries (see Chapter 14)</li>
        <li>✓ Implement CSRF protection for forms (see Chapter 15)</li>
        <li>✓ Set proper HTTP headers (<code>Content-Security-Policy</code>)</li>
        <li>✓ Keep PHP and all libraries up to date</li>
    </ul>
</body>

</html>