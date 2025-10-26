---
title: "15: Managing State with Sessions and Cookies"
description: "Learn how to remember user data across multiple page loads using sessions and cookies, a key ingredient for features like user logins and shopping carts."
series: "php-basics"
chapter: 15
order: 15
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/14-interacting-with-databases-using-pdo"
---

# Chapter 15: Managing State with Sessions and Cookies

## Overview

HTTP, the protocol of the web, is **stateless**. This means that each request a browser makes to a server is treated as an independent event. The server doesn't automatically remember anything about previous requests from the same user. This poses a problem: how do we build features like a shopping cart or a user login system if the server forgets who you are on every single page load?

The solution is to manually create and manage **state**. State is the "memory" of an application. The two primary mechanisms for this in PHP are **cookies** and **sessions**. In this chapter, you'll learn how both work and when to use them.

## Objectives

- Understand what "stateless" means and why we need to manage state.
- Set and retrieve **cookies** to store small pieces of data on the user's browser.
- Start a **session** to store larger, more sensitive data on the server.
- Use the `$_SESSION` superglobal array to read and write session data.
- Understand the basic security implications of sessions and cookies.
- Implement secure session management with proper configuration.
- Protect forms from CSRF attacks using tokens.
- Create a flash message system for one-time user notifications.
- Understand database-backed sessions for production applications.

## What You'll Build

By the end of this chapter, you will have built:

- A cookie-based preference system that remembers user choices across page loads
- A complete session-based login system with authentication, logout, and timeout
- CSRF-protected forms with token generation and validation
- A flash message system for displaying one-time notifications after redirects
- Understanding of security best practices for sessions, cookies, and form submissions
- Knowledge of database-backed session storage for production applications

## Quick Start

Want to see sessions in action immediately? Create a file called `quick-session.php` and paste this:

```php
<?php
session_start();

// Increment page view counter
$_SESSION['views'] = ($_SESSION['views'] ?? 0) + 1;

// Store first visit time
if (!isset($_SESSION['first_visit'])) {
    $_SESSION['first_visit'] = date('Y-m-d H:i:s');
}
?>
<!DOCTYPE html>
<html>
<body>
    <h1>Session Demo</h1>
    <p>Page views: <?= $_SESSION['views'] ?></p>
    <p>First visit: <?= $_SESSION['first_visit'] ?></p>
    <p><a href="?">Refresh page</a> | <a href="?clear=1">Reset</a></p>
</body>
</html>
<?php
if (isset($_GET['clear'])) {
    session_destroy();
    header('Location: quick-session.php');
    exit;
}
?>
```

Run `php -S localhost:8000` and visit `http://localhost:8000/quick-session.php`. Refresh the page a few times to see the counter increase. Click "Reset" to clear the session. You've just created persistent state!

Now let's understand how this works and build something more practical.

## Step 1: Understanding and Using Cookies (~5 min)

A **cookie** is a small piece of text that the server sends to the user's browser. The browser then stores this cookie and sends it back to the server with every subsequent request. Cookies are useful for remembering non-sensitive information, like a user's preferred language or a "Remember Me" token.

1.  **Create Files**:
    Create two files: `set-cookie.php` and `view-cookie.php`.

2.  **Set a Cookie**:
    Use the `setcookie()` function to send a cookie to the browser. **Important:** This function _must_ be called before any HTML or other output is sent.

    **File: `set-cookie.php`**

    ```php
    <?php

    $cookieName = 'user_preference';
    $cookieValue = 'dark_mode';
    // Expire in 30 days (time() is now, 86400 is seconds in a day)
    $expiry = time() + (86400 * 30);
    $path = '/'; // Available on the whole site
    $domain = ''; // Leave empty for current domain
    $secure = false; // Set to true if using HTTPS
    $httponly = true; // Prevents JavaScript access (more secure)

    setcookie($cookieName, $cookieValue, [
        'expires' => $expiry,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Lax' // Helps prevent CSRF attacks
    ]);

    ?>
    <!DOCTYPE html>
    <html>
    <body>
        <h1>Cookie Has Been Set!</h1>
        <p>A cookie named 'user_preference' has been sent to your browser.</p>
        <p><a href="view-cookie.php">Click here to view the cookie</a></p>
    </body>
    </html>
    ```

    **Why it works**: The modern array syntax for `setcookie()` (PHP 7.3+) makes it clear what each option does. The `httponly` flag prevents JavaScript from reading the cookie, and `samesite` helps protect against Cross-Site Request Forgery (CSRF) attacks.

3.  **View the Cookie**:
    PHP makes all cookies sent by the browser available in the `$_COOKIE` superglobal array.

    **File: `view-cookie.php`**

    ```php
    <?php

    $preference = 'Not set';

    // Check if the cookie exists before trying to access it
    if (isset($_COOKIE['user_preference'])) {
        $preference = htmlspecialchars($_COOKIE['user_preference']);
    }

    ?>
    <!DOCTYPE html>
    <html>
    <body>
        <h1>Viewing the Cookie</h1>
        <p>Your saved preference is: <strong><?php echo $preference; ?></strong></p>
    </body>
    </html>
    ```

**Validation**:

- Visit `http://localhost:8000/set-cookie.php` in your browser
- You should see "Cookie Has Been Set!"
- Click the link to `view-cookie.php`
- You should see "Your saved preference is: **dark_mode**"
- Open your browser's developer tools (F12) → Application/Storage tab → Cookies
- You should see a cookie named `user_preference` with the value `dark_mode`

**Troubleshooting**:

- **"Cookie not being set"**: Make sure `setcookie()` is called _before_ any HTML output. Even a single space or newline before `<?php` will cause the function to fail silently.
- **"Headers already sent" error**: This means output was sent before `setcookie()`. Check for any echo statements, HTML, or even whitespace before the `<?php` tag.
- **Cookie not persisting**: Check that the expiry time is in the future. `time() + (86400 * 30)` gives you 30 days from now.

> **Security Note**: Cookies are stored on the user's computer and can be easily viewed and modified. **Never** store sensitive information like passwords or personal data in a cookie. Always validate and sanitize cookie data before using it, just like you would with form input.

## Step 2: How Sessions Work (~2 min)

Sessions solve the security problem of cookies. A session also uses a cookie, but only to store a single, random, meaningless piece of information: the **Session ID**.

Here's the flow:

1.  You start a session in PHP with `session_start()`.
2.  PHP generates a unique Session ID (a long random string) and sends it to the user's browser as a cookie (usually named `PHPSESSID`).
3.  PHP creates a file on the **server** (typically in `/tmp` or a configured session directory) corresponding to this Session ID.
4.  When you write data to the `$_SESSION` array, it's stored in that server-side file, not in the browser.
5.  On the next request, the browser sends the Session ID cookie back. PHP finds the corresponding file on the server, loads the data from it into the `$_SESSION` array, and your script can then access it.

This way, all the sensitive data stays safely on the server. The browser only knows the Session ID—a meaningless token that can't be used to directly access data.

> **Note**: You can check where PHP stores session files by running `php -i | grep session.save_path` in your terminal.

## Step 3: Implementing Sessions (~6 min)

Let's build a simple "login" system to demonstrate.

1.  **Create Files**:
    Create three files: `login.php` (our form), `authenticate.php` (processes the login), and `profile.php` (a protected page).

2.  **The Login Form**:
    **File: `login.php`**

    ```html
    <!DOCTYPE html>
    <html>
      <body>
        <h2>Login</h2>
        <form action="authenticate.php" method="post">
          <label for="username">Username:</label><br />
          <input type="text" id="username" name="username" /><br />
          <input type="submit" value="Login" />
        </form>
      </body>
    </html>
    ```

3.  **The Authentication Script**:
    This script will start the session and store the username. The `session_start()` function _must_ be called at the very top of any file that needs access to the session, before any output.

    **File: `authenticate.php`**

    ```php
    <?php
    // 1. Start the session
    session_start();

    // 2. In a real app, you would check username/password against a database.
    // For now, we'll just check if the username is not empty.
    if (!empty($_POST['username'])) {
        // 3. Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // 4. Store user information in the session array.
        $_SESSION['username'] = htmlspecialchars($_POST['username']);
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // 5. Redirect to the protected page.
        header('Location: profile.php');
        exit;
    } else {
        echo "Please provide a username.";
    }
    ?>
    ```

    **Why it works**: The `session_regenerate_id(true)` function creates a new Session ID and deletes the old one. This prevents **session fixation** attacks, where an attacker tricks a user into using a Session ID that the attacker already knows. We also sanitize the username with `htmlspecialchars()` before storing it.

4.  **The Profile Page**:
    This page also starts the session, then checks if the `username` key exists in the `$_SESSION` array. If it doesn't, it means the user isn't logged in, and we redirect them. We'll also add a simple timeout mechanism.

    **File: `profile.php`**

    ```php
    <?php
    // Start the session on every page that needs it!
    session_start();

    // Check if the user is logged in.
    // If not, redirect them to the login page.
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }

    // Optional: Implement a session timeout (e.g., 30 minutes of inactivity)
    $timeout_duration = 1800; // 30 minutes in seconds

    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Session has expired due to inactivity
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    $username = htmlspecialchars($_SESSION['username']);
    $loginTime = date('Y-m-d H:i:s', $_SESSION['login_time']);
    ?>
    <!DOCTYPE html>
    <html>
    <body>
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <p>You have successfully logged in.</p>
        <p>Login time: <?php echo $loginTime; ?></p>
        <p><a href="logout.php">Logout</a></p>
    </body>
    </html>
    ```

    **Why it works**: The timeout mechanism checks how long it's been since the user's last activity. If they've been idle for more than 30 minutes, their session is destroyed and they're redirected to login. This is a basic security measure that limits the window of opportunity for session hijacking.

5.  **The Logout Script**:
    To log a user out, you destroy their session data and optionally delete the session cookie.

    **File: `logout.php`**

    ```php
    <?php
    session_start();

    // Unset all session variables
    $_SESSION = [];

    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: login.php');
    exit;
    ?>
    ```

    **Why it works**: We clear the `$_SESSION` array, delete the session cookie from the browser, and then destroy the session file on the server. This ensures a complete logout with no remnants of the session.

**Validation**:

- Start your PHP server: `php -S localhost:8000`
- Visit `http://localhost:8000/login.php`
- Enter any username (e.g., "John") and submit
- You should be redirected to `profile.php` and see "Welcome, John!"
- Refresh the page several times—you should remain logged in
- Try visiting `profile.php` directly in a new incognito/private window—you should be redirected to `login.php`
- Click "Logout" and confirm you're sent back to the login page
- Use your browser's back button to try to access `profile.php`—you should be redirected to login again

**Troubleshooting**:

- **"Cannot modify header information" error**: This is the most common session error. `session_start()` must be called before _any_ output (HTML, whitespace, even a blank line). Check that your `<?php` tag is the very first thing in the file.
- **Session data not persisting**: Make sure you're calling `session_start()` at the top of every page that needs access to the session.
- **Session data appears empty**: Confirm that the session was actually started and that data was written to `$_SESSION` before trying to read it. Use `var_dump($_SESSION)` to debug.
- **"Session already started" warning**: You called `session_start()` twice. Use `if (session_status() === PHP_SESSION_NONE) { session_start(); }` if you're unsure whether a session has been started.

## Step 4: Session Security Best Practices (~3 min)

While sessions are more secure than cookies, they still require careful handling. Here are the most important security practices:

### 1. Configure Session Security Settings

Before calling `session_start()`, you can configure session behavior using `ini_set()`:

```php
<?php
// Prevent JavaScript from accessing the session cookie
ini_set('session.cookie_httponly', 1);

// Only send session cookie over HTTPS (set to 1 in production with SSL)
ini_set('session.cookie_secure', 0); // Set to 1 when using HTTPS

// Help prevent CSRF attacks
ini_set('session.cookie_samesite', 'Lax');

// Use only cookies for session ID (don't allow URLs)
ini_set('session.use_only_cookies', 1);

// Use strong session ID hashing
ini_set('session.hash_function', 'sha256');

session_start();
?>
```

### 2. Regenerate Session ID on Privilege Changes

Always regenerate the Session ID when a user's privilege level changes:

```php
// After successful login
session_regenerate_id(true);

// After logout
session_destroy();

// After changing from regular user to admin
session_regenerate_id(true);
```

### 3. Store User Agent and IP for Validation (Optional)

Some applications store the user's User Agent string and IP address on login, then validate them on each request to detect session hijacking:

```php
// On login
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

// On protected pages
if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] ||
    $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    // Possible session hijacking attempt
    session_destroy();
    header('Location: login.php');
    exit;
}
```

> **Warning**: IP validation can cause issues for users whose IP addresses change frequently (mobile networks, corporate proxies). Use this technique with caution.

### 4. Set Session Lifetime

Control how long session data persists:

```php
// Set session to expire after 30 minutes of inactivity
ini_set('session.gc_maxlifetime', 1800);

// Session cookie expires when browser closes (0) or after specific time
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
```

## Step 5: CSRF Protection with Tokens (~7 min)

**Cross-Site Request Forgery (CSRF)** is an attack where a malicious website tricks a user's browser into making unwanted requests to your application while they're logged in. For example, if you're logged into a banking site, a malicious site could try to submit a transfer form on your behalf.

The solution is to use **CSRF tokens**—random, secret values that are embedded in your forms and validated on submission.

### How CSRF Tokens Work

1. When displaying a form, generate a random token and store it in the session
2. Include the token as a hidden field in the form
3. When the form is submitted, verify that the submitted token matches the one in the session
4. If they match, the request is legitimate. If not, reject it.

### Implementation

Let's create a complete CSRF-protected form system.

**File: `csrf-functions.php`**

```php
<?php
/**
 * Generate a CSRF token and store it in the session
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Generate a random token
    $token = bin2hex(random_bytes(32));

    // Store it in the session
    $_SESSION['csrf_token'] = $token;

    return $token;
}

/**
 * Validate the submitted CSRF token against the session token
 */
function validateCsrfToken(string $submittedToken): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if token exists in session
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Generate an HTML hidden input field with CSRF token
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}
```

**File: `protected-form.php`**

```php
<?php
session_start();
require_once 'csrf-functions.php';

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $message = 'Invalid CSRF token. Possible CSRF attack detected.';
        $messageType = 'error';
    } else {
        // Token is valid, process the form
        $email = htmlspecialchars($_POST['email'] ?? '');
        $message = "Form submitted successfully! Email: $email";
        $messageType = 'success';

        // Regenerate token after successful submission (one-time use tokens)
        generateCsrfToken();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Protected Form</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 50px auto; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        label { display: block; margin: 10px 0 5px; }
        input[type="email"] { width: 100%; padding: 8px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Protected Form</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>

        <button type="submit">Submit</button>
    </form>

    <p style="color: #666; font-size: 0.9em;">
        This form is protected against CSRF attacks using a secure token.
    </p>
</body>
</html>
```

**Why it works**: The `random_bytes(32)` function generates cryptographically secure random bytes, which we convert to a hexadecimal string with `bin2hex()`. We use `hash_equals()` for comparison instead of `===` because it prevents timing attacks—`hash_equals()` takes the same amount of time regardless of where the strings differ, making it harder for attackers to guess the token character by character.

**Validation**:

- Visit `http://localhost:8000/protected-form.php`
- Submit the form with your email—you should see a success message
- Try to submit the form by modifying the CSRF token in the browser's DevTools before submitting—you should see an error message about an invalid token
- View the page source and note the hidden CSRF token field

**Troubleshooting**:

- **"Invalid CSRF token" on legitimate submission**: Make sure the session is started before calling `csrfField()` and that cookies are enabled in your browser.
- **Token validation always fails**: Check that `session_start()` is called before generating and validating tokens.
- **Form works without CSRF token**: Verify you're checking for the token's presence with `isset($_POST['csrf_token'])` before validating.

## Step 6: Flash Messages (~5 min)

**Flash messages** are one-time messages that persist across a single redirect. They're perfect for displaying status messages like "Post saved successfully!" or "Error: Invalid email address." after form submissions.

The pattern is simple: store the message in the session, display it on the next page load, then immediately delete it so it doesn't show up again.

### Implementation

**File: `flash-functions.php`**

```php
<?php
/**
 * Set a flash message in the session
 */
function setFlash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear the flash message (read once, then destroy)
 */
function getFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // Remove it after reading
        return $flash;
    }

    return null;
}

/**
 * Display the flash message as HTML
 */
function displayFlash(): string
{
    $flash = getFlash();

    if ($flash === null) {
        return '';
    }

    $type = htmlspecialchars($flash['type']);
    $message = htmlspecialchars($flash['message']);

    $styles = [
        'success' => 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;',
        'error' => 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;',
        'warning' => 'background: #fff3cd; color: #856404; border: 1px solid #ffeaa7;',
        'info' => 'background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;'
    ];

    $style = $styles[$type] ?? $styles['info'];

    return sprintf(
        '<div class="flash-message" style="padding: 10px; margin: 10px 0; border-radius: 4px; %s">%s</div>',
        $style,
        $message
    );
}
```

**File: `flash-demo-form.php`**

```php
<?php
session_start();
require_once 'flash-functions.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'save':
            // Simulate saving data
            setFlash('success', 'Your changes have been saved successfully!');
            break;
        case 'delete':
            // Simulate deletion
            setFlash('error', 'Item could not be deleted. Please try again.');
            break;
        case 'warning':
            setFlash('warning', 'Your session will expire in 5 minutes.');
            break;
        case 'info':
            setFlash('info', 'New features are now available. Check them out!');
            break;
    }

    // Redirect to avoid form resubmission
    header('Location: flash-demo-form.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Flash Messages Demo</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 50px auto; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .success-btn { background: #28a745; color: white; border: none; }
        .error-btn { background: #dc3545; color: white; border: none; }
        .warning-btn { background: #ffc107; color: black; border: none; }
        .info-btn { background: #17a2b8; color: white; border: none; }
    </style>
</head>
<body>
    <h1>Flash Messages Demo</h1>

    <?= displayFlash() ?>

    <p>Click any button to see a flash message appear after redirect:</p>

    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="save">
        <button type="submit" class="success-btn">Save (Success)</button>
    </form>

    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="error-btn">Delete (Error)</button>
    </form>

    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="warning">
        <button type="submit" class="warning-btn">Warn (Warning)</button>
    </form>

    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="info">
        <button type="submit" class="info-btn">Info (Info)</button>
    </form>

    <p style="color: #666; margin-top: 30px; font-size: 0.9em;">
        <strong>Try this:</strong> Click a button and refresh the page.
        Notice the message disappears—that's the "flash" behavior!
    </p>
</body>
</html>
```

**Why it works**: The key is the redirect after form submission (POST-Redirect-GET pattern). When you submit the form, we save the message to the session, then redirect. On the redirected GET request, we display the message and immediately remove it from the session using `unset()`. This ensures it only shows once.

**Validation**:

- Visit `http://localhost:8000/flash-demo-form.php`
- Click any button—you should be redirected and see a colored message
- Refresh the page (F5)—the message should disappear
- Click multiple buttons in sequence—each should show its own message once

**Troubleshooting**:

- **Message persists after refresh**: Make sure you're calling `unset($_SESSION['flash'])` after reading the message.
- **No message appears**: Check that the session is started before setting the flash message, and that you're redirecting after setting it.
- **Message appears on wrong page**: Make sure you're redirecting to the page where you call `displayFlash()`.

## Step 7: Database-Backed Sessions (~3 min)

By default, PHP stores sessions as files on the server's filesystem. This works fine for small applications, but production applications often need to store sessions in a database for better scalability, security, and reliability.

### When to Use Database Sessions

- **Multiple servers**: If your app runs on multiple servers (load balanced), file-based sessions won't work because each server has its own filesystem
- **Persistence**: Database sessions survive server restarts
- **Control**: You can easily query, analyze, or purge sessions
- **Security**: You can encrypt session data in the database

### Quick Example Setup

Here's a minimal example showing how to set up database sessions using PDO:

```php
<?php
// 1. Create a sessions table (run once)
/*
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT,
    last_activity INT,
    INDEX (last_activity)
);
*/

// 2. Create a custom session handler
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_activity) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$id, $data, time()]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sessions WHERE last_activity < ?'
        );
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}

// 3. Use it in your application
$pdo = new PDO('sqlite:database.db');
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();
```

> **Note**: This is a simplified example. Production implementations should add encryption, error handling, and possibly use Redis or Memcached for even better performance.

**Why it works**: PHP's `session_set_save_handler()` function lets you replace the default file-based session handler with your own. The `SessionHandlerInterface` defines the methods PHP will call when it needs to read, write, or delete session data. We implement these methods to store data in a database instead of files.

## Exercises

1.  **Page View Counter** (~5 min):

    - Create a single PHP file called `counter.php`.
    - Start a session at the top.
    - Create a session variable `$_SESSION['view_count']`.
    - On each page load, check if the variable is set. If not, initialize it to 1. If it is set, increment its value.
    - Display a message on the page: "You have visited this page X times."
    - Add a "Reset Counter" link that clears the session variable.

2.  **Secure Session Configuration** (~5 min):

    - Create a file called `secure-session-config.php`.
    - Implement all the security best practices from Step 4 (httponly, secure, samesite, etc.).
    - Create a simple login system using this secure configuration.
    - Test that the session cookie has the correct security attributes using your browser's developer tools.

3.  **Simple Shopping Cart** (~15 min):

    - Create three pages: `products.php`, `add-to-cart.php`, and `cart.php`.
    - On `products.php`, display a list of at least 3 products with names and prices. Each product should have an "Add to Cart" link.
    - The link should point to `add-to-cart.php?id=1&name=Product&price=19.99`.
    - In `add-to-cart.php`, add the product to `$_SESSION['cart']` as an array of product details, then redirect back to `products.php`.
    - On `cart.php`, display all items in the cart with a total price calculation.
    - Add a "Clear Cart" button that empties `$_SESSION['cart']`.
    - **Bonus**: Add flash messages when items are added or the cart is cleared.

4.  **Remember Me with Cookies** (~10 min):

    - Enhance your login system to include a "Remember Me" checkbox.
    - If checked, set a secure cookie that stores a random token (not the password!).
    - On subsequent visits, check for this cookie and automatically log the user in if it exists and is valid.
    - Make sure the token expires after 30 days.

5.  **CSRF-Protected Contact Form** (~15 min):

    - Create a contact form with fields: name, email, subject, and message.
    - Implement CSRF protection using the functions from Step 5.
    - Use flash messages to show success or error messages after form submission.
    - Make the form "sticky" (repopulate fields on validation errors).
    - Validate that all fields are filled in before accepting the submission.

6.  **Flash Message System Enhancement** (~10 min):
    - Enhance the flash message system to support multiple flash messages at once (store as an array).
    - Modify `setFlash()` to accept an optional third parameter: a category/key (e.g., 'auth', 'cart', 'profile').
    - Allow displaying only flash messages for a specific category.
    - Test by setting multiple flash messages and displaying them together.

## Wrap-up

You've just unlocked the ability to create stateful applications that can "remember" users across multiple requests and protect them from common security vulnerabilities.

**What you accomplished:**

- Understood why HTTP is stateless and why state management is necessary
- Implemented cookies with modern security options (HttpOnly, SameSite, Secure)
- Built a complete session-based authentication system with login, logout, and timeout
- Learned session security best practices including session ID regeneration and proper configuration
- Discovered how to protect against common attacks like session fixation, session hijacking, and CSRF
- Implemented CSRF token generation and validation for form protection
- Created a flash message system for one-time user notifications
- Learned about database-backed sessions for production scalability

**Key takeaways:**

- **Cookies** are client-side, visible to users, and suitable only for non-sensitive data
- **Sessions** are server-side, more secure, and ideal for authentication and sensitive data
- Always call `session_start()` before any output
- Always regenerate session IDs after login or privilege changes
- Always sanitize data before storing it in cookies or sessions
- **CSRF tokens** are essential for protecting forms in authenticated sessions
- **Flash messages** provide great UX for post-action feedback
- Use `hash_equals()` for token comparison to prevent timing attacks
- Configure session security settings (`httponly`, `samesite`, `secure`) in production
- Consider database or Redis-backed sessions for distributed/production systems

This chapter covered the foundation of stateful web applications—from e-commerce sites with shopping carts to social media platforms with user profiles. Combined with proper security practices, you now have the tools to build production-ready session management.

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`session-basics.php`](/series/php-basics/code/15-sessions/session-basics.php) - Session fundamentals and basic usage
- [`cookie-basics.php`](/series/php-basics/code/15-sessions/cookie-basics.php) - Working with cookies
- [`auth-system.php`](/series/php-basics/code/15-sessions/auth-system.php) - Complete authentication system with sessions
- [`README.md`](/series/php-basics/code/15-sessions/README.md) - Complete guide and exercise solutions
  :::

## Further Reading

- [PHP Manual: Sessions](https://www.php.net/manual/en/book.session.php) — Official PHP session documentation
- [PHP Manual: Cookies](https://www.php.net/manual/en/function.setcookie.php) — Complete setcookie() reference
- [OWASP Session Management Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html) — Security best practices
- [OWASP CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) — Comprehensive CSRF protection guide
- [MDN: Using HTTP Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies) — Deep dive into how cookies work
- [PHP SessionHandlerInterface](https://www.php.net/manual/en/class.sessionhandlerinterface.php) — Documentation for custom session handlers

## Knowledge Check

Test your understanding of sessions and cookies:

<Quiz
title="Chapter 15 Quiz: Sessions and Cookies"
:questions="[
{
question: 'What is the difference between cookies and sessions?',
options: [
{ text: 'Cookies store data on the client, sessions store data on the server', correct: true, explanation: 'Cookies are stored in the browser and sent with requests; sessions store data server-side with only a session ID in the cookie.' },
{ text: 'Cookies are more secure than sessions', correct: false, explanation: 'Sessions are generally more secure since data stays on the server, not in the browser.' },
{ text: 'Sessions expire when the browser closes, cookies never expire', correct: false, explanation: 'Both can have expiration times; session cookies expire on browser close by default, but persistent cookies can last longer.' },
{ text: 'They are the same thing', correct: false, explanation: 'They work differently: cookies store data client-side, sessions server-side.' }
]
},
{
question: 'What must you call before using the $\_SESSION superglobal?',
options: [
{ text: 'session_start()', correct: true, explanation: 'session_start() must be called before any output to initialize the session system and load session data.' },
{ text: 'session_begin()', correct: false, explanation: 'The function is called session_start(), not session_begin().' },
{ text: 'session_init()', correct: false, explanation: 'The function is called session_start(), not session_init().' },
{ text: 'Nothing, sessions work automatically', correct: false, explanation: 'You must explicitly call session_start() to use sessions.' }
]
},
{
question: 'What is a CSRF token used for?',
options: [
{ text: 'To prevent unauthorized form submissions from other sites', correct: true, explanation: 'CSRF tokens verify that form submissions come from your site, not from a malicious third-party site.' },
{ text: 'To encrypt form data', correct: false, explanation: 'CSRF tokens verify request origin; encryption uses different mechanisms like HTTPS.' },
{ text: 'To validate user input', correct: false, explanation: 'Input validation is separate; CSRF tokens verify the request came from your application.' },
{ text: 'To store session data', correct: false, explanation: 'Sessions store data; CSRF tokens verify request authenticity.' }
]
},
{
question: 'When should you regenerate a session ID?',
options: [
{ text: 'After login or privilege changes', correct: true, explanation: 'Regenerating prevents session fixation attacks where an attacker might know the old session ID.' },
{ text: 'On every page load', correct: false, explanation: 'That would be excessive and could cause issues; regenerate on significant auth changes.' },
{ text: 'Never, session IDs should remain constant', correct: false, explanation: 'Not regenerating leaves you vulnerable to session fixation attacks.' },
{ text: 'Only when the session expires', correct: false, explanation: 'Regenerate on privilege changes like login, not just expiration.' }
]
},
{
question: 'What does the HttpOnly flag on a cookie do?',
options: [
{ text: 'Prevents JavaScript from accessing the cookie', correct: true, explanation: 'HttpOnly cookies can only be accessed by the server, protecting against XSS attacks that try to steal cookies.' },
{ text: 'Makes the cookie work only over HTTP, not HTTPS', correct: false, explanation: 'That would be counterproductive; HttpOnly prevents JavaScript access for security.' },
{ text: 'Encrypts the cookie data', correct: false, explanation: 'HttpOnly controls access; encryption is separate. Use Secure flag for HTTPS-only cookies.' },
{ text: 'Makes cookies visible to all domains', correct: false, explanation: 'That would be a security risk; HttpOnly actually enhances security by limiting access.' }
]
}
]"
/>

## Next Steps

In the next chapter, we'll take a look at coding standards and how to automatically format our code to keep it clean and consistent, a hallmark of a professional developer.
