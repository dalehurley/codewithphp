<?php

/**
 * Searching in Objects and Complex Data Structures
 *
 * Demonstrates search techniques for objects, associative arrays,
 * multidimensional arrays, and nested structures.
 *
 * @package CodeWithPHP\Algorithms\Chapter11
 */

declare(strict_types=1);

/**
 * Simple User class for demonstrations
 */
class User
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role = 'user',
        public bool $active = true
    ) {}
}

/**
 * Product class for e-commerce examples
 */
class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public string $category,
        public int $stock
    ) {}
}

/**
 * Find object by property value
 *
 * @param array $objects Array of objects
 * @param string $property Property name
 * @param mixed $value Value to match
 * @return object|null The found object or null
 */
function findByProperty(array $objects, string $property, mixed $value): ?object
{
    foreach ($objects as $obj) {
        if (property_exists($obj, $property) && $obj->$property === $value) {
            return $obj;
        }
    }

    return null;
}

/**
 * Find all objects by property value
 *
 * @param array $objects Array of objects
 * @param string $property Property name
 * @param mixed $value Value to match
 * @return array Array of matching objects
 */
function findAllByProperty(array $objects, string $property, mixed $value): array
{
    $results = [];

    foreach ($objects as $obj) {
        if (property_exists($obj, $property) && $obj->$property === $value) {
            $results[] = $obj;
        }
    }

    return $results;
}

/**
 * Search in multidimensional associative array
 *
 * @param array $arr Array of associative arrays
 * @param string $key Key to search
 * @param mixed $value Value to match
 * @return array|null The found array or null
 */
function searchMultidimensional(array $arr, string $key, mixed $value): ?array
{
    foreach ($arr as $item) {
        if (isset($item[$key]) && $item[$key] === $value) {
            return $item;
        }
    }

    return null;
}

/**
 * Find all in multidimensional array
 *
 * @param array $arr Array of associative arrays
 * @param string $key Key to search
 * @param mixed $value Value to match
 * @return array Array of matching items
 */
function findAllMultidimensional(array $arr, string $key, mixed $value): array
{
    $results = [];

    foreach ($arr as $item) {
        if (isset($item[$key]) && $item[$key] === $value) {
            $results[] = $item;
        }
    }

    return $results;
}

/**
 * Deep search in nested structures (recursive)
 *
 * @param array $data Nested array structure
 * @param string $key Key to search
 * @param mixed $value Value to match
 * @return array|null The found array or null
 */
function deepSearch(array $data, string $key, mixed $value): ?array
{
    foreach ($data as $item) {
        if (is_array($item)) {
            // Check current level
            if (isset($item[$key]) && $item[$key] === $value) {
                return $item;
            }

            // Recursively search nested arrays
            $result = deepSearch($item, $key, $value);
            if ($result !== null) {
                return $result;
            }
        }
    }

    return null;
}

/**
 * Search with multiple criteria
 *
 * @param array $objects Array of objects
 * @param array $criteria Key-value pairs to match
 * @return array Array of matching objects
 */
function findByMultipleCriteria(array $objects, array $criteria): array
{
    $results = [];

    foreach ($objects as $obj) {
        $matches = true;

        foreach ($criteria as $property => $value) {
            if (!property_exists($obj, $property) || $obj->$property !== $value) {
                $matches = false;
                break;
            }
        }

        if ($matches) {
            $results[] = $obj;
        }
    }

    return $results;
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "=== Searching in Objects and Complex Structures ===\n\n";

// Example 1: Search in array of User objects
echo "Example 1: Find user by email\n";
echo str_repeat('-', 50) . "\n";
$users = [
    new User(1, 'Alice', 'alice@example.com', 'admin'),
    new User(2, 'Bob', 'bob@example.com', 'user'),
    new User(3, 'Charlie', 'charlie@example.com', 'moderator'),
];

$user = findByProperty($users, 'email', 'bob@example.com');
if ($user) {
    echo "Found: {$user->name} ({$user->email}) - Role: {$user->role}\n";
} else {
    echo "User not found\n";
}
echo "\n";

// Example 2: Find all users by role
echo "Example 2: Find all users with role 'user'\n";
echo str_repeat('-', 50) . "\n";
$users[] = new User(4, 'Dave', 'dave@example.com', 'user');
$users[] = new User(5, 'Eve', 'eve@example.com', 'user');

$regularUsers = findAllByProperty($users, 'role', 'user');
echo "Users with role 'user':\n";
foreach ($regularUsers as $user) {
    echo "  - {$user->name} ({$user->email})\n";
}
echo "Total: " . count($regularUsers) . "\n\n";

// Example 3: Search in products
echo "Example 3: Find products by category\n";
echo str_repeat('-', 50) . "\n";
$products = [
    new Product(1, 'Laptop', 1200.00, 'Electronics', 10),
    new Product(2, 'Mouse', 25.00, 'Electronics', 50),
    new Product(3, 'Desk', 350.00, 'Furniture', 5),
    new Product(4, 'Keyboard', 75.00, 'Electronics', 30),
    new Product(5, 'Chair', 200.00, 'Furniture', 8),
];

$electronics = findAllByProperty($products, 'category', 'Electronics');
echo "Electronics products:\n";
foreach ($electronics as $product) {
    printf("  - %s: $%.2f (Stock: %d)\n", $product->name, $product->price, $product->stock);
}
echo "\n";

// Example 4: Search in associative array
echo "Example 4: Search in multidimensional associative array\n";
echo str_repeat('-', 50) . "\n";
$customers = [
    ['id' => 1, 'name' => 'John Doe', 'city' => 'New York'],
    ['id' => 2, 'name' => 'Jane Smith', 'city' => 'Los Angeles'],
    ['id' => 3, 'name' => 'Bob Johnson', 'city' => 'Chicago'],
];

$customer = searchMultidimensional($customers, 'city', 'Los Angeles');
if ($customer) {
    echo "Found: {$customer['name']} in {$customer['city']}\n";
}
echo "\n";

// Example 5: Deep search in nested structure
echo "Example 5: Deep search in nested arrays\n";
echo str_repeat('-', 50) . "\n";
$organization = [
    'departments' => [
        'engineering' => [
            ['name' => 'Alice', 'position' => 'Senior Dev'],
            ['name' => 'Bob', 'position' => 'Junior Dev'],
        ],
        'sales' => [
            ['name' => 'Charlie', 'position' => 'Sales Manager'],
            ['name' => 'Diana', 'position' => 'Sales Rep'],
        ],
    ],
];

$person = deepSearch($organization, 'name', 'Charlie');
if ($person) {
    echo "Found: {$person['name']} - {$person['position']}\n";
}
echo "\n";

// Example 6: Multiple criteria search
echo "Example 6: Search with multiple criteria\n";
echo str_repeat('-', 50) . "\n";
$users = [
    new User(1, 'Alice', 'alice@example.com', 'admin', true),
    new User(2, 'Bob', 'bob@example.com', 'user', true),
    new User(3, 'Charlie', 'charlie@example.com', 'user', false),
    new User(4, 'Dave', 'dave@example.com', 'user', true),
];

$activeRegularUsers = findByMultipleCriteria($users, [
    'role' => 'user',
    'active' => true
]);

echo "Active users with role 'user':\n";
foreach ($activeRegularUsers as $user) {
    echo "  - {$user->name}\n";
}
echo "\n";

// Example 7: Complex search criteria
echo "Example 7: Find products by price range and category\n";
echo str_repeat('-', 50) . "\n";

function findProductsByPriceRange(array $products, string $category, float $minPrice, float $maxPrice): array
{
    $results = [];

    foreach ($products as $product) {
        if ($product->category === $category &&
            $product->price >= $minPrice &&
            $product->price <= $maxPrice) {
            $results[] = $product;
        }
    }

    return $results;
}

$affordableElectronics = findProductsByPriceRange($products, 'Electronics', 0, 100);
echo "Electronics under $100:\n";
foreach ($affordableElectronics as $product) {
    printf("  - %s: $%.2f\n", $product->name, $product->price);
}
echo "\n";

// Example 8: Search with custom comparator
echo "Example 8: Custom search comparator\n";
echo str_repeat('-', 50) . "\n";

function findWithComparator(array $arr, callable $comparator): ?array
{
    foreach ($arr as $item) {
        if ($comparator($item)) {
            return $item;
        }
    }
    return null;
}

$expensiveProduct = findWithComparator($products, fn($p) => $p->price > 500);
if ($expensiveProduct) {
    printf("Expensive product: %s at $%.2f\n", $expensiveProduct->name, $expensiveProduct->price);
}
echo "\n";

// Example 9: Search and transform
echo "Example 9: Search and extract specific fields\n";
echo str_repeat('-', 50) . "\n";

function extractField(array $objects, string $property, string $field): array
{
    $results = [];

    foreach ($objects as $obj) {
        if (property_exists($obj, $property)) {
            $results[] = $obj->$field;
        }
    }

    return $results;
}

// Extract all user names
$userNames = array_map(fn($u) => $u->name, $users);
echo "All user names: " . implode(', ', $userNames) . "\n";

// Extract emails of active users only
$activeUserEmails = array_map(
    fn($u) => $u->email,
    findAllByProperty($users, 'active', true)
);
echo "Active user emails: " . implode(', ', $activeUserEmails) . "\n";
echo "\n";

// Example 10: Performance comparison - Object vs Array search
echo "Example 10: Performance - Objects vs Arrays\n";
echo str_repeat('-', 50) . "\n";

// Create test data
$testUsers = [];
$testArrays = [];
for ($i = 1; $i <= 10000; $i++) {
    $testUsers[] = new User($i, "User$i", "user$i@example.com");
    $testArrays[] = ['id' => $i, 'name' => "User$i", 'email' => "user$i@example.com"];
}

// Search in objects
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    findByProperty($testUsers, 'email', 'user9999@example.com');
}
$objectTime = microtime(true) - $start;

// Search in arrays
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    searchMultidimensional($testArrays, 'email', 'user9999@example.com');
}
$arrayTime = microtime(true) - $start;

printf("Object search: %.6f seconds\n", $objectTime);
printf("Array search:  %.6f seconds\n", $arrayTime);
printf("Difference:    %.2f%%\n", abs($objectTime - $arrayTime) / min($objectTime, $arrayTime) * 100);
