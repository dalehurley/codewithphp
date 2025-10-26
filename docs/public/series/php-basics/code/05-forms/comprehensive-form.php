<?php

declare(strict_types=1);

/**
 * Comprehensive Form with Multiple Input Types
 * 
 * Demonstrates handling various form input types including:
 * - Text, email, number inputs
 * - Select dropdowns
 * - Checkboxes and radio buttons
 * - Textareas
 * - Multiple selections
 */

// Initialize variables
$formData = [
    'fullName' => '',
    'email' => '',
    'age' => '',
    'country' => '',
    'gender' => '',
    'interests' => [],
    'newsletter' => false,
    'bio' => ''
];

$errors = [];
$submitted = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;

    // Get form data
    $formData['fullName'] = trim($_POST['fullName'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['age'] = trim($_POST['age'] ?? '');
    $formData['country'] = $_POST['country'] ?? '';
    $formData['gender'] = $_POST['gender'] ?? '';
    $formData['interests'] = $_POST['interests'] ?? [];
    $formData['newsletter'] = isset($_POST['newsletter']);
    $formData['bio'] = trim($_POST['bio'] ?? '');

    // Validate
    if (empty($formData['fullName'])) {
        $errors['fullName'] = 'Full name is required';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }

    if (empty($formData['age']) || !is_numeric($formData['age']) || $formData['age'] < 1) {
        $errors['age'] = 'Valid age is required';
    }

    if (empty($formData['country'])) {
        $errors['country'] = 'Please select a country';
    }

    if (empty($formData['gender'])) {
        $errors['gender'] = 'Please select a gender';
    }

    // If validation passes
    if (empty($errors)) {
        $successData = $formData;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error {
            border-color: #dc3545 !important;
        }

        .error-message {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }

        .checkbox-group,
        .radio-group {
            margin-top: 10px;
        }

        .checkbox-group label,
        .radio-group label {
            display: inline-block;
            margin-right: 20px;
            font-weight: normal;
        }

        .checkbox-group input,
        .radio-group input {
            margin-right: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-box {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
        }

        .success-box h2 {
            margin-top: 0;
        }

        .required {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>User Profile Form</h1>

        <?php if (isset($successData)): ?>
            <div class="success-box">
                <h2>✓ Registration Successful!</h2>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($successData['fullName']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($successData['email']) ?></p>
                <p><strong>Age:</strong> <?= htmlspecialchars($successData['age']) ?></p>
                <p><strong>Country:</strong> <?= htmlspecialchars($successData['country']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($successData['gender']) ?></p>
                <p><strong>Interests:</strong> <?= !empty($successData['interests']) ? htmlspecialchars(implode(', ', $successData['interests'])) : 'None selected' ?></p>
                <p><strong>Newsletter:</strong> <?= $successData['newsletter'] ? 'Subscribed' : 'Not subscribed' ?></p>
                <?php if (!empty($successData['bio'])): ?>
                    <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($successData['bio'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="comprehensive-form.php">
            <!-- Text Input -->
            <div class="form-group">
                <label for="fullName">Full Name <span class="required">*</span></label>
                <input
                    type="text"
                    id="fullName"
                    name="fullName"
                    value="<?= htmlspecialchars($formData['fullName']) ?>"
                    class="<?= isset($errors['fullName']) ? 'error' : '' ?>">
                <?php if (isset($errors['fullName'])): ?>
                    <div class="error-message"><?= $errors['fullName'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Email Input -->
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($formData['email']) ?>"
                    class="<?= isset($errors['email']) ? 'error' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Number Input -->
            <div class="form-group">
                <label for="age">Age <span class="required">*</span></label>
                <input
                    type="number"
                    id="age"
                    name="age"
                    value="<?= htmlspecialchars($formData['age']) ?>"
                    class="<?= isset($errors['age']) ? 'error' : '' ?>">
                <?php if (isset($errors['age'])): ?>
                    <div class="error-message"><?= $errors['age'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Select Dropdown -->
            <div class="form-group">
                <label for="country">Country <span class="required">*</span></label>
                <select
                    id="country"
                    name="country"
                    class="<?= isset($errors['country']) ? 'error' : '' ?>">
                    <option value="">-- Select Country --</option>
                    <option value="US" <?= $formData['country'] === 'US' ? 'selected' : '' ?>>United States</option>
                    <option value="UK" <?= $formData['country'] === 'UK' ? 'selected' : '' ?>>United Kingdom</option>
                    <option value="CA" <?= $formData['country'] === 'CA' ? 'selected' : '' ?>>Canada</option>
                    <option value="AU" <?= $formData['country'] === 'AU' ? 'selected' : '' ?>>Australia</option>
                    <option value="DE" <?= $formData['country'] === 'DE' ? 'selected' : '' ?>>Germany</option>
                </select>
                <?php if (isset($errors['country'])): ?>
                    <div class="error-message"><?= $errors['country'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Radio Buttons -->
            <div class="form-group">
                <label>Gender <span class="required">*</span></label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="gender" value="male" <?= $formData['gender'] === 'male' ? 'checked' : '' ?>>
                        Male
                    </label>
                    <label>
                        <input type="radio" name="gender" value="female" <?= $formData['gender'] === 'female' ? 'checked' : '' ?>>
                        Female
                    </label>
                    <label>
                        <input type="radio" name="gender" value="other" <?= $formData['gender'] === 'other' ? 'checked' : '' ?>>
                        Other
                    </label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error-message"><?= $errors['gender'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Checkboxes (Multiple Selection) -->
            <div class="form-group">
                <label>Interests (Optional)</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="interests[]" value="coding" <?= in_array('coding', $formData['interests']) ? 'checked' : '' ?>>
                        Coding
                    </label>
                    <label>
                        <input type="checkbox" name="interests[]" value="music" <?= in_array('music', $formData['interests']) ? 'checked' : '' ?>>
                        Music
                    </label>
                    <label>
                        <input type="checkbox" name="interests[]" value="sports" <?= in_array('sports', $formData['interests']) ? 'checked' : '' ?>>
                        Sports
                    </label>
                    <label>
                        <input type="checkbox" name="interests[]" value="reading" <?= in_array('reading', $formData['interests']) ? 'checked' : '' ?>>
                        Reading
                    </label>
                </div>
            </div>

            <!-- Single Checkbox -->
            <div class="form-group">
                <label>
                    <input type="checkbox" name="newsletter" value="1" <?= $formData['newsletter'] ? 'checked' : '' ?>>
                    Subscribe to newsletter
                </label>
            </div>

            <!-- Textarea -->
            <div class="form-group">
                <label for="bio">Bio (Optional)</label>
                <textarea id="bio" name="bio" placeholder="Tell us about yourself..."><?= htmlspecialchars($formData['bio']) ?></textarea>
            </div>

            <button type="submit">Submit Registration</button>
        </form>
    </div>
</body>

</html>