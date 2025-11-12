<?php

declare(strict_types=1);

// PHP 8.0+ attributes (similar to Python decorators)
// Attributes provide metadata that can be read via reflection

// Define an attribute (like a Python decorator class)
#[\Attribute]
class Route
{
    public function __construct(
        public string $path,
        public string $method = 'GET'
    ) {}
}

#[\Attribute]
class Validate
{
    public function __construct(
        public string $rule
    ) {}
}

// Use attributes on classes (like Python class decorators)
#[Route('/api/users', 'GET')]
class UserController
{
    // Use attributes on methods (like Python function decorators)
    #[Route('/api/users/{id}', 'GET')]
    #[Validate('required|integer')]
    public function getUser(int $id): array
    {
        return ['id' => $id, 'name' => 'John Doe'];
    }

    #[Route('/api/users', 'POST')]
    #[Validate('required|string|min:3')]
    public function createUser(string $name): array
    {
        return ['id' => 1, 'name' => $name];
    }
}

// Attributes can be read via reflection
$reflection = new ReflectionClass(UserController::class);
$attributes = $reflection->getAttributes(Route::class);

foreach ($attributes as $attribute) {
    $route = $attribute->newInstance();
    echo "Class Route: {$route->method} {$route->path}\n";
}

// Get method attributes
$method = $reflection->getMethod('getUser');
$methodAttributes = $method->getAttributes();

foreach ($methodAttributes as $attr) {
    $instance = $attr->newInstance();
    if ($instance instanceof Route) {
        echo "Method Route: {$instance->method} {$instance->path}\n";
    } elseif ($instance instanceof Validate) {
        echo "Validation Rule: {$instance->rule}\n";
    }
}

// Attributes are used extensively in Laravel for:
// - Routing: #[Route('/path')]
// - Validation: #[Validate('required|email')]
// - Dependency Injection: #[Inject(Service::class)]
// - Middleware: #[Middleware('auth')]

