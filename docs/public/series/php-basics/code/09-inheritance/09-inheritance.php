<?php

// Parent class
class Employee
{
    protected string $name;
    protected string $title;

    public function __construct(string $name, string $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getInfo(): string
    {
        return "$this->name is a $this->title.";
    }
}

// Child class inherits from Employee
class Manager extends Employee
{
    // This class inherits the properties and methods of Employee
    // and adds its own.
    public function approveExpense(): string
    {
        return "$this->name approved the expense.";
    }
}

$employee = new Employee('Alice', 'Web Developer');
echo $employee->getInfo() . PHP_EOL;

$manager = new Manager('Bob', 'Engineering Manager');
echo $manager->getInfo() . PHP_EOL; // Can call parent's method
echo $manager->approveExpense() . PHP_EOL; // Can call its own method
