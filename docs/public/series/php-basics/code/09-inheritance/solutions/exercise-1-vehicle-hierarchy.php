<?php

declare(strict_types=1);

/**
 * Exercise 1: Vehicle Hierarchy
 * 
 * Create a vehicle class hierarchy:
 * - Vehicle (abstract base class)
 * - Car (extends Vehicle)
 * - Motorcycle (extends Vehicle)
 * - ElectricCar (extends Car)
 */

abstract class Vehicle
{
    public function __construct(
        protected string $brand,
        protected string $model,
        protected int $year
    ) {}

    abstract public function start(): string;
    abstract public function stop(): string;
    abstract public function getFuelType(): string;

    public function getInfo(): string
    {
        return "{$this->year} {$this->brand} {$this->model}";
    }

    public function honk(): string
    {
        return "Beep beep!";
    }
}

class Car extends Vehicle
{
    public function __construct(
        string $brand,
        string $model,
        int $year,
        private int $doors = 4
    ) {
        parent::__construct($brand, $model, $year);
    }

    public function start(): string
    {
        return "Starting car engine...";
    }

    public function stop(): string
    {
        return "Stopping car engine...";
    }

    public function getFuelType(): string
    {
        return "Gasoline";
    }

    public function getDoors(): int
    {
        return $this->doors;
    }
}

class Motorcycle extends Vehicle
{
    public function __construct(
        string $brand,
        string $model,
        int $year,
        private bool $hasSidecar = false
    ) {
        parent::__construct($brand, $model, $year);
    }

    public function start(): string
    {
        return "Kick-starting motorcycle...";
    }

    public function stop(): string
    {
        return "Turning off motorcycle...";
    }

    public function getFuelType(): string
    {
        return "Gasoline";
    }

    public function honk(): string
    {
        return "Vroom vroom!";
    }

    public function hasSidecar(): bool
    {
        return $this->hasSidecar;
    }
}

class ElectricCar extends Car
{
    public function __construct(
        string $brand,
        string $model,
        int $year,
        int $doors,
        private int $batteryCapacity
    ) {
        parent::__construct($brand, $model, $year, $doors);
    }

    public function start(): string
    {
        return "Powering up electric motor...";
    }

    public function stop(): string
    {
        return "Shutting down electric motor...";
    }

    public function getFuelType(): string
    {
        return "Electric";
    }

    public function getBatteryCapacity(): int
    {
        return $this->batteryCapacity;
    }

    public function charge(): string
    {
        return "Charging battery to {$this->batteryCapacity}kWh...";
    }
}

// Test the hierarchy
echo "=== Vehicle Hierarchy Demo ===" . PHP_EOL . PHP_EOL;

$car = new Car("Toyota", "Camry", 2024, 4);
echo $car->getInfo() . PHP_EOL;
echo $car->start() . PHP_EOL;
echo "Fuel type: " . $car->getFuelType() . PHP_EOL;
echo "Doors: " . $car->getDoors() . PHP_EOL;
echo $car->honk() . PHP_EOL;
echo $car->stop() . PHP_EOL;
echo PHP_EOL;

$motorcycle = new Motorcycle("Harley-Davidson", "Street 750", 2023);
echo $motorcycle->getInfo() . PHP_EOL;
echo $motorcycle->start() . PHP_EOL;
echo "Fuel type: " . $motorcycle->getFuelType() . PHP_EOL;
echo "Has sidecar: " . ($motorcycle->hasSidecar() ? "Yes" : "No") . PHP_EOL;
echo $motorcycle->honk() . PHP_EOL;
echo $motorcycle->stop() . PHP_EOL;
echo PHP_EOL;

$electricCar = new ElectricCar("Tesla", "Model 3", 2024, 4, 75);
echo $electricCar->getInfo() . PHP_EOL;
echo $electricCar->start() . PHP_EOL;
echo "Fuel type: " . $electricCar->getFuelType() . PHP_EOL;
echo "Battery: " . $electricCar->getBatteryCapacity() . "kWh" . PHP_EOL;
echo $electricCar->charge() . PHP_EOL;
echo $electricCar->honk() . PHP_EOL;
echo $electricCar->stop() . PHP_EOL;
echo PHP_EOL;

// Polymorphism demo
echo "=== Polymorphism Demo ===" . PHP_EOL;
$vehicles = [$car, $motorcycle, $electricCar];

foreach ($vehicles as $vehicle) {
    echo "{$vehicle->getInfo()} runs on {$vehicle->getFuelType()}" . PHP_EOL;
}
