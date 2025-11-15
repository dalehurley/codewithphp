<?php

declare(strict_types=1);

abstract class Employee
{
    private static int $nextId = 1;
    protected int $id;

    public function __construct(
        protected string $name,
        protected string $email,
        protected float $baseSalary
    ) {
        $this->id = self::$nextId++;
    }

    abstract public function calculateSalary(): float;
    abstract public function getRole(): string;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getBaseSalary(): float
    {
        return $this->baseSalary;
    }

    public function getDetails(): string
    {
        return sprintf(
            "ID: %d | Name: %s | Role: %s | Salary: $%.2f",
            $this->id,
            $this->name,
            $this->getRole(),
            $this->calculateSalary()
        );
    }
}

class FullTimeEmployee extends Employee
{
    public function __construct(
        string $name,
        string $email,
        float $baseSalary,
        private float $bonus = 0
    ) {
        parent::__construct($name, $email, $baseSalary);
    }

    public function calculateSalary(): float
    {
        return $this->baseSalary + $this->bonus;
    }

    public function getRole(): string
    {
        return "Full-Time Employee";
    }

    public function setBonus(float $bonus): void
    {
        $this->bonus = $bonus;
    }
}

class ContractEmployee extends Employee
{
    public function __construct(
        string $name,
        string $email,
        private float $hourlyRate,
        private int $hoursWorked
    ) {
        parent::__construct($name, $email, 0);
    }

    public function calculateSalary(): float
    {
        return $this->hourlyRate * $this->hoursWorked;
    }

    public function getRole(): string
    {
        return "Contract Employee";
    }

    public function addHours(int $hours): void
    {
        $this->hoursWorked += $hours;
    }
}

class Manager extends FullTimeEmployee
{
    public function __construct(
        string $name,
        string $email,
        float $baseSalary,
        float $bonus,
        private int $teamSize
    ) {
        parent::__construct($name, $email, $baseSalary, $bonus);
    }

    public function getRole(): string
    {
        return "Manager (Team of {$this->teamSize})";
    }

    public function calculateSalary(): float
    {
        $teamBonus = $this->teamSize * 500;
        return parent::calculateSalary() + $teamBonus;
    }
}

class Company
{
    /** @var Employee[] */
    private array $employees = [];

    public function hire(Employee $employee): void
    {
        $this->employees[] = $employee;
        echo "Hired: {$employee->getName()} as {$employee->getRole()}\n";
    }

    public function calculateTotalPayroll(): float
    {
        $total = 0;
        foreach ($this->employees as $employee) {
            $total += $employee->calculateSalary();
        }
        return $total;
    }

    public function listEmployees(): void
    {
        echo "\n=== Employee List ===\n";
        foreach ($this->employees as $employee) {
            echo $employee->getDetails() . "\n";
        }
        echo "\nTotal Payroll: $" . number_format($this->calculateTotalPayroll(), 2) . "\n";
    }

    public function getEmployeesByType(string $className): array
    {
        return array_filter(
            $this->employees,
            fn($e) => $e instanceof $className
        );
    }
}

// Usage
$company = new Company();

$company->hire(new FullTimeEmployee("Alice Johnson", "alice@company.com", 75000, 5000));
$company->hire(new FullTimeEmployee("Bob Smith", "bob@company.com", 65000, 3000));
$company->hire(new ContractEmployee("Charlie Brown", "charlie@contractor.com", 50, 160));
$company->hire(new Manager("Diana Prince", "diana@company.com", 95000, 10000, 5));

$company->listEmployees();

$managers = $company->getEmployeesByType(Manager::class);
echo "\nManagers: " . count($managers) . "\n";
