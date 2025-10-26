<?php

declare(strict_types=1);

/**
 * Exercise 2: Handle POST Requests
 * 
 * Create a simple contact form:
 * 
 * Requirements:
 * - GET route /form to serve the contact form
 * - POST route /submit-form to handle form submission
 * - Display submitted data in a thank you page
 */

require_once __DIR__ . '/../Router.php';

$router = new Router();

// Home route
$router->get('/', function () {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; text-align: center; }
        a { 
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover { background: #2980b9; }
    </style>
</head>
<body>
    <h1>Welcome</h1>
    <a href='/form'>Contact Us</a>
</body>
</html>";
});

// GET route to display the form
$router->get('/form', function () {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Contact Form</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 0 20px;
        }
        h1 { color: #2c3e50; }
        .form-group { margin-bottom: 20px; }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold;
            color: #555;
        }
        input[type='text'],
        input[type='email'],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        button {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background: #229954; }
        .required { color: #e74c3c; }
    </style>
</head>
<body>
    <h1>Contact Us</h1>
    <form action='/submit-form' method='POST'>
        <div class='form-group'>
            <label>Name <span class='required'>*</span></label>
            <input type='text' name='name' required />
        </div>
        
        <div class='form-group'>
            <label>Email <span class='required'>*</span></label>
            <input type='email' name='email' required />
        </div>
        
        <div class='form-group'>
            <label>Phone (optional)</label>
            <input type='text' name='phone' />
        </div>
        
        <div class='form-group'>
            <label>Message <span class='required'>*</span></label>
            <textarea name='message' required></textarea>
        </div>
        
        <button type='submit'>Submit</button>
    </form>
</body>
</html>";
});

// POST route to handle form submission
$router->post('/submit-form', function () {
    // Get form data with sanitization
    $name = htmlspecialchars($_POST['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($_POST['phone'] ?? 'Not provided', ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'] ?? 'No message', ENT_QUOTES, 'UTF-8');

    // Validate email
    $isValidEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

    echo "<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 600px; 
            margin: 50px auto; 
            padding: 0 20px;
        }
        .success-box {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .success-box h1 {
            margin-top: 0;
            color: #155724;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .info-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class='success-box'>
        <h1>✓ Thank You!</h1>
        <p>We received your message, <strong>{$name}</strong>.</p>
    </div>
    
    <div class='info-box'>
        <h2>Submission Details</h2>
        
        <div class='info-row'>
            <span class='info-label'>Name:</span>
            <span class='info-value'>{$name}</span>
        </div>
        
        <div class='info-row'>
            <span class='info-label'>Email:</span>
            <span class='info-value'>{$email}</span>";

    if (!$isValidEmail) {
        echo "<div class='warning'>⚠ This email address appears to be invalid.</div>";
    } else {
        echo "<div style='margin-top: 10px; color: #28a745;'>We'll reply to this address.</div>";
    }

    echo "
        </div>
        
        <div class='info-row'>
            <span class='info-label'>Phone:</span>
            <span class='info-value'>{$phone}</span>
        </div>
        
        <div class='info-row'>
            <span class='info-label'>Message:</span>
            <div class='info-value' style='margin-top: 5px; white-space: pre-wrap;'>{$message}</div>
        </div>
    </div>
    
    <a href='/form' class='back-link'>← Submit Another Message</a>
</body>
</html>";
});

// Dispatch the request
$router->dispatch();
