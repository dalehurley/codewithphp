<?php

declare(strict_types=1);

session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Form</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .error { color: #d32f2f; font-size: 14px; margin-top: 5px; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        input { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        button { background: #2196f3; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #1976d2; }
    </style>
</head>
<body>
    <h1>Email Subscription</h1>
    
    <?php if ($success): ?>
        <div class="success">Thank you! Your email has been registered.</div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" 
               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif; ?>
        <button type="submit">Subscribe</button>
    </form>
</body>
</html>





