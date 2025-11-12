<?php

declare(strict_types=1);

/**
 * Basic Form Processing
 * 
 * Demonstrates the fundamentals of handling HTML form submissions in PHP.
 * Shows how to receive data from GET and POST requests.
 */

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form was submitted - process the data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    echo "<!DOCTYPE html>";
    echo "<html><head><title>Form Submitted</title></head><body>";
    echo "<h1>Thank You!</h1>";
    echo "<p>Name: " . htmlspecialchars($name) . "</p>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    echo "<a href='basic-form.php'>Back to form</a>";
    echo "</body></html>";
    exit;
}

// If not POST, show the form
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basic Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Contact Form</h1>
    <p>Fill out this form and click submit.</p>

    <form method="POST" action="basic-form.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <button type="submit">Submit</button>
    </form>
</body>

</html>