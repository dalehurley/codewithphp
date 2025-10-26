<?php

declare(strict_types=1);

/**
 * Form with Server-Side Validation
 * 
 * Demonstrates proper validation, error handling, and form repopulation.
 * This is the pattern you should use for production forms.
 */

// Initialize variables
$name = '';
$email = '';
$age = '';
$website = '';
$errors = [];
$submitted = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;

    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $website = trim($_POST['website'] ?? '');

    // Validate name
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters';
    } elseif (strlen($name) > 50) {
        $errors['name'] = 'Name must not exceed 50 characters';
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Validate age
    if (empty($age)) {
        $errors['age'] = 'Age is required';
    } elseif (!is_numeric($age)) {
        $errors['age'] = 'Age must be a number';
    } elseif ((int)$age < 1 || (int)$age > 120) {
        $errors['age'] = 'Age must be between 1 and 120';
    }

    // Validate website (optional field)
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors['website'] = 'Please enter a valid URL';
    }

    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would:
        // - Save to database
        // - Send email
        // - Redirect to success page

        $successMessage = "Form submitted successfully!";
        // Reset form values
        $name = '';
        $email = '';
        $age = '';
        $website = '';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form with Validation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
        input[type="number"],
        input[type="url"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input.error {
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
    <h1>Registration Form</h1>

    <?php if (isset($successMessage)): ?>
        <div class="success-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="validation-example.php">
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name) ?>"
                class="<?= isset($errors['name']) ? 'error' : '' ?>">
            <?php if (isset($errors['name'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['name']) ?></div>
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
                <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="age">Age <span class="required">*</span></label>
            <input
                type="number"
                id="age"
                name="age"
                value="<?= htmlspecialchars($age) ?>"
                class="<?= isset($errors['age']) ? 'error' : '' ?>">
            <?php if (isset($errors['age'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['age']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="website">Website (optional)</label>
            <input
                type="url"
                id="website"
                name="website"
                value="<?= htmlspecialchars($website) ?>"
                class="<?= isset($errors['website']) ? 'error' : '' ?>"
                placeholder="https://example.com">
            <?php if (isset($errors['website'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['website']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Register</button>
    </form>

    <?php if ($submitted && !empty($errors)): ?>
        <div style="margin-top: 20px; padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 4px;">
            <strong>Please correct the errors above.</strong>
        </div>
    <?php endif; ?>
</body>

</html>