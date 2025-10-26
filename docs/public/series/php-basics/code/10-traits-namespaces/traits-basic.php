<?php

declare(strict_types=1);

/**
 * Traits - Code Reusability
 * 
 * Traits allow you to reuse code across multiple classes
 * without using inheritance. Perfect for horizontal reuse.
 */

echo "=== Traits Basics ===" . PHP_EOL . PHP_EOL;

// Example 1: Basic trait
echo "1. Basic Trait Usage:" . PHP_EOL;

trait Timestampable
{
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = date('Y-m-d H:i:s');
        }
    }

    public function setUpdatedAt(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}

class Post
{
    use Timestampable;

    public function __construct(
        public string $title,
        public string $content
    ) {
        $this->setCreatedAt();
    }
}

$post = new Post("First Post", "This is content");
echo "Post created at: {$post->getCreatedAt()}" . PHP_EOL;
sleep(1);
$post->setUpdatedAt();
echo "Post updated at: {$post->getUpdatedAt()}" . PHP_EOL;
echo PHP_EOL;

// Example 2: Multiple traits
echo "2. Using Multiple Traits:" . PHP_EOL;

trait Loggable
{
    protected array $logs = [];

    public function log(string $message): void
    {
        $this->logs[] = date('H:i:s') . " - " . $message;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}

trait Cacheable
{
    private array $cache = [];

    protected function getCached(string $key): mixed
    {
        return $this->cache[$key] ?? null;
    }

    protected function setCache(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }
}

class Article
{
    use Timestampable, Loggable, Cacheable;

    public function __construct(public string $title)
    {
        $this->setCreatedAt();
        $this->log("Article created");
    }
}

$article = new Article("PHP Traits");
$article->log("Processing article");
$article->log("Article published");

echo "Article: {$article->title}" . PHP_EOL;
echo "Logs:" . PHP_EOL;
foreach ($article->getLogs() as $log) {
    echo "  - $log" . PHP_EOL;
}
echo PHP_EOL;

// Example 3: Trait with abstract methods
echo "3. Trait with Abstract Methods:" . PHP_EOL;

trait Validatable
{
    abstract public function getRules(): array;

    public function validate(): array
    {
        $errors = [];
        $rules = $this->getRules();

        foreach ($rules as $field => $rule) {
            if (!$this->checkRule($field, $rule)) {
                $errors[] = "Validation failed for $field";
            }
        }

        return $errors;
    }

    private function checkRule(string $field, string $rule): bool
    {
        // Simplified validation
        return !empty($this->$field);
    }
}

class User
{
    use Validatable;

    public function __construct(
        public string $username = '',
        public string $email = ''
    ) {}

    public function getRules(): array
    {
        return [
            'username' => 'required',
            'email' => 'required'
        ];
    }
}

$user1 = new User('john', 'john@example.com');
$errors1 = $user1->validate();
echo "User 1 errors: " . (empty($errors1) ? "None" : implode(', ', $errors1)) . PHP_EOL;

$user2 = new User('', '');
$errors2 = $user2->validate();
echo "User 2 errors: " . implode(', ', $errors2) . PHP_EOL;
echo PHP_EOL;

// Example 4: Trait conflict resolution
echo "4. Resolving Trait Conflicts:" . PHP_EOL;

trait TraitA
{
    public function greet(): string
    {
        return "Hello from Trait A";
    }
}

trait TraitB
{
    public function greet(): string
    {
        return "Hello from Trait B";
    }
}

class MyClass
{
    use TraitA, TraitB {
        TraitA::greet insteadof TraitB;  // Use TraitA's version
        TraitB::greet as greetB;          // Alias TraitB's version
    }
}

$obj = new MyClass();
echo $obj->greet() . PHP_EOL;      // TraitA's version
echo $obj->greetB() . PHP_EOL;     // TraitB's version (aliased)
echo PHP_EOL;

// Example 5: Practical example - Repository pattern
echo "5. Practical Example - CRUD Trait:" . PHP_EOL;

trait CrudOperations
{
    protected array $items = [];
    protected int $nextId = 1;

    public function create(array $data): int
    {
        $id = $this->nextId++;
        $this->items[$id] = $data;
        return $id;
    }

    public function read(int $id): ?array
    {
        return $this->items[$id] ?? null;
    }

    public function update(int $id, array $data): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }
        $this->items[$id] = array_merge($this->items[$id], $data);
        return true;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->items[$id])) {
            return false;
        }
        unset($this->items[$id]);
        return true;
    }

    public function all(): array
    {
        return $this->items;
    }
}

class ProductRepository
{
    use CrudOperations;
}

$repo = new ProductRepository();
$id1 = $repo->create(['name' => 'Laptop', 'price' => 999]);
$id2 = $repo->create(['name' => 'Mouse', 'price' => 25]);

echo "Created products:" . PHP_EOL;
foreach ($repo->all() as $id => $product) {
    echo "  [$id] {$product['name']} - \${$product['price']}" . PHP_EOL;
}

$repo->update($id1, ['price' => 899]);
echo "Updated product 1: \${$repo->read($id1)['price']}" . PHP_EOL;

$repo->delete($id2);
echo "Remaining products: " . count($repo->all()) . PHP_EOL;
