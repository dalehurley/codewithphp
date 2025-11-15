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

# Chapter 11: Dependency Injection

<Badge type="warning">Intermediate</Badge>

## Overview

Dependency Injection (DI) is a fundamental design pattern that promotes loose coupling, testability, and maintainability. If you're coming from Java, you're likely familiar with Spring's DI container. PHP has similar capabilities through DI containers that manage object dependencies automatically. This chapter explores DI principles, container implementation, and best practices for building maintainable PHP applications.

**What You'll Learn:**
- Dependency Injection principles and benefits
- Constructor, setter, and method injection patterns
- Building a DI container from scratch
- Auto-wiring and reflection
- Service providers and binding
- Interface-to-implementation binding
- Singleton and transient service lifetimes
- Circular dependency detection
- Integration with real-world applications
- PSR-11 Container Interface standard

## Prerequisites

Before starting this chapter, you should be comfortable with:
- Object-oriented programming (Chapters 3-5)
- Interfaces and type hints
- Namespaces and autoloading (Chapter 6)
- Reflection API basics

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Explain DI principles** and their advantages over manual dependency management
2. **Implement constructor injection** to declare dependencies explicitly
3. **Build a DI container** with automatic dependency resolution
4. **Use reflection** to inspect class constructors and auto-wire dependencies
5. **Configure service providers** to register application services
6. **Bind interfaces to implementations** for flexible architecture
7. **Manage service lifetimes** (singleton vs transient)
8. **Detect circular dependencies** and handle them appropriately
9. **Apply DI patterns** to real-world application architecture

---

## Section 1: Understanding Dependency Injection

Dependency Injection is a design pattern where objects receive their dependencies from external sources rather than creating them internally.

### The Problem: Tight Coupling

Without DI, classes create their own dependencies, leading to tight coupling:

```php
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

## Section 4: Auto-wiring with Reflection

Auto-wiring automatically resolves dependencies using PHP's Reflection API.

### Enhanced Container with Auto-wiring

```php
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

## Section 5: Service Providers

Service providers organize service registration and bootstrapping.

### Service Provider Pattern

```php
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
<?php

declare(strict_types=1);

// bootstrap/app.php

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

## Section 6: Interface Binding

Binding interfaces to implementations enables flexible, testable architecture.

### Define Interfaces

```php
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

## Section 7: Service Lifetimes

Control how long service instances live.

### Transient Services

New instance created each time:

```php
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

## Section 8: Circular Dependency Detection

Detect and prevent circular dependencies.

### Enhanced Container with Cycle Detection

```php
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

## Section 9: PSR-11 Container Interface

PHP-FIG standard for containers.

### PSR-11 Implementation

```php
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

## Section 10: Real-World Application

Complete example integrating DI throughout an application.

### Application Bootstrap

```php
<?php

declare(strict_types=1);

// public/index.php

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
<?php

declare(strict_types=1);

// config/services.php

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

Practice dependency injection concepts:

### Exercise 1: Build a Container

Implement a basic DI container with:
- `bind()` and `singleton()` methods
- Auto-resolution using reflection
- Parameter injection

### Exercise 2: Service Provider

Create a service provider for a payment gateway:
- Define `PaymentGatewayInterface`
- Create implementations for Stripe and PayPal
- Register in service provider with environment-based selection

### Exercise 3: Interface Binding

Refactor tightly-coupled code to use DI:
- Extract interfaces
- Inject dependencies
- Bind in container

```php
<?php

declare(strict_types=1);

// Refactor this tightly-coupled code:
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

---

## Common Pitfalls

**❌ Creating Dependencies in Constructor**

```php
<?php
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
<?php
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
<?php
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

## Further Reading

- [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11/)
- [PHP-DI Documentation](https://php-di.org/)
- [Symfony Dependency Injection](https://symfony.com/doc/current/components/dependency_injection.html)
- [Laravel Service Container](https://laravel.com/docs/container)
- [Dependency Injection Principles](https://en.wikipedia.org/wiki/Dependency_injection)

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain the benefits of dependency injection
- [ ] Implement constructor injection in your classes
- [ ] Build a simple DI container with reflection
- [ ] Use auto-wiring to resolve dependencies automatically
- [ ] Create service providers to organize service registration
- [ ] Bind interfaces to implementations
- [ ] Understand service lifetimes (singleton vs transient)
- [ ] Detect and resolve circular dependencies
- [ ] Integrate DI throughout an application
- [ ] Follow PSR-11 container interface standard

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/10-building-rest-apis">← Chapter 10</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit">Chapter 12 →</a></div>
</div>
