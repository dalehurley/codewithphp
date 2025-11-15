<?php
declare(strict_types=1);

/**
 * Traits - PHP's Mixin Alternative
 * Traits allow code reuse without inheritance
 */

echo "=== Basic Trait Usage ===" . PHP_EOL . PHP_EOL;

trait Timestampable {
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function touch(): void {
        $now = new \DateTimeImmutable();
        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable {
        return $this->updatedAt;
    }
}

trait Sluggable {
    private string $slug = '';

    public function generateSlug(string $text): void {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $this->slug = trim($slug, '-');
    }

    public function getSlug(): string {
        return $this->slug;
    }
}

// Using multiple traits
class BlogPost {
    use Timestampable, Sluggable;

    public function __construct(
        private string $title,
        private string $content
    ) {
        $this->touch();
        $this->generateSlug($title);
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function update(string $content): void {
        $this->content = $content;
        $this->touch();
    }
}

$post = new BlogPost("Hello World", "This is my first post");
echo "Title: {$post->getTitle()}" . PHP_EOL;
echo "Slug: {$post->getSlug()}" . PHP_EOL;
echo "Created: {$post->getCreatedAt()->format('Y-m-d H:i:s')}" . PHP_EOL;

sleep(1);
$post->update("Updated content");
echo "Updated: {$post->getUpdatedAt()->format('Y-m-d H:i:s')}" . PHP_EOL;

// Trait conflict resolution
echo PHP_EOL . "=== Trait Conflict Resolution ===" . PHP_EOL . PHP_EOL;

trait LoggerA {
    public function log(string $message): void {
        echo "[LoggerA] {$message}" . PHP_EOL;
    }
}

trait LoggerB {
    public function log(string $message): void {
        echo "[LoggerB] {$message}" . PHP_EOL;
    }
}

class Service {
    use LoggerA, LoggerB {
        // Resolve conflict: use LoggerA's log method
        LoggerA::log insteadof LoggerB;
        // Create alias for LoggerB's log method
        LoggerB::log as logB;
    }
}

$service = new Service();
$service->log("Using LoggerA");  // Uses LoggerA
$service->logB("Using LoggerB"); // Uses LoggerB via alias

// Changing trait method visibility
echo PHP_EOL . "=== Changing Trait Method Visibility ===" . PHP_EOL . PHP_EOL;

trait Helper {
    public function doSomething(): void {
        echo "Doing something" . PHP_EOL;
    }
}

class MyClass {
    use Helper {
        doSomething as private;
    }

    public function execute(): void {
        $this->doSomething(); // Private now
    }
}

$obj = new MyClass();
$obj->execute(); // Works
// $obj->doSomething(); // Error: Cannot access private method

// Practical example: Serialization trait
echo PHP_EOL . "=== Practical: JSON Serializable ===" . PHP_EOL . PHP_EOL;

trait JsonSerializable {
    public function toJson(): string {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function toArray(): array {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $data[$name] = $this->$name;
        }

        return $data;
    }
}

class User {
    use JsonSerializable;

    public function __construct(
        public int $id,
        public string $name,
        public string $email
    ) {}
}

$user = new User(1, "Alice", "alice@example.com");
echo $user->toJson() . PHP_EOL;

// Practical example: Observable pattern
echo PHP_EOL . "=== Practical: Observable Pattern ===" . PHP_EOL . PHP_EOL;

trait Observable {
    /** @var array<callable> */
    private array $observers = [];

    public function addObserver(callable $observer): void {
        $this->observers[] = $observer;
    }

    protected function notify(string $event, mixed $data = null): void {
        foreach ($this->observers as $observer) {
            $observer($event, $data);
        }
    }
}

class Account {
    use Observable;

    public function __construct(
        private float $balance = 0
    ) {}

    public function deposit(float $amount): void {
        $this->balance += $amount;
        $this->notify('deposit', ['amount' => $amount, 'balance' => $this->balance]);
    }

    public function withdraw(float $amount): void {
        if ($amount > $this->balance) {
            $this->notify('insufficient_funds', ['requested' => $amount, 'balance' => $this->balance]);
            return;
        }
        $this->balance -= $amount;
        $this->notify('withdraw', ['amount' => $amount, 'balance' => $this->balance]);
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

$account = new Account(100);

$account->addObserver(function(string $event, mixed $data) {
    echo "Event: {$event}, Data: " . json_encode($data) . PHP_EOL;
});

$account->deposit(50);
$account->withdraw(30);
$account->withdraw(200); // Insufficient funds

// Trait composition
echo PHP_EOL . "=== Trait Composition ===" . PHP_EOL . PHP_EOL;

trait Validatable {
    /** @var array<string> */
    private array $errors = [];

    protected function addError(string $field, string $message): void {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function isValid(): bool {
        return empty($this->errors);
    }
}

trait Fillable {
    public function fill(array $data): void {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}

class FormModel {
    use Validatable, Fillable;

    public string $email = '';
    public int $age = 0;

    public function validate(): void {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Invalid email format');
        }

        if ($this->age < 18) {
            $this->addError('age', 'Must be 18 or older');
        }
    }
}

$model = new FormModel();
$model->fill(['email' => 'invalid-email', 'age' => 16]);
$model->validate();

if (!$model->isValid()) {
    echo "Validation errors:" . PHP_EOL;
    foreach ($model->getErrors() as $field => $error) {
        echo "  {$field}: {$error}" . PHP_EOL;
    }
}
