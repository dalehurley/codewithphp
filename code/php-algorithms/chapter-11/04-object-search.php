<?php

declare(strict_types=1);

/**
 * Chapter 11: Linear Search & Variants
 * Part 4: Searching Objects and Complex Structures
 * 
 * Demonstrates searching in:
 * - Objects with properties
 * - Associative arrays
 * - Nested structures
 * - Multidimensional arrays
 * - Collections with relationships
 */

echo "=== Searching Objects and Complex Structures ===\n\n";

// ============================================================================
// 1. Search Objects by Property
// ============================================================================

echo "1. Search Objects by Property\n";
echo str_repeat("-", 50) . "\n";

class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public string $category
    ) {}
}

$products = [
    new Product('Laptop', 1200, 'Electronics'),
    new Product('Mouse', 25, 'Electronics'),
    new Product('PHP Book', 40, 'Books'),
    new Product('Desk', 300, 'Furniture'),
];

function findByProperty(array $objects, string $property, mixed $value): mixed
{
    foreach ($objects as $obj) {
        if (isset($obj->$property) && $obj->$property === $value) {
            return $obj;
        }
    }
    return null;
}

echo "Products:\n";
foreach ($products as $p) {
    echo "  {$p->name} (\${$p->price}) - {$p->category}\n";
}
echo "\n";

$found = findByProperty($products, 'category', 'Books');
echo "First book: " . ($found ? $found->name : 'None') . "\n";

$found = findByProperty($products, 'price', 25);
echo "Product with price $25: " . ($found ? $found->name : 'None') . "\n\n";

// ============================================================================
// 2. Search Associative Arrays
// ============================================================================

echo "2. Search Associative Arrays\n";
echo str_repeat("-", 50) . "\n";

$users = [
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
    ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
    ['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com'],
];

function findByKey(array $array, string $key, mixed $value): mixed
{
    foreach ($array as $item) {
        if (isset($item[$key]) && $item[$key] === $value) {
            return $item;
        }
    }
    return null;
}

echo "Users:\n";
foreach ($users as $u) {
    echo "  ID {$u['id']}: {$u['name']} ({$u['email']})\n";
}
echo "\n";

$user = findByKey($users, 'id', 2);
echo "User with ID 2: " . ($user ? $user['name'] : 'None') . "\n";

$user = findByKey($users, 'email', 'charlie@example.com');
echo "User with email charlie@example.com: " . ($user ? $user['name'] : 'None') . "\n\n";

// ============================================================================
// 3. Search Nested Structures
// ============================================================================

echo "3. Search Nested Structures\n";
echo str_repeat("-", 50) . "\n";

$companies = [
    [
        'name' => 'TechCorp',
        'employees' => [
            ['name' => 'Alice', 'role' => 'Developer'],
            ['name' => 'Bob', 'role' => 'Designer'],
        ]
    ],
    [
        'name' => 'StartupInc',
        'employees' => [
            ['name' => 'Charlie', 'role' => 'Developer'],
            ['name' => 'Diana', 'role' => 'Manager'],
        ]
    ],
];

function findEmployeeByName(array $companies, string $name): ?array
{
    foreach ($companies as $company) {
        foreach ($company['employees'] as $employee) {
            if ($employee['name'] === $name) {
                return [
                    'employee' => $employee,
                    'company' => $company['name']
                ];
            }
        }
    }
    return null;
}

echo "Companies and employees:\n";
foreach ($companies as $c) {
    echo "  {$c['name']}:\n";
    foreach ($c['employees'] as $e) {
        echo "    - {$e['name']} ({$e['role']})\n";
    }
}
echo "\n";

$result = findEmployeeByName($companies, 'Charlie');
if ($result) {
    echo "Charlie works at {$result['company']} as {$result['employee']['role']}\n";
}
echo "\n";

// ============================================================================
// 4. Search Multidimensional Arrays
// ============================================================================

echo "4. Search Multidimensional Arrays\n";
echo str_repeat("-", 50) . "\n";

$matrix = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
];

function findInMatrix(array $matrix, mixed $target): ?array
{
    foreach ($matrix as $row => $cols) {
        foreach ($cols as $col => $value) {
            if ($value === $target) {
                return ['row' => $row, 'col' => $col];
            }
        }
    }
    return null;
}

echo "Matrix:\n";
foreach ($matrix as $row) {
    echo "  " . implode(' ', $row) . "\n";
}
echo "\n";

$pos = findInMatrix($matrix, 5);
echo "Position of 5: " . ($pos ? "row {$pos['row']}, col {$pos['col']}" : "Not found") . "\n";

$pos = findInMatrix($matrix, 10);
echo "Position of 10: " . ($pos ? "row {$pos['row']}, col {$pos['col']}" : "Not found") . "\n\n";

// ============================================================================
// 5. Search with Multiple Criteria
// ============================================================================

echo "5. Search with Multiple Criteria\n";
echo str_repeat("-", 50) . "\n";

$cars = [
    ['make' => 'Toyota', 'model' => 'Camry', 'year' => 2020, 'price' => 25000],
    ['make' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'price' => 22000],
    ['make' => 'Toyota', 'model' => 'Corolla', 'year' => 2021, 'price' => 20000],
    ['make' => 'Ford', 'model' => 'Mustang', 'year' => 2020, 'price' => 35000],
];

function findByCriteria(array $items, array $criteria): array
{
    $results = [];
    
    foreach ($items as $item) {
        $matches = true;
        foreach ($criteria as $key => $value) {
            if (!isset($item[$key]) || $item[$key] !== $value) {
                $matches = false;
                break;
            }
        }
        if ($matches) {
            $results[] = $item;
        }
    }
    
    return $results;
}

echo "All cars:\n";
foreach ($cars as $c) {
    echo "  {$c['year']} {$c['make']} {$c['model']} - \${$c['price']}\n";
}
echo "\n";

$toyotas2021 = findByCriteria($cars, ['make' => 'Toyota', 'year' => 2021]);
echo "2021 Toyotas:\n";
foreach ($toyotas2021 as $c) {
    echo "  {$c['model']} - \${$c['price']}\n";
}
echo "\n";

// ============================================================================
// 6. Search with Range Criteria
// ============================================================================

echo "6. Search with Range Criteria\n";
echo str_repeat("-", 50) . "\n";

function findInRange(array $items, string $key, mixed $min, mixed $max): array
{
    $results = [];
    
    foreach ($items as $item) {
        if (isset($item[$key]) && $item[$key] >= $min && $item[$key] <= $max) {
            $results[] = $item;
        }
    }
    
    return $results;
}

$affordableCars = findInRange($cars, 'price', 20000, 25000);
echo "Cars in price range $20,000-$25,000:\n";
foreach ($affordableCars as $c) {
    echo "  {$c['make']} {$c['model']} - \${$c['price']}\n";
}
echo "\n";

// ============================================================================
// 7. Deep Property Search
// ============================================================================

echo "7. Deep Property Search (Nested Objects)\n";
echo str_repeat("-", 50) . "\n";

$orders = [
    [
        'id' => 1,
        'customer' => ['name' => 'Alice', 'email' => 'alice@example.com'],
        'total' => 150
    ],
    [
        'id' => 2,
        'customer' => ['name' => 'Bob', 'email' => 'bob@example.com'],
        'total' => 200
    ],
];

function findByNestedKey(array $items, array $path, mixed $value): mixed
{
    foreach ($items as $item) {
        $current = $item;
        $found = true;
        
        // Traverse path
        foreach ($path as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                $found = false;
                break;
            }
        }
        
        if ($found && $current === $value) {
            return $item;
        }
    }
    return null;
}

echo "Orders:\n";
foreach ($orders as $o) {
    echo "  Order #{$o['id']}: {$o['customer']['name']} - \${$o['total']}\n";
}
echo "\n";

$order = findByNestedKey($orders, ['customer', 'name'], 'Bob');
echo "Order by Bob: " . ($order ? "Order #{$order['id']}" : "None") . "\n\n";

// ============================================================================
// 8. Class with Custom Search Method
// ============================================================================

echo "8. Class with Custom Search Method\n";
echo str_repeat("-", 50) . "\n";

class StudentCollection
{
    private array $students = [];
    
    public function add(array $student): void
    {
        $this->students[] = $student;
    }
    
    public function findByGrade(string $grade): array
    {
        $results = [];
        foreach ($this->students as $student) {
            if ($student['grade'] === $grade) {
                $results[] = $student;
            }
        }
        return $results;
    }
    
    public function findTopStudents(int $minScore): array
    {
        $results = [];
        foreach ($this->students as $student) {
            if ($student['score'] >= $minScore) {
                $results[] = $student;
            }
        }
        return $results;
    }
    
    public function getAll(): array
    {
        return $this->students;
    }
}

$collection = new StudentCollection();
$collection->add(['name' => 'Alice', 'grade' => 'A', 'score' => 95]);
$collection->add(['name' => 'Bob', 'grade' => 'B', 'score' => 85]);
$collection->add(['name' => 'Charlie', 'grade' => 'A', 'score' => 92]);

echo "All students:\n";
foreach ($collection->getAll() as $s) {
    echo "  {$s['name']}: {$s['grade']} ({$s['score']})\n";
}
echo "\n";

$aStudents = $collection->findByGrade('A');
echo "A students:\n";
foreach ($aStudents as $s) {
    echo "  {$s['name']} ({$s['score']})\n";
}
echo "\n";

$topStudents = $collection->findTopStudents(90);
echo "Students with score >= 90:\n";
foreach ($topStudents as $s) {
    echo "  {$s['name']} ({$s['score']})\n";
}
echo "\n";

// ============================================================================
// 9. Search with Property Path
// ============================================================================

echo "9. Search with Property Path (Dot Notation)\n";
echo str_repeat("-", 50) . "\n";

function getValueByPath(array $array, string $path): mixed
{
    $keys = explode('.', $path);
    $current = $array;
    
    foreach ($keys as $key) {
        if (isset($current[$key])) {
            $current = $current[$key];
        } else {
            return null;
        }
    }
    
    return $current;
}

function findByPath(array $items, string $path, mixed $value): array
{
    $results = [];
    
    foreach ($items as $item) {
        $itemValue = getValueByPath($item, $path);
        if ($itemValue === $value) {
            $results[] = $item;
        }
    }
    
    return $results;
}

$data = [
    ['user' => ['profile' => ['city' => 'New York'], 'age' => 25]],
    ['user' => ['profile' => ['city' => 'Boston'], 'age' => 30]],
    ['user' => ['profile' => ['city' => 'New York'], 'age' => 28]],
];

$newYorkers = findByPath($data, 'user.profile.city', 'New York');
echo "Users from New York: " . count($newYorkers) . "\n\n";

// ============================================================================
// 10. Real-World Example: Search Blog Posts
// ============================================================================

echo "10. Real-World Example: Search Blog Posts\n";
echo str_repeat("-", 50) . "\n";

$blogPosts = [
    [
        'id' => 1,
        'title' => 'Introduction to PHP',
        'author' => 'Alice',
        'tags' => ['PHP', 'Tutorial', 'Beginner'],
        'published' => true
    ],
    [
        'id' => 2,
        'title' => 'Advanced PHP Patterns',
        'author' => 'Bob',
        'tags' => ['PHP', 'Advanced', 'OOP'],
        'published' => true
    ],
    [
        'id' => 3,
        'title' => 'Draft: PHP 8.4 Features',
        'author' => 'Alice',
        'tags' => ['PHP', 'News'],
        'published' => false
    ],
];

// Find by author
function findPostsByAuthor(array $posts, string $author): array
{
    $results = [];
    foreach ($posts as $post) {
        if ($post['author'] === $author) {
            $results[] = $post;
        }
    }
    return $results;
}

// Find by tag
function findPostsByTag(array $posts, string $tag): array
{
    $results = [];
    foreach ($posts as $post) {
        if (in_array($tag, $post['tags'])) {
            $results[] = $post;
        }
    }
    return $results;
}

// Find published posts
function findPublishedPosts(array $posts): array
{
    $results = [];
    foreach ($posts as $post) {
        if ($post['published']) {
            $results[] = $post;
        }
    }
    return $results;
}

echo "All blog posts:\n";
foreach ($blogPosts as $p) {
    echo "  [{$p['id']}] {$p['title']} by {$p['author']} - " . ($p['published'] ? 'published' : 'draft') . "\n";
}
echo "\n";

$alicePosts = findPostsByAuthor($blogPosts, 'Alice');
echo "Posts by Alice: " . count($alicePosts) . "\n";

$tutorialPosts = findPostsByTag($blogPosts, 'Tutorial');
echo "Tutorial posts: " . count($tutorialPosts) . "\n";

$published = findPublishedPosts($blogPosts);
echo "Published posts: " . count($published) . "\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- Search objects by property\n";
echo "- Search associative arrays by key\n";
echo "- Search nested structures\n";
echo "- Search multidimensional arrays\n";
echo "- Multiple criteria and range queries\n";
echo "- Deep property path searches\n";
echo "- Custom collection classes\n";
echo str_repeat("=", 50) . "\n";




