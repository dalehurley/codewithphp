<?php

declare(strict_types=1);

/**
 * Namespaces and Autoloading - PHP vs Python
 * 
 * This file demonstrates PHP namespace syntax compared to Python modules.
 * Key differences:
 * - PHP: namespace App\Models; (explicit declaration)
 * - Python: file-based modules (app/models/user.py)
 * - PHP: use App\Models\User; (similar to Python import)
 * - PHP: \ for namespace separator (Python uses .)
 * - PHP requires autoloader (Composer) vs Python's built-in import
 */

// ============================================================================
// 1. Namespace Declaration
// ============================================================================

// Python: File app/models/user.py automatically becomes app.models.user module
// PHP:    Must explicitly declare namespace

namespace App\Models;

class User
{
    public function __construct(
        public string $name
    ) {}
}

// ============================================================================
// 2. Use Statements
// ============================================================================

// This file demonstrates usage - typically in a different file

// Python:
// from app.models import User, Post
// from app.utils.helpers import format_date

// PHP:
// use App\Models\User;
// use App\Models\Post;
// use App\Utils\Helpers\formatDate;

// Example usage (would be in index.php or another file):
/*
namespace App;

use App\Models\User;
use App\Models\Post;

$user = new User("Alice");
*/

// ============================================================================
// 3. Fully Qualified Names
// ============================================================================

// Python: import app.models.user; user.User("Alice")
// PHP:    \App\Models\User("Alice") (without use statement)

// Can use fully qualified name without use statement
// $user = new \App\Models\User("Alice");

// ============================================================================
// 4. Namespace Aliasing
// ============================================================================

// Python: from app.models import User as UserModel
// PHP:    use App\Models\User as UserModel;

// Example:
// use App\Models\User as UserModel;
// $user = new UserModel("Alice");

// ============================================================================
// 5. Grouped Use Statements (PHP 7.0+)
// ============================================================================

// Python: from app.models import User, Post
// PHP:    use App\Models\{User, Post};

// Example:
// use App\Models\{User, Post};
// use App\Services\{EmailService, NotificationService};

// ============================================================================
// 6. Global Namespace
// ============================================================================

// Access global namespace with leading backslash
// $date = new \DateTime();  // Global DateTime class

// ============================================================================
// 7. Namespace Functions and Constants
// ============================================================================

namespace App\Utils;

function formatDate(string $date): string
{
    return date('Y-m-d', strtotime($date));
}

const API_VERSION = "v1.0";

// Usage from another namespace:
// use function App\Utils\formatDate;
// use const App\Utils\API_VERSION;

// ============================================================================
// 8. PSR-4 Autoloading Example
// ============================================================================

/*
 * composer.json:
 * {
 *     "autoload": {
 *         "psr-4": {
 *             "App\\": "app/"
 *         }
 *     }
 * }
 * 
 * Then run: composer dump-autoload
 * 
 * Usage:
 * require_once 'vendor/autoload.php';
 * use App\Models\User;
 * $user = new User("Alice");  // Automatically loads app/Models/User.php
 */

// ============================================================================
// 9. File Structure Example
// ============================================================================

/*
 * Python structure:
 * app/
 *   models/
 *     __init__.py
 *     user.py        # Contains: class User
 * 
 * Usage: from app.models.user import User
 * 
 * PHP structure (PSR-4):
 * app/
 *   Models/
 *     User.php       # Contains: namespace App\Models; class User
 * 
 * Usage: use App\Models\User;
 */

// ============================================================================
// 10. Namespace Resolution
// ============================================================================

// PHP resolves namespaces in this order:
// 1. Fully qualified name (\App\Models\User)
// 2. Imported name (use App\Models\User; then User)
// 3. Relative to current namespace

namespace App\Controllers;

// If we're in App\Controllers namespace:
// use App\Models\User;  // Fully qualified import
// $user = new User("Alice");  // Uses imported User

// Or without import:
// $user = new \App\Models\User("Alice");  // Fully qualified

// Relative to current namespace:
// $user = new Models\User("Alice");  // Resolves to App\Controllers\Models\User (probably wrong!)

