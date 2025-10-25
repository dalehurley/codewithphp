---
title: "01: Your First PHP Script"
description: 'Learn the fundamental syntax of PHP by writing and running your first "Hello, World!" script in the browser and terminal.'
series: "php-basics"
chapter: 1
order: 1
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/00-setting-up-your-development-environment"
---

# Chapter 01: Your First PHP Script

## Overview

With your development environment ready, it's time to write your first line of PHP code. This is a rite of passage in programming: the "Hello, World!" script. It's simple, but it's the perfect way to confirm that your setup is working and to learn the most basic syntax of the language.

In this chapter, you'll learn what PHP tags look like, how to output text, and the different ways you can run a PHP script.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- A code editor (VS Code recommended) installed
- A terminal or command prompt ready
- A project directory named `php-from-scratch` from the previous chapter
- Basic familiarity with navigating directories in the terminal

**Estimated Time**: ~15 minutes

## What You'll Build

By the end of this chapter, you will have created:

- `hello.php` — A simple script that outputs "Hello, World!" to the terminal
- `hello-web.php` — An HTML page with embedded PHP that displays dynamic content
- `concatenation.php` — A script demonstrating string concatenation and the `.` operator
- Working knowledge of PHP syntax fundamentals (tags, echo, comments, concatenation)
- Understanding of when to use closing PHP tags and short echo syntax
- Experience running PHP scripts both from the command line and in a browser

## Objectives

- Understand the purpose of PHP tags (`<?php ... ?>`) and when to use closing tags.
- Use the `echo` statement and short echo syntax (`<?=`) to output strings.
- Run a PHP script from both the command line and a web browser.
- Learn how to add comments to your code.
- Master string concatenation with the `.` operator.
- Distinguish between concatenation and string interpolation.

## Step 1: Create a "Hello, World!" Script (~2 min)

Let's start by creating a simple file that will output the text "Hello, World!" to the screen.

1.  **Navigate to Your Project Directory**:
    Open your terminal and navigate to the `php-from-scratch` folder you created in the previous chapter.

2.  **Create a New File**:
    In that directory, create a new file named `hello.php`. You can delete the `index.php` file from the last chapter if you like.

3.  **Add the PHP Code**:
    Open `hello.php` in VS Code and add the following code:

```php
# filename: hello.php
<?php

echo 'Hello, World!';
```

### Expected Result

You should now have a file named `hello.php` in your project directory with three lines of code.

### Why it works

- `<?php`: This is the opening PHP tag. It tells the server, "Everything after this point is PHP code, so please execute it."
- `echo`: This is a language construct (very similar to a function) that outputs one or more strings.
- `'Hello, World!'`: This is a string literal. The text inside the single quotes is the data we want to `echo`.
- `;`: The semicolon marks the end of a statement. Most lines in PHP must end with a semicolon.

## Step 2: Run the Script from the Command Line (~1 min)

The simplest way to run a PHP script is directly from your terminal. This is useful for running maintenance scripts, tools, or just quickly testing a piece of code.

1.  **Open Your Terminal**:
    Make sure you are still inside your `php-from-scratch` project directory.

2.  **Run the Script**:
    Execute the file using the `php` command:

```bash
# Execute the PHP script
php hello.php
```

### Expected Result

You should see the output printed directly to your terminal:

```text
Hello, World!
```

> **Note**: You might notice your command prompt appears on the same line as the output. To add a newline for cleaner output in the terminal, you can add the special `PHP_EOL` constant (End Of Line) like this: `echo 'Hello, World!' . PHP_EOL;`

### Troubleshooting

**Error: `php: command not found`**

- PHP is not installed or not in your system's PATH. Return to Chapter 00 and follow the installation instructions for your operating system.
- Verify PHP installation with: `php --version`

**Error: `Could not open input file: hello.php`**

- You're in the wrong directory. Use `pwd` (macOS/Linux) or `cd` (Windows) to check your current location, then navigate to your project folder.
- The file doesn't exist. Check the filename is exactly `hello.php` with `ls` (macOS/Linux) or `dir` (Windows).

**Nothing happens or blank output**

- Check that your file contains the exact code shown above, including the opening `<?php` tag.

## Step 3: Run the Script in the Browser (~2 min)

The real power of PHP shines when it's used to create dynamic web pages. Let's run the same script using the built-in web server.

1.  **Start the Server**:
    In your terminal, from the project directory, start the server:

```bash
# Start the built-in PHP development server
php -S localhost:8000
```

You should see output similar to:

```text
[Sat Oct 25 14:30:00 2025] PHP 8.2.0 Development Server (http://localhost:8000) started
```

2.  **Open Your Browser**:
    Navigate to `http://localhost:8000/hello.php`.

### Expected Result

You will see the text "Hello, World!" displayed in your browser window. It may look plain, but this content was dynamically generated by the PHP engine on the server and sent to your browser as HTML.

> **Tip**: Keep the terminal window with the server running open. You'll see a log entry each time you access a page. Press `Ctrl+C` to stop the server when you're done.

### Troubleshooting

**Error: `Failed to listen on localhost:8000`**

- Port 8000 is already in use. Try a different port: `php -S localhost:8001`
- Update your browser URL to match the new port.

**Browser shows "This site can't be reached"**

- The server isn't running. Check the terminal where you started it.
- Make sure you included `http://` in the URL: `http://localhost:8000/hello.php`

**Browser shows a blank page**

- Check the server terminal for PHP errors. Common issues: syntax errors in your code.
- Try viewing the page source (right-click → View Page Source) to see if any content was generated.

## Step 4: Embedding PHP in HTML (~3 min)

PHP was designed to be embedded directly inside HTML, allowing you to build dynamic pages easily. Let's create a proper HTML document and use PHP to insert some dynamic content.

1.  **Create a New File**:
    Create a new file called `hello-web.php`.

2.  **Add the Code**:
    Add the following HTML and PHP code to the new file:

```php
# filename: hello-web.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My First PHP Page</title>
</head>
<body>
    <h1><?php echo 'Hello from the Web!'; ?></h1>
    <p>This page was generated on <?php echo date('Y-m-d H:i:s'); ?>.</p>
</body>
</html>
```

> **Note**: The `date()` function is a built-in PHP function that returns the current date and time formatted according to the string you provide. The format string 'Y-m-d H:i:s' produces output like "2025-10-25 14:30:45".

3.  **View in Browser**:
    If your server is still running, you can now visit `http://localhost:8000/hello-web.php`.

### Expected Result

You will see a proper web page with:

- A heading that says "Hello from the Web!"
- A paragraph showing the current date and time

The heading and timestamp were dynamically inserted by PHP, which runs on the server before sending the HTML to your browser.

## Step 5: Adding Comments (~1 min)

Comments are parts of your code that are ignored by the PHP engine. They are essential for explaining what your code does, for yourself and for other developers.

PHP supports three comment styles:

```php
# filename: comments-demo.php (optional demo file)
<?php

// This is a single-line comment. It's great for short notes.

/*
  This is a multi-line comment block.
  You can write as many lines as you want here.
  Useful for longer explanations or documentation.
*/

# This is also a single-line comment, but it's less common.
# It's borrowed from shell scripting syntax.

echo 'Hello with comments!'; // Comments can also be placed at the end of a line.
```

### Why use comments

- **Clarify intent**: Explain _why_ you wrote something a certain way, not just _what_ it does.
- **Document complex logic**: Help future you (or other developers) understand tricky code.
- **Disable code temporarily**: Comment out lines during debugging without deleting them.

> **Tip**: Use `//` for most single-line comments in PHP. The `/* ... */` style is ideal for multi-line documentation or temporarily disabling blocks of code.

## Step 6: PHP Tags and String Operations (~3 min)

Now that you understand the basics, let's explore some important details about PHP tags and how to work with strings more effectively.

### The Closing PHP Tag

You may have noticed that our PHP files don't have a closing `?>` tag. This is intentional and follows modern best practices.

**When to OMIT the closing tag** (recommended):

- In pure PHP files (files containing only PHP code)
- Prevents accidental whitespace after the tag from causing "headers already sent" errors
- This is the PSR-12 coding standard recommendation

**When to USE the closing tag**:

- When mixing PHP with HTML in the same file
- The closing tag tells PHP, "Stop executing PHP and treat everything after this as HTML"

Here's an example showing when to use it:

```php
# filename: mixed-example.php
<?php
$greeting = 'Welcome';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mixed PHP and HTML</title>
</head>
<body>
    <h1><?php echo $greeting; ?></h1>
    <p>This HTML comes after the closing PHP tag.</p>
</body>
</html>
```

> **Best Practice**: For pure PHP files (like classes, configuration files, or standalone scripts), always omit the closing `?>` tag.

### Short Echo Syntax

When embedding PHP in HTML templates, writing `<?php echo ... ?>` repeatedly can be tedious. PHP provides a shorthand syntax specifically for outputting values:

```php
# filename: short-echo-demo.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Short Echo Demo</title>
</head>
<body>
    <?php $title = 'My Awesome Page'; ?>
    <?php $author = 'Dale Hurley'; ?>

    <!-- Long form -->
    <h1><?php echo $title; ?></h1>

    <!-- Short form (much cleaner!) -->
    <h2><?= $author ?></h2>

    <p>Current time: <?= date('H:i:s') ?></p>
</body>
</html>
```

The `<?= ?>` syntax is equivalent to `<?php echo ... ?>` and is perfect for templates.

> **Note**: The short echo tag `<?=` has been available since PHP 5.4 and is always enabled, even if short tags are disabled.

### String Concatenation

In PHP, you can join strings together using the concatenation operator (`.`):

```php
# filename: concatenation.php
<?php

// Basic concatenation
echo 'Hello' . ' ' . 'World!';
echo PHP_EOL;

// Concatenating variables and strings
$firstName = 'Jane';
$lastName = 'Doe';
$fullName = $firstName . ' ' . $lastName;

echo 'Full name: ' . $fullName;
echo PHP_EOL;

// You can also use the .= operator to append to a string
$message = 'Hello';
$message .= ' World';
$message .= '!';

echo $message; // Outputs: Hello World!
```

**When to use concatenation vs string interpolation**:

```php
# filename: string-comparison.php
<?php

$name = 'Alice';
$age = 25;

// Concatenation - works with single or double quotes
echo 'Name: ' . $name . ', Age: ' . $age;
echo PHP_EOL;

// String interpolation - only works with double quotes
echo "Name: $name, Age: $age";
echo PHP_EOL;

// For complex expressions, concatenation is clearer
echo 'Next year, ' . $name . ' will be ' . ($age + 1) . ' years old.';
```

### Expected Result

Try running the concatenation example:

```bash
# Run the concatenation script
php concatenation.php
```

You should see:

```text
Hello World!
Full name: Jane Doe
Hello World!
```

### Troubleshooting

**Parse error: syntax error, unexpected '='**

- You're using `<?=` in a pure PHP context. The short echo syntax is only for outputting values in mixed HTML/PHP files.
- Make sure to use it like this: `<?= $value ?>` not `<?= $value; ?>`

**Concatenation produces unexpected results**

- Check for missing spaces: `'Hello' . 'World'` produces "HelloWorld" not "Hello World"
- Add spaces explicitly: `'Hello' . ' ' . 'World'`

## Exercises

Test your understanding with these hands-on challenges:

1.  **Modify the Message**: Change your `hello.php` script to print "Hello, [Your Name]!" instead using string concatenation. Run it from the command line to verify.

2.  **Display the Date**: Create a new HTML page that uses PHP to display today's date in the format "Month Day, Year" (e.g., "October 25, 2025").

    - **Hint**: Look up the [date() function](https://www.php.net/manual/en/function.date.php) documentation on php.net and use the format string `'F j, Y'`.

3.  **Short Echo Practice**: Create a new file called `profile.php` that uses the short echo syntax (`<?=`) to display:

    - Your name in an `<h1>` tag
    - Your favorite programming language in a `<p>` tag
    - The current year in a footer
    - **Bonus**: Add comments explaining what each section does

4.  **Concatenation Challenge**: Create a file called `greeting.php` that:

    - Declares three variables: `$greeting`, `$name`, and `$punctuation`
    - Uses the `.=` operator to build a complete greeting message
    - Outputs the final message using `echo`
    - Example: "Hello" + " " + "Alice" + "!" = "Hello Alice!"

5.  **Mixed Content**: Update `hello-web.php` to display three different pieces of dynamic information using short echo syntax:

    - The current day of the week
    - The current time (formatted as "HH:MM AM/PM")
    - A custom greeting message based on the time of day
      Use comments to explain what each PHP block does.

6.  **Experiment with Echo**: In a new file, compare these three approaches:

    ```php
    // Method 1: Multiple arguments to echo
    echo 'Hello', ' ', 'World!';

    // Method 2: Concatenation
    echo 'Hello' . ' ' . 'World!';

    // Method 3: String interpolation
    echo "Hello World!";
    ```

    Run the script. What differences do you notice? Which method do you prefer and why?

## Further Reading

To deepen your understanding of the topics covered in this chapter:

- [PHP Tags](https://www.php.net/manual/en/language.basic-syntax.phptags.php) — Official documentation on PHP opening and closing tags, including short echo syntax
- [Echo Statement](https://www.php.net/manual/en/function.echo.php) — Learn more about outputting data
- [String Operators](https://www.php.net/manual/en/language.operators.string.php) — Complete guide to concatenation and string manipulation
- [PHP Comments](https://www.php.net/manual/en/language.basic-syntax.comments.php) — Best practices for commenting your code
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) — Industry-standard PHP coding style guide (includes closing tag best practices)
- [Built-in Web Server](https://www.php.net/manual/en/features.commandline.webserver.php) — Advanced options for the development server
- [Date and Time Functions](https://www.php.net/manual/en/ref.datetime.php) — Complete reference for working with dates

## Wrap-up

Congratulations! You've successfully written and executed your first PHP scripts. Here's what you've accomplished:

- ✓ Created and ran a PHP script from the command line
- ✓ Started a local development server and viewed PHP output in a browser
- ✓ Embedded PHP within HTML to create dynamic web pages
- ✓ Learned the basic syntax: PHP tags, echo statement, and comments
- ✓ Understood when to use (and omit) closing PHP tags
- ✓ Mastered the short echo syntax (`<?=`) for cleaner templates
- ✓ Learned string concatenation with the `.` and `.=` operators
- ✓ Gained experience with troubleshooting common issues

### What's Next

In the next chapter, we'll explore **variables** — the foundation for storing and manipulating data in your scripts. You'll learn about different data types, how to create and use variables, and how to work with constants.

### Quick Recap

```php
<?php
// Pure PHP files - no closing tag needed
echo 'Output text'; // Echo statement
echo 'Hello' . ' ' . 'World'; // Concatenation

// Comments
// Single line comment
/* Multi-line comment */
```

**In HTML templates:**

```php
<!DOCTYPE html>
<html>
<body>
    <!-- Long form -->
    <h1><?php echo $title; ?></h1>

    <!-- Short echo syntax -->
    <p><?= $description ?></p>
</body>
</html>
```
