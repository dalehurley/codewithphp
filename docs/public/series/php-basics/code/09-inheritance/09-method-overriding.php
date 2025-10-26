<?php

class Employee
{
    protected string $name;
    protected string $title;
    protected float $salary;

    public function __construct(string $name, string $title, float $salary)
    {
        $this->name = $name;
        $this->title = $title;
        $this->salary = $salary;
    }

    public function getInfo(): string
    {
        return "$this->name is a $this->title.";
    }

    public function getAnnualBonus(): float
    {
        return $this->salary * 0.05; // 5% bonus
    }
}

class Manager extends Employee
{
    private int $teamSize;

    public function __construct(string $name, string $title, float $salary, int $teamSize)
    {
        // Call the parent constructor using parent::
        parent::__construct($name, $title, $salary);
        $this->teamSize = $teamSize;
    }

    // Override the parent's getInfo() method
    public function getInfo(): string
    {
        // Call the parent's version and add to it
        return parent::getInfo() . " (Managing {$this->teamSize} people)";
    }

    // Override the bonus calculation for managers
    public function getAnnualBonus(): float
    {
        // Managers get 10% plus $1000 per team member
        return ($this->salary * 0.10) + ($this->teamSize * 1000);
    }
}

$employee = new Employee('Alice', 'Developer', 80000);
echo $employee->getInfo() . PHP_EOL;
echo "Annual bonus: $" . number_format($employee->getAnnualBonus(), 2) . PHP_EOL;
echo PHP_EOL;

$manager = new Manager('Bob', 'Engineering Manager', 120000, 5);
echo $manager->getInfo() . PHP_EOL;
echo "Annual bonus: $" . number_format($manager->getAnnualBonus(), 2) . PHP_EOL;
