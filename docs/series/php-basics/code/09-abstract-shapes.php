<?php

// An abstract class cannot be instantiated. It's a blueprint.
abstract class Shape
{
    protected string $color;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    // An abstract method has no body. It defines a "contract".
    // Any class extending Shape MUST implement this method.
    abstract public function getArea(): float;
}

class Circle extends Shape
{
    private float $radius;

    public function __construct(string $color, float $radius)
    {
        parent::__construct($color); // Call the parent's constructor
        $this->radius = $radius;
    }

    // Provide the required implementation for the abstract method.
    public function getArea(): float
    {
        return pi() * $this->radius * $this->radius;
    }
}

class Square extends Shape
{
    private float $side;

    public function __construct(string $color, float $side)
    {
        parent::__construct($color);
        $this->side = $side;
    }

    public function getArea(): float
    {
        return $this->side * $this->side;
    }
}

// $shape = new Shape('red'); // FATAL ERROR! Cannot instantiate abstract class.

$circle = new Circle('red', 5);
echo "The red circle has an area of: " . $circle->getArea() . PHP_EOL;

$square = new Square('blue', 4);
echo "The blue square has an area of: " . $square->getArea() . PHP_EOL;
