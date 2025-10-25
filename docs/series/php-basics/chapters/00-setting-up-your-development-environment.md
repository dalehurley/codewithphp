---
title: "00: Setting Up Your Development Environment"
description: "Install PHP, a web server, and VS Code to start building amazing applications."
series: "php-basics"
chapter: 0
order: 0
difficulty: "Beginner"
prerequisites: []
---

# Chapter 00: Setting Up Your Development Environment

## Overview

Welcome to the first chapter of PHP From Scratch! Before we can start writing code, we need to set up a proper development environment. This foundational step is crucial because it gives you all the tools you need to write, test, debug, and track your PHP applications like a professional developer.

In this chapter, you'll install PHP itself, learn to use its simple built-in web server, and set up a modern code editor (Visual Studio Code) with extensions that will make writing PHP a joy. You'll also set up Git for version control to track your progress, and install Xdebug for powerful debugging capabilities. By the end, you'll have a complete, professional development environment and will run your first PHP script to verify everything works.

## Prerequisites

- **Operating System**: macOS, Windows, or Linux (Ubuntu/Debian)
- **Time Required**: ~30–40 minutes
- **Skills**: Basic command line familiarity (running commands in a terminal)
- **Internet Connection**: Required for downloading PHP, VS Code, and Git

> **Note**: No prior PHP knowledge is needed. This is where your journey begins!

## What You'll Build

By the end of this chapter, you'll have:

- PHP 8.4+ installed and verified on your system
- A working project folder with a test script
- PHP's built-in development server running locally
- Visual Studio Code configured with PHP extensions
- Git installed and initialized in your project for version control
- Xdebug configured for step-through debugging in VS Code
- A complete, professional-grade PHP development environment

## Quick Start

If you're already familiar with command-line tools and want to get up and running quickly, here's the express version:

```bash
# macOS (with Homebrew)
brew install php

# Windows (with Scoop)
scoop install php

# Linux (Ubuntu/Debian)
sudo apt update && sudo apt install php-cli php-mbstring php-xml

# Verify installation
php -v

# Create a test project
mkdir php-from-scratch && cd php-from-scratch
echo "<?php phpinfo();" > index.php

# Start the server
php -S localhost:8000
```

Then install [VS Code](https://code.visualstudio.com/) and add the **PHP Intelephense** extension. Open `http://localhost:8000` in your browser to verify.

For detailed steps with troubleshooting, continue reading below.

## Objectives

- Install the latest stable version of PHP (8.4 or higher).
- Verify the installation using the command line.
- Learn to use PHP's built-in development web server.
- Install and configure Visual Studio Code with essential extensions for PHP development.
- Set up Git for version control and make your first commit.
- Install and configure Xdebug for professional debugging with breakpoints.
- Run a test script to confirm your environment is working correctly.

## Step 1: Install PHP (~5-10 min)

First, you need to get PHP running on your machine. The process varies slightly depending on your operating system.

### macOS

For macOS, the easiest way to install PHP is with [Homebrew](https://brew.sh/), a popular package manager for macOS and Linux.

1.  **Check if you have Homebrew**:
    First, check if Homebrew is already installed:

    ```bash
    # Check if Homebrew is installed
    brew --version
    ```

    If you see a version number (e.g., `Homebrew 4.x.x`), skip to step 2. If you get `command not found`, continue below.

2.  **Install Homebrew** (if you don't have it):
    Visit [brew.sh](https://brew.sh/) for the latest instructions, or run this command in your terminal:

    ```bash
    # Install Homebrew
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    ```

    The installer will guide you through the process. You may need to enter your password. Follow any additional instructions shown at the end (like adding Homebrew to your PATH).

    > **Note**: The installation may take a few minutes and will download Xcode Command Line Tools if needed.

3.  **Install PHP**:
    Once Homebrew is ready, run the following command to install the latest stable version of PHP:

    ```bash
    # Install PHP using Homebrew
    brew install php
    ```

### Windows

For Windows, [Scoop](https://scoop.sh/) is an excellent command-line package manager that simplifies installing development tools. It's similar to Homebrew but for Windows.

> **Alternative**: If you prefer [Chocolatey](https://chocolatey.org/), you can use it instead. The process is similar.

1.  **Check if you have Scoop**:
    First, check if Scoop is already installed. Open **PowerShell** and run:

    ```powershell
    # Check if Scoop is installed
    scoop --version
    ```

    If you see a version number, skip to step 2. If you get an error, continue below.

2.  **Install Scoop** (if you don't have it):
    Visit [scoop.sh](https://scoop.sh/) for the latest instructions, or run these commands in **PowerShell** (not Command Prompt):

    ```powershell
    # Set execution policy to allow Scoop installation
    Set-ExecutionPolicy RemoteSigned -Scope CurrentUser

    # Install Scoop
    irm get.scoop.sh | iex
    ```

    The first command allows PowerShell to run the installation script. You may see a prompt asking to confirm—type **Y** and press Enter.

    > **Note**: Make sure you're using PowerShell (the blue icon), not Command Prompt (the black icon). You can find PowerShell by searching for it in the Start menu.

3.  **Install PHP**:
    Once Scoop is ready, run this command in PowerShell to install the latest stable PHP:

    ```powershell
    # Install PHP using Scoop
    scoop install php
    ```

### Linux (Ubuntu/Debian)

For Debian-based Linux distributions like Ubuntu, you can use the built-in `apt` package manager.

1.  **Update Your Package List**:
    Open your terminal and run:

    ```bash
    sudo apt update
    ```

2.  **Install PHP**:
    Install PHP and some common extensions with this command:

    ```bash
    sudo apt install php-cli php-mbstring php-xml
    ```

### Validation

After the installation finishes, you can verify that PHP is installed correctly by checking its version. Open a **new** terminal window and run:

```bash
# Check PHP version and confirm installation
php -v
```

**Expected Output:**

```
PHP 8.4 (cli) (built: Mar 14 2024 10:28:48) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.3.4, Copyright (c) Zend Technologies
```

Your version number may differ slightly, but as long as it starts with `PHP 8.` or higher, you're good to go.

### Troubleshooting

**Problem**: `php: command not found` or `'php' is not recognized`

**Solutions**:

1. Close and reopen your terminal window completely
2. On Windows, restart PowerShell as Administrator
3. If still not working, restart your computer (this updates the PATH environment variable)
4. On macOS with Homebrew, run `brew link php` to ensure PHP is linked correctly
5. On Linux, run `which php` to check if PHP is installed but not in PATH

> **Why it works**: Package managers add PHP to your system's PATH variable, which tells your terminal where to find the `php` command. Sometimes this requires a fresh terminal session or restart to take effect.

## Step 2: Run PHP's Built-in Web Server (~5 min)

**Goal**: Create a test project and run it on PHP's built-in web server to verify everything works.

You don't need a complex setup like Apache or Nginx to get started. PHP comes with a lightweight, built-in web server that is perfect for local development.

### Actions

1.  **Create a Project Folder**:
    Make a new directory for your PHP projects and navigate into it.

    ```bash
    # Create and enter your project directory
    mkdir php-from-scratch
    cd php-from-scratch
    ```

2.  **Create an `index.php` File**:
    Using your preferred method, create a file named `index.php` with the following content:

    ```php
    <?php

    phpinfo();
    ```

    You can create this file in several ways:

    **Option A: Using a text editor**

    - Open any text editor (Notepad, TextEdit, nano, vim)
    - Paste the code above
    - Save as `index.php` in your `php-from-scratch` folder

    **Option B: Using the command line**

    ```bash
    # macOS/Linux
    echo '<?php phpinfo();' > index.php

    # Windows (PowerShell)
    echo '<?php phpinfo();' | Out-File -FilePath index.php -Encoding utf8
    ```

3.  **Start the Web Server**:
    From your project directory, run the following command:

    ```bash
    # Start PHP's built-in web server on port 8000
    php -S localhost:8000
    ```

    **Expected Output:**

    ```
    [Sat Oct 25 2025 10:30:00] PHP 8.4.0 Development Server (http://localhost:8000) started
    ```

4.  **View the Result**:
    Open your web browser and navigate to `http://localhost:8000`. You should see a beautifully formatted page displaying all the details of your PHP configuration, including version, loaded extensions, and system information.

    The page will have a purple header with "PHP Version 8.4.0" and tables showing configuration details.

### Validation

You've successfully set up the server if:

- The terminal shows "Development Server started"
- Your browser displays the PHP info page at `http://localhost:8000`
- You see detailed PHP configuration tables with a purple theme

### Troubleshooting

**Problem**: `Address already in use` error

**Solutions**:

1. Port 8000 is occupied. Try a different port: `php -S localhost:8080`
2. Or find and stop the process using port 8000

**Problem**: Browser shows "This site can't be reached" or connection refused

**Solutions**:

1. Ensure the PHP server is still running in your terminal (look for the "started" message)
2. Double-check you're visiting `http://localhost:8000` (not `https`)
3. Try `http://127.0.0.1:8000` instead

**Problem**: Browser downloads the PHP file instead of executing it

**Solutions**:

1. Ensure the file is named `index.php` (not `index.php.txt`)
2. Restart the PHP server by pressing `Ctrl+C` and running `php -S localhost:8000` again

> **Why it works**: The `php -S` command starts PHP's built-in development server. `localhost:8000` specifies the network address (localhost/127.0.0.1) and port (8000). When you visit this address in your browser, the server looks for `index.php` in the current directory, executes the PHP code, and sends the HTML output back to your browser. The `phpinfo()` function generates a detailed report about your PHP installation.

**To stop the server**: Go back to your terminal and press `Ctrl+C`.

## Step 3: Set Up Your Code Editor (~5 min)

**Goal**: Install Visual Studio Code and configure it with PHP extensions for an optimal development experience.

A good code editor makes development faster and more enjoyable. We recommend [Visual Studio Code](https://code.visualstudio.com/) (VS Code), which is free, powerful, and has excellent support for PHP through extensions.

### Actions

1.  **Install VS Code**:

    - Visit the [official VS Code website](https://code.visualstudio.com/)
    - Download the installer for your operating system
    - Run the installer and follow the installation wizard
    - Launch VS Code when installation completes

2.  **Open Your Project**:
    In VS Code, open your project folder:

    - Click **File** → **Open Folder** (or **File** → **Open** on macOS)
    - Navigate to your `php-from-scratch` folder and select it
    - Click **Open** or **Select Folder**

3.  **Install Recommended Extensions**:
    Extensions add new features to VS Code. We'll install two essential PHP extensions.

    **To open Extensions view:**

    - Press `Ctrl+Shift+X` (Windows/Linux) or `Cmd+Shift+X` (macOS)
    - Or click the Extensions icon in the left sidebar (looks like four squares)

    **Install these extensions:**

    - **PHP Intelephense** (by Ben Mewburn)

      - Search for "PHP Intelephense" in the Extensions view
      - Click the **Install** button
      - Provides intelligent code completion, error checking, and symbol navigation

    - **Prettier - Code formatter** (by Prettier)
      - Search for "Prettier - Code formatter"
      - Click the **Install** button
      - Automatically formats your code to maintain consistent style

### Validation

Verify your VS Code setup:

1.  **Check Extensions**:

    - Open the Extensions view (`Ctrl+Shift+X` or `Cmd+Shift+X`)
    - You should see both "PHP Intelephense" and "Prettier - Code formatter" listed with green checkmarks

2.  **Test PHP Intelephense**:
    - Open your `index.php` file in VS Code
    - Type `<?php echo` on a new line
    - You should see autocomplete suggestions pop up
    - Hover over `phpinfo` and you should see documentation appear

**Expected Result:** When you hover over PHP functions, you'll see tooltips with documentation. As you type, autocomplete suggestions will appear.

### Troubleshooting

**Problem**: Intelephense shows "PHP executable not found" warning

**Solutions**:

1. Restart VS Code after installing PHP
2. Open VS Code settings (`Ctrl+,` or `Cmd+,`)
3. Search for "intelephense.environment.phpVersion"
4. Set it to your PHP version (e.g., "8.4.0")

**Problem**: Extensions don't show autocomplete or suggestions

**Solutions**:

1. Reload VS Code: Press `Ctrl+Shift+P` (or `Cmd+Shift+P`), type "Reload Window", and press Enter
2. Ensure the extension is enabled: Check the Extensions view for green checkmarks
3. Check that you've opened the folder (not just a single file)

**Problem**: Multiple PHP extensions conflict

**Solutions**:

1. Disable or uninstall the built-in PHP extension: Search for "PHP Language Features" and click "Disable"
2. Keep only Intelephense enabled for the best experience

> **Why these extensions?**: Intelephense provides IntelliSense (intelligent code completion), real-time error detection, and documentation on hover, making you far more productive. Prettier keeps your code formatted consistently, so you can focus on logic rather than spacing and style.

## Step 4: Set Up Version Control with Git (~5-7 min)

**Goal**: Install Git and initialize your project for version control to track changes and maintain a history of your work.

Version control is an essential tool for every developer. Git allows you to track changes, revert mistakes, and collaborate with others. Starting with Git from day one builds good habits and gives you a safety net as you learn.

### Actions

1.  **Check if you have Git**:
    First, check if Git is already installed:

    ```bash
    # Check if Git is installed
    git --version
    ```

    If you see a version number (e.g., `git version 2.x.x`), skip to step 3. If you get `command not found`, continue below.

2.  **Install Git**:

    **macOS:**

    If you installed Homebrew earlier, Git might already be installed. If not:

    ```bash
    # Install Git using Homebrew
    brew install git
    ```

    **Windows:**

    ```powershell
    # Install Git using Scoop
    scoop install git
    ```

    Alternatively, download the installer from [git-scm.com](https://git-scm.com/download/win).

    **Linux (Ubuntu/Debian):**

    ```bash
    # Install Git using apt
    sudo apt install git
    ```

3.  **Configure Git** (first-time setup):
    Set your name and email. These will be attached to your commits:

    ```bash
    # Set your name (replace with your actual name)
    git config --global user.name "Your Name"

    # Set your email (replace with your actual email)
    git config --global user.email "your.email@example.com"
    ```

4.  **Initialize Your Project**:
    Navigate to your project folder and initialize a Git repository:

    ```bash
    # Make sure you're in your project directory
    cd php-from-scratch

    # Initialize a Git repository
    git init
    ```

    **Expected Output:**

    ```
    Initialized empty Git repository in /path/to/php-from-scratch/.git/
    ```

5.  **Create a `.gitignore` File**:
    Create a `.gitignore` file to tell Git which files to ignore:

    ```bash
    # Create .gitignore file
    echo ".DS_Store" > .gitignore
    ```

    Or create it in VS Code with these contents:

    ```
    .DS_Store
    Thumbs.db
    .vscode/
    *.log
    ```

6.  **Make Your First Commit**:
    Track your files and create your first commit:

    ```bash
    # Add all files to staging
    git add .

    # Create your first commit
    git commit -m "Initial commit: Set up PHP development environment"
    ```

    **Expected Output:**

    ```
    [main (root-commit) abc1234] Initial commit: Set up PHP development environment
     2 files changed, 3 insertions(+)
     create mode 100644 .gitignore
     create mode 100644 index.php
    ```

### Validation

Verify your Git setup:

```bash
# Check Git status
git status

# View commit history
git log --oneline
```

**Expected Result:**

- `git status` should show "nothing to commit, working tree clean"
- `git log --oneline` should show your initial commit

### Troubleshooting

**Problem**: `git: command not found` after installation

**Solutions:**

1. Close and reopen your terminal completely
2. On Windows, restart PowerShell
3. Run `git --version` again to verify

**Problem**: Git asks for username/email when committing

**Solutions:**

1. This is normal for first-time setup
2. Run the `git config --global` commands from step 3 above
3. Try your commit again

**Problem**: "fatal: not a git repository"

**Solutions:**

1. Make sure you're in the correct directory: `pwd` (macOS/Linux) or `cd` (Windows)
2. Run `git init` in your project folder
3. Check that a `.git` folder exists (it may be hidden)

> **Why version control?**: Git is the industry standard for tracking changes to code. It lets you experiment freely, undo mistakes, and see the complete history of your project. Every commit is a snapshot you can return to. As you progress through this series, you'll be able to commit after each chapter, creating a clear timeline of your learning journey.

## Step 5: Install Xdebug for Debugging (~7-10 min)

**Goal**: Install and configure Xdebug so you can set breakpoints, step through code, and inspect variables in real-time.

Debugging with `var_dump()` and `echo` statements works, but professional developers use proper debugging tools. Xdebug integrates with VS Code to let you pause execution, inspect variables, and step through your code line by line.

### Actions

1.  **Install Xdebug**:

    **macOS (with Homebrew):**

    ```bash
    # Install Xdebug
    pecl install xdebug
    ```

    If `pecl` isn't found, install it first:

    ```bash
    brew install php-pecl
    pecl install xdebug
    ```

    **Windows (with Scoop):**

    Scoop's PHP package typically includes Xdebug. Check if it's already installed:

    ```powershell
    # Check for Xdebug
    php -v
    ```

    If you don't see "with Xdebug" in the output:

    ```powershell
    # Install PHP with Xdebug
    scoop uninstall php
    scoop install php-xdebug
    ```

    **Linux (Ubuntu/Debian):**

    ```bash
    # Install Xdebug
    sudo apt install php-xdebug
    ```

2.  **Find Your `php.ini` File**:
    Locate your PHP configuration file:

    ```bash
    # Find php.ini location
    php --ini
    ```

    Look for the line that says "Loaded Configuration File". Note this path.

3.  **Configure Xdebug**:
    Open your `php.ini` file in VS Code or any text editor. Add these lines at the end:

    ```ini
    [xdebug]
    zend_extension=xdebug.so
    xdebug.mode=debug
    xdebug.start_with_request=yes
    xdebug.client_port=9003
    ```

    > **Note for Windows**: Use `zend_extension=php_xdebug.dll` instead of `.so`

    Save the file.

4.  **Verify Xdebug Installation**:
    Restart your terminal and check if Xdebug is loaded:

    ```bash
    # Check for Xdebug
    php -v
    ```

    **Expected Output:**

    ```
    PHP 8.4.0 (cli) (built: Mar 14 2024 10:28:48) (NTS)
    Copyright (c) The PHP Group
    Zend Engine v4.4.0, Copyright (c) Zend Technologies
        with Xdebug v3.3.0, Copyright (c) 2002-2023, by Derick Rethans
    ```

    You should see "with Xdebug" in the output.

5.  **Install PHP Debug Extension in VS Code**:

    - Open VS Code
    - Go to Extensions (`Ctrl+Shift+X` or `Cmd+Shift+X`)
    - Search for "PHP Debug" by Xdebug
    - Click **Install**

6.  **Create a Debug Configuration**:
    In VS Code, create a debug configuration:

    - Press `Ctrl+Shift+D` (or `Cmd+Shift+D`) to open the Run and Debug panel
    - Click "create a launch.json file"
    - Select "PHP" from the dropdown
    - VS Code will create a `.vscode/launch.json` file

    The file should look like this:

    ```json
    {
      "version": "0.2.0",
      "configurations": [
        {
          "name": "Listen for Xdebug",
          "type": "php",
          "request": "launch",
          "port": 9003
        }
      ]
    }
    ```

### Validation

Test your debugging setup:

1.  **Create a test file** (`debug-test.php`):

    ```php
    <?php

    $name = "PHP Developer";
    $message = "Hello, $name!";

    echo $message;
    ```

2.  **Set a breakpoint**:

    - Open `debug-test.php` in VS Code
    - Click in the left margin next to line 4 (the `$message = ...` line)
    - A red dot should appear (this is your breakpoint)

3.  **Start debugging**:
    - Press `F5` or click the green play button in the Run and Debug panel
    - You should see "Listen for Xdebug" in the status bar
4.  **Run your script**:
    In your terminal:

    ```bash
    # Run the script with Xdebug
    php debug-test.php
    ```

5.  **Inspect variables**:
    - VS Code should pause at your breakpoint
    - You should see the variables panel showing `$name` with its value
    - Use `F10` to step to the next line
    - Use `F5` to continue execution

**Expected Result:** VS Code pauses at your breakpoint, shows variable values, and lets you step through code.

### Troubleshooting

**Problem**: `php -v` doesn't show Xdebug

**Solutions:**

1. Double-check your `php.ini` path with `php --ini`
2. Make sure you edited the correct file (CLI version, not FPM or Apache)
3. Verify the Xdebug extension line syntax matches your OS (`.so` vs `.dll`)
4. Restart your terminal completely
5. On macOS, you may need to specify the full path: `zend_extension="/usr/local/lib/php/pecl/20230831/xdebug.so"`

**Problem**: Breakpoints don't work / VS Code doesn't pause

**Solutions:**

1. Ensure "Listen for Xdebug" is running (green play button in Debug panel)
2. Check that `xdebug.client_port` in `php.ini` matches `launch.json` (both should be 9003)
3. Verify `xdebug.start_with_request=yes` is in your `php.ini`
4. Try running `php -dxdebug.start_with_request=yes debug-test.php`

**Problem**: "Cannot find php.ini file"

**Solutions:**

1. Run `php --ini` to see all configuration files PHP checks
2. If no file is loaded, create one in the location shown under "Configuration File Path"
3. Copy settings from a template if available

**Problem**: PECL install fails on macOS

**Solutions:**

1. Install Xcode Command Line Tools: `xcode-select --install`
2. Try installing via Homebrew: `brew install php@8.4-xdebug` (if available)
3. Manual installation: Download from [xdebug.org/download](https://xdebug.org/download)

> **Why Xdebug?**: Debugging is a critical skill. Xdebug transforms how you understand your code by letting you pause execution at any point, inspect variables, evaluate expressions, and step through logic. It's invaluable for learning, troubleshooting bugs, and understanding how PHP executes your code. The initial setup takes a few minutes but will save you countless hours throughout your journey.

## What You've Accomplished

Let's recap what you've achieved in this chapter:

✅ **Installed PHP 8.4+** on your system using a package manager  
✅ **Verified the installation** using the `php -v` command  
✅ **Created your first project folder** and test script  
✅ **Started PHP's built-in web server** and viewed a working PHP page  
✅ **Installed and configured VS Code** with PHP Intelephense and Prettier extensions  
✅ **Set up Git for version control** and made your first commit  
✅ **Installed and configured Xdebug** for professional debugging with breakpoints  
✅ **Tested your complete development environment** end-to-end

You now have a professional-grade PHP development setup that mirrors what developers use in production environments. You have version control for tracking changes, intelligent code completion, and powerful debugging tools—everything you need to learn and build with confidence.

## Further Reading

Want to dive deeper into your new tools? Here are some excellent resources:

- [PHP Official Documentation](https://www.php.net/docs.php) – The authoritative source for all things PHP
- [VS Code PHP Development Tips](https://code.visualstudio.com/docs/languages/php) – Official VS Code PHP guide
- [PHP: The Right Way](https://phptherightway.com/) – A comprehensive quick reference for PHP best practices
- [Intelephense Documentation](https://intelephense.com/) – Learn advanced features of your PHP extension
- [Pro Git Book](https://git-scm.com/book/en/v2) – Free, comprehensive guide to Git (official)
- [GitHub Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf) – Quick reference for common Git commands
- [Xdebug Documentation](https://xdebug.org/docs/) – Official Xdebug documentation and configuration guide
- [VS Code Debugging Guide](https://code.visualstudio.com/docs/editor/debugging) – Master debugging in VS Code

## Wrap-up

Congratulations! You now have a complete, modern PHP development environment set up on your local machine. You've installed PHP, learned how to run a local server, configured a professional code editor with intelligent features, set up version control with Git, and enabled powerful debugging with Xdebug.

This is the same professional setup that experienced developers use every day. You have all the tools you need to write, test, debug, and track your code effectively.

This foundation will serve you throughout your entire PHP journey. Every line of code you write in this series will run on the environment you just built, and you'll be able to commit your progress after each chapter, building a complete history of your learning journey.

**Next Steps**: In the next chapter, [01: Your First PHP Script](/series/php-basics/chapters/01-your-first-php-script), you'll write real PHP code, learn about syntax, and build your first dynamic web page. See you there!

## Knowledge Check

Test your understanding of setting up a PHP development environment:

<Quiz
title="Chapter 00 Quiz: Development Environment Setup"
:questions="[
{
question: 'What command do you use to check if PHP is installed and see its version?',
options: [
{ text: 'php -v', correct: true, explanation: 'The -v flag displays the PHP version and confirms installation.' },
{ text: 'php --check', correct: false, explanation: 'This is not a valid PHP command.' },
{ text: 'php version', correct: false, explanation: 'PHP uses -v or --version flags, not a version command.' },
{ text: 'check php', correct: false, explanation: 'This is not a valid command in any shell.' }
]
},
{
question: 'What is the purpose of PHP\'s built-in web server started with `php -S localhost:8000`?',
options: [
{ text: 'To run PHP scripts locally during development', correct: true, explanation: 'The built-in server is perfect for local development and testing without Apache or Nginx.' },
{ text: 'To deploy PHP applications to production', correct: false, explanation: 'The built-in server is for development only, not production use.' },
{ text: 'To compile PHP code into machine code', correct: false, explanation: 'PHP is interpreted, not compiled, and the server runs your code dynamically.' },
{ text: 'To install PHP packages and dependencies', correct: false, explanation: 'Package management is handled by Composer, not the web server.' }
]
},
{
question: 'Which VS Code extension provides intelligent code completion and error checking for PHP?',
options: [
{ text: 'PHP Intelephense', correct: true, explanation: 'Intelephense provides IntelliSense, error detection, and documentation for PHP.' },
{ text: 'Prettier', correct: false, explanation: 'Prettier is a code formatter, not a PHP language server.' },
{ text: 'PHP Debug', correct: false, explanation: 'PHP Debug is for debugging with Xdebug, not code completion.' },
{ text: 'ESLint', correct: false, explanation: 'ESLint is for JavaScript/TypeScript, not PHP.' }
]
},
{
question: 'What is the primary benefit of using Xdebug over echo/var_dump for debugging?',
options: [
{ text: 'You can set breakpoints and step through code line by line', correct: true, explanation: 'Xdebug lets you pause execution, inspect variables in real-time, and step through logic systematically.' },
{ text: 'It makes your code run faster', correct: false, explanation: 'Xdebug actually adds overhead; it\'s for debugging, not performance.' },
{ text: 'It automatically fixes syntax errors', correct: false, explanation: 'Xdebug helps you find bugs but doesn\'t fix them automatically.' },
{ text: 'It\'s required for PHP to work', correct: false, explanation: 'Xdebug is optional; PHP works fine without it, but it greatly enhances debugging.' }
]
},
{
question: 'Why should you use Git for version control from the start of your PHP journey?',
options: [
{ text: 'To track changes and have the ability to undo mistakes', correct: true, explanation: 'Git creates snapshots of your code, letting you experiment safely and revert when needed.' },
{ text: 'It makes PHP run faster', correct: false, explanation: 'Version control doesn\'t affect runtime performance.' },
{ text: 'It\'s required to run PHP code', correct: false, explanation: 'PHP works without Git; version control is a development best practice.' },
{ text: 'To automatically deploy code to production', correct: false, explanation: 'While Git enables deployment workflows, that\'s not its primary purpose for beginners.' }
]
}
]"
/>
