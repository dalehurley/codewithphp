<?php

declare(strict_types=1);

/**
 * Chapter 11: Linear Search & Variants
 * Part 5: Practical Real-World Applications
 * 
 * Demonstrates practical uses:
 * - Simple grep implementation
 * - Autocomplete/search suggestions
 * - Form validation
 * - Database-like queries
 * - Text search in files
 * - Inventory management
 */

echo "=== Practical Real-World Applications ===\n\n";

// ============================================================================
// 1. Simple Grep Implementation
// ============================================================================

echo "1. Simple Grep (Text Search in Lines)\n";
echo str_repeat("-", 50) . "\n";

function grep(array $lines, string $pattern, bool $caseInsensitive = false): array
{
    $results = [];
    
    foreach ($lines as $lineNum => $line) {
        $haystack = $caseInsensitive ? strtolower($line) : $line;
        $needle = $caseInsensitive ? strtolower($pattern) : $pattern;
        
        if (str_contains($haystack, $needle)) {
            $results[] = [
                'line' => $lineNum + 1,
                'content' => $line
            ];
        }
    }
    
    return $results;
}

$logFile = [
    "2024-01-01 10:00:00 INFO User logged in",
    "2024-01-01 10:05:00 ERROR Database connection failed",
    "2024-01-01 10:10:00 INFO User logged out",
    "2024-01-01 10:15:00 ERROR File not found",
    "2024-01-01 10:20:00 WARNING Slow query detected",
];

echo "Log file:\n";
foreach ($logFile as $i => $line) {
    echo "  " . ($i + 1) . ": {$line}\n";
}
echo "\n";

$errors = grep($logFile, 'ERROR');
echo "Lines containing 'ERROR':\n";
foreach ($errors as $result) {
    echo "  Line {$result['line']}: {$result['content']}\n";
}
echo "\n";

// ============================================================================
// 2. Autocomplete/Search Suggestions
// ============================================================================

echo "2. Autocomplete/Search Suggestions\n";
echo str_repeat("-", 50) . "\n";

function autocomplete(array $items, string $prefix, int $maxResults = 5): array
{
    $results = [];
    $prefix = strtolower($prefix);
    
    foreach ($items as $item) {
        if (str_starts_with(strtolower($item), $prefix)) {
            $results[] = $item;
            
            if (count($results) >= $maxResults) {
                break;
            }
        }
    }
    
    return $results;
}

$cities = [
    'New York', 'New Orleans', 'Newark', 'Newport',
    'Boston', 'Buffalo', 'Baltimore',
    'Chicago', 'Cleveland', 'Charlotte',
];

sort($cities); // Better to pre-sort for autocomplete

echo "Cities: " . implode(', ', $cities) . "\n\n";

$suggestions = autocomplete($cities, 'New', 3);
echo "Autocomplete 'New' (max 3): " . implode(', ', $suggestions) . "\n";

$suggestions = autocomplete($cities, 'Bo', 5);
echo "Autocomplete 'Bo': " . implode(', ', $suggestions) . "\n\n";

// ============================================================================
// 3. Form Validation
// ============================================================================

echo "3. Form Validation\n";
echo str_repeat("-", 50) . "\n";

function validateUnique(array $existingValues, mixed $newValue): bool
{
    return !in_array($newValue, $existingValues);
}

function validateInList(array $allowedValues, mixed $value): bool
{
    return in_array($value, $allowedValues);
}

$existingUsernames = ['alice', 'bob', 'charlie'];
$allowedRoles = ['admin', 'user', 'moderator'];

echo "Existing usernames: " . implode(', ', $existingUsernames) . "\n";
echo "Allowed roles: " . implode(', ', $allowedRoles) . "\n\n";

// Check if username is available
$newUsername = 'diana';
$isAvailable = validateUnique($existingUsernames, $newUsername);
echo "Username '{$newUsername}' available? " . ($isAvailable ? 'Yes' : 'No') . "\n";

$newUsername = 'bob';
$isAvailable = validateUnique($existingUsernames, $newUsername);
echo "Username '{$newUsername}' available? " . ($isAvailable ? 'Yes' : 'No') . "\n";

// Validate role
$role = 'admin';
$isValid = validateInList($allowedRoles, $role);
echo "Role '{$role}' valid? " . ($isValid ? 'Yes' : 'No') . "\n";

$role = 'superuser';
$isValid = validateInList($allowedRoles, $role);
echo "Role '{$role}' valid? " . ($isValid ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// 4. Database-Like Queries
// ============================================================================

echo "4. Database-Like Queries (In-Memory)\n";
echo str_repeat("-", 50) . "\n";

class SimpleDatabase
{
    private array $data = [];
    
    public function insert(array $record): void
    {
        $this->data[] = $record;
    }
    
    public function select(array $conditions = []): array
    {
        if (empty($conditions)) {
            return $this->data;
        }
        
        $results = [];
        foreach ($this->data as $record) {
            $matches = true;
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] !== $value) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                $results[] = $record;
            }
        }
        
        return $results;
    }
    
    public function update(array $conditions, array $updates): int
    {
        $count = 0;
        foreach ($this->data as &$record) {
            $matches = true;
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] !== $value) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                foreach ($updates as $key => $value) {
                    $record[$key] = $value;
                }
                $count++;
            }
        }
        return $count;
    }
    
    public function delete(array $conditions): int
    {
        $newData = [];
        $count = 0;
        
        foreach ($this->data as $record) {
            $matches = true;
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] !== $value) {
                    $matches = false;
                    break;
                }
            }
            if (!$matches) {
                $newData[] = $record;
            } else {
                $count++;
            }
        }
        
        $this->data = $newData;
        return $count;
    }
}

$db = new SimpleDatabase();
$db->insert(['id' => 1, 'name' => 'Alice', 'role' => 'admin']);
$db->insert(['id' => 2, 'name' => 'Bob', 'role' => 'user']);
$db->insert(['id' => 3, 'name' => 'Charlie', 'role' => 'user']);

echo "All records:\n";
foreach ($db->select() as $record) {
    echo "  ID {$record['id']}: {$record['name']} ({$record['role']})\n";
}
echo "\n";

$admins = $db->select(['role' => 'admin']);
echo "Admins: " . count($admins) . " found\n";
foreach ($admins as $record) {
    echo "  {$record['name']}\n";
}
echo "\n";

// ============================================================================
// 5. Inventory Search
// ============================================================================

echo "5. Inventory Search\n";
echo str_repeat("-", 50) . "\n";

class Inventory
{
    private array $items = [];
    
    public function addItem(string $sku, string $name, int $quantity, float $price): void
    {
        $this->items[] = [
            'sku' => $sku,
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price
        ];
    }
    
    public function findBySKU(string $sku): ?array
    {
        foreach ($this->items as $item) {
            if ($item['sku'] === $sku) {
                return $item;
            }
        }
        return null;
    }
    
    public function findLowStock(int $threshold): array
    {
        $results = [];
        foreach ($this->items as $item) {
            if ($item['quantity'] <= $threshold) {
                $results[] = $item;
            }
        }
        return $results;
    }
    
    public function searchByName(string $query): array
    {
        $results = [];
        $query = strtolower($query);
        
        foreach ($this->items as $item) {
            if (str_contains(strtolower($item['name']), $query)) {
                $results[] = $item;
            }
        }
        
        return $results;
    }
    
    public function getAll(): array
    {
        return $this->items;
    }
}

$inventory = new Inventory();
$inventory->addItem('LAP001', 'Laptop Dell XPS', 5, 1200);
$inventory->addItem('MOU001', 'Wireless Mouse', 2, 25);
$inventory->addItem('KEY001', 'Mechanical Keyboard', 8, 80);
$inventory->addItem('MON001', 'Monitor 27 inch', 3, 400);

echo "Inventory:\n";
foreach ($inventory->getAll() as $item) {
    echo "  [{$item['sku']}] {$item['name']} - Qty: {$item['quantity']}, \${$item['price']}\n";
}
echo "\n";

$item = $inventory->findBySKU('MOU001');
echo "Item MOU001: " . ($item ? $item['name'] : 'Not found') . "\n";

$lowStock = $inventory->findLowStock(3);
echo "\nLow stock items (≤3):\n";
foreach ($lowStock as $item) {
    echo "  {$item['name']}: {$item['quantity']} remaining\n";
}
echo "\n";

$monitors = $inventory->searchByName('monitor');
echo "Search 'monitor':\n";
foreach ($monitors as $item) {
    echo "  {$item['name']}\n";
}
echo "\n";

// ============================================================================
// 6. Tag/Category Search
// ============================================================================

echo "6. Tag/Category Search\n";
echo str_repeat("-", 50) . "\n";

$articles = [
    ['title' => 'PHP Basics', 'tags' => ['PHP', 'Tutorial', 'Beginner']],
    ['title' => 'Advanced OOP', 'tags' => ['PHP', 'OOP', 'Advanced']],
    ['title' => 'JavaScript ES6', 'tags' => ['JavaScript', 'ES6', 'Tutorial']],
    ['title' => 'PHP Design Patterns', 'tags' => ['PHP', 'Patterns', 'Advanced']],
];

function findByTag(array $items, string $tag): array
{
    $results = [];
    foreach ($items as $item) {
        if (in_array($tag, $item['tags'])) {
            $results[] = $item;
        }
    }
    return $results;
}

function findByTags(array $items, array $tags, bool $matchAll = false): array
{
    $results = [];
    foreach ($items as $item) {
        if ($matchAll) {
            // All tags must match
            $matches = true;
            foreach ($tags as $tag) {
                if (!in_array($tag, $item['tags'])) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                $results[] = $item;
            }
        } else {
            // Any tag matches
            foreach ($tags as $tag) {
                if (in_array($tag, $item['tags'])) {
                    $results[] = $item;
                    break;
                }
            }
        }
    }
    return $results;
}

echo "All articles:\n";
foreach ($articles as $a) {
    echo "  {$a['title']} - Tags: " . implode(', ', $a['tags']) . "\n";
}
echo "\n";

$phpArticles = findByTag($articles, 'PHP');
echo "PHP articles: " . count($phpArticles) . "\n";

$advancedPHP = findByTags($articles, ['PHP', 'Advanced'], true);
echo "Advanced PHP articles (all tags): " . count($advancedPHP) . "\n";

$tutorials = findByTags($articles, ['Tutorial', 'Beginner'], false);
echo "Tutorial OR Beginner articles: " . count($tutorials) . "\n\n";

// ============================================================================
// 7. Permission Check
// ============================================================================

echo "7. Permission Check\n";
echo str_repeat("-", 50) . "\n";

function hasPermission(array $userPermissions, string $requiredPermission): bool
{
    return in_array($requiredPermission, $userPermissions);
}

function hasAllPermissions(array $userPermissions, array $requiredPermissions): bool
{
    foreach ($requiredPermissions as $permission) {
        if (!in_array($permission, $userPermissions)) {
            return false;
        }
    }
    return true;
}

$adminPermissions = ['read', 'write', 'delete', 'admin'];
$userPermissions = ['read', 'write'];

echo "Admin permissions: " . implode(', ', $adminPermissions) . "\n";
echo "User permissions: " . implode(', ', $userPermissions) . "\n\n";

echo "Admin can delete? " . (hasPermission($adminPermissions, 'delete') ? 'Yes' : 'No') . "\n";
echo "User can delete? " . (hasPermission($userPermissions, 'delete') ? 'Yes' : 'No') . "\n";

$requiredPerms = ['read', 'write'];
echo "\nAdmin has [read, write]? " . (hasAllPermissions($adminPermissions, $requiredPerms) ? 'Yes' : 'No') . "\n";
echo "User has [read, write]? " . (hasAllPermissions($userPermissions, $requiredPerms) ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// 8. Configuration Lookup
// ============================================================================

echo "8. Configuration Lookup\n";
echo str_repeat("-", 50) . "\n";

class Config
{
    private array $config = [];
    
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
    
    public function has(string $key): bool
    {
        return isset($this->config[$key]);
    }
    
    public function getByPrefix(string $prefix): array
    {
        $results = [];
        foreach ($this->config as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $results[$key] = $value;
            }
        }
        return $results;
    }
}

$config = new Config();
$config->set('database.host', 'localhost');
$config->set('database.port', 3306);
$config->set('database.name', 'myapp');
$config->set('app.name', 'My Application');
$config->set('app.debug', true);

echo "Get 'database.host': " . $config->get('database.host') . "\n";
echo "Get 'cache.ttl' (with default): " . $config->get('cache.ttl', 3600) . "\n";
echo "Has 'app.debug'? " . ($config->has('app.debug') ? 'Yes' : 'No') . "\n\n";

$dbConfig = $config->getByPrefix('database.');
echo "All 'database.*' config:\n";
foreach ($dbConfig as $key => $value) {
    echo "  {$key} = {$value}\n";
}
echo "\n";

// ============================================================================
// 9. Menu Navigation Search
// ============================================================================

echo "9. Menu Navigation Search\n";
echo str_repeat("-", 50) . "\n";

$menu = [
    ['label' => 'Home', 'url' => '/', 'icon' => 'home'],
    ['label' => 'About', 'url' => '/about', 'icon' => 'info'],
    ['label' => 'Products', 'url' => '/products', 'icon' => 'shopping'],
    ['label' => 'Contact', 'url' => '/contact', 'icon' => 'mail'],
];

function findMenuByLabel(array $menu, string $label): ?array
{
    foreach ($menu as $item) {
        if (strcasecmp($item['label'], $label) === 0) {
            return $item;
        }
    }
    return null;
}

function findMenuByUrl(array $menu, string $url): ?array
{
    foreach ($menu as $item) {
        if ($item['url'] === $url) {
            return $item;
        }
    }
    return null;
}

$item = findMenuByLabel($menu, 'Products');
echo "Menu item 'Products': " . ($item ? $item['url'] : 'Not found') . "\n";

$item = findMenuByUrl($menu, '/contact');
echo "Menu for '/contact': " . ($item ? $item['label'] : 'Not found') . "\n";

// ============================================================================
// Summary
// ============================================================================

echo "\n" . str_repeat("=", 50) . "\n";
echo "Summary:\n";
echo "- Grep: Search text in lines\n";
echo "- Autocomplete: Prefix matching\n";
echo "- Validation: Unique/allowed values\n";
echo "- In-memory database queries\n";
echo "- Inventory management\n";
echo "- Tag/category filtering\n";
echo "- Permission checks\n";
echo "- Configuration lookup\n";
echo "- Linear search powers many real-world features!\n";
echo str_repeat("=", 50) . "\n";


