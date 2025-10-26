<?php

declare(strict_types=1);

/**
 * Exercise 1: Contact Form with Validation
 * 
 * Create a contact form with name, email, subject, and message fields.
 * Validate all fields and display errors if validation fails.
 */

// Initialize variables
$name = '';
$email = '';
$subject = '';
$message = '';
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate name
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Validate subject
    if (empty($subject)) {
        $errors['subject'] = 'Subject is required';
    }

    // Validate message
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters';
    }

    // If no errors, process the form
    if (empty($errors)) {
        $success = true;
        // In a real application, you would:
        // - Send email using mail() or PHPMailer
        // - Save to database
        // - Log the submission

        // Reset form
        $name = '';
        $email = '';
        $subject = '';
        $message = '';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form - Exercise 1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }

        h1 {
            color: #333;
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
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .error {
            border-color: #dc3545;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        button {
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .required {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <div class="success-message">
            <strong>✓ Message Sent Successfully!</strong><br>
            Thank you for contacting us. We'll get back to you soon.
        </div>
    <?php endif; ?>

    <form method="POST" action="exercise-1-contact-form.php">
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name) ?>"
                class="<?= isset($errors['name']) ? 'error' : '' ?>">
            <?php if (isset($errors['name'])): ?>
                <div class="error-message"><?= $errors['name'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email) ?>"
                class="<?= isset($errors['email']) ? 'error' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="error-message"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="subject">Subject <span class="required">*</span></label>
            <input
                type="text"
                id="subject"
                name="subject"
                value="<?= htmlspecialchars($subject) ?>"
                class="<?= isset($errors['subject']) ? 'error' : '' ?>">
            <?php if (isset($errors['subject'])): ?>
                <div class="error-message"><?= $errors['subject'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea
                id="message"
                name="message"
                class="<?= isset($errors['message']) ? 'error' : '' ?>"><?= htmlspecialchars($message) ?></textarea>
            <?php if (isset($errors['message'])): ?>
                <div class="error-message"><?= $errors['message'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Send Message</button>
    </form>
</body>

</html>