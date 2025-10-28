---
title: "05: Handling HTML Forms and User Input"
description: "Make your web pages interactive by learning how to process data from HTML forms using GET and POST requests."
series: "php-basics"
chapter: 5
order: 5
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/04-understanding-and-using-functions"
---

![Handling HTML Forms and User Input](/images/php-basics/chapter-05-html-forms-user-input-hero-full.webp)

# Chapter 05: Handling HTML Forms and User Input

## Overview

We've learned how to make PHP scripts that can think (with control structures) and organize themselves (with functions). Now, it's time to make them listen. The most common way a web application gets information from a user is through HTML forms—for contact pages, search bars, login screens, and so much more.

In this chapter, you'll learn how to create a simple HTML form and write the PHP code to securely receive and process the data that a user submits.

## Prerequisites

- Completion of [Chapter 04: Understanding and Using Functions](/series/php-basics/chapters/04-understanding-and-using-functions)
- PHP 8.4 installed and working
- A text editor
- Basic understanding of HTML forms
- **Estimated time**: 30–35 minutes

## What You'll Build

By the end of this chapter, you'll have:

- A working contact form with validation and error handling
- A search form demonstrating GET requests
- A comprehensive preferences form with radio buttons, checkboxes, select dropdowns, and textareas
- Understanding of sticky forms (repopulating data after validation errors)
- Secure input validation and output sanitization practices
- A calculator and feedback form (exercises)

## Quick Start

If you want to see a working form immediately, create `form.php` with this code and visit `http://localhost:8000/form.php`:

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
    $name = htmlspecialchars($_POST['user_name']);
    echo "<h3>Hello, $name!</h3>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quick Form</title>
</head>
<body>
    <form method="POST">
        <input type="text" name="user_name" required>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
```

Start your server with `php -S localhost:8000` and test it. Now let's build something more robust.

## Objectives

- Create a basic HTML form.
- Understand the difference between the `GET` and `POST` request methods.
- Access submitted data in PHP using the `$_GET` and `$_POST` superglobal arrays.
- Learn the importance of validating and sanitizing user input to prevent common security vulnerabilities.

## Step 1: Creating a Simple HTML Form (~4 min)

First, let's build the form. This is standard HTML. The important parts for us are the `action` attribute, which tells the browser where to send the form data, and the `method` attribute, which tells it _how_ to send it. Each input element should have a `name` attribute, which will become the key for accessing its value in PHP.

1.  **Create the Form File**:
    Create a new file named `form.php`.

2.  **Add the HTML**:
    This file will contain both our HTML form and the PHP to process it. For now, let's just add the HTML.

    ```php
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Simple Form</title>
        <style>
            body { font-family: sans-serif; }
            .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; }
            label, input { display: block; width: 100%; margin-bottom: 10px; }
            input[type="submit"] { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Contact Us</h2>
            <form action="form.php" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="user_name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="user_email" required>

                <input type="submit" value="Submit">
            </form>
        </div>
    </body>
    </html>
    ```

    Start your built-in server (`php -S localhost:8000`) and navigate to `http://localhost:8000/form.php` to see it. If you submit it, the page will just reload. Now let's make it do something.

## Step 2: Handling `POST` Requests (~5 min)

We set our form's `method` to `POST`. This means the form data is sent in the body of the HTTP request, which is hidden from the user and suitable for sending sensitive information or data that will modify the server (like creating a new user).

PHP makes this data available to us in a special associative array called a **superglobal**, `$_POST`.

Let's add the PHP code to the top of `form.php` to handle the submission. Here's the complete file:

```php
<?php

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'], $_POST['user_email'])) {
    // The form was submitted. Process the data.
    $name = $_POST['user_name'];
    $email = $_POST['user_email'];

    echo "<h3>Thank You!</h3>";
    echo "Hello, " . htmlspecialchars($name) . ".<br>";
    echo "Your email is " . htmlspecialchars($email) . ".";

    // Stop the rest of the page from rendering
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Form</title>
    <style>
        body { font-family: sans-serif; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; }
        label, input { display: block; width: 100%; margin-bottom: 10px; }
        input[type="submit"] { background: #007bff; color: white; border: none; padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Contact Us</h2>
        <form action="form.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="user_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="user_email" required>

            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
```

### How it works:

- `$_SERVER['REQUEST_METHOD']`: This is another superglobal that contains information about the server and the current request. We check if the request method was `POST`.
- `isset($_POST['user_name'], $_POST['user_email'])`: We check that both form fields are present before trying to use them. This prevents PHP warnings about undefined array keys.
- `$_POST['user_name']`: We access the submitted data by using the `name` attribute from the HTML input field as the key in the `$_POST` array.
- `htmlspecialchars()`: **This is extremely important.** This function converts special HTML characters (like `<` and `>`) into their HTML entities. This prevents a common attack called Cross-Site Scripting (XSS), where a malicious user could inject harmful HTML or JavaScript into your page. **Always sanitize user output.**
- `exit;`: We call `exit` to stop the script from executing further, so it doesn't render the HTML form again after a successful submission.

### Expected Result:

When you submit the form with name "Dale" and email "dale@example.com", you should see:

```
Thank You!
Hello, Dale.
Your email is dale@example.com.
```

### Troubleshooting:

**Problem**: Page just refreshes, no output shown.

- **Solution**: Check that your form's `action` attribute matches the filename. If your file is `form.php`, the action should be `action="form.php"` or just `action=""` to submit to itself.

**Problem**: PHP Warning: Undefined array key "user_name"

- **Solution**: Make sure you're checking with `isset()` before accessing POST variables, and that the `name` attributes in your HTML match the keys you're using in PHP.

**Problem**: Seeing HTML tags in the output (like `<script>alert('XSS')</script>`).

- **Solution**: You forgot to use `htmlspecialchars()`. Never output user input directly—always sanitize it first.

## Step 3: Understanding `GET` Requests (~3 min)

The other common request method is `GET`. With `GET`, the form data is appended to the URL as a query string. This is useful for things like search forms or filters, where you want the URL to be shareable.

Let's see the difference.

1.  **Create a Search Form**:
    Create a new file called `search.php` (or modify your existing `form.php`).

2.  **Add the GET Handler**:
    Change the method to `GET` and update the PHP handler to use `$_GET`.

    ```php
    <?php

    // Check if the form has been submitted via GET and if 'search_term' is present
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_term']) && !empty($_GET['search_term'])) {
        $searchTerm = $_GET['search_term'];

        echo "<h3>Search Results</h3>";
        echo "You searched for: " . htmlspecialchars($searchTerm);

        exit;
    }

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Search Form</title>
    </head>
    <body>
        <h2>Search</h2>
        <form action="search.php" method="GET">
            <label for="search">Search Term:</label>
            <input type="text" id="search" name="search_term" required>
            <input type="submit" value="Search">
        </form>
    </body>
    </html>
    ```

### Expected Result:

When you submit the form with "PHP tutorial", the URL will become:

```
http://localhost:8000/search.php?search_term=PHP+tutorial
```

The data is visible in the URL, which is why `GET` should **never** be used for passwords or other sensitive information.

### When to Use GET vs POST:

- **Use GET for**:

  - Search forms
  - Filters and sorting
  - Any action that doesn't change server state
  - When you want URLs to be shareable or bookmarkable

- **Use POST for**:
  - Login forms
  - Any form with sensitive data
  - File uploads
  - Actions that create, update, or delete data

## Step 4: Input Validation and Sanitization (~5 min)

Before processing user data, you should always validate it to make sure it's in the format you expect. **Validation checks if the data is acceptable; sanitization cleans it for safe use.**

Let's enhance our contact form with proper validation. Update `form.php` with this improved version:

```php
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'], $_POST['user_email'])) {
    $name = trim($_POST['user_name']);
    $email = trim($_POST['user_email']);
    $errors = [];

    // Validation: Check if name is not empty
    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters.";
    }

    // Validation: Check if email is valid
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // If no errors, process the form
    if (empty($errors)) {
        echo "<h3>Thank You!</h3>";
        echo "Hello, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ".<br>";
        echo "Your email is " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . ".";
        exit;
    } else {
        // Display errors
        echo "<h3>Errors:</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
        echo "<p><a href='form.php'>Go back</a></p>";
        exit;
    }
}

?>
<!DOCTYPE html>
<!-- Rest of your form HTML here -->
```

### How it works:

- `trim()`: Removes whitespace from the beginning and end of strings. Users often accidentally add spaces.
- `empty()`: Checks if a variable is empty (null, empty string, 0, false, etc.).
- `strlen()`: Returns the length of a string. Useful for minimum/maximum length checks.
- `filter_var($email, FILTER_VALIDATE_EMAIL)`: PHP's built-in email validator. It checks for proper email format.
- `htmlspecialchars($text, ENT_QUOTES, 'UTF-8')`: The more complete form of `htmlspecialchars()` that also encodes quotes and specifies UTF-8 encoding for international characters.

### Expected Result:

Try submitting:

1. An empty form → You'll see validation errors
2. A name with 1 character → Error: "Name must be at least 2 characters"
3. An invalid email like "notanemail" → Error: "Please enter a valid email address"
4. Valid data → Success message

### Important Security Note:

> **Always validate on the server, even if you validate on the client.** HTML5's `required` attribute and `type="email"` are helpful for user experience, but a malicious user can bypass client-side validation. Server-side validation with PHP is your real security layer.

## Step 5: Working with More Form Elements (~7 min)

So far we've only worked with text inputs. Real-world forms use many different input types. Let's explore radio buttons, checkboxes, select dropdowns, and textareas, along with how to keep user data when validation fails (sticky forms).

Create a new file called `preferences.php`:

```php
<?php

// Initialize variables to avoid "undefined variable" notices
$name = '';
$favoriteColor = '';
$interests = [];
$country = '';
$bio = '';
$newsletter = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim all inputs
    $name = trim($_POST['name'] ?? '');
    $favoriteColor = $_POST['favorite_color'] ?? '';
    $interests = $_POST['interests'] ?? []; // Array of selected checkboxes
    $country = $_POST['country'] ?? '';
    $bio = trim($_POST['bio'] ?? '');
    $newsletter = isset($_POST['newsletter']); // Checkbox: checked = exists

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (empty($favoriteColor)) {
        $errors[] = "Please select a favorite color.";
    }

    if (empty($interests)) {
        $errors[] = "Please select at least one interest.";
    }

    if (empty($country)) {
        $errors[] = "Please select a country.";
    }

    // If no errors, show success
    if (empty($errors)) {
        echo "<h2>Thank You, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!</h2>";
        echo "<p><strong>Favorite Color:</strong> " . htmlspecialchars($favoriteColor, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Interests:</strong> " . htmlspecialchars(implode(', ', $interests), ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Country:</strong> " . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Bio:</strong> " . nl2br(htmlspecialchars($bio, ENT_QUOTES, 'UTF-8')) . "</p>";
        echo "<p><strong>Newsletter:</strong> " . ($newsletter ? 'Yes' : 'No') . "</p>";
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Preferences</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 50px auto; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input[type="text"], select, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        .error { color: red; margin-bottom: 15px; }
        .checkbox-group, .radio-group { margin-top: 5px; }
        .checkbox-group label, .radio-group label { display: inline; font-weight: normal; margin-left: 5px; }
    </style>
</head>
<body>
    <h2>User Preferences Form</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <!-- Text Input -->
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" required>

        <!-- Radio Buttons (single choice) -->
        <label>Favorite Color:</label>
        <div class="radio-group">
            <input type="radio" id="red" name="favorite_color" value="red" <?php echo $favoriteColor === 'red' ? 'checked' : ''; ?>>
            <label for="red">Red</label><br>

            <input type="radio" id="blue" name="favorite_color" value="blue" <?php echo $favoriteColor === 'blue' ? 'checked' : ''; ?>>
            <label for="blue">Blue</label><br>

            <input type="radio" id="green" name="favorite_color" value="green" <?php echo $favoriteColor === 'green' ? 'checked' : ''; ?>>
            <label for="green">Green</label>
        </div>

        <!-- Checkboxes (multiple choices) - note the [] in the name -->
        <label>Interests:</label>
        <div class="checkbox-group">
            <input type="checkbox" id="coding" name="interests[]" value="coding" <?php echo in_array('coding', $interests) ? 'checked' : ''; ?>>
            <label for="coding">Coding</label><br>

            <input type="checkbox" id="design" name="interests[]" value="design" <?php echo in_array('design', $interests) ? 'checked' : ''; ?>>
            <label for="design">Design</label><br>

            <input type="checkbox" id="music" name="interests[]" value="music" <?php echo in_array('music', $interests) ? 'checked' : ''; ?>>
            <label for="music">Music</label><br>

            <input type="checkbox" id="sports" name="interests[]" value="sports" <?php echo in_array('sports', $interests) ? 'checked' : ''; ?>>
            <label for="sports">Sports</label>
        </div>

        <!-- Select Dropdown -->
        <label for="country">Country:</label>
        <select id="country" name="country">
            <option value="">-- Select a country --</option>
            <option value="us" <?php echo $country === 'us' ? 'selected' : ''; ?>>United States</option>
            <option value="uk" <?php echo $country === 'uk' ? 'selected' : ''; ?>>United Kingdom</option>
            <option value="ca" <?php echo $country === 'ca' ? 'selected' : ''; ?>>Canada</option>
            <option value="au" <?php echo $country === 'au' ? 'selected' : ''; ?>>Australia</option>
        </select>

        <!-- Textarea (multi-line text) -->
        <label for="bio">Bio:</label>
        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></textarea>

        <!-- Single Checkbox (for agreements, opt-ins, etc.) -->
        <div style="margin-top: 15px;">
            <input type="checkbox" id="newsletter" name="newsletter" <?php echo $newsletter ? 'checked' : ''; ?>>
            <label for="newsletter" style="display: inline; font-weight: normal;">Subscribe to newsletter</label>
        </div>

        <button type="submit" style="margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">Submit</button>
    </form>
</body>
</html>
```

### How it works:

#### Radio Buttons (Single Choice):

- All radio buttons in a group share the same `name` attribute
- Only one can be selected at a time
- Access the selected value with `$_POST['favorite_color']`
- Use `checked` attribute to pre-select based on previous input

#### Checkboxes (Multiple Choices):

- Use `name="interests[]"` with `[]` to create an array
- Access selected values with `$_POST['interests']` which returns an array
- Use `in_array('value', $array)` to check if a value was selected
- Always check if the array exists: `$_POST['interests'] ?? []`

#### Select Dropdown:

- Use `<select>` with `<option>` elements
- Access selected value with `$_POST['country']`
- Use `selected` attribute to pre-select based on previous input

#### Textarea:

- For multi-line text input
- Access value with `$_POST['bio']`
- Use `nl2br()` to convert newlines to `<br>` tags when displaying
- Put the value between opening and closing tags, not in a `value` attribute

#### Single Checkbox (Opt-in):

- Access with `isset($_POST['newsletter'])`
- If checked, it exists in `$_POST`; if unchecked, it doesn't exist at all
- Cannot use `$_POST['newsletter']` directly as it won't exist when unchecked

#### Sticky Forms (Repopulating Data):

- Initialize variables before the form is submitted
- After submission, store values in variables
- Use these variables in the HTML to repopulate fields
- This prevents users from re-entering data after validation errors

### Expected Result:

1. Load the page and submit without filling anything → See validation errors, but the page doesn't clear
2. Fill some fields and submit → Failed validation shows errors, but your data remains in the form
3. Fill all fields correctly → Success message displays all your choices

### Important Notes:

**Checkboxes and Arrays:**

```php
// Wrong - will cause errors if no checkboxes selected
$interests = $_POST['interests'];  // Don't do this!

// Right - provides empty array if nothing selected
$interests = $_POST['interests'] ?? [];
```

**Escaping in Different Contexts:**

```php
// Inside HTML attribute
value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"

// Between HTML tags (textarea)
<textarea><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></textarea>

// Displaying with line breaks preserved
echo nl2br(htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'));
```

## Exercises

1.  **Simple Calculator**: Create a file `calculator.php`.

    - Build a form with two number inputs (`num1`, `num2`) and a submit button.
    - When submitted via `POST`, calculate and display the sum (e.g., "The sum of 5 and 10 is 15").
    - Add validation to ensure both values are numeric using `is_numeric()`.
    - **Bonus**: Add a dropdown to select the operation (add, subtract, multiply, divide) and implement all four operations. Don't forget to check for division by zero!

2.  **Feedback Form**: Create a file `feedback.php`.
    - Build a form with fields: name (text), email (email), rating (select dropdown with values 1-5), and comments (textarea).
    - Validate that all fields are filled and that the email is valid.
    - Display a summary of the feedback when successfully submitted.
    - Make sure to use `htmlspecialchars()` on all output, especially the comments field which could be longer text.

## Wrap-up

Great job! You can now build interactive web pages that accept and process user input. You've learned the critical difference between `GET` and `POST`, how to access the submitted data using superglobals, and, most importantly, the basics of securing your application by validating input and sanitizing output.

### Key Takeaways:

- Use `$_POST` for sensitive data and state-changing operations
- Use `$_GET` for searches, filters, and shareable URLs
- Always check if keys exist with `isset()` before accessing superglobal arrays
- Validate input to ensure it meets your requirements
- Always use `htmlspecialchars()` when outputting user data to prevent XSS attacks
- Never trust client-side validation alone—always validate on the server
- Use `name="field[]"` with `[]` to create array inputs for checkboxes and multi-selects
- Implement sticky forms to preserve user input after validation errors
- Single checkboxes only exist in `$_POST` when checked; use `isset()` to check them

In the next chapter, we'll dive into arrays, PHP's incredibly powerful and versatile tool for storing and managing lists of related data.

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`basic-form.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/05-forms/basic-form.php) - Simple form handling with POST
- [`get-vs-post.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/05-forms/get-vs-post.php) - Demonstrating GET vs POST methods
- [`validation-example.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/05-forms/validation-example.php) - Server-side validation
- [`sanitization-demo.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/05-forms/sanitization-demo.php) - Input sanitization and security
- [`comprehensive-form.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/05-forms/comprehensive-form.php) - Complete form with all input types
- `solutions/` - Solutions to chapter exercises
  :::

## Knowledge Check

Test your understanding of HTML forms and user input:

<Quiz
title="Chapter 05 Quiz: HTML Forms and User Input"
:questions="[
{
question: 'What is the primary difference between GET and POST methods?',
options: [
{ text: 'GET passes data in the URL, POST sends it in the request body', correct: true, explanation: 'GET appends data to the URL (visible and bookmarkable), while POST sends data in the request body (not visible in URL).' },
{ text: 'GET is faster than POST', correct: false, explanation: 'Performance is nearly identical; the difference is in how data is transmitted.' },
{ text: 'POST is for reading data, GET is for writing', correct: false, explanation: 'It\'s the opposite: GET is for reading/retrieving, POST is for creating/modifying data.' },
{ text: 'They are exactly the same', correct: false, explanation: 'They differ significantly in how they transmit data and when they should be used.' }
]
},
{
question: 'Why must you always use htmlspecialchars() when outputting user data?',
options: [
{ text: 'To prevent XSS (Cross-Site Scripting) attacks', correct: true, explanation: 'htmlspecialchars() escapes HTML special characters, preventing malicious scripts from executing in the browser.' },
{ text: 'To make the output look prettier', correct: false, explanation: 'htmlspecialchars() is for security, not formatting.' },
{ text: 'To validate the user input', correct: false, explanation: 'Validation checks input correctness; htmlspecialchars() sanitizes output for safe display.' },
{ text: 'To convert strings to uppercase', correct: false, explanation: 'That\'s what strtoupper() does; htmlspecialchars() escapes special characters.' }
]
},
{
question: 'What does the $_POST superglobal contain?',
options: [
{ text: 'An associative array of data submitted via POST method', correct: true, explanation: '$\_POST contains all form data sent with method=\'POST\', with field names as keys.' },
{ text: 'All data from any form submission', correct: false, explanation: 'Only POST submissions; GET data is in $_GET.' },
{ text: 'Only validated form data', correct: false, explanation: '$\_POST contains raw submitted data; you must validate it yourself.' },
{ text: 'Data from the URL query string', correct: false, explanation: 'URL query string data is in $_GET, not $_POST.' }
]
},
{
question: 'When checking if a checkbox was checked, why should you use isset() instead of checking the value directly?',
options: [
{ text: 'Unchecked checkboxes are not included in $_POST at all', correct: true, explanation: 'Checkboxes only appear in $_POST when checked; unchecked ones are absent entirely.' },
{ text: 'isset() validates the checkbox value', correct: false, explanation: 'isset() only checks existence; it doesn\'t validate content.' },
{ text: 'Checkboxes always return null when unchecked', correct: false, explanation: 'They don\'t exist in $_POST when unchecked, so you\'d get an undefined index error.' },
{ text: 'isset() makes the form load faster', correct: false, explanation: 'isset() is for checking if a variable exists, not performance.' }
]
},
{
question: 'What is the purpose of filter_var($email, FILTER_VALIDATE_EMAIL)?',
options: [
{ text: 'To validate that a string is a properly formatted email address', correct: true, explanation: 'This built-in filter checks if the email format is valid according to email standards.' },
{ text: 'To send an email to the address', correct: false, explanation: 'Validation only checks format; sending requires mail() or a mailer library.' },
{ text: 'To sanitize an email by removing special characters', correct: false, explanation: 'That would be FILTER_SANITIZE_EMAIL; FILTER_VALIDATE_EMAIL only validates.' },
{ text: 'To convert the email to lowercase', correct: false, explanation: 'Validation doesn\'t modify the value; use strtolower() for that.' }
]
}
]"
/>

## Further Reading

- [PHP: Handling file uploads](https://www.php.net/manual/en/features.file-upload.php) — Learn how to accept file uploads through forms
- [PHP: Filter functions](https://www.php.net/manual/en/book.filter.php) — Complete documentation on `filter_var()` and related functions
- [OWASP: Cross-Site Scripting (XSS)](https://owasp.org/www-community/attacks/xss/) — Deep dive into XSS attacks and prevention
- [PHP: Data filtering](https://www.php.net/manual/en/book.filter.php) — Comprehensive guide to validating and sanitizing data in PHP
