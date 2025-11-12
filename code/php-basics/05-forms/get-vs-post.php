<?php

declare(strict_types=1);

/**
 * GET vs POST Methods
 * 
 * Demonstrates the difference between GET and POST request methods.
 * Shows when to use each method and how to access their data.
 */

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GET vs POST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .method-section {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .result {
            background-color: #d4edda;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }

        input[type="text"] {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .info {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }

        code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <h1>GET vs POST Methods</h1>

    <div class="info">
        <strong>Key Differences:</strong>
        <ul>
            <li><strong>GET:</strong> Parameters visible in URL, used for retrieving data (search, filters)</li>
            <li><strong>POST:</strong> Parameters hidden, used for submitting sensitive data (forms, passwords)</li>
        </ul>
    </div>

    <!-- GET Method Example -->
    <div class="method-section">
        <h2>GET Method Example</h2>
        <p>Data will be visible in the URL</p>

        <form method="GET" action="get-vs-post.php">
            <input type="text" name="search" placeholder="Search term" required>
            <button type="submit">Search (GET)</button>
        </form>

        <?php if (isset($_GET['search'])): ?>
            <div class="result">
                <strong>GET Request Received:</strong><br>
                Search term: <?= htmlspecialchars($_GET['search']) ?><br>
                <small>Notice the URL: <code>?search=<?= urlencode($_GET['search']) ?></code></small>
            </div>
        <?php endif; ?>
    </div>

    <!-- POST Method Example -->
    <div class="method-section">
        <h2>POST Method Example</h2>
        <p>Data will NOT be visible in the URL</p>

        <form method="POST" action="get-vs-post.php">
            <input type="text" name="username" placeholder="Username" required>
            <button type="submit">Login (POST)</button>
        </form>

        <?php if (isset($_POST['username'])): ?>
            <div class="result">
                <strong>POST Request Received:</strong><br>
                Username: <?= htmlspecialchars($_POST['username']) ?><br>
                <small>Notice the URL: <code>get-vs-post.php</code> (no parameters visible)</small>
            </div>
        <?php endif; ?>
    </div>

    <!-- When to Use Each -->
    <div class="method-section">
        <h2>When to Use Each Method</h2>

        <h3>Use GET when:</h3>
        <ul>
            <li>✓ Retrieving data (search queries, filters)</li>
            <li>✓ Users should be able to bookmark the URL</li>
            <li>✓ Data is not sensitive</li>
            <li>✓ Idempotent operations (multiple requests don't change state)</li>
        </ul>

        <h3>Use POST when:</h3>
        <ul>
            <li>✓ Submitting sensitive data (passwords, credit cards)</li>
            <li>✓ Changing server state (creating, updating, deleting)</li>
            <li>✓ Sending large amounts of data</li>
            <li>✓ Uploading files</li>
        </ul>
    </div>

    <!-- Accessing Data in PHP -->
    <div class="method-section">
        <h2>Accessing Data in PHP</h2>

        <h3>GET Data:</h3>
        <pre><code>$searchTerm = $_GET['search'] ?? 'default';
$page = $_GET['page'] ?? 1;</code></pre>

        <h3>POST Data:</h3>
        <pre><code>$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';</code></pre>

        <h3>Check Request Method:</h3>
        <pre><code>if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request
}</code></pre>
    </div>

    <!-- Current Request Info -->
    <div class="method-section">
        <h2>Current Request Information</h2>
        <p><strong>Method:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'CLI') ?></p>
        <p><strong>Query String:</strong> <?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? 'none') ?></p>

        <?php if (!empty($_GET)): ?>
            <p><strong>GET Parameters:</strong></p>
            <pre><?php print_r($_GET); ?></pre>
        <?php endif; ?>

        <?php if (!empty($_POST)): ?>
            <p><strong>POST Parameters:</strong></p>
            <pre><?php print_r($_POST); ?></pre>
        <?php endif; ?>
    </div>
</body>

</html>