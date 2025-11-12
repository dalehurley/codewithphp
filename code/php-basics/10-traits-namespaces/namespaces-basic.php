<?php

declare(strict_types=1);

/**
 * Namespaces - Organizing Code
 * 
 * Namespaces prevent naming conflicts and organize code logically.
 * Essential for modern PHP applications.
 */

// Note: In real applications, each class would be in its own file
// This single-file demo shows the concepts

namespace App\Models {
    class User
    {
        public function __construct(public string $name) {}
    }

    class Post
    {
        public function __construct(public string $title) {}
    }
}

namespace App\Controllers {
    class UserController
    {
        public function index(): string
        {
            return "UserController::index()";
        }
    }
}

namespace App\Services {
    class EmailService
    {
        public function send(string $to, string $subject): string
        {
            return "Sending email to $to: $subject";
        }
    }
}

namespace Vendor\Package {
    // Simulating a third-party package with same class name
    class User
    {
        public function __construct(public string $username) {}
    }
}

// Switch to global namespace for examples
namespace {

    echo "=== Namespaces Basics ===" . PHP_EOL . PHP_EOL;

    // Example 1: Using fully qualified names
    echo "1. Fully Qualified Names:" . PHP_EOL;

    $user1 = new \App\Models\User("Alice");
    echo "App User: {$user1->name}" . PHP_EOL;

    $user2 = new \Vendor\Package\User("Bob");
    echo "Vendor User: {$user2->username}" . PHP_EOL;
    echo PHP_EOL;

    // Example 2: Use statement (importing)
    echo "2. Use Statement:" . PHP_EOL;

    use App\Models\Post;
    use App\Services\EmailService;

    $post = new Post("My First Post");
    echo "Post: {$post->title}" . PHP_EOL;

    $email = new EmailService();
    echo $email->send("user@example.com", "Welcome") . PHP_EOL;
    echo PHP_EOL;

    // Example 3: Aliasing with 'as'
    echo "3. Aliasing to Resolve Conflicts:" . PHP_EOL;

    use App\Models\User as AppUser;
    use Vendor\Package\User as VendorUser;

    $appUser = new AppUser("Charlie");
    $vendorUser = new VendorUser("David");

    echo "App User: {$appUser->name}" . PHP_EOL;
    echo "Vendor User: {$vendorUser->username}" . PHP_EOL;
    echo PHP_EOL;

    // Example 4: Group use declarations
    echo "4. Group Use Declarations:" . PHP_EOL;

    // Instead of:
    // use App\Models\User;
    // use App\Models\Post;

    // You can write:
    use App\Models\{User, Post as BlogPost};

    $user = new User("Eve");
    $post = new BlogPost("PHP Namespaces");
    echo "Created user: {$user->name}" . PHP_EOL;
    echo "Created post: {$post->title}" . PHP_EOL;
    echo PHP_EOL;

    // Example 5: Namespace constants and functions
    echo "5. Namespace Constants and Functions:" . PHP_EOL;
}

namespace App\Helpers {
    const APP_VERSION = '1.0.0';
    const DEBUG_MODE = true;

    function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    function formatDate(string $date): string
    {
        return date('Y-m-d', strtotime($date));
    }
}

namespace {

    use function App\Helpers\sanitize;
    use function App\Helpers\formatDate;
    use const App\Helpers\APP_VERSION;
    use const App\Helpers\DEBUG_MODE;

    echo "App Version: " . APP_VERSION . PHP_EOL;
    echo "Debug Mode: " . (DEBUG_MODE ? 'On' : 'Off') . PHP_EOL;
    echo "Sanitized: " . sanitize('<script>alert("xss")</script>') . PHP_EOL;
    echo "Date: " . formatDate('2024-01-15') . PHP_EOL;
    echo PHP_EOL;

    // Example 6: Practical example - project structure
    echo "6. Typical Project Structure:" . PHP_EOL;
    echo <<<'STRUCTURE'
    Project/
    ├── src/
    │   ├── App/
    │   │   ├── Models/
    │   │   │   ├── User.php          → namespace App\Models;
    │   │   │   └── Post.php          → namespace App\Models;
    │   │   ├── Controllers/
    │   │   │   ├── UserController.php → namespace App\Controllers;
    │   │   │   └── PostController.php → namespace App\Controllers;
    │   │   ├── Services/
    │   │   │   └── EmailService.php  → namespace App\Services;
    │   │   └── Helpers/
    │   │       └── functions.php     → namespace App\Helpers;
    │   └── vendor/                   → Third-party packages
    └── composer.json                 → Autoloading configuration
    
STRUCTURE;
    echo PHP_EOL;

    // Example 7: Best practices
    echo "7. Namespace Best Practices:" . PHP_EOL;
    echo "✓ One class per file" . PHP_EOL;
    echo "✓ Match namespace to directory structure" . PHP_EOL;
    echo "✓ Use PSR-4 autoloading standard" . PHP_EOL;
    echo "✓ Vendor\\Project\\Component structure" . PHP_EOL;
    echo "✓ Use 'use' statements at top of file" . PHP_EOL;
    echo "✓ Group related classes in same namespace" . PHP_EOL;
    echo PHP_EOL;

    // Example 8: Common patterns
    echo "8. Common Namespace Patterns:" . PHP_EOL;
    echo "App\\Models\\         - Domain entities" . PHP_EOL;
    echo "App\\Controllers\\    - HTTP request handlers" . PHP_EOL;
    echo "App\\Services\\       - Business logic" . PHP_EOL;
    echo "App\\Repositories\\   - Data access" . PHP_EOL;
    echo "App\\Middleware\\     - Request/response processing" . PHP_EOL;
    echo "App\\Exceptions\\     - Custom exceptions" . PHP_EOL;
    echo "App\\Interfaces\\     - Contracts/interfaces" . PHP_EOL;
    echo "App\\Traits\\         - Reusable trait code" . PHP_EOL;
}
