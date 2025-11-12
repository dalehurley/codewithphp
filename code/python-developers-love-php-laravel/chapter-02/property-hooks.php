<?php

declare(strict_types=1);

// PHP 8.4 property hooks
// Similar to Python's @property decorator, but more powerful

class User
{
    private string $email;

    // Property hook: custom getter
    public function getEmail(): string
    {
        return $this->email;
    }

    // Property hook: custom setter with validation
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }
        $this->email = $email;
    }

    // Property hook: accessor (combines getter and setter)
    private string $_name = '';

    public string $name {
        get => strtoupper($this->_name);
        set => $this->_name = trim($value);
    }
}

$user = new User();

// Using property hooks
$user->setEmail('john@example.com');
echo "Email: {$user->getEmail()}\n";  // Output: Email: john@example.com

// Using accessor property hook
$user->name = '  john doe  ';
echo "Name: {$user->name}\n";  // Output: Name: JOHN DOE (trimmed and uppercased)

// Property hooks with computed values
class Rectangle
{
    public function __construct(
        private float $width,
        private float $height
    ) {}

    // Computed property using property hook
    public float $area {
        get => $this->width * $this->height;
    }

    public float $perimeter {
        get => 2 * ($this->width + $this->height);
    }
}

$rect = new Rectangle(10.0, 5.0);
echo "Area: {$rect->area}\n";  // Output: Area: 50
echo "Perimeter: {$rect->perimeter}\n";  // Output: Perimeter: 30

