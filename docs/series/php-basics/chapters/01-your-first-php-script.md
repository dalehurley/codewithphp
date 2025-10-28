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

![Your First PHP Script](/images/php-basics/chapter-01-first-php-script-hero-full.webp)

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

### Goal

Create your first PHP file and print a message to confirm your environment works.

### Actions

1. **Navigate to your project directory**: In your terminal, move into the `php-from-scratch` folder you created earlier.
2. **Create a new file**: Inside that directory, create a file named `hello.php`. You can delete the previous `index.php` if you no longer need it.
3. **Add the PHP code**: Open `hello.php` in your editor and add the following:

```php
# filename: hello.php
<?php

echo 'Hello, World!';
```

### Expected Result

You now have a `hello.php` file that outputs a classic greeting when executed.

### Why It Works

- `<?php` tells PHP where code execution begins.
- `echo` outputs the provided string to the terminal or browser.
- `'Hello, World!'` is a string literal containing the text to display.
- The trailing semicolon (`;`) ends the statement so PHP knows it’s complete.

### Troubleshooting

- **Error: `syntax error, unexpected '$name'`** — Make sure the file starts with `<?php`.
- **Error: `Undefined function 'echo'`** — `echo` is a language construct, not a function; double-check you typed it correctly.
- **Blank file created** — Ensure the code snippet was saved in `hello.php` before moving on.

## Step 2: Run the Script from the Command Line (~1 min)

### Goal

Execute a PHP file from the terminal to verify that the interpreter is installed correctly.

### Actions

1. **Stay in your project directory**: Confirm you’re still inside `php-from-scratch`.
2. **Run the script**:

```bash
# Execute the PHP script
php hello.php
```

### Expected Result

Your terminal prints:

```text
Hello, World!
```

> **Tip**: If the prompt appears on the same line, append `PHP_EOL` to your echo statement to add a newline.

### Why It Works

- The `php` CLI executes the file using the installed PHP interpreter.
- PHP reads `hello.php`, runs the statements, and sends the output to STDOUT.

### Troubleshooting

- **`php: command not found`** — PHP isn’t installed or isn’t in your PATH. Verify with `php --version` and revisit Chapter 00 if needed.
- **`Could not open input file: hello.php`** — You’re in the wrong directory or the file is named differently. Use `ls`/`dir` to confirm.
- **No output** — Ensure your script matches the previous step and includes the `echo` statement.

## Step 3: Run the Script in the Browser (~2 min)

### Goal

Serve the script through PHP’s built-in web server so you can view output in the browser.

### Actions

1. **Start the development server**:

```bash
# Start the built-in PHP development server
php -S localhost:8000
```

You should see something like:

```text
[Sat Oct 25 14:30:00 2025] PHP 8.4.0 Development Server (http://localhost:8000) started
```

2. **Open the script in a browser**: Visit `http://localhost:8000/hello.php`.

### Expected Result

The browser displays “Hello, World!” exactly as the terminal did, confirming PHP is executing on the server and returning HTML to the client.

### Why It Works

- `php -S` spins up a lightweight web server ideal for local development.
- When the browser requests `/hello.php`, PHP executes the script and returns the rendered HTML response.

### Troubleshooting

- **`Failed to listen on localhost:8000`** — Another process is using the port. Try `php -S localhost:8001` and adjust the URL accordingly.
- **“This site can’t be reached”** — Ensure the server is still running in your terminal and that the URL includes `http://`.
- **Blank page** — Check the terminal running the server for syntax errors or warnings; view the page source to confirm any HTML output.

## Step 4: Embedding PHP in HTML (~3 min)

### Goal

Mix PHP with HTML to render dynamic content in a traditional web page structure.

### Actions

1. **Create a new file**: Name it `hello-web.php`.
2. **Add the following HTML/PHP hybrid code**:

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

> **Note**: `date('Y-m-d H:i:s')` returns a formatted timestamp such as `2025-10-25 14:30:45`.

3. **View the file**: With the dev server running, open `http://localhost:8000/hello-web.php`.

### Expected Result

The browser shows an HTML page titled “My First PHP Page” with a heading and a timestamp that reflects the current date and time.

### Why It Works

- PHP executes on the server and outputs plain HTML for the browser to render.
- Embedding code inside `<?php ... ?>` blocks lets you insert dynamic values anywhere in the markup.

### Troubleshooting

- **Raw PHP printed in the browser** — Ensure the file has a `.php` extension and the server is serving it via PHP.
- **Date always the same** — You’re likely viewing a cached version. Refresh the page or disable caching in your browser.

## Step 5: Adding Comments (~1 min)

### Goal

Learn how to document code using PHP’s comment syntax.

### Actions

1. **Create an optional demo file** with comment examples:

```php
# filename: comments-demo.php
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

2. **Run the file** if you’d like to confirm the comments are ignored when the script executes.

### Expected Result

The script outputs `Hello with comments!` and nothing else, proving comments have no effect on runtime behavior.

### Why It Works

- `//` and `#` mark single-line comments that PHP skips when executing.
- `/* ... */` wraps multi-line comments, perfect for longer explanations or temporarily disabling blocks of code.

### Troubleshooting

- **Unexpected output** — Make sure echo statements aren’t inside comment blocks.
- **Syntax error near `*/`** — Ensure multi-line comments start with `/*` and end with a matching `*/`.

## Step 6: PHP Tags and String Operations (~3 min)

### Goal

Understand PHP tag usage and practice essential string-output techniques.

### Actions

1. **Review closing tag best practices**:
   - Omit `?>` in pure PHP files to avoid stray whitespace issues.
   - Include `?>` only when mixing PHP and HTML in the same file, as shown below:

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

2. **Experiment with short echo tags** to simplify templates:

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

    <!-- Short form (cleaner) -->
    <h2><?= $author ?></h2>

    <p>Current time: <?= date('H:i:s') ?></p>
</body>
</html>
```

3. **Practice concatenation and interpolation**:

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

```php
# filename: string-comparison.php
<?php

$name = 'Alice';
$age = 25;

// Concatenation
echo 'Name: ' . $name . ', Age: ' . $age . PHP_EOL;

// String interpolation (double quotes only)
echo "Name: $name, Age: $age" . PHP_EOL;

// Complex expressions often read better with concatenation
echo 'Next year, ' . $name . ' will be ' . ($age + 1) . ' years old.' . PHP_EOL;
```

4. **Run the concatenation example**:

```bash
php concatenation.php
```

### Expected Result

```
Hello World!
Full name: Jane Doe
Hello World!
```

### Why It Works

- Omitting `?>` prevents accidental whitespace that can break HTTP headers.
- The short echo tag (`<?=`) is shorthand for `<?php echo ... ?>` and is always enabled in PHP 8.4.
- The dot operator (`.`) concatenates strings; `.=` appends to an existing string variable.
- Interpolation within double quotes offers concise syntax for embedding variables.

### Troubleshooting

- **`<?=` causes a parse error** — You’re likely executing the script via CLI without HTML context. Use standard `echo` in pure PHP scripts.
- **Unexpected concatenation results** — Add explicit spaces (`'Hello' . ' ' . 'World'`) to avoid merged words.
- **Output shows literal `$variable`** — Interpolation requires double quotes. Use `"` for interpolation and `'` for literal strings.

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
    - Example: 'Hello' + ' ' + 'Alice' + '!' = 'Hello Alice!'

5.  **Mixed Content**: Update `hello-web.php` to display three different pieces of dynamic information using short echo syntax:

    - The current day of the week
    - The current time (formatted as 'HH:MM AM/PM')
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

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`basic-syntax.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/01-first-script/basic-syntax.php) - Basic PHP syntax and echo examples
- [`mixing-html.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/01-first-script/mixing-html.php) - Mixing PHP with HTML
- [`variables-demo.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/01-first-script/variables-demo.php) - Working with variables and concatenation
- `solutions/` - Solutions to chapter exercises
  :::

## Further Reading

To deepen your understanding of the topics covered in this chapter:

- [PHP Tags](https://www.php.net/manual/en/language.basic-syntax.phptags.php) — Official documentation on PHP opening and closing tags, including short echo syntax
- [Echo Statement](https://www.php.net/manual/en/function.echo.php) — Learn more about outputting data
- [String Operators](https://www.php.net/manual/en/language.operators.string.php) — Complete guide to concatenation and string manipulation
- [PHP Comments](https://www.php.net/manual/en/language.basic-syntax.comments.php) — Best practices for commenting your code
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) — Industry-standard PHP coding style guide (includes closing tag best practices)
- [Built-in Web Server](https://www.php.net/manual/en/features.commandline.webserver.php) — Advanced options for the development server
- [Date and Time Functions](https://www.php.net/manual/en/ref.datetime.php) — Complete reference for working with dates

## Knowledge Check

Test your understanding of PHP basics and syntax:

<Quiz
title="Chapter 01 Quiz: Your First PHP Script"
:questions="[
{
question: 'What is the correct way to open a PHP code block?',
options: [
{ text: '<?php', correct: true, explanation: 'The <?php tag is the standard way to begin a PHP code block.' },
{ text: '<php>', correct: false, explanation: 'HTML-style tags don\'t work for PHP. Use <?php instead.' },
{ text: '<?', correct: false, explanation: 'Short tags are deprecated. Always use <?php for opening tags.' },
{ text: '<%php%>', correct: false, explanation: 'This syntax is from other languages like ASP, not PHP.' }
]
},
{
question: 'When should you omit the closing ?> tag in PHP files?',
options: [
{ text: 'In pure PHP files that contain only PHP code', correct: true, explanation: 'Omitting the closing tag prevents accidental whitespace and follows PSR-12 standards.' },
{ text: 'Never, all PHP files must have a closing tag', correct: false, explanation: 'Modern PHP best practices recommend omitting the closing tag in pure PHP files.' },
{ text: 'Only in files smaller than 100 lines', correct: false, explanation: 'File size doesn\'t matter; it\'s about whether the file mixes HTML and PHP.' },
{ text: 'Only when using namespaces', correct: false, explanation: 'The closing tag rule applies to all pure PHP files, not just those with namespaces.' }
]
},
{
question: 'What does the short echo syntax <?= $variable ?> do?',
options: [
{ text: 'It outputs the value of $variable (equivalent to <?php echo $variable; ?>)', correct: true, explanation: 'The <?= syntax is shorthand for echoing values in HTML templates.' },
{ text: 'It assigns a value to $variable', correct: false, explanation: 'The <?= syntax is for output only, not assignment.' },
{ text: 'It creates a comment', correct: false, explanation: 'Comments use //, #, or /* */ syntax, not <?=.' },
{ text: 'It imports a variable from another file', correct: false, explanation: 'Importing uses require/include, not <?=.' }
]
},
{
question: 'How do you concatenate (join) two strings in PHP?',
options: [
{ text: 'Using the . (dot) operator', correct: true, explanation: 'PHP uses the . operator for string concatenation: \'Hello\' . \' World\'.' },
{ text: 'Using the + (plus) operator', correct: false, explanation: 'The + operator is for arithmetic. Use . for string concatenation.' },
{ text: 'Using the & (ampersand) operator', correct: false, explanation: 'The & is used for references and bitwise operations, not concatenation.' },
{ text: 'Using the , (comma) operator', correct: false, explanation: 'Commas separate arguments in echo, but . is the concatenation operator.' }
]
},
{
question: 'Which comment syntax is NOT valid in PHP?',
options: [
{ text: '<!-- This is a comment -->', correct: true, explanation: 'HTML comments don\'t work as PHP comments. Use //, #, or /* */.' },
{ text: '// This is a comment', correct: false, explanation: 'This is valid PHP single-line comment syntax.' },
{ text: '# This is a comment', correct: false, explanation: 'Shell-style comments work in PHP for single lines.' },
{ text: '/* This is a comment */', correct: false, explanation: 'This is valid PHP multi-line comment syntax.' }
]
}
]"
/>
