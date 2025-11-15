<?php

declare(strict_types=1);

class Person
{
    private string $name;
    private int $age;

    // Constructor
    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    // Getter methods
    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    // Method
    public function introduce(): string
    {
        return "Hi, I'm {$this->name} and I'm {$this->age} years old.";
    }
}

// Usage
$person = new Person("Alice", 30);
echo $person->introduce() . "\n";
echo "Name: " . $person->getName() . "\n";
