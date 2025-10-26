<?php

declare(strict_types=1);

/**
 * Exercise 1: Create a Car Class
 * 
 * Goal: Model a real-world object with proper encapsulation.
 * 
 * Requirements:
 * - Car class with private properties for make, model, and year
 * - Use constructor property promotion with type declarations
 * - Add displayInfo() method that returns a descriptive string
 * - Add getAge() method that calculates car's age
 * - Instantiate two different Car objects
 */

class Car
{
    public function __construct(
        private string $make,
        private string $model,
        private int $year
    ) {}

    public function displayInfo(): string
    {
        return "This car is a {$this->year} {$this->make} {$this->model}.";
    }

    public function getAge(): int
    {
        return date('Y') - $this->year;
    }
}

// Test the Car class
echo "=== Car Class Demo ===" . PHP_EOL . PHP_EOL;

$car1 = new Car('Ford', 'Mustang', 2023);
echo $car1->displayInfo() . PHP_EOL;
echo "Car age: " . $car1->getAge() . " years old" . PHP_EOL;
echo PHP_EOL;

$car2 = new Car('Toyota', 'Camry', 2010);
echo $car2->displayInfo() . PHP_EOL;
echo "Car age: " . $car2->getAge() . " years old" . PHP_EOL;
