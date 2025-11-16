---
title: "11: Dependency Injection"
description: "DI containers, constructor injection, service providers"
series: "php-for-java-developers"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/10-building-rest-apis"
---

![Dependency Injection](/images/php-for-java-developers/chapter-11-dependency-injection-hero-full.webp)

# Chapter 11: Dependency Injection

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Dependency Injection (DI) is a fundamental design pattern that promotes loose coupling, testability, and maintainability. If you're coming from Java, you're likely familiar with Spring's DI container. PHP has similar capabilities through DI containers that manage object dependencies automatically. This chapter explores DI principles, container implementation, and best practices for building maintainable PHP applications.

**What You'll Learn:**
- Dependency Injection principles and benefits
- Constructor, setter, and method injection patterns
- Building a DI container from scratch
- Auto-wiring and reflection
- Factory pattern integration with DI containers
- Service providers and binding
- Service decorators for cross-cutting concerns
- Contextual binding (similar to Spring's @Qualifier)
- Service tagging for grouping services
- Interface-to-implementation binding
- PHP 8 attributes for declarative DI
- Singleton and transient service lifetimes
- Circular dependency detection and resolution
- Performance optimization strategies
- Testing DI containers
- Conditional bindings based on environment/features
- Service aliases and lazy loading patterns
- Integration with real-world applications
- PSR-11 Container Interface standard

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:
- Object-oriented programming (Chapters 3-5)
- Interfaces and type hints
- Namespaces and autoloading (Chapter 6)
- Reflection API basics

## What You'll Build

By the end of this chapter, you will have created:

- A fully functional DI container with automatic dependency resolution
- Service providers for organizing application services
- Interface-to-implementation bindings for flexible architecture
- A complete application bootstrap using DI patterns
- Circular dependency detection and resolution mechanisms
- PSR-11 compliant container implementation

You'll understand how to apply dependency injection throughout your PHP applications, making them more testable, maintainable, and loosely coupled.

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Explain DI principles** and their advantages over manual dependency management
2. **Implement constructor injection** to declare dependencies explicitly
3. **Build a DI container** with automatic dependency resolution
4. **Use reflection** to inspect class constructors and auto-wire dependencies
5. **Integrate factory patterns** with DI containers for complex object creation
6. **Create service decorators** to add cross-cutting concerns without modifying services
7. **Use contextual binding** to provide different implementations based on context
8. **Tag services** for grouping and retrieving related services
9. **Configure service providers** to register application services
10. **Bind interfaces to implementations** for flexible architecture
11. **Use PHP 8 attributes** for declarative dependency injection
12. **Manage service lifetimes** (singleton vs transient vs scoped)
13. **Detect circular dependencies** and handle them appropriately
14. **Optimize container performance** with reflection caching and lazy loading
15. **Test DI containers** to ensure bindings work correctly
16. **Apply conditional bindings** based on environment or feature flags
17. **Use service aliases** and lazy loading patterns effectively
18. **Apply DI patterns** to real-world application architecture

---

## Section 1: Understanding Dependency Injection

Dependency Injection is a design pattern where objects receive their dependencies from external sources rather than creating them internally.

### The Problem: Tight Coupling

Without DI, classes create their own dependencies, leading to tight coupling:

```php
# filename: UserService.php (tightly coupled)
<?php

declare(strict_types=1);

namespace App\Services;

// ❌ Bad: Tight coupling - hard to test and modify
class UserService
{
    private UserRepository $repository;
    private EmailService $emailService;
    private Logger $logger;

    public function __construct()
    {
        // Creating dependencies internally = tight coupling
        $this->repository = new UserRepository(
            new PDO('mysql:host=localhost;dbname=app', 'user', 'pass')
        );
        $this->emailService = new EmailService(
            new SMTPMailer('smtp.example.com', 587)
        );
        $this->logger = new FileLogger('/var/log/app.log');
    }

    public function registerUser(array $data): User
    {
        $user = $this->repository->create($data);
        $this->emailService->sendWelcomeEmail($user);
        $this->logger->info("User registered: {$user->email}");
        return $user;
    }
}

// Testing is difficult - can't mock dependencies
$service = new UserService(); // Always uses real database, email, logger
```

**Problems:**
- Hard to test (can't mock dependencies)
- Violates Single Responsibility Principle
- Changes to dependencies require changing UserService
- Can't reuse with different implementations
- Difficult to configure

### The Solution: Dependency Injection

Inject dependencies through the constructor:

::: code-group

```php [PHP]
# filename: UserService.php (with DI)
<?php

declare(strict_types=1);

namespace App\Services;

// ✅ Good: Dependencies injected via constructor
class UserService
{
    public function __construct(
        private UserRepository $repository,
        private EmailService $emailService,
        private LoggerInterface $logger
    ) {}

    public function registerUser(array $data): User
    {
        $user = $this->repository->create($data);
        $this->emailService->sendWelcomeEmail($user);
        $this->logger->info("User registered: {$user->email}");
        return $user;
    }
}

// Dependencies created externally and injected
$pdo = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$repository = new UserRepository($pdo);
$mailer = new SMTPMailer('smtp.example.com', 587);
$emailService = new EmailService($mailer);
$logger = new FileLogger('/var/log/app.log');

$service = new UserService($repository, $emailService, $logger);

// Easy to test - inject mocks
$service = new UserService(
    new InMemoryUserRepository(),
    new FakeEmailService(),
    new NullLogger()
);
```

```java [Java]
// Java Spring equivalent
@Service
public class UserService {
    private final UserRepository repository;
    private final EmailService emailService;
    private final Logger logger;

    // Constructor injection (Spring @Autowired is optional)
    @Autowired
    public UserService(
        UserRepository repository,
        EmailService emailService,
        Logger logger
    ) {
        this.repository = repository;
        this.emailService = emailService;
        this.logger = logger;
    }

    public User registerUser(Map<String, Object> data) {
        User user = repository.create(data);
        emailService.sendWelcomeEmail(user);
        logger.info("User registered: " + user.getEmail());
        return user;
    }
}
```

:::

### Benefits of Dependency Injection

1. **Testability**: Easy to inject mocks and stubs
2. **Loose Coupling**: Depend on abstractions, not concrete classes
3. **Flexibility**: Swap implementations without changing code
4. **Single Responsibility**: Classes focus on their core purpose
5. **Reusability**: Services can be composed in different ways
6. **Configuration**: Centralize dependency configuration

---

## Section 2: Injection Types

There are three main types of dependency injection.

### Constructor Injection (Recommended)

Dependencies are provided through the constructor:

```php
# filename: PostController.php
<?php

declare(strict_types=1);

namespace App\Controllers;

// ✅ Constructor injection - dependencies are explicit and immutable
class PostController
{
    public function __construct(
        private PostRepository $posts,
        private Validator $validator,
        private EventDispatcher $events
    ) {}

    public function create(Request $request): Response
    {
        // Use injected dependencies
        $data = $this->validator->validate($request->all(), [
            'title' => 'required|min:3',
            'content' => 'required',
        ]);

        $post = $this->posts->create($data);
        $this->events->dispatch(new PostCreated($post));

        return Response::created($post);
    }
}
```

**Advantages:**
- Dependencies are explicit and visible
- Object is always in valid state
- Dependencies are immutable
- Easy to identify required dependencies

### Setter Injection

Dependencies are set via setter methods:

```php
# filename: CacheableUserService.php
<?php

declare(strict_types=1);

namespace App\Services;

// Setter injection - for optional dependencies
class CacheableUserService
{
    private UserRepository $repository;
    private ?CacheInterface $cache = null;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    // Optional dependency via setter
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function findById(int $id): ?User
    {
        // Use cache if available
        if ($this->cache !== null) {
            $cached = $this->cache->get("user:{$id}");
            if ($cached !== null) {
                return $cached;
            }
        }

        $user = $this->repository->findById($id);

        if ($this->cache !== null && $user !== null) {
            $this->cache->set("user:{$id}", $user, 3600);
        }

        return $user;
    }
}

// Usage
$service = new CacheableUserService($repository);
$service->setCache($redisCache); // Optional
```

**Use Cases:**
- Optional dependencies
- Circular dependencies (rare cases)
- Changing dependencies after construction

**Disadvantages:**
- Object can be in invalid state
- Dependencies not immediately visible
- Mutable state

### Method Injection

Dependencies are passed to specific methods:

```php
# filename: ReportGenerator.php
<?php

declare(strict_types=1);

namespace App\Services;

class ReportGenerator
{
    public function __construct(
        private ReportRepository $reports
    ) {}

    // Logger injected per method call
    public function generate(
        int $reportId,
        LoggerInterface $logger
    ): string {
        $logger->info("Starting report generation: {$reportId}");

        $report = $this->reports->findById($reportId);
        $content = $this->buildReport($report);

        $logger->info("Report generated successfully");
        return $content;
    }

    private function buildReport(Report $report): string
    {
        // Generate report content
        return "Report content...";
    }
}

// Usage with different loggers per call
$generator = new ReportGenerator($repository);
$result = $generator->generate(123, new FileLogger('/var/log/reports.log'));
$result = $generator->generate(456, new SyslogLogger());
```

**Use Cases:**
- Different dependency per method call
- Runtime-determined dependencies
- Framework integrations

---

## Section 3: Building a Simple DI Container

A DI container manages object creation and dependency resolution.

### Basic Container Implementation

```php
# filename: Container.php
<?php

declare(strict_types=1);

namespace App\Container;

class Container
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /**
     * Bind a service to the container
     */
    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Bind a singleton service
     */
    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bind($abstract, function (Container $container) use ($concrete, $abstract) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete($container);
            }
            return $this->instances[$abstract];
        });
    }

    /**
     * Resolve a service from the container
     */
    public function make(string $abstract): mixed
    {
        // Check if binding exists
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }

        // Try to auto-resolve using reflection
        return $this->resolve($abstract);
    }

    /**
     * Auto-resolve class using reflection
     */
    private function resolve(string $class): object
    {
        $reflector = new \ReflectionClass($class);

        // Check if class is instantiable
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$class} is not instantiable");
        }

        // Get constructor
        $constructor = $reflector->getConstructor();

        // No constructor - create instance
        if ($constructor === null) {
            return new $class();
        }

        // Get constructor parameters
        $parameters = $constructor->getParameters();

        // Resolve dependencies
        $dependencies = $this->resolveDependencies($parameters);

        // Create instance with dependencies
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve method/constructor parameters
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            // No type hint
            if ($type === null) {
                // Check for default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception(
                        "Cannot resolve parameter \${$parameter->getName()}"
                    );
                }
                continue;
            }

            // Type is built-in (string, int, etc.)
            if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception(
                        "Cannot resolve built-in type \${$parameter->getName()}"
                    );
                }
                continue;
            }

            // Resolve class dependency
            $dependencies[] = $this->make($type->getName());
        }

        return $dependencies;
    }

    /**
     * Check if service is bound
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }
}
```

### Using the Container

```php
# filename: bootstrap.php
<?php

declare(strict_types=1);

use App\Container\Container;

$container = new Container();

// Bind database connection as singleton
$container->singleton(PDO::class, function (Container $c) {
    return new PDO(
        'mysql:host=localhost;dbname=app',
        'username',
        'password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
});

// Bind repository (new instance each time)
$container->bind(UserRepository::class, function (Container $c) {
    return new UserRepository($c->make(PDO::class));
});

// Bind interface to implementation
$container->bind(LoggerInterface::class, function (Container $c) {
    return new FileLogger('/var/log/app.log');
});

// Bind service with multiple dependencies
$container->bind(UserService::class, function (Container $c) {
    return new UserService(
        $c->make(UserRepository::class),
        $c->make(EmailService::class),
        $c->make(LoggerInterface::class)
    );
});

// Resolve services
$userService = $container->make(UserService::class);

// Auto-resolution (if all dependencies can be resolved)
$postController = $container->make(PostController::class);
```

---

## Section 4: Factory Pattern Integration

Factories are commonly used with DI containers to create objects that require complex setup or conditional logic. They provide a clean way to encapsulate object creation logic.

### Factory Pattern Basics

Factories create objects without exposing the instantiation logic:

```php
# filename: UserFactory.php
<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\User;
use App\Services\EmailService;

class UserFactory
{
    public function __construct(
        private EmailService $emailService
    ) {}

    public function create(array $data): User
    {
        $user = new User($data);
        
        // Complex initialization logic
        if ($user->isAdmin()) {
            $user->setRole('admin');
            $this->emailService->sendAdminWelcome($user);
        } else {
            $this->emailService->sendWelcome($user);
        }
        
        return $user;
    }
}
```

### Registering Factories in Container

```php
# filename: bootstrap.php
<?php

declare(strict_types=1);

use App\Container\Container;
use App\Factories\UserFactory;

$container = new Container();

// Register factory
$container->singleton(UserFactory::class, function ($c) {
    return new UserFactory($c->make(EmailService::class));
});

// Use factory
$factory = $container->make(UserFactory::class);
$user = $factory->create(['name' => 'John', 'email' => 'john@example.com']);
```

### Factory Methods in Service Providers

Factories can be registered directly in service providers:

```php
# filename: UserServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Factories\UserFactory;
use App\Services\EmailService;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register factory as singleton
        $this->container->singleton(UserFactory::class, function ($c) {
            return new UserFactory($c->make(EmailService::class));
        });
        
        // Or bind factory method directly
        $this->container->bind('user.factory', function ($c) {
            return fn(array $data) => $c->make(UserFactory::class)->create($data);
        });
    }
}
```

### Factory for Complex Object Graphs

Factories are especially useful for creating complex object graphs:

```php
# filename: OrderFactory.php
<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\Order;
use App\Services\{PaymentService, ShippingService, NotificationService};

class OrderFactory
{
    public function __construct(
        private PaymentService $payment,
        private ShippingService $shipping,
        private NotificationService $notifications
    ) {}

    public function createFromCart(array $cartData): Order
    {
        $order = new Order();
        $order->setItems($cartData['items']);
        $order->setTotal($cartData['total']);
        
        // Process payment
        $transaction = $this->payment->process($order->getTotal());
        $order->setTransactionId($transaction->getId());
        
        // Calculate shipping
        $shippingCost = $this->shipping->calculate($order);
        $order->setShippingCost($shippingCost);
        
        // Send notifications
        $this->notifications->sendOrderConfirmation($order);
        
        return $order;
    }
}
```

### Factory Interface Pattern

Define factory interfaces for flexibility:

```php
# filename: UserFactoryInterface.php
<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface UserFactoryInterface
{
    public function create(array $data): User;
    public function createAdmin(array $data): User;
    public function createGuest(array $data): User;
}

// Implementation
class UserFactory implements UserFactoryInterface
{
    // ... implementation
}

// Bind interface to implementation
$container->bind(UserFactoryInterface::class, UserFactory::class);
```

---

## Section 5: Auto-wiring with Reflection

Auto-wiring automatically resolves dependencies using PHP's Reflection API.

### Enhanced Container with Auto-wiring

```php
# filename: AutowiringContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class AutowiringContainer extends Container
{
    /**
     * Automatically resolve and call a method
     */
    public function call(callable|array $callback, array $parameters = []): mixed
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;
            $reflector = new \ReflectionMethod($class, $method);
        } else {
            $reflector = new \ReflectionFunction($callback);
        }

        $dependencies = $this->resolveDependencies(
            $reflector->getParameters(),
            $parameters
        );

        if (is_array($callback)) {
            $instance = is_object($callback[0])
                ? $callback[0]
                : $this->make($callback[0]);
            return $reflector->invokeArgs($instance, $dependencies);
        }

        return $reflector->invokeArgs($dependencies);
    }

    /**
     * Enhanced dependency resolution with user parameters
     */
    private function resolveDependencies(
        array $parameters,
        array $primitives = []
    ): array {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // User provided this parameter
            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }

            $type = $parameter->getType();

            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception(
                        "Cannot resolve parameter \${$name}"
                    );
                }
                continue;
            }

            // Resolve from container
            $dependencies[] = $this->make($type->getName());
        }

        return $dependencies;
    }
}
```

### Using Auto-wiring for Controllers

```php
# filename: Router.php
<?php

declare(strict_types=1);

namespace App\Routing;

class Router
{
    public function __construct(
        private Container $container
    ) {}

    private function executeRoute(Route $route, Request $request): Response
    {
        $handler = $route->getHandler();

        if (is_array($handler)) {
            // Auto-wire controller method
            return $this->container->call(
                $handler,
                ['request' => $request]
            );
        }

        return $handler($request);
    }
}

// Controller with auto-wired dependencies
class UserController
{
    // Dependencies auto-wired by container
    public function __construct(
        private UserRepository $users,
        private LoggerInterface $logger
    ) {}

    // Request injected by router, other dependencies auto-wired
    public function show(Request $request, int $id): Response
    {
        $this->logger->info("Fetching user: {$id}");
        $user = $this->users->findById($id);

        if ($user === null) {
            return Response::error('Not found', 404);
        }

        return Response::json($user);
    }
}
```

---

## Section 6: Service Providers

Service providers organize service registration and bootstrapping.

### Service Provider Pattern

```php
# filename: ServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Container\Container;

abstract class ServiceProvider
{
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Register bindings in the container
     */
    abstract public function register(): void;

    /**
     * Bootstrap services (after all providers registered)
     */
    public function boot(): void
    {
        // Optional - override in subclasses
    }
}
```

### Concrete Service Providers

```php
# filename: MailServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\{EmailService, SMTPMailer};
use App\Contracts\MailerInterface;

class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind mailer implementation
        $this->container->singleton(MailerInterface::class, function ($c) {
            return new SMTPMailer(
                $_ENV['MAIL_HOST'],
                (int) $_ENV['MAIL_PORT'],
                $_ENV['MAIL_USERNAME'],
                $_ENV['MAIL_PASSWORD']
            );
        });

        // Bind email service
        $this->container->singleton(EmailService::class, function ($c) {
            return new EmailService(
                $c->make(MailerInterface::class)
            );
        });
    }

    public function boot(): void
    {
        // Test mail connection on boot
        $mailer = $this->container->make(MailerInterface::class);
        // $mailer->testConnection();
    }
}

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind PDO singleton
        $this->container->singleton(PDO::class, function ($c) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME']
            );

            return new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        });
    }
}

class LogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(LoggerInterface::class, function ($c) {
            $logPath = $_ENV['LOG_PATH'] ?? '/var/log/app.log';
            return new FileLogger($logPath);
        });
    }
}

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->container->bind(UserRepository::class, function ($c) {
            return new UserRepository($c->make(PDO::class));
        });

        $this->container->bind(PostRepository::class, function ($c) {
            return new PostRepository($c->make(PDO::class));
        });
    }
}
```

### Application Class with Providers

```php
# filename: Application.php
<?php

declare(strict_types=1);

namespace App;

use App\Container\Container;
use App\Providers\ServiceProvider;

class Application
{
    private Container $container;
    private array $providers = [];
    private bool $booted = false;

    public function __construct()
    {
        $this->container = new Container();

        // Bind container instance
        $this->container->singleton(Container::class, fn() => $this->container);
        $this->container->singleton(Application::class, fn() => $this);
    }

    /**
     * Register a service provider
     */
    public function register(string|ServiceProvider $provider): void
    {
        if (is_string($provider)) {
            $provider = new $provider($this->container);
        }

        $provider->register();
        $this->providers[] = $provider;
    }

    /**
     * Boot all registered providers
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     * Get container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Make a service from container
     */
    public function make(string $abstract): mixed
    {
        return $this->container->make($abstract);
    }
}
```

### Bootstrap Application

```php
# filename: bootstrap/app.php
<?php

declare(strict_types=1);

use App\Application;
use App\Providers\{
    DatabaseServiceProvider,
    LogServiceProvider,
    MailServiceProvider,
    RepositoryServiceProvider
};

$app = new Application();

// Register providers
$app->register(DatabaseServiceProvider::class);
$app->register(LogServiceProvider::class);
$app->register(MailServiceProvider::class);
$app->register(RepositoryServiceProvider::class);

// Boot application
$app->boot();

return $app;
```

---

## Section 7: Service Decorators

Decorators wrap services to add functionality without modifying the original service. This pattern is useful for cross-cutting concerns like logging, caching, and validation.

### Decorator Pattern Basics

A decorator implements the same interface as the service it wraps:

```php
# filename: CacheableUserRepository.php
<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\{UserRepositoryInterface, CacheInterface};
use App\Models\User;

class CacheableUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private CacheInterface $cache
    ) {}

    public function findById(int $id): ?User
    {
        $key = "user:{$id}";
        
        // Check cache first
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }
        
        // Delegate to wrapped repository
        $user = $this->repository->findById($id);
        
        // Cache result
        if ($user !== null) {
            $this->cache->set($key, $user, 3600);
        }
        
        return $user;
    }

    public function create(array $data): User
    {
        $user = $this->repository->create($data);
        $this->cache->set("user:{$user->getId()}", $user, 3600);
        return $user;
    }
}
```

### Registering Decorators

```php
# filename: RepositoryServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\{UserRepository, CacheableUserRepository};
use App\Contracts\{UserRepositoryInterface, CacheInterface};

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register base repository
        $this->container->bind(UserRepository::class, function ($c) {
            return new UserRepository($c->make(PDO::class));
        });
        
        // Register decorator wrapping the base repository
        $this->container->singleton(UserRepositoryInterface::class, function ($c) {
            return new CacheableUserRepository(
                $c->make(UserRepository::class),
                $c->make(CacheInterface::class)
            );
        });
    }
}
```

### Logging Decorator

Add logging to any service:

```php
# filename: LoggingEmailService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\{EmailServiceInterface, LoggerInterface};

class LoggingEmailService implements EmailServiceInterface
{
    public function __construct(
        private EmailServiceInterface $emailService,
        private LoggerInterface $logger
    ) {}

    public function send(string $to, string $subject, string $body): bool
    {
        $this->logger->info("Sending email", [
            'to' => $to,
            'subject' => $subject,
        ]);
        
        $result = $this->emailService->send($to, $subject, $body);
        
        if ($result) {
            $this->logger->info("Email sent successfully", ['to' => $to]);
        } else {
            $this->logger->error("Failed to send email", ['to' => $to]);
        }
        
        return $result;
    }
}
```

### Chaining Decorators

Multiple decorators can be chained:

```php
# filename: DecoratorChain.php
<?php

declare(strict_types=1);

// Chain: Logging -> Caching -> Base Service
$baseService = $container->make(BaseService::class);
$cachedService = new CachingDecorator($baseService, $cache);
$loggedService = new LoggingDecorator($cachedService, $logger);

$container->singleton(ServiceInterface::class, fn() => $loggedService);
```

### Validation Decorator

Add validation to services:

```php
# filename: ValidatingUserService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\{UserServiceInterface, ValidatorInterface};
use App\Models\User;

class ValidatingUserService implements UserServiceInterface
{
    public function __construct(
        private UserServiceInterface $userService,
        private ValidatorInterface $validator
    ) {}

    public function create(array $data): User
    {
        // Validate before delegating
        $validated = $this->validator->validate($data, [
            'email' => 'required|email',
            'name' => 'required|min:3',
        ]);
        
        return $this->userService->create($validated);
    }
}
```

---

## Section 8: Contextual Binding

Contextual binding allows you to provide different implementations based on where a dependency is injected. This is similar to Spring's `@Qualifier` annotation.

### Basic Contextual Binding

Bind different implementations for different classes:

```php
# filename: ContextualContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class ContextualContainer extends Container
{
    /** @var array<string, array<string, callable>> */
    private array $contextual = [];

    /**
     * Bind implementation for specific context
     */
    public function when(string $context): ContextualBinding
    {
        return new ContextualBinding($this, $context);
    }

    public function addContextualBinding(
        string $context,
        string $abstract,
        callable $concrete
    ): void {
        if (!isset($this->contextual[$context])) {
            $this->contextual[$context] = [];
        }
        $this->contextual[$context][$abstract] = $concrete;
    }

    protected function resolve(string $class): object
    {
        // Check contextual bindings first
        $context = $this->getCurrentContext();
        if (isset($this->contextual[$context][$class])) {
            return $this->contextual[$context][$class]($this);
        }

        return parent::resolve($class);
    }

    private function getCurrentContext(): string
    {
        // Get the class that's requesting the dependency
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($backtrace as $frame) {
            if (isset($frame['class']) && $frame['class'] !== self::class) {
                return $frame['class'];
            }
        }
        return '';
    }
}

class ContextualBinding
{
    public function __construct(
        private ContextualContainer $container,
        private string $context
    ) {}

    public function needs(string $abstract): self
    {
        $this->abstract = $abstract;
        return $this;
    }

    public function give(callable|string $concrete): void
    {
        $this->container->addContextualBinding(
            $this->context,
            $this->abstract,
            is_string($concrete) ? fn($c) => $c->make($concrete) : $concrete
        );
    }
}
```

### Using Contextual Binding

```php
# filename: example-contextual.php
<?php

declare(strict_types=1);

use App\Container\ContextualContainer;

$container = new ContextualContainer();

// Different cache implementations for different services
$container->when(UserService::class)
    ->needs(CacheInterface::class)
    ->give(RedisCache::class);

$container->when(ProductService::class)
    ->needs(CacheInterface::class)
    ->give(FileCache::class);

// Different loggers for different contexts
$container->when(AdminController::class)
    ->needs(LoggerInterface::class)
    ->give(fn() => new FileLogger('/var/log/admin.log'));

$container->when(ApiController::class)
    ->needs(LoggerInterface::class)
    ->give(fn() => new SyslogLogger());
```

### Contextual Binding in Service Providers

```php
# filename: ContextualServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Controllers\{AdminController, ApiController};
use App\Services\{UserService, ProductService};
use App\Contracts\{CacheInterface, LoggerInterface};

class ContextualServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // UserService uses Redis cache
        $this->container->when(UserService::class)
            ->needs(CacheInterface::class)
            ->give(RedisCache::class);
        
        // ProductService uses file cache
        $this->container->when(ProductService::class)
            ->needs(CacheInterface::class)
            ->give(FileCache::class);
        
        // Different loggers for different controllers
        $this->container->when(AdminController::class)
            ->needs(LoggerInterface::class)
            ->give(fn($c) => new FileLogger('/var/log/admin.log'));
        
        $this->container->when(ApiController::class)
            ->needs(LoggerInterface::class)
            ->give(fn($c) => new SyslogLogger());
    }
}
```

---

## Section 9: Service Tagging

Tagging allows you to group services and retrieve them by tag. This is useful for event listeners, middleware, and plugin systems.

### Basic Tagging Implementation

```php
# filename: TaggedContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class TaggedContainer extends Container
{
    /** @var array<string, array<string>> */
    private array $tags = [];

    /**
     * Tag a service
     */
    public function tag(string $tag, array $services): void
    {
        if (!isset($this->tags[$tag])) {
            $this->tags[$tag] = [];
        }
        
        foreach ($services as $service) {
            $this->tags[$tag][] = $service;
        }
    }

    /**
     * Get all services with a tag
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }
        
        $services = [];
        foreach ($this->tags[$tag] as $serviceName) {
            $services[] = $this->make($serviceName);
        }
        
        return $services;
    }

    /**
     * Check if service has tag
     */
    public function hasTag(string $service, string $tag): bool
    {
        return isset($this->tags[$tag]) && in_array($service, $this->tags[$tag], true);
    }
}
```

### Using Tags for Event Listeners

```php
# filename: EventServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\{UserRegisteredListener, OrderCreatedListener, PaymentProcessedListener};

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register listeners
        $this->container->bind(UserRegisteredListener::class, fn($c) => new UserRegisteredListener());
        $this->container->bind(OrderCreatedListener::class, fn($c) => new OrderCreatedListener());
        $this->container->bind(PaymentProcessedListener::class, fn($c) => new PaymentProcessedListener());
        
        // Tag listeners
        $this->container->tag('event.listeners', [
            UserRegisteredListener::class,
            OrderCreatedListener::class,
            PaymentProcessedListener::class,
        ]);
    }
}

// Usage
$listeners = $container->tagged('event.listeners');
foreach ($listeners as $listener) {
    $listener->handle($event);
}
```

### Tagging Middleware

```php
# filename: MiddlewareServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Middleware\{AuthMiddleware, CorsMiddleware, RateLimitMiddleware};

class MiddlewareServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register middleware
        $this->container->bind(AuthMiddleware::class, fn($c) => new AuthMiddleware());
        $this->container->bind(CorsMiddleware::class, fn($c) => new CorsMiddleware());
        $this->container->bind(RateLimitMiddleware::class, fn($c) => new RateLimitMiddleware());
        
        // Tag middleware
        $this->container->tag('middleware', [
            AuthMiddleware::class,
            CorsMiddleware::class,
            RateLimitMiddleware::class,
        ]);
    }
}

// Apply all middleware
$middleware = $container->tagged('middleware');
foreach ($middleware as $m) {
    $request = $m->handle($request);
}
```

### Multiple Tags

Services can have multiple tags:

```php
# Register service
$container->bind(EmailNotification::class, fn($c) => new EmailNotification());

// Tag with multiple tags
$container->tag('notifications', [EmailNotification::class]);
$container->tag('channels.email', [EmailNotification::class]);
$container->tag('async', [EmailNotification::class]);

// Retrieve by any tag
$notifications = $container->tagged('notifications');
$emailChannels = $container->tagged('channels.email');
```

---

## Section 10: Interface Binding

Binding interfaces to implementations enables flexible, testable architecture.

### Define Interfaces

```php
# filename: CacheInterface.php
<?php

declare(strict_types=1);

namespace App\Contracts;

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function has(string $key): bool;
    public function clear(): bool;
}

interface MailerInterface
{
    public function send(string $to, string $subject, string $body): bool;
}

interface LoggerInterface
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
}
```

### Multiple Implementations

```php
# filename: RedisCache.php
<?php

declare(strict_types=1);

namespace App\Services;

// Redis cache implementation
class RedisCache implements CacheInterface
{
    public function __construct(
        private \Redis $redis
    ) {}

    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->redis->setex($key, $ttl, serialize($value));
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key) > 0;
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }
}

// File cache implementation
# filename: FileCache.php
class FileCache implements CacheInterface
{
    public function __construct(
        private string $cacheDir
    ) {}

    public function get(string $key): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];

        return file_put_contents(
            $this->getFilePath($key),
            serialize($data)
        ) !== false;
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        return file_exists($file) && unlink($file);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    private function getFilePath(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}

// Array cache (for testing)
# filename: ArrayCache.php
class ArrayCache implements CacheInterface
{
    private array $storage = [];

    public function get(string $key): mixed
    {
        if (!isset($this->storage[$key])) {
            return null;
        }

        if ($this->storage[$key]['expires'] < time()) {
            unset($this->storage[$key]);
            return null;
        }

        return $this->storage[$key]['value'];
    }

    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->storage[$key] = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];
        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->storage[$key]);
        return true;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function clear(): bool
    {
        $this->storage = [];
        return true;
    }
}
```

### Environment-Based Binding

```php
# filename: CacheServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

class CacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(CacheInterface::class, function ($c) {
            $driver = $_ENV['CACHE_DRIVER'] ?? 'file';

            return match ($driver) {
                'redis' => new RedisCache($c->make(\Redis::class)),
                'file' => new FileCache($_ENV['CACHE_PATH'] ?? '/tmp/cache'),
                'array' => new ArrayCache(),
                default => throw new \Exception("Unknown cache driver: {$driver}")
            };
        });
    }
}
```

### Using Interface Binding

```php
# filename: UserService.php
<?php

declare(strict_types=1);

namespace App\Services;

// Usage - always depends on interface
class UserService
{
    public function __construct(
        private UserRepository $users,
        private CacheInterface $cache  // Interface, not concrete class
    ) {}

    public function findById(int $id): ?User
    {
        $key = "user:{$id}";

        // Use cache through interface
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $user = $this->users->findById($id);

        if ($user !== null) {
            $this->cache->set($key, $user, 3600);
        }

        return $user;
    }
}
```

---

## Section 11: Service Lifetimes

Control how long service instances live.

### Transient Services

New instance created each time:

```php
# filename: example-transient.php
<?php

declare(strict_types=1);

// Transient - new instance every time
$container->bind(ReportGenerator::class, function ($c) {
    return new ReportGenerator(
        $c->make(ReportRepository::class)
    );
});

$gen1 = $container->make(ReportGenerator::class);
$gen2 = $container->make(ReportGenerator::class);

var_dump($gen1 === $gen2); // false - different instances
```

**Use Cases:**
- Stateful services
- Services with per-request data
- Lightweight objects

### Singleton Services

Single instance shared across application:

```php
# filename: example-singleton.php
<?php

declare(strict_types=1);

// Singleton - same instance every time
$container->singleton(PDO::class, function ($c) {
    return new PDO(
        'mysql:host=localhost;dbname=app',
        'user',
        'pass'
    );
});

$pdo1 = $container->make(PDO::class);
$pdo2 = $container->make(PDO::class);

var_dump($pdo1 === $pdo2); // true - same instance
```

**Use Cases:**
- Database connections
- Configuration objects
- Expensive-to-create services
- Shared state

### Scoped Services (Request Scope)

One instance per request:

```php
# filename: ScopedContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class ScopedContainer extends Container
{
    private array $scoped = [];

    public function scoped(string $abstract, callable $concrete): void
    {
        $this->scoped[$abstract] = $concrete;
    }

    public function make(string $abstract): mixed
    {
        // Check scoped instances
        if (isset($this->scoped[$abstract])) {
            $scopeId = $this->getCurrentScopeId();

            if (!isset($this->instances[$scopeId][$abstract])) {
                $this->instances[$scopeId][$abstract] =
                    $this->scoped[$abstract]($this);
            }

            return $this->instances[$scopeId][$abstract];
        }

        return parent::make($abstract);
    }

    public function beginScope(): string
    {
        $scopeId = uniqid('scope_', true);
        $this->instances[$scopeId] = [];
        return $scopeId;
    }

    public function endScope(string $scopeId): void
    {
        unset($this->instances[$scopeId]);
    }

    private function getCurrentScopeId(): string
    {
        // Get current request ID or generate one
        return $_SERVER['REQUEST_ID'] ?? 'global';
    }
}

// Usage
$container->scoped(RequestContext::class, function ($c) {
    return new RequestContext();
});

// Each request gets its own instance
```

---

## Section 12: Circular Dependency Detection

Detect and prevent circular dependencies.

### Enhanced Container with Cycle Detection

```php
# filename: CycleDetectingContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class CycleDetectingContainer extends Container
{
    private array $resolving = [];

    protected function resolve(string $class): object
    {
        // Check for circular dependency
        if (isset($this->resolving[$class])) {
            $chain = array_keys($this->resolving);
            $chain[] = $class;
            throw new CircularDependencyException(
                "Circular dependency detected: " . implode(' -> ', $chain)
            );
        }

        // Mark as resolving
        $this->resolving[$class] = true;

        try {
            $instance = parent::resolve($class);
        } finally {
            // Always unmark, even if exception thrown
            unset($this->resolving[$class]);
        }

        return $instance;
    }
}

class CircularDependencyException extends \Exception {}
```

### Example Circular Dependency

```php
# filename: CircularDependencyExample.php
<?php

declare(strict_types=1);

// ❌ Circular dependency
class ServiceA
{
    public function __construct(private ServiceB $b) {}
}

class ServiceB
{
    public function __construct(private ServiceA $a) {}
}

// This will throw CircularDependencyException
$container->make(ServiceA::class);
// Exception: Circular dependency detected: ServiceA -> ServiceB -> ServiceA
```

### Breaking Circular Dependencies

```php
# filename: BreakingCircularDependencies.php
<?php

declare(strict_types=1);

// ✅ Solution 1: Use setter injection
class ServiceA
{
    private ?ServiceB $b = null;

    public function setServiceB(ServiceB $b): void
    {
        $this->b = $b;
    }
}

class ServiceB
{
    public function __construct(private ServiceA $a) {}
}

// Manual wiring
$a = new ServiceA();
$b = new ServiceB($a);
$a->setServiceB($b);

// ✅ Solution 2: Introduce interface/abstraction
interface ServiceBInterface
{
    public function doSomething(): void;
}

class ServiceA
{
    public function __construct(private ServiceBInterface $b) {}
}

class ServiceB implements ServiceBInterface
{
    // No dependency on ServiceA
    public function doSomething(): void {}
}

// ✅ Solution 3: Use factory or lazy loading
class ServiceA
{
    public function __construct(
        private Closure $bFactory  // Lazy load ServiceB
    ) {}

    private function getServiceB(): ServiceB
    {
        return ($this->bFactory)();
    }
}
```

---

## Section 13: PHP 8 Attributes for Dependency Injection

PHP 8 introduced attributes (annotations) that can be used for dependency injection configuration. This provides a modern, declarative approach to DI.

### Basic Attribute Usage

Define attributes for dependency injection:

```php
# filename: Inject.php
<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct(
        public ?string $name = null,
        public bool $required = true
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS)]
class Service
{
    public function __construct(
        public bool $singleton = false
    ) {}
}
```

### Using Attributes in Classes

```php
# filename: UserService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\{Service, Inject};
use App\Contracts\{UserRepositoryInterface, LoggerInterface};

#[Service(singleton: true)]
class UserService
{
    public function __construct(
        #[Inject] private UserRepositoryInterface $repository,
        #[Inject('logger')] private LoggerInterface $logger
    ) {}
}
```

### Attribute-Based Container

```php
# filename: AttributeContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

use App\Attributes\{Inject, Service};
use ReflectionClass;
use ReflectionParameter;

class AttributeContainer extends Container
{
    protected function resolve(string $class): object
    {
        $reflector = new ReflectionClass($class);
        
        // Check for Service attribute
        $serviceAttr = $reflector->getAttributes(Service::class)[0] ?? null;
        $isSingleton = $serviceAttr?->newInstance()->singleton ?? false;
        
        // Resolve instance
        $instance = parent::resolve($class);
        
        // Handle singleton
        if ($isSingleton) {
            $this->instances[$class] = $instance;
        }
        
        return $instance;
    }

    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            // Check for Inject attribute
            $injectAttr = $parameter->getAttributes(Inject::class)[0] ?? null;
            
            if ($injectAttr !== null) {
                $inject = $injectAttr->newInstance();
                $serviceName = $inject->name ?? $parameter->getType()?->getName();
                
                if ($serviceName) {
                    $dependencies[] = $this->make($serviceName);
                    continue;
                }
            }
            
            // Fall back to type hint resolution
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            }
        }

        return $dependencies;
    }
}
```

### Property Injection with Attributes

```php
# filename: PropertyInjection.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\Inject;
use App\Contracts\LoggerInterface;

class PropertyInjectedService
{
    #[Inject]
    private LoggerInterface $logger;
    
    public function doSomething(): void
    {
        $this->logger->info('Doing something');
    }
}
```

### Method Injection with Attributes

```php
# filename: MethodInjection.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\Inject;
use App\Contracts\EmailServiceInterface;

class MethodInjectedService
{
    public function sendEmail(
        string $to,
        #[Inject] EmailServiceInterface $emailService
    ): void {
        $emailService->send($to, 'Subject', 'Body');
    }
}
```

---

## Section 14: PSR-11 Container Interface

PHP-FIG standard for containers.

### PSR-11 Implementation

```php
# filename: PSR11Container.php
<?php

declare(strict_types=1);

namespace App\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class PSR11Container implements ContainerInterface
{
    private Container $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Finds an entry of the container by its identifier
     */
    public function get(string $id): mixed
    {
        try {
            return $this->container->make($id);
        } catch (\Exception $e) {
            if (!$this->has($id)) {
                throw new NotFoundException("Service {$id} not found", 0, $e);
            }
            throw new ContainerException("Error resolving {$id}", 0, $e);
        }
    }

    /**
     * Returns true if container can return an entry for identifier
     */
    public function has(string $id): bool
    {
        return $this->container->has($id) || class_exists($id);
    }

    // Delegate to internal container
    public function bind(string $abstract, callable $concrete): void
    {
        $this->container->bind($abstract, $concrete);
    }

    public function singleton(string $abstract, callable $concrete): void
    {
        $this->container->singleton($abstract, $concrete);
    }
}

class NotFoundException extends \Exception implements NotFoundExceptionInterface {}
class ContainerException extends \Exception implements ContainerExceptionInterface {}
```

---

## Section 15: Performance Optimization

DI containers can impact performance, especially with reflection. Here are optimization strategies for production use.

### Reflection Caching

Cache reflection data to avoid repeated analysis:

```php
# filename: CachedReflectionContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

use ReflectionClass;
use ReflectionParameter;

class CachedReflectionContainer extends Container
{
    /** @var array<string, ReflectionClass> */
    private array $reflectionCache = [];
    
    /** @var array<string, array<ReflectionParameter>> */
    private array $parameterCache = [];

    protected function resolve(string $class): object
    {
        // Cache reflection class
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }
        
        $reflector = $this->reflectionCache[$class];
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$class} is not instantiable");
        }
        
        $constructor = $reflector->getConstructor();
        
        if ($constructor === null) {
            return new $class();
        }
        
        // Cache parameters
        if (!isset($this->parameterCache[$class])) {
            $this->parameterCache[$class] = $constructor->getParameters();
        }
        
        $dependencies = $this->resolveDependencies($this->parameterCache[$class]);
        
        return $reflector->newInstanceArgs($dependencies);
    }
}
```

### Pre-compiled Container

Compile container bindings at build time:

```php
# filename: CompiledContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class CompiledContainer extends Container
{
    private array $compiled = [];

    public function compile(): void
    {
        // Pre-resolve all singletons
        foreach ($this->bindings as $abstract => $concrete) {
            if ($this->isSingleton($abstract)) {
                $this->compiled[$abstract] = $concrete($this);
            }
        }
    }

    public function make(string $abstract): mixed
    {
        // Check compiled cache first
        if (isset($this->compiled[$abstract])) {
            return $this->compiled[$abstract];
        }
        
        return parent::make($abstract);
    }
    
    private function isSingleton(string $abstract): bool
    {
        // Check if binding is singleton
        // Implementation depends on your container structure
        return false;
    }
}
```

### Lazy Loading Optimization

Only resolve dependencies when actually used:

```php
# filename: LazyService.php
<?php

declare(strict_types=1);

namespace App\Services;

use Closure;

class LazyService
{
    private ?object $service = null;
    
    public function __construct(
        private Closure $factory
    ) {}
    
    public function get(): object
    {
        if ($this->service === null) {
            $this->service = ($this->factory)();
        }
        
        return $this->service;
    }
}

// Usage
$container->bind(ExpensiveService::class, function ($c) {
    return new LazyService(fn() => new ExpensiveService(/* heavy initialization */));
});
```

### Container Benchmarking

Measure container performance:

```php
# filename: benchmark-container.php
<?php

declare(strict_types=1);

use App\Container\Container;

$container = new Container();
// ... register services ...

$iterations = 10000;
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $service = $container->make(SomeService::class);
}

$end = microtime(true);
$time = ($end - $start) * 1000; // Convert to milliseconds

echo "Resolved {$iterations} services in {$time}ms\n";
echo "Average: " . ($time / $iterations) . "ms per resolution\n";
```

### Production Recommendations

1. **Cache reflection data** - Store ReflectionClass instances
2. **Pre-compile singletons** - Resolve singletons at bootstrap
3. **Use lazy loading** - Defer expensive object creation
4. **Minimize container lookups** - Store resolved services when possible
5. **Profile your container** - Identify bottlenecks

---

## Section 16: Testing DI Containers

Testing DI containers ensures bindings work correctly and dependencies resolve properly.

### Testing Basic Bindings

```php
# filename: ContainerTest.php
<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Container\Container;
use App\Services\UserService;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function test_can_bind_and_resolve_service(): void
    {
        $this->container->bind('test', fn() => new \stdClass());
        
        $instance1 = $this->container->make('test');
        $instance2 = $this->container->make('test');
        
        $this->assertInstanceOf(\stdClass::class, $instance1);
        $this->assertNotSame($instance1, $instance2); // Transient
    }

    public function test_singleton_returns_same_instance(): void
    {
        $this->container->singleton('test', fn() => new \stdClass());
        
        $instance1 = $this->container->make('test');
        $instance2 = $this->container->make('test');
        
        $this->assertSame($instance1, $instance2);
    }

    public function test_can_resolve_with_dependencies(): void
    {
        $this->container->bind(PDO::class, fn() => new \PDO('sqlite::memory:'));
        $this->container->bind(UserService::class, fn($c) => 
            new UserService($c->make(PDO::class))
        );
        
        $service = $this->container->make(UserService::class);
        
        $this->assertInstanceOf(UserService::class, $service);
    }
}
```

### Testing Auto-wiring

```php
public function test_auto_wires_dependencies(): void
{
    $this->container->bind(PDO::class, fn() => new \PDO('sqlite::memory:'));
    
    // Should auto-resolve UserService and its dependencies
    $service = $this->container->make(UserService::class);
    
    $this->assertInstanceOf(UserService::class, $service);
}
```

### Testing Circular Dependencies

```php
public function test_detects_circular_dependencies(): void
{
    $this->container->bind(ServiceA::class, fn($c) => 
        new ServiceA($c->make(ServiceB::class))
    );
    $this->container->bind(ServiceB::class, fn($c) => 
        new ServiceB($c->make(ServiceA::class))
    );
    
    $this->expectException(CircularDependencyException::class);
    $this->container->make(ServiceA::class);
}
```

### Testing Contextual Bindings

```php
public function test_contextual_binding(): void
{
    $container = new ContextualContainer();
    
    $container->when(UserService::class)
        ->needs(CacheInterface::class)
        ->give(RedisCache::class);
    
    $service = $container->make(UserService::class);
    
    // Verify correct implementation injected
    $this->assertInstanceOf(RedisCache::class, $service->getCache());
}
```

### Testing Service Providers

```php
public function test_service_provider_registers_services(): void
{
    $container = new Container();
    $provider = new DatabaseServiceProvider($container);
    
    $provider->register();
    
    $this->assertTrue($container->has(PDO::class));
    $pdo = $container->make(PDO::class);
    $this->assertInstanceOf(PDO::class, $pdo);
}
```

---

## Section 17: Conditional Bindings

Bind services conditionally based on environment, configuration, or feature flags.

### Environment-Based Conditional Binding

```php
# filename: ConditionalServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\CacheInterface;
use App\Services\{RedisCache, FileCache, ArrayCache};

class ConditionalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $driver = $_ENV['CACHE_DRIVER'] ?? 'file';
        
        match ($driver) {
            'redis' => $this->container->singleton(
                CacheInterface::class,
                fn($c) => new RedisCache($c->make(\Redis::class))
            ),
            'file' => $this->container->singleton(
                CacheInterface::class,
                fn($c) => new FileCache($_ENV['CACHE_PATH'] ?? '/tmp/cache')
            ),
            'array' => $this->container->singleton(
                CacheInterface::class,
                fn($c) => new ArrayCache()
            ),
            default => throw new \Exception("Unknown cache driver: {$driver}")
        };
    }
}
```

### Feature Flag Conditional Binding

```php
public function register(): void
{
    // Use new payment gateway if feature enabled
    if ($this->isFeatureEnabled('new_payment_gateway')) {
        $this->container->bind(
            PaymentGatewayInterface::class,
            NewPaymentGateway::class
        );
    } else {
        $this->container->bind(
            PaymentGatewayInterface::class,
            LegacyPaymentGateway::class
        );
    }
}

private function isFeatureEnabled(string $feature): bool
{
    return $_ENV["FEATURE_{$feature}"] === 'true';
}
```

### Configuration-Based Conditional Binding

```php
public function register(): void
{
    $config = require __DIR__ . '/../config/services.php';
    
    foreach ($config['bindings'] as $abstract => $concrete) {
        if (is_callable($concrete)) {
            $this->container->bind($abstract, $concrete);
        } elseif (is_string($concrete)) {
            $this->container->bind($abstract, fn($c) => $c->make($concrete));
        }
    }
    
    foreach ($config['singletons'] as $abstract => $concrete) {
        $this->container->singleton($abstract, $concrete);
    }
}
```

### Runtime Conditional Binding

```php
public function register(): void
{
    // Bind based on runtime check
    if (extension_loaded('redis')) {
        $this->container->singleton(
            CacheInterface::class,
            fn($c) => new RedisCache($c->make(\Redis::class))
        );
    } else {
        $this->container->singleton(
            CacheInterface::class,
            fn($c) => new FileCache('/tmp/cache')
        );
    }
}
```

---

## Section 18: Service Aliases (Expanded)

Service aliases provide convenient shortcuts and allow multiple names for the same service.

### Basic Aliases

```php
# filename: AliasContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class AliasContainer extends Container
{
    /** @var array<string, string> */
    private array $aliases = [];

    /**
     * Register an alias
     */
    public function alias(string $alias, string $abstract): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function make(string $abstract): mixed
    {
        // Resolve alias if needed
        $abstract = $this->aliases[$abstract] ?? $abstract;
        
        return parent::make($abstract);
    }

    public function has(string $abstract): bool
    {
        $abstract = $this->aliases[$abstract] ?? $abstract;
        return parent::has($abstract);
    }
}
```

### Using Aliases

```php
# Register service
$container->singleton(LoggerInterface::class, fn() => new FileLogger());

// Create aliases
$container->alias('logger', LoggerInterface::class);
$container->alias('log', LoggerInterface::class);

// Use aliases
$logger1 = $container->make('logger');
$logger2 = $container->make('log');
$logger3 = $container->make(LoggerInterface::class);
// All return the same instance
```

### Aliases in Service Providers

```php
class AliasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register services
        $this->container->singleton(LoggerInterface::class, fn() => new FileLogger());
        $this->container->singleton(CacheInterface::class, fn() => new RedisCache());
        
        // Register aliases
        $this->container->alias('logger', LoggerInterface::class);
        $this->container->alias('cache', CacheInterface::class);
        $this->container->alias('db', PDO::class);
    }
}
```

### Alias Chains

```php
// Chain aliases
$container->alias('app.logger', LoggerInterface::class);
$container->alias('logger', 'app.logger');
$container->alias('log', 'logger');

// All resolve to LoggerInterface
$log = $container->make('log');
```

---

## Section 19: Lazy Loading Details

Lazy loading defers object creation until the object is actually needed, improving performance and memory usage.

### Lazy Service Wrapper

```php
# filename: LazyService.php
<?php

declare(strict_types=1);

namespace App\Services;

use Closure;

class LazyService
{
    private ?object $instance = null;
    
    public function __construct(
        private Closure $factory
    ) {}
    
    public function get(): object
    {
        if ($this->instance === null) {
            $this->instance = ($this->factory)();
        }
        
        return $this->instance;
    }
    
    public function isLoaded(): bool
    {
        return $this->instance !== null;
    }
}
```

### Lazy Loading in Container

```php
# filename: LazyContainer.php
<?php

declare(strict_types=1);

namespace App\Container;

class LazyContainer extends Container
{
    /**
     * Bind a lazy service
     */
    public function lazy(string $abstract, callable $concrete): void
    {
        $this->bind($abstract, function ($c) use ($concrete) {
            return new LazyService(fn() => $concrete($c));
        });
    }
}

// Usage
$container->lazy(ExpensiveService::class, function ($c) {
    // This closure won't be called until get() is invoked
    return new ExpensiveService(/* heavy initialization */);
});

// Service not created yet
$lazy = $container->make(ExpensiveService::class);

// Now service is created
$service = $lazy->get();
```

### Proxy Pattern for Lazy Loading

```php
# filename: ServiceProxy.php
<?php

declare(strict_types=1);

namespace App\Services;

use Closure;

class ServiceProxy
{
    private ?object $target = null;
    
    public function __construct(
        private Closure $factory,
        private string $interface
    ) {}
    
    public function __call(string $method, array $args): mixed
    {
        if ($this->target === null) {
            $this->target = ($this->factory)();
        }
        
        return $this->target->$method(...$args);
    }
}

// Usage
$container->bind(ServiceInterface::class, function ($c) {
    return new ServiceProxy(
        fn() => new ExpensiveService(),
        ServiceInterface::class
    );
});

$service = $container->make(ServiceInterface::class);
// No expensive initialization yet

$service->doSomething(); // Now initialized
```

### Lazy Loading for Collections

```php
# filename: LazyCollection.php
<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Iterator;

class LazyCollection implements Iterator
{
    private array $items = [];
    private int $position = 0;
    
    public function __construct(
        private Closure $factory,
        private int $count
    ) {}
    
    public function current(): mixed
    {
        if (!isset($this->items[$this->position])) {
            $this->items[$this->position] = ($this->factory)($this->position);
        }
        
        return $this->items[$this->position];
    }
    
    public function next(): void
    {
        $this->position++;
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function valid(): bool
    {
        return $this->position < $this->count;
    }
    
    public function rewind(): void
    {
        $this->position = 0;
    }
}
```

---

## Section 20: Real-World Application

Complete example integrating DI throughout an application.

### Application Bootstrap

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application;
use App\Http\{Request, Response};
use App\Routing\Router;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create application
$app = require __DIR__ . '/../bootstrap/app.php';

// Get router from container
$router = $app->make(Router::class);

// Load routes
require __DIR__ . '/../routes/api.php';

// Capture request
$request = Request::capture();

try {
    // Dispatch request (controller dependencies auto-wired)
    $response = $router->dispatch($request);
} catch (\Throwable $e) {
    $handler = $app->make(ExceptionHandler::class);
    $response = $handler->handle($e);
}

$response->send();
```

### Service Configuration

```php
# filename: config/services.php
<?php

declare(strict_types=1);

return [
    // Singletons
    'singletons' => [
        PDO::class => function ($c) {
            return new PDO(/* ... */);
        },
        \Redis::class => function ($c) {
            $redis = new \Redis();
            $redis->connect($_ENV['REDIS_HOST'], (int) $_ENV['REDIS_PORT']);
            return $redis;
        },
        LoggerInterface::class => function ($c) {
            return new FileLogger($_ENV['LOG_PATH']);
        },
    ],

    // Bindings
    'bindings' => [
        CacheInterface::class => function ($c) {
            return new RedisCache($c->make(\Redis::class));
        },
        MailerInterface::class => function ($c) {
            return new SMTPMailer(/* ... */);
        },
    ],

    // Aliases
    'aliases' => [
        'cache' => CacheInterface::class,
        'logger' => LoggerInterface::class,
        'mailer' => MailerInterface::class,
    ],
];
```

### Controller with Full DI

```php
# filename: AuthController.php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\{Request, Response};
use App\Services\{UserService, AuthService};
use App\Validation\Validator;
use App\Contracts\{LoggerInterface, CacheInterface};

class AuthController
{
    // All dependencies injected by container
    public function __construct(
        private UserService $users,
        private AuthService $auth,
        private Validator $validator,
        private LoggerInterface $logger,
        private CacheInterface $cache
    ) {}

    public function login(Request $request): Response
    {
        try {
            // Validate input
            $data = $this->validator->validate($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Authenticate
            $token = $this->auth->attempt($data['email'], $data['password']);

            if ($token === null) {
                $this->logger->warning("Failed login attempt: {$data['email']}");
                return Response::error('Invalid credentials', 401);
            }

            // Cache user session
            $user = $this->users->findByEmail($data['email']);
            $this->cache->set("user:{$user->id}", $user, 3600);

            $this->logger->info("User logged in: {$data['email']}");

            return Response::json([
                'token' => $token,
                'user' => $user,
            ]);

        } catch (ValidationException $e) {
            return Response::error('Validation failed', 422, $e->getErrors());
        }
    }
}
```

---

## Exercises

Practice dependency injection concepts with these hands-on exercises:

### Exercise 1: Build a Container

**Goal**: Create a fully functional DI container from scratch

Implement a basic DI container with:
- `bind(string $abstract, callable $concrete): void` method for transient bindings
- `singleton(string $abstract, callable $concrete): void` method for singleton bindings
- `make(string $abstract): mixed` method for resolving services
- Auto-resolution using reflection for classes with type-hinted constructors
- Parameter injection for constructor dependencies

**Validation**: Test your container:

```php
# filename: test-container.php
<?php

declare(strict_types=1);

require_once 'Container.php';

class Database {}
class UserRepository {
    public function __construct(private Database $db) {}
}

$container = new Container();

// Test singleton
$container->singleton(Database::class, fn() => new Database());
$db1 = $container->make(Database::class);
$db2 = $container->make(Database::class);
assert($db1 === $db2, "Singleton should return same instance");

// Test auto-resolution
$repo = $container->make(UserRepository::class);
assert($repo instanceof UserRepository, "Should auto-resolve UserRepository");
assert($repo->db instanceof Database, "Should inject Database dependency");

echo "✓ Container tests passed!\n";
```

Expected output:
```
✓ Container tests passed!
```

### Exercise 2: Service Provider

**Goal**: Organize service registration using the service provider pattern

Create a service provider for a payment gateway:
- Define `PaymentGatewayInterface` with `processPayment(float $amount): bool` method
- Create `StripePaymentGateway` implementation
- Create `PayPalPaymentGateway` implementation
- Create `PaymentServiceProvider` that registers the gateway based on `PAYMENT_GATEWAY` environment variable
- Use the provider to register services in a container

**Validation**: Test your service provider:

```php
# filename: test-payment-provider.php
<?php

declare(strict_types=1);

require_once 'PaymentServiceProvider.php';

// Set environment
$_ENV['PAYMENT_GATEWAY'] = 'stripe';

$container = new Container();
$provider = new PaymentServiceProvider($container);
$provider->register();

$gateway = $container->make(PaymentGatewayInterface::class);
assert($gateway instanceof StripePaymentGateway, "Should use Stripe when configured");

// Change to PayPal
$_ENV['PAYMENT_GATEWAY'] = 'paypal';
$container2 = new Container();
$provider2 = new PaymentServiceProvider($container2);
$provider2->register();

$gateway2 = $container2->make(PaymentGatewayInterface::class);
assert($gateway2 instanceof PayPalPaymentGateway, "Should use PayPal when configured");

echo "✓ Payment provider tests passed!\n";
```

Expected output:
```
✓ Payment provider tests passed!
```

### Exercise 3: Interface Binding and Refactoring

**Goal**: Refactor tightly-coupled code to use dependency injection

Refactor the following tightly-coupled code:
- Extract interfaces for `PaymentProcessor` and `Mailer`
- Extract interface for `Logger`
- Refactor `OrderProcessor` to use constructor injection
- Create a service provider to bind interfaces to implementations
- Ensure the refactored code is testable with mocks

**Starting Code**:

```php
# filename: OrderProcessor.php (before refactoring)
<?php

declare(strict_types=1);

class OrderProcessor
{
    public function process(Order $order): void
    {
        $payment = new StripePayment();
        $payment->charge($order->getTotal());

        $email = new SMTPMailer();
        $email->send($order->getCustomerEmail(), 'Order Confirmed', '...');

        file_put_contents('/var/log/orders.log', "Order {$order->id} processed");
    }
}
```

**Validation**: Test your refactored code:

```php
# filename: test-refactored-order-processor.php
<?php

declare(strict_types=1);

require_once 'OrderProcessor.php';
require_once 'MockPaymentProcessor.php';
require_once 'MockMailer.php';
require_once 'MockLogger.php';

// Test with mocks
$mockPayment = new MockPaymentProcessor();
$mockMailer = new MockMailer();
$mockLogger = new MockLogger();

$processor = new OrderProcessor($mockPayment, $mockMailer, $mockLogger);
$order = new Order(123, 99.99, 'customer@example.com');

$processor->process($order);

assert($mockPayment->wasCharged(99.99), "Should charge correct amount");
assert($mockMailer->wasSentTo('customer@example.com'), "Should send email");
assert($mockLogger->wasLogged("Order 123 processed"), "Should log order");

echo "✓ Refactored OrderProcessor tests passed!\n";
```

Expected output:
```
✓ Refactored OrderProcessor tests passed!
```

---

## Troubleshooting

Common issues when working with dependency injection and how to resolve them:

### Error: "Cannot resolve parameter $name"

**Symptom**: Container throws exception when trying to resolve a dependency

```php
Fatal error: Uncaught Exception: Cannot resolve parameter $database
```

**Cause**: The container cannot resolve a parameter because:
- The parameter has no type hint
- The parameter is a built-in type (string, int, etc.) without a default value
- The required class doesn't exist or isn't autoloadable

**Solution**: Ensure all constructor parameters have type hints for classes:

```php
// ❌ Bad - no type hint
public function __construct($db) {}

// ✅ Good - type hinted
public function __construct(PDO $db) {}

// ✅ Good - optional with default
public function __construct(string $name = 'default') {}
```

### Error: "Circular dependency detected"

**Symptom**: Container throws `CircularDependencyException`

```php
Circular dependency detected: ServiceA -> ServiceB -> ServiceA
```

**Cause**: Two or more services depend on each other directly or indirectly

**Solution**: Break the cycle using one of these approaches:

1. **Use setter injection** for one dependency
2. **Introduce an interface** to break the direct dependency
3. **Use lazy loading** with a factory closure
4. **Refactor** to remove the circular dependency

See Section 8 for detailed solutions.

### Problem: Singleton Not Working

**Symptom**: `singleton()` binding returns different instances

**Cause**: The singleton closure is being called multiple times instead of caching the instance

**Solution**: Ensure the singleton closure properly checks and caches:

```php
// ❌ Bad - creates new instance each time
$container->singleton(PDO::class, fn() => new PDO(...));

// ✅ Good - checks cache first
$container->singleton(PDO::class, function ($c) use ($abstract) {
    if (!isset($this->instances[$abstract])) {
        $this->instances[$abstract] = new PDO(...);
    }
    return $this->instances[$abstract];
});
```

### Problem: Auto-wiring Not Resolving Dependencies

**Symptom**: Container cannot auto-resolve a class even though it has type hints

**Cause**: 
- Class is not autoloadable
- Constructor has parameters without type hints
- Required dependency is not bound in container

**Solution**: 

1. **Check autoloading**: Ensure the class is in the autoloader path
2. **Add type hints**: All constructor parameters need type hints
3. **Bind dependencies**: Register required dependencies in the container

```php
// Check if class exists
if (!class_exists(MyService::class)) {
    throw new Exception("Class not found");
}

// Ensure type hints
public function __construct(MyDependency $dep) {} // ✅ Has type hint

// Register dependencies
$container->bind(MyDependency::class, fn() => new MyDependency());
```

### Error: "Class is not instantiable"

**Symptom**: Container throws exception when trying to resolve abstract class or interface

**Cause**: Attempting to resolve an abstract class or interface without a binding

**Solution**: Bind the interface/abstract class to a concrete implementation:

```php
// ❌ Bad - trying to resolve interface directly
$container->make(LoggerInterface::class); // Fails - interface can't be instantiated

// ✅ Good - bind interface to implementation
$container->bind(LoggerInterface::class, fn() => new FileLogger());
$container->make(LoggerInterface::class); // Works
```

---

## Common Pitfalls

**❌ Creating Dependencies in Constructor**

```php
# filename: UserService.php (bad example)
<?php

declare(strict_types=1);

// Bad - defeats DI
class UserService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository(); // Don't do this!
    }
}

// Good - inject dependencies
class UserService
{
    public function __construct(
        private UserRepository $users
    ) {}
}
```

**❌ Service Locator Anti-Pattern**

```php
# filename: UserService.php (bad example)
<?php

declare(strict_types=1);

// Bad - service locator hides dependencies
class UserService
{
    public function register(array $data): User
    {
        $logger = Container::get(LoggerInterface::class); // Hidden dependency
        // ...
    }
}

// Good - explicit dependencies
class UserService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}
}
```

**❌ Overusing Singletons**

```php
# filename: example-bad-singleton.php
<?php

declare(strict_types=1);

// Bad - singleton for stateful service
$container->singleton(ShoppingCart::class, fn($c) => new ShoppingCart());

// Good - transient for stateful services
$container->bind(ShoppingCart::class, fn($c) => new ShoppingCart());
```

---

## Best Practices Summary

✅ **Prefer constructor injection** - Makes dependencies explicit
✅ **Depend on abstractions** - Use interfaces, not concrete classes
✅ **Use service providers** - Organize service registration
✅ **Singleton for stateless** - Only singleton stateless services
✅ **Auto-wire when possible** - Let container resolve dependencies
✅ **Validate on construction** - Ensure object is always valid
✅ **Avoid service locator** - Don't hide dependencies
✅ **Keep containers simple** - Don't over-engineer
✅ **Test with mocks** - Inject test doubles easily
✅ **Follow PSR-11** - Use standard container interface

---

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="11"
  label="Mastered dependency injection patterns and container implementation!"
/>

---

## Further Reading

- [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11/)
- [PHP-DI Documentation](https://php-di.org/)
- [Symfony Dependency Injection](https://symfony.com/doc/current/components/dependency_injection.html)
- [Laravel Service Container](https://laravel.com/docs/container)
- [Dependency Injection Principles](https://en.wikipedia.org/wiki/Dependency_injection)

---

## Wrap-up

Congratulations! You've completed a comprehensive exploration of dependency injection in PHP. This chapter covered the fundamental patterns and practices that make PHP applications more maintainable, testable, and flexible.

### What You Accomplished

✓ **Understood DI principles** - You learned how dependency injection promotes loose coupling and testability  
✓ **Implemented injection patterns** - You explored constructor, setter, and method injection  
✓ **Built a DI container** - You created a fully functional container with automatic dependency resolution using reflection  
✓ **Integrated factories** - You learned to use factory patterns with DI containers for complex object creation  
✓ **Created decorators** - You implemented service decorators for cross-cutting concerns like logging and caching  
✓ **Used contextual binding** - You learned to provide different implementations based on injection context  
✓ **Tagged services** - You grouped services using tags for event listeners and middleware  
✓ **Organized services** - You learned to use service providers for clean service registration  
✓ **Bound interfaces** - You implemented interface-to-implementation bindings for flexible architecture  
✓ **Used PHP 8 attributes** - You explored modern declarative DI using attributes  
✓ **Managed lifetimes** - You understood singleton, transient, and scoped service patterns  
✓ **Detected cycles** - You implemented circular dependency detection and resolution  
✓ **Optimized performance** - You learned reflection caching and lazy loading strategies  
✓ **Tested containers** - You wrote tests to verify container bindings and resolution  
✓ **Applied conditional logic** - You implemented environment and feature-flag based bindings  
✓ **Used aliases and lazy loading** - You created service aliases and implemented lazy loading patterns  
✓ **Applied PSR-11** - You created a standards-compliant container implementation  

### Key Concepts Learned

- **Dependency Injection** is a design pattern where dependencies are provided externally rather than created internally
- **DI Containers** automate dependency resolution using reflection and type hints
- **Factory Pattern** integrates with DI containers to encapsulate complex object creation logic
- **Service Decorators** wrap services to add functionality without modifying the original service
- **Contextual Binding** provides different implementations based on where dependencies are injected
- **Service Tagging** groups related services for retrieval (similar to Spring's component scanning)
- **Service Providers** organize and register application services in a structured way
- **Interface Binding** enables flexible architecture by depending on abstractions
- **PHP 8 Attributes** provide a modern declarative approach to dependency injection
- **Service Lifetimes** control how long service instances persist (singleton, transient, scoped)
- **Auto-wiring** automatically resolves dependencies based on constructor type hints
- **Performance Optimization** includes reflection caching, lazy loading, and pre-compilation
- **Testing Containers** ensures bindings work correctly and dependencies resolve properly
- **Conditional Bindings** allow environment and feature-flag based service selection
- **Service Aliases** provide convenient shortcuts and multiple names for services
- **Lazy Loading** defers object creation until actually needed, improving performance

### Real-World Application

The patterns you've learned in this chapter are used extensively in modern PHP frameworks:
- **Laravel** uses a sophisticated service container with auto-wiring
- **Symfony** provides a powerful dependency injection component
- **PHP-DI** offers annotation-based dependency injection

These concepts will be essential as you build larger applications where managing dependencies manually becomes unwieldy.

### Next Steps

In the next chapter, you'll learn about **Unit Testing with PHPUnit**, where dependency injection becomes crucial for creating testable code. The ability to inject mocks and stubs makes unit testing much easier, and you'll see how DI patterns enable comprehensive test coverage.

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain the benefits of dependency injection
- [ ] Implement constructor injection in your classes
- [ ] Build a simple DI container with reflection
- [ ] Use auto-wiring to resolve dependencies automatically
- [ ] Integrate factory patterns with DI containers
- [ ] Create service decorators for cross-cutting concerns
- [ ] Use contextual binding to provide different implementations
- [ ] Tag services for grouping and retrieval
- [ ] Create service providers to organize service registration
- [ ] Bind interfaces to implementations
- [ ] Use PHP 8 attributes for declarative DI
- [ ] Understand service lifetimes (singleton vs transient vs scoped)
- [ ] Detect and resolve circular dependencies
- [ ] Optimize container performance with reflection caching
- [ ] Write tests for DI containers
- [ ] Apply conditional bindings based on environment or features
- [ ] Use service aliases and lazy loading patterns
- [ ] Integrate DI throughout an application
- [ ] Follow PSR-11 container interface standard

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/10-building-rest-apis">← Chapter 10</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit">Chapter 12 →</a></div>
</div>
