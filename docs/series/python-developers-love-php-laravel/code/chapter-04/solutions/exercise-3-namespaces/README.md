# Exercise 3 Solution: Namespace and Autoloading

This directory demonstrates the solution for Exercise 3 from Chapter 04.

## Project Structure

```
exercise-3-namespaces/
├── app/
│   ├── Models/
│   │   └── User.php          # App\Models namespace
│   └── Services/
│       └── EmailService.php   # App\Services namespace
├── composer.json              # PSR-4 autoload configuration
├── index.php                  # Main entry point
└── README.md                  # This file
```

## Setup

1. Install Composer dependencies (if not already installed):
   ```bash
   composer install
   ```

2. Or if composer.json already exists, regenerate autoloader:
   ```bash
   composer dump-autoload
   ```

## Running

```bash
php index.php
```

Expected output:
```
Sending welcome email to alice@example.com for Alice
Sending welcome email to bob@example.com for Bob
```

## Key Concepts Demonstrated

- **PSR-4 Autoloading**: The `composer.json` file configures PSR-4 autoloading
- **Namespaces**: Classes are organized in `App\Models` and `App\Services` namespaces
- **Use Statements**: `index.php` uses `use` statements to import classes
- **Cross-Namespace Usage**: `User` class uses `EmailService` from a different namespace

## Files Explained

- **composer.json**: Configures PSR-4 autoloading mapping `App\` namespace to `app/` directory
- **app/Models/User.php**: User model in `App\Models` namespace
- **app/Services/EmailService.php**: Email service in `App\Services` namespace
- **index.php**: Main entry point demonstrating namespace usage

