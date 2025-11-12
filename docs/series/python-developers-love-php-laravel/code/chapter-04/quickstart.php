<?php

declare(strict_types=1);

/**
 * Quick Start: PHP Syntax Differences from Python
 * 
 * This file demonstrates the key syntax differences shown in the Quick Start section
 * of Chapter 04. Compare this with the Python equivalent to see how PHP syntax differs.
 */

// Python equivalent:
// from typing import Optional
//
// def greet(name: str, age: Optional[int] = None) -> str:
//     if age is None:
//         return f"Hello, {name}!"
//     return f"Hello, {name}! You are {age} years old."
//
// class User:
//     def __init__(self, name: str, email: str):
//         self.name = name
//         self.email = email

function greet(string $name, ?int $age = null): string
{
    if ($age === null) {
        return "Hello, {$name}!";
    }
    return "Hello, {$name}! You are {$age} years old.";
}

class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

// Example usage
echo greet("Alice", 30) . "\n";
echo greet("Bob") . "\n";

$user = new User("Alice", "alice@example.com");
echo "User: {$user->name}, Email: {$user->email}\n";

