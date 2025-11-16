---
title: "21: Symfony Components"
description: "Build applications using Symfony's reusable components: HttpFoundation, Console, EventDispatcher, and DependencyInjection"
series: "php-for-java-developers"
chapter: 21
order: 21
difficulty: "Advanced"
prerequisites:
  - "/series/php-for-java-developers/chapters/20-laravel-fundamentals"
---

![Symfony Components](/images/php-for-java-developers/chapter-21-symfony-components-hero-full.webp)

# Chapter 21: Symfony Components

<Badge type="danger">Advanced</Badge> <Badge type="info">180-240 min</Badge>

## Overview

Symfony Components are a collection of reusable PHP libraries that form the foundation of many PHP frameworks, including Symfony Framework itself, Laravel, Drupal, and others. Think of them as PHP's equivalent to Apache Commons for Java—modular, well-tested libraries that solve common problems without requiring a full framework.

Unlike monolithic frameworks, Symfony Components can be used independently, allowing you to pick and choose exactly what you need for your application. This modular approach gives you the flexibility to build lightweight applications or compose a full-stack framework tailored to your needs.

**What You'll Learn:**

- HttpFoundation component for handling HTTP requests and responses
- Console component for building command-line applications
- EventDispatcher for implementing the observer pattern
- DependencyInjection container for managing object dependencies
- Routing component for URL matching and generation
- How these components compare to Java equivalents
- Building a complete application using standalone components

## Prerequisites

::: info Time Estimate
⏱️ **180-240 minutes** to complete this chapter (includes advanced topics)
:::

Before starting this chapter, you should be comfortable with:

- PHP namespaces and autoloading (Chapter 6)
- Object-oriented programming (Chapters 3-5)
- Composer dependency management (Chapter 8)
- HTTP fundamentals (methods, headers, status codes)
- Basic understanding of design patterns (observer, dependency injection)
- Familiarity with Java's Spring Framework or Apache Commons (helpful but not required)

**Verify your setup:**

```bash
# Check PHP version
php --version  # Should be PHP 8.4+

# Check Composer
composer --version

# If Composer not installed, install it:
# curl -sS https://getcomposer.org/installer | php
# sudo mv composer.phar /usr/local/bin/composer
```

## What You'll Build

By the end of this chapter, you will have created:

- A standalone HTTP application using HttpFoundation
- A command-line tool using the Console component
- An event-driven system using EventDispatcher
- A dependency injection container setup
- A complete application combining multiple components
- Advanced features: file uploads, sessions, progress bars, interactive commands
- Integration patterns: kernel, middleware pipeline, command bus
- Performance optimizations: cached containers and routes
- Comprehensive test suite for components
- Production-ready deployment setup with Docker
- Error handling and logging system
- Understanding of how Symfony Components compare to Java libraries

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Use HttpFoundation** to handle HTTP requests and responses in a framework-agnostic way
2. **Build CLI applications** using Symfony Console component
3. **Implement event-driven architecture** with EventDispatcher
4. **Manage dependencies** using DependencyInjection container
5. **Understand component architecture** and when to use standalone components vs full frameworks
6. **Compare Symfony Components** to Java equivalents (Apache Commons, Spring components)
7. **Compose multiple components** to build custom solutions
8. **Use Symfony Flex** to bootstrap projects efficiently
9. **Apply advanced features** like file uploads, sessions, progress bars, and event priorities
10. **Implement integration patterns** like kernel, middleware pipeline, and command bus
11. **Optimize performance** with caching and production configurations
12. **Write comprehensive tests** for component-based applications
13. **Deploy applications** using Docker and modern DevOps practices
14. **Handle errors and logging** properly in production

## Quick Start

Get started with Symfony Components in 5 minutes:

```bash
# Create project
mkdir symfony-demo && cd symfony-demo
composer init --no-interaction --name="demo/components"

# Install HttpFoundation
composer require symfony/http-foundation

# Create simple HTTP handler
cat > public/index.php << 'EOF'
<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$request = Request::createFromGlobals();
$response = new JsonResponse(['message' => 'Hello from Symfony Components!']);
$response->send();
EOF

# Run server
php -S localhost:8000 -t public
```

Visit `http://localhost:8000` to see your first Symfony Components application!

---

## Step 1: Understanding Symfony Components Architecture (~10 min)

### Goal

Understand what Symfony Components are and how they differ from full frameworks.

### Actions

1. **Understand the component philosophy**:

Symfony Components are standalone, framework-agnostic libraries. Each component:

- Has no external dependencies (except other Symfony components if needed)
- Follows PSR standards (PSR-7, PSR-11, etc.)
- Can be used independently
- Is well-tested and documented

2. **Compare to Java equivalents**:

::: code-group

```php [PHP]
<?php
// Symfony Components are standalone, reusable libraries
// Similar to Apache Commons + Spring Framework components

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// HttpFoundation ≈ Servlet API + Spring MVC
$request = Request::createFromGlobals();
$response = new Response('Hello', 200);
```

```java [Java]
// Java equivalent: Servlet API + Spring MVC
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import org.springframework.web.bind.annotation.*;

@RestController
public class HelloController {
    @GetMapping("/hello")
    public ResponseEntity<String> hello(HttpServletRequest request) {
        return ResponseEntity.ok("Hello");
    }
}
```

:::

**Component Comparison Table:**

| Symfony Component   | Java Equivalent                      | Purpose                        |
| ------------------- | ------------------------------------ | ------------------------------ |
| HttpFoundation      | Servlet API + Spring MVC             | HTTP request/response handling |
| Console             | Apache Commons CLI + Spring Shell    | Command-line applications      |
| EventDispatcher     | `java.util.Observer` + Spring Events | Event-driven architecture      |
| DependencyInjection | Spring IoC Container                 | Dependency management          |
| Routing             | Spring MVC `@RequestMapping`         | URL routing                    |
| Validator           | Bean Validation (JSR-303)            | Data validation                |
| Serializer          | Jackson/Gson                         | Object serialization           |

### Why It Works

Symfony Components follow the **Single Responsibility Principle**—each component solves one specific problem. This makes them:

- **Reusable**: Use in any PHP project
- **Testable**: Easy to unit test in isolation
- **Composable**: Combine components as needed
- **Framework-agnostic**: Not tied to Symfony Framework

### Troubleshooting

- **"Component not found"** — Ensure you've installed via Composer: `composer require symfony/http-foundation`
- **"Class not found"** — Check that you're using the correct namespace and have autoloaded via Composer

---

## Step 2: HttpFoundation Component (~20 min)

### Goal

Use HttpFoundation to handle HTTP requests and responses without a full framework.

### Actions

1. **Install HttpFoundation**:

```bash
# Create project directory
mkdir symfony-components-demo
cd symfony-components-demo

# Initialize Composer
composer init --no-interaction --name="demo/components"

# Install HttpFoundation
composer require symfony/http-foundation
```

2. **Create a basic HTTP handler**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

// Create request from global variables
$request = Request::createFromGlobals();

// Handle different routes
$path = $request->getPathInfo();

if ($path === '/') {
    $response = new Response('Hello, World!', Response::HTTP_OK);
} elseif ($path === '/api/users') {
    $users = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
    ];
    $response = new JsonResponse($users);
} elseif ($path === '/api/user' && $request->getMethod() === 'POST') {
    $data = json_decode($request->getContent(), true);
    $response = new JsonResponse(['message' => 'User created', 'data' => $data], Response::HTTP_CREATED);
} else {
    $response = new Response('Not Found', Response::HTTP_NOT_FOUND);
}

// Send response
$response->send();
```

3. **Access request data**:

```php
# filename: examples/request-example.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

// Query parameters
$page = $request->query->get('page', 1); // Default value: 1
$limit = $request->query->getInt('limit', 10);

// POST data
$name = $request->request->get('name');
$email = $request->request->get('email');

// Headers
$contentType = $request->headers->get('Content-Type');
$userAgent = $request->headers->get('User-Agent');

// Files
$file = $request->files->get('upload');

// Cookies
$sessionId = $request->cookies->get('session_id');

// Server variables
$method = $request->getMethod();
$uri = $request->getUri();
$ip = $request->getClientIp();
```

4. **Create responses**:

```php
# filename: examples/response-example.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Plain text response
$response = new Response('Hello World', Response::HTTP_OK);
$response->headers->set('Content-Type', 'text/plain');

// JSON response
$data = ['status' => 'success', 'data' => ['id' => 1]];
$response = new JsonResponse($data, Response::HTTP_OK);

// Redirect response
$response = new RedirectResponse('/dashboard', Response::HTTP_FOUND);

// Streamed response (for large files)
$response = new StreamedResponse(function () {
    $file = fopen('large-file.txt', 'r');
    while (!feof($file)) {
        echo fread($file, 8192);
        flush();
    }
    fclose($file);
});
$response->headers->set('Content-Type', 'text/plain');
```

### Expected Result

You can handle HTTP requests and responses using HttpFoundation, accessing all request data and creating various response types.

### Why It Works

HttpFoundation provides an object-oriented abstraction over PHP's superglobals (`$_GET`, `$_POST`, `$_SERVER`, etc.), similar to Java's Servlet API. Let's compare:

::: code-group

```php [PHP HttpFoundation]
<?php
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

// Type-safe access
$page = $request->query->getInt('page', 1);
$name = $request->request->get('name');
$file = $request->files->get('upload');

// Headers
$contentType = $request->headers->get('Content-Type');
```

```java [Java Servlet API]
import javax.servlet.http.HttpServletRequest;

// Servlet request (similar abstraction)
int page = Integer.parseInt(
    request.getParameter("page") != null
        ? request.getParameter("page")
        : "1"
);
String name = request.getParameter("name");
Part file = request.getPart("upload");

// Headers
String contentType = request.getHeader("Content-Type");
```

:::

**Key Benefits:**

- **Normalizes data**: Consistent API regardless of request method
- **Type safety**: Methods like `getInt()`, `getBoolean()` for type conversion
- **Immutable**: Request objects are immutable, preventing accidental modification
- **PSR-7 compatible**: Can be converted to PSR-7 objects if needed

### Troubleshooting

- **"Request::createFromGlobals() not working"** — Ensure you're running via web server, not CLI. For CLI testing, create requests manually: `Request::create('/api/users', 'GET')`
- **"Headers not set"** — Headers must be set before calling `send()`. Use `$response->headers->set()` before sending
- **"JSON response shows HTML"** — Ensure `Content-Type` header is set to `application/json` or use `JsonResponse` class

---

## Step 3: Console Component (~20 min)

### Goal

Build command-line applications using Symfony Console component.

### Actions

1. **Install Console component**:

```bash
composer require symfony/console
```

2. **Create a basic command**:

```php
# filename: src/Command/GreetCommand.php
<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GreetCommand extends Command
{
    protected static $defaultName = 'app:greet';
    protected static $defaultDescription = 'Greets a user';

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the user')
            ->addOption('times', 't', InputOption::VALUE_OPTIONAL, 'Number of times to greet', 1)
            ->setHelp('This command greets a user by name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');
        $times = (int) $input->getOption('times');

        if ($times < 1) {
            $io->error('Times must be a positive integer');
            return Command::FAILURE;
        }

        for ($i = 0; $i < $times; $i++) {
            $io->success("Hello, {$name}!");
        }

        $io->note("Greeted {$name} {$times} time(s)");

        return Command::SUCCESS;
    }
}
```

3. **Create application entry point**:

```php
# filename: bin/console
#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Command\GreetCommand;

$application = new Application('My Console App', '1.0.0');
$application->add(new GreetCommand());
$application->run();
```

4. **Make it executable and run**:

```bash
# Make executable
chmod +x bin/console

# Run the command
php bin/console app:greet Alice --times=3

# Expected output:
# [OK] Hello, Alice!
# [OK] Hello, Alice!
# [OK] Hello, Alice!
#
# [NOTE] Greeted Alice 3 time(s)
```

5. **Advanced command with table output**:

```php
# filename: src/Command/ListUsersCommand.php
<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ListUsersCommand extends Command
{
    protected static $defaultName = 'app:users:list';
    protected static $defaultDescription = 'List all users';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Simulate user data
        $users = [
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
            ['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com'],
        ];

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Email']);

        foreach ($users as $user) {
            $table->addRow([$user['id'], $user['name'], $user['email']]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
```

### Expected Result

You can create and run command-line applications with arguments, options, styled output, and tables.

### Why It Works

Symfony Console provides a structured way to build CLI applications, similar to Java's Apache Commons CLI or Spring Shell. Compare the approaches:

::: code-group

```php [PHP Symfony Console]
<?php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->addOption('times', 't', InputOption::VALUE_OPTIONAL, '', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $times = (int) $input->getOption('times');

        for ($i = 0; $i < $times; $i++) {
            $output->writeln("Hello, {$name}!");
        }

        return Command::SUCCESS;
    }
}
```

```java [Java Apache Commons CLI]
import org.apache.commons.cli.*;

public class GreetCommand {
    public static void main(String[] args) {
        Options options = new Options();
        options.addOption("t", "times", true, "Number of times");
        options.addOption(Option.builder("n")
            .longOpt("name")
            .required()
            .hasArg()
            .build());

        CommandLineParser parser = new DefaultParser();
        try {
            CommandLine cmd = parser.parse(options, args);
            String name = cmd.getOptionValue("name");
            int times = Integer.parseInt(
                cmd.getOptionValue("times", "1")
            );

            for (int i = 0; i < times; i++) {
                System.out.println("Hello, " + name + "!");
            }
        } catch (ParseException e) {
            System.err.println(e.getMessage());
        }
    }
}
```

:::

**Key Features:**

- **Argument parsing**: Automatic parsing of command-line arguments
- **Option handling**: Short and long options with default values
- **Output formatting**: Styled output, tables, progress bars
- **Help generation**: Automatic `--help` command generation
- **Exit codes**: Proper exit codes for success/failure

### Troubleshooting

- **"Command not found"** — Ensure command is registered: `$application->add(new YourCommand())`
- **"Argument required"** — Check that required arguments are provided or marked as optional
- **"Permission denied"** — Make script executable: `chmod +x bin/console`
- **"Class not found"** — Verify autoloading and namespace usage

---

## Step 4: EventDispatcher Component (~20 min)

### Goal

Implement event-driven architecture using EventDispatcher.

### Actions

1. **Install EventDispatcher**:

```bash
composer require symfony/event-dispatcher
```

2. **Create custom event class**:

```php
# filename: src/Event/UserRegisteredEvent.php
<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    public function __construct(
        private readonly int $userId,
        private readonly string $email,
        private readonly string $name
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```

3. **Create event listeners**:

```php
# filename: src/EventListener/SendWelcomeEmailListener.php
<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\UserRegisteredEvent;

class SendWelcomeEmailListener
{
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        echo sprintf(
            "Sending welcome email to %s (%s)\n",
            $event->getName(),
            $event->getEmail()
        );
        // In real app: $this->mailer->send(...)
    }
}
```

```php
# filename: src/EventListener/LogUserRegistrationListener.php
<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\UserRegisteredEvent;

class LogUserRegistrationListener
{
    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        echo sprintf(
            "Logging: User %d (%s) registered at %s\n",
            $event->getUserId(),
            $event->getEmail(),
            date('Y-m-d H:i:s')
        );
        // In real app: $this->logger->info(...)
    }
}
```

4. **Register listeners and dispatch events**:

```php
# filename: examples/event-dispatcher-example.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Event\UserRegisteredEvent;
use App\EventListener\SendWelcomeEmailListener;
use App\EventListener\LogUserRegistrationListener;

// Create dispatcher
$dispatcher = new EventDispatcher();

// Register listeners
$dispatcher->addListener(
    UserRegisteredEvent::class,
    [new SendWelcomeEmailListener(), 'onUserRegistered']
);

$dispatcher->addListener(
    UserRegisteredEvent::class,
    [new LogUserRegistrationListener(), 'onUserRegistered']
);

// Dispatch event
$event = new UserRegisteredEvent(
    userId: 123,
    email: 'alice@example.com',
    name: 'Alice'
);

$dispatcher->dispatch($event);

// Expected output:
// Sending welcome email to Alice (alice@example.com)
// Logging: User 123 (alice@example.com) registered at 2024-01-15 10:30:00
```

5. **Use event subscribers** (alternative approach):

```php
# filename: src/EventSubscriber/UserEventSubscriber.php
<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => [
                ['onUserRegistered', 10], // Priority: 10
                ['sendNotification', 5],  // Priority: 5 (runs after)
            ],
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        echo "User registered: {$event->getName()}\n";
    }

    public function sendNotification(UserRegisteredEvent $event): void
    {
        echo "Sending notification to {$event->getEmail()}\n";
    }
}

// Register subscriber
$dispatcher->addSubscriber(new UserEventSubscriber());
```

### Expected Result

You can dispatch events and have multiple listeners respond to them, implementing the observer pattern.

### Why It Works

EventDispatcher implements the **Observer Pattern**, similar to Java's `java.util.Observer` or Spring's event system. Compare implementations:

::: code-group

```php [PHP EventDispatcher]
<?php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

class UserRegisteredEvent extends Event
{
    public function __construct(
        private readonly int $userId,
        private readonly string $email
    ) {}
}

$dispatcher = new EventDispatcher();
$dispatcher->addListener(
    UserRegisteredEvent::class,
    function (UserRegisteredEvent $event) {
        echo "User {$event->userId} registered\n";
    }
);

$event = new UserRegisteredEvent(123, 'alice@example.com');
$dispatcher->dispatch($event);
```

```java [Java Spring Events]
import org.springframework.context.ApplicationEventPublisher;
import org.springframework.context.ApplicationListener;

public class UserRegisteredEvent extends ApplicationEvent {
    private final int userId;
    private final String email;

    public UserRegisteredEvent(Object source, int userId, String email) {
        super(source);
        this.userId = userId;
        this.email = email;
    }
}

@Component
public class UserService {
    @Autowired
    private ApplicationEventPublisher eventPublisher;

    public void registerUser(String email) {
        // ... registration logic
        eventPublisher.publishEvent(
            new UserRegisteredEvent(this, 123, email)
        );
    }
}

@Component
public class EmailListener implements ApplicationListener<UserRegisteredEvent> {
    @Override
    public void onApplicationEvent(UserRegisteredEvent event) {
        System.out.println("User " + event.getUserId() + " registered");
    }
}
```

:::

**Key Benefits:**

- **Decoupling**: Event publishers don't know about listeners
- **Extensibility**: Add new listeners without modifying existing code
- **Priority**: Control listener execution order
- **Type safety**: Use typed event classes instead of string event names

### Troubleshooting

- **"Listener not called"** — Verify listener is registered before event is dispatched
- **"Wrong execution order"** — Use priorities: higher numbers execute first
- **"Event not found"** — Ensure event class name matches exactly

---

## Step 5: DependencyInjection Component (~25 min)

### Goal

Manage object dependencies using Symfony's DependencyInjection container.

### Actions

1. **Install DependencyInjection**:

```bash
composer require symfony/dependency-injection
```

2. **Create service classes**:

```php
# filename: src/Service/Mailer.php
<?php

declare(strict_types=1);

namespace App\Service;

class Mailer
{
    public function send(string $to, string $subject, string $body): void
    {
        echo "Sending email to {$to}: {$subject}\n";
        // In real app: mail($to, $subject, $body)
    }
}
```

```php
# filename: src/Service/UserService.php
<?php

declare(strict_types=1);

namespace App\Service;

class UserService
{
    public function __construct(
        private readonly Mailer $mailer
    ) {
    }

    public function registerUser(string $email, string $name): int
    {
        $userId = 123; // Simulate user creation

        // Use injected mailer
        $this->mailer->send(
            $email,
            'Welcome!',
            "Hello {$name}, welcome to our service!"
        );

        return $userId;
    }
}
```

3. **Configure container using PHP**:

```php
# filename: examples/dependency-injection-php.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use App\Service\Mailer;
use App\Service\UserService;

// Create container
$container = new ContainerBuilder();

// Register Mailer service
$container->register('mailer', Mailer::class);

// Register UserService with Mailer dependency
$container->register('user_service', UserService::class)
    ->addArgument(new Reference('mailer'));

// Compile container (optimizes for production)
$container->compile();

// Get service from container
$userService = $container->get('user_service');
$userId = $userService->registerUser('alice@example.com', 'Alice');

// Expected output:
// Sending email to alice@example.com: Welcome!
// Hello Alice, welcome to our service!
```

4. **Install Config component** (required for YAML loading):

```bash
composer require symfony/config symfony/yaml
```

5. **Configure container using YAML** (more common):

```yaml
# filename: config/services.yaml
services:
  # Default configuration for all services
  _defaults:
    autowire: true
    autoconfigure: true

  # Service definitions
  App\Service\Mailer:
    # No arguments needed - autowiring handles it

  App\Service\UserService:
    arguments:
      $mailer: '@App\Service\Mailer'
```

```php
# filename: examples/dependency-injection-yaml.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');

$container->compile();

$userService = $container->get('App\Service\UserService');
```

6. **Use autowiring** (automatic dependency resolution):

```php
# filename: examples/dependency-injection-autowire.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');

// Enable autowiring
$container->setParameter('container.autowiring.strict_mode', true);

$container->compile();

// Container automatically resolves Mailer dependency
$userService = $container->get('App\Service\UserService');
```

### Expected Result

You can manage object dependencies through a container, avoiding manual instantiation and improving testability.

### Why It Works

DependencyInjection implements the **Inversion of Control** pattern, similar to Spring's IoC container. Compare the approaches:

::: code-group

```php [PHP DependencyInjection]
<?php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();
$container->register('mailer', Mailer::class);
$container->register('user_service', UserService::class)
    ->addArgument(new Reference('mailer'));

$container->compile();
$userService = $container->get('user_service');
```

```java [Java Spring IoC]
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class AppConfig {
    @Bean
    public Mailer mailer() {
        return new Mailer();
    }

    @Bean
    public UserService userService() {
        return new UserService(mailer());
    }
}

// Or with autowiring
@Service
public class UserService {
    private final Mailer mailer;

    @Autowired
    public UserService(Mailer mailer) {
        this.mailer = mailer;
    }
}
```

:::

**Key Benefits:**

- **Manages lifecycle**: Container creates and manages service instances
- **Resolves dependencies**: Automatically injects required dependencies
- **Improves testability**: Easy to swap implementations for testing
- **Reduces coupling**: Classes don't create their own dependencies

### Troubleshooting

- **"Service not found"** — Ensure service is registered before calling `get()`
- **"Circular dependency"** — Two services depend on each other. Use lazy loading or refactor
- **"Autowiring failed"** — Check that all dependencies are registered or use manual configuration

---

## Step 6: Routing Component (~15 min)

### Goal

Use Routing component to match URLs and generate URLs.

### Actions

1. **Install Routing**:

```bash
composer require symfony/routing
```

2. **Define routes**:

```php
# filename: examples/routing-example.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;

// Create route collection
$routes = new RouteCollection();

// Add routes
$routes->add('home', new Route('/', [
    '_controller' => 'HomeController::index',
]));

$routes->add('user_show', new Route('/users/{id}', [
    '_controller' => 'UserController::show',
    'id' => '\d+', // Require numeric ID
]));

$routes->add('user_edit', new Route('/users/{id}/edit', [
    '_controller' => 'UserController::edit',
    'id' => '\d+',
]));

// Create matcher
$context = new RequestContext();
$matcher = new UrlMatcher($routes, $context);

// Match URL
$pathInfo = '/users/123';
$parameters = $matcher->match($pathInfo);

print_r($parameters);
// Output:
// Array
// (
//     [id] => 123
//     [_controller] => UserController::show
//     [_route] => user_show
// )

// Generate URL
$generator = new UrlGenerator($routes, $context);
$url = $generator->generate('user_edit', ['id' => 456]);
echo $url; // Output: /users/456/edit
```

3. **Load routes from YAML**:

```yaml
# filename: config/routes.yaml
home:
  path: /
  controller: HomeController::index

user_show:
  path: /users/{id}
  controller: UserController::show
  requirements:
    id: '\d+'

user_edit:
  path: /users/{id}/edit
  controller: UserController::edit
  requirements:
    id: '\d+'
```

```php
# filename: examples/routing-yaml.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Router;

$locator = new FileLocator([__DIR__ . '/../config']);
$loader = new YamlFileLoader($locator);

$router = new Router(
    $loader,
    'routes.yaml',
    ['cache_dir' => __DIR__ . '/../var/cache']
);

$routes = $router->getRouteCollection();
```

### Expected Result

You can match URLs to routes and generate URLs from route names, similar to Spring MVC routing.

### Why It Works

Routing component separates URL matching logic from controllers, similar to Spring MVC routing. Compare:

::: code-group

```php [PHP Symfony Routing]
<?php
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('user_show', new Route('/users/{id}', [
    '_controller' => 'UserController::show',
    'id' => '\d+',
]));

$matcher = new UrlMatcher($routes, $context);
$params = $matcher->match('/users/123');
// ['id' => '123', '_controller' => 'UserController::show']
```

```java [Java Spring MVC]
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/users")
public class UserController {
    @GetMapping("/{id}")
    public User show(@PathVariable("id") @Pattern(regexp = "\\d+") int id) {
        return userService.findById(id);
    }
}

// Route matching handled by Spring's DispatcherServlet
// URL: /users/123 → matches @GetMapping("/{id}")
```

:::

**Key Features:**

- **Flexible matching**: Support for patterns, requirements, defaults
- **URL generation**: Generate URLs from route names (avoids hardcoding)
- **Caching**: Routes can be cached for performance
- **PSR compatibility**: Works with PSR-7 requests

### Troubleshooting

- **"Route not found"** — Check route path matches exactly (including leading/trailing slashes)
- **"Parameter missing"** — Ensure all required parameters are provided when generating URLs
- **"Requirements not met"** — Verify parameter values match route requirements (e.g., `\d+` for numeric)

---

## Step 7: Combining Components (~20 min)

### Goal

Build a complete application using multiple Symfony Components together.

### Actions

1. **Create a complete application structure**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Event\UserRegisteredEvent;
use App\Service\UserService;
use App\Service\Mailer;

// Bootstrap
$dispatcher = new EventDispatcher();
$mailer = new Mailer();
$userService = new UserService($mailer);

// Register event listener
$dispatcher->addListener(
    UserRegisteredEvent::class,
    function (UserRegisteredEvent $event) use ($mailer) {
        $mailer->send(
            $event->getEmail(),
            'Welcome!',
            "Hello {$event->getName()}, welcome!"
        );
    }
);

// Simple routing (in production, use Routing component)
$request = Request::createFromGlobals();
$path = $request->getPathInfo();
$method = $request->getMethod();

try {
    if ($path === '/api/users' && $method === 'GET') {
        $response = new JsonResponse([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);
    } elseif ($path === '/api/users' && $method === 'POST') {
        $data = json_decode($request->getContent(), true);

        // Create user (simulated)
        $userId = $userService->registerUser(
            $data['email'] ?? '',
            $data['name'] ?? ''
        );

        // Dispatch event
        $event = new UserRegisteredEvent(
            userId: $userId,
            email: $data['email'] ?? '',
            name: $data['name'] ?? ''
        );
        $dispatcher->dispatch($event);

        $response = new JsonResponse([
            'id' => $userId,
            'message' => 'User created',
        ], Response::HTTP_CREATED);
    } else {
        $response = new Response('Not Found', Response::HTTP_NOT_FOUND);
    }
} catch (\Exception $e) {
    $response = new JsonResponse([
        'error' => $e->getMessage(),
    ], Response::HTTP_INTERNAL_SERVER_ERROR);
}

$response->send();
```

2. **Test the application**:

```bash
# Start PHP built-in server
php -S localhost:8000 -t public

# In another terminal, test endpoints:
curl http://localhost:8000/api/users
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com"}'
```

### Expected Result

A working application that combines HttpFoundation, EventDispatcher, and DependencyInjection to handle HTTP requests, dispatch events, and manage dependencies.

### Why It Works

Symfony Components are designed to work together seamlessly:

- **HttpFoundation** handles HTTP layer
- **EventDispatcher** provides decoupled event handling
- **DependencyInjection** manages service dependencies
- **Routing** (when added) handles URL matching

This modular approach lets you build exactly what you need without a full framework.

### Troubleshooting

- **"Components not working together"** — Ensure all components are installed and autoloaded
- **"Event not dispatched"** — Verify listener is registered before dispatch
- **"Service not found"** — Check dependency injection setup

---

## Step 8: Symfony Flex & Project Setup (~15 min)

### Goal

Use Symfony Flex to bootstrap component-based projects efficiently.

### Actions

1. **Install Symfony Flex**:

```bash
# Create new Symfony skeleton project
composer create-project symfony/skeleton my-project
cd my-project

# Flex automatically configures components
```

2. **Understand Flex recipes**:

Symfony Flex automatically configures components when you install them:

```bash
# Install HttpFoundation - Flex auto-configures it
composer require symfony/http-foundation

# Install Console - Flex creates bin/console automatically
composer require symfony/console

# Install EventDispatcher - Flex sets up service configuration
composer require symfony/event-dispatcher
```

3. **Manual setup vs Flex**:

::: code-group

```php [PHP Manual Setup]
<?php
// Without Flex - manual bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$response = new Response('Hello');
$response->send();
```

```php [PHP With Flex]
<?php
// With Flex - pre-configured kernel
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
```

:::

### Why It Works

Symfony Flex is a Composer plugin that:

- **Auto-configures** components when installed
- **Creates directory structure** automatically
- **Sets up service configuration** in `config/services.yaml`
- **Generates boilerplate** code (like `bin/console`)
- **Manages environment variables** via `.env` files

Similar to Spring Boot's auto-configuration, Flex reduces setup time.

### Troubleshooting

- **"Flex not working"** — Ensure you're using Composer 2.0+ and have `symfony/flex` installed globally: `composer global require symfony/flex`
- **"Recipes not applying"** — Check `.symfony.lock` file exists and recipes are enabled

---

## Step 9: Advanced Component Features (~25 min)

### Goal

Explore advanced features of Symfony Components for production use.

### Actions

1. **HttpFoundation: File Uploads & Sessions**:

```php
# filename: examples/http-foundation-advanced.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

$request = Request::createFromGlobals();

// Handle file uploads
$file = $request->files->get('upload');
if ($file instanceof UploadedFile && $file->isValid()) {
    $file->move('uploads/', $file->getClientOriginalName());
    echo "File uploaded: {$file->getClientOriginalName()}\n";
}

// Session management
$session = new Session(new NativeSessionStorage());
$session->start();

// Store data in session
$session->set('user_id', 123);
$session->set('username', 'alice');

// Retrieve from session
$userId = $session->get('user_id');
echo "User ID: {$userId}\n";
```

2. **Console: Progress Bars & Interactive Questions**:

```php
# filename: src/Command/ImportCommand.php
<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ImportCommand extends Command
{
    protected static $defaultName = 'app:import';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Interactive question
        $question = new Question('Enter filename: ', 'data.csv');
        $filename = $helper->ask($input, $output, $question);

        // Choice question
        $choice = new ChoiceQuestion(
            'Select format:',
            ['csv', 'json', 'xml'],
            0
        );
        $format = $helper->ask($input, $output, $choice);

        // Progress bar
        $items = range(1, 100);
        $progressBar = new ProgressBar($output, count($items));
        $progressBar->start();

        foreach ($items as $item) {
            // Simulate work
            usleep(10000);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nImport complete!");

        return Command::SUCCESS;
    }
}
```

3. **EventDispatcher: Async Events & Event Subscribers**:

```php
# filename: examples/event-dispatcher-advanced.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\UserRegisteredEvent;

// Event subscriber with priorities
class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => [
                ['onUserRegistered', 10], // High priority
                ['sendWelcomeEmail', 5],   // Medium priority
                ['updateCache', 0],        // Low priority
            ],
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        echo "[Priority 10] Logging user registration\n";
    }

    public function sendWelcomeEmail(UserRegisteredEvent $event): void
    {
        echo "[Priority 5] Sending welcome email\n";
    }

    public function updateCache(UserRegisteredEvent $event): void
    {
        echo "[Priority 0] Updating cache\n";
    }
}

$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new UserEventSubscriber());

$event = new UserRegisteredEvent(123, 'alice@example.com', 'Alice');
$dispatcher->dispatch($event);

// Output (in priority order):
// [Priority 10] Logging user registration
// [Priority 5] Sending welcome email
// [Priority 0] Updating cache
```

4. **DependencyInjection: Tagged Services & Factories**:

```php
# filename: examples/dependency-injection-advanced.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

$container = new ContainerBuilder();

// Factory service
$container->register('mailer.factory', MailerFactory::class);
$container->register('mailer', Mailer::class)
    ->setFactory([new Reference('mailer.factory'), 'create'])
    ->addArgument('smtp');

// Tagged services (for collecting multiple services)
$container->register('logger.file', FileLogger::class)
    ->addTag('logger');
$container->register('logger.console', ConsoleLogger::class)
    ->addTag('logger');

// Find all tagged services
$loggers = $container->findTaggedServiceIds('logger');
foreach ($loggers as $id => $tags) {
    echo "Found logger: {$id}\n";
}
```

### Expected Result

You can use advanced features like file uploads, sessions, progress bars, interactive questions, event priorities, and service factories.

### Why It Works

Symfony Components provide production-ready features:

- **File handling**: Secure file upload validation and storage
- **Session management**: PSR-7 compatible session handling
- **CLI enhancements**: Progress bars, tables, interactive prompts
- **Event priorities**: Control listener execution order
- **Service factories**: Create services dynamically

### Troubleshooting

- **"File upload fails"** — Check `upload_max_filesize` and `post_max_size` in `php.ini`
- **"Session not working"** — Ensure session storage is properly configured
- **"Progress bar not showing"** — Use `ProgressBar` with proper output interface

---

## Step 10: Component Integration Patterns (~20 min)

### Goal

Learn how to integrate components together using common architectural patterns.

### Actions

1. **Kernel Pattern** (Application Bootstrap):

```php
# filename: src/Kernel.php
<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Kernel
{
    private EventDispatcher $dispatcher;
    private ContainerBuilder $container;
    private UrlMatcher $matcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
        $this->container = new ContainerBuilder();
        $this->matcher = new UrlMatcher(/* routes */, new RequestContext());
    }

    public function handle(Request $request): Response
    {
        try {
            // Match route
            $parameters = $this->matcher->match($request->getPathInfo());

            // Get controller from container
            $controller = $this->container->get($parameters['_controller']);

            // Execute controller
            $response = $controller($request);

            // Dispatch response event
            $this->dispatcher->dispatch(new ResponseEvent($response), 'kernel.response');

            return $response;
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }
}
```

2. **Middleware Pipeline Pattern**:

```php
# filename: src/Middleware/Pipeline.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Pipeline
{
    /** @var callable[] */
    private array $middlewares = [];

    public function add(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function handle(Request $request): Response
    {
        $stack = array_reverse($this->middlewares);

        $next = function (Request $request) use (&$stack, &$next) {
            if (empty($stack)) {
                return new Response('Not Found', 404);
            }

            $middleware = array_pop($stack);
            return $middleware($request, $next);
        };

        return $next($request);
    }
}

// Usage
$pipeline = new Pipeline();
$pipeline
    ->add(function (Request $request, callable $next) {
        // Authentication middleware
        if (!$request->headers->has('Authorization')) {
            return new Response('Unauthorized', 401);
        }
        return $next($request);
    })
    ->add(function (Request $request, callable $next) {
        // Logging middleware
        echo "Request: {$request->getMethod()} {$request->getPathInfo()}\n";
        $response = $next($request);
        echo "Response: {$response->getStatusCode()}\n";
        return $response;
    });

$response = $pipeline->handle($request);
```

3. **Command Bus Pattern** (using EventDispatcher):

```php
# filename: src/CommandBus.php
<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\EventDispatcher\EventDispatcher;

class CommandBus
{
    public function __construct(
        private readonly EventDispatcher $dispatcher
    ) {
    }

    public function dispatch(object $command): void
    {
        $eventName = get_class($command);
        $this->dispatcher->dispatch($command, $eventName);
    }
}

// Usage
class CreateUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $name
    ) {
    }
}

$commandBus = new CommandBus($dispatcher);
$commandBus->dispatch(new CreateUserCommand('alice@example.com', 'Alice'));
```

### Expected Result

You can integrate components using kernel, middleware pipeline, and command bus patterns.

### Why It Works

These patterns provide:

- **Separation of concerns**: Each component handles its responsibility
- **Testability**: Easy to test components in isolation
- **Flexibility**: Swap implementations without changing core logic
- **Scalability**: Add new features without modifying existing code

### Troubleshooting

- **"Kernel not found"** — Ensure Kernel class is properly autoloaded
- **"Middleware not executing"** — Check middleware order in pipeline
- **"Command not handled"** — Verify event listener is registered for command class

---

## Step 11: Performance & Caching (~15 min)

### Goal

Optimize Symfony Components for production performance.

### Actions

1. **Cache DI Container**:

```php
# filename: examples/performance-di-cache.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
$loader->load('services.yaml');

// Compile container
$container->compile();

// Dump compiled container to PHP file (for production)
$dumper = new PhpDumper($container);
file_put_contents(
    __DIR__ . '/../var/cache/container.php',
    $dumper->dump(['class' => 'CachedContainer'])
);

// In production, load cached container:
// require_once __DIR__ . '/var/cache/container.php';
// $container = new CachedContainer();
```

2. **Cache Routes**:

```php
# filename: examples/performance-route-cache.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

$router = new Router(
    new YamlFileLoader(new FileLocator([__DIR__ . '/../config'])),
    'routes.yaml',
    [
        'cache_dir' => __DIR__ . '/../var/cache',
        'debug' => false, // Set to false in production
    ]
);

// Routes are automatically cached
$routes = $router->getRouteCollection();
```

3. **Environment Configuration**:

```bash
# filename: .env
APP_ENV=prod
APP_DEBUG=false

# Cache configuration
CACHE_DIR=var/cache
ROUTE_CACHE_ENABLED=true
```

```php
# filename: examples/performance-config.php
<?php

declare(strict_types=1);

$env = $_ENV['APP_ENV'] ?? 'dev';
$debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($env === 'prod' && !$debug) {
    // Use cached container
    require_once __DIR__ . '/../var/cache/container.php';
    $container = new CachedContainer();
} else {
    // Build container from scratch (development)
    $container = new ContainerBuilder();
    // ... build container
}
```

### Expected Result

Your application uses caching for improved performance in production.

### Why It Works

Caching provides:

- **Faster startup**: Pre-compiled containers load instantly
- **Reduced memory**: Compiled code uses less memory
- **Better performance**: No runtime compilation overhead
- **Production-ready**: Optimized for high-traffic applications

### Troubleshooting

- **"Cache not updating"** — Clear cache directory: `rm -rf var/cache/*`
- **"Cache errors"** — Ensure cache directory is writable: `chmod -R 777 var/cache`
- **"Performance not improved"** — Verify `APP_ENV=prod` and caching is enabled

---

## Step 12: Testing Symfony Components (~20 min)

### Goal

Write tests for applications using Symfony Components.

### Actions

1. **Install PHPUnit**:

```bash
composer require --dev phpunit/phpunit
```

2. **Test HttpFoundation**:

```php
# filename: tests/Http/RequestHandlerTest.php
<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class RequestHandlerTest extends TestCase
{
    public function testHandlesGetRequest(): void
    {
        $request = Request::create('/api/users', 'GET');
        $handler = new RequestHandler();

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testHandlesPostRequest(): void
    {
        $request = Request::create(
            '/api/users',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Alice', 'email' => 'alice@example.com'])
        );

        $handler = new RequestHandler();
        $response = $handler->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
    }
}
```

3. **Test Console Commands**:

```php
# filename: tests/Command/GreetCommandTest.php
<?php

declare(strict_types=1);

namespace Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\GreetCommand;

class GreetCommandTest extends TestCase
{
    public function testGreetCommand(): void
    {
        $application = new Application();
        $application->add(new GreetCommand());

        $command = $application->find('app:greet');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name' => 'Alice',
            '--times' => 2,
        ]);

        $this->assertEquals(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Hello, Alice!', $commandTester->getDisplay());
    }
}
```

4. **Test EventDispatcher**:

```php
# filename: tests/Event/EventDispatcherTest.php
<?php

declare(strict_types=1);

namespace Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Event\UserRegisteredEvent;

class EventDispatcherTest extends TestCase
{
    public function testEventIsDispatched(): void
    {
        $dispatcher = new EventDispatcher();
        $called = false;

        $dispatcher->addListener(
            UserRegisteredEvent::class,
            function (UserRegisteredEvent $event) use (&$called) {
                $called = true;
                $this->assertEquals(123, $event->getUserId());
            }
        );

        $event = new UserRegisteredEvent(123, 'alice@example.com', 'Alice');
        $dispatcher->dispatch($event);

        $this->assertTrue($called);
    }
}
```

5. **Test Dependency Injection**:

```php
# filename: tests/Service/UserServiceTest.php
<?php

declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Service\UserService;
use App\Service\Mailer;

class UserServiceTest extends TestCase
{
    public function testUserServiceWithMockMailer(): void
    {
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with('alice@example.com', 'Welcome!', $this->anything());

        $userService = new UserService($mailer);
        $userId = $userService->registerUser('alice@example.com', 'Alice');

        $this->assertIsInt($userId);
    }
}
```

### Expected Result

You can write comprehensive tests for all Symfony Components.

### Why It Works

Testing strategies:

- **Unit tests**: Test individual components in isolation
- **Integration tests**: Test component interactions
- **Mock objects**: Replace dependencies for isolated testing
- **Command testing**: Test CLI commands with CommandTester

### Troubleshooting

- **"PHPUnit not found"** — Install as dev dependency: `composer require --dev phpunit/phpunit`
- **"Tests not running"** — Check `phpunit.xml` configuration
- **"Mock not working"** — Ensure you're using PHPUnit's mock builder correctly

---

## Step 13: Deployment & DevOps (~15 min)

### Goal

Deploy Symfony Components applications to production.

### Actions

1. **Docker Setup**:

```dockerfile
# filename: Dockerfile
FROM php:8.4-fpm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy application
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

```yaml
# filename: docker-compose.yml
version: "3.8"

services:
  app:
    build: .
    volumes:
      - .:/var/www
    ports:
      - "8000:9000"

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - .:/var/www
    depends_on:
      - app
```

2. **Environment Configuration**:

```bash
# filename: .env.prod
APP_ENV=prod
APP_DEBUG=false

# Database
DATABASE_URL=mysql://user:password@db:3306/dbname

# Cache
CACHE_DIR=/var/www/var/cache
```

3. **Production Bootstrap**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    (new Dotenv())->load(__DIR__ . '/../.env');
}

$env = $_ENV['APP_ENV'] ?? 'prod';
$debug = filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($env === 'prod') {
    // Use cached container
    require_once __DIR__ . '/../var/cache/container.php';
    $container = new CachedContainer();
} else {
    // Development: build container
    $container = new ContainerBuilder();
    // ... build container
}

// Handle request
$request = Request::createFromGlobals();
$response = $container->get('kernel')->handle($request);
$response->send();
```

4. **Nginx Configuration**:

```nginx
# filename: nginx.conf
server {
    listen 80;
    server_name localhost;
    root /var/www/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Expected Result

Your application is ready for production deployment with Docker and Nginx.

### Why It Works

Production setup includes:

- **Containerization**: Docker for consistent environments
- **Web server**: Nginx for high-performance HTTP handling
- **Environment variables**: Secure configuration management
- **Caching**: Optimized performance with compiled containers

### Troubleshooting

- **"Docker build fails"** — Check Dockerfile syntax and base image
- **"Nginx 502 error"** — Verify PHP-FPM is running and accessible
- **"Environment variables not loading"** — Ensure `.env` file exists and is readable

---

## Step 14: Error Handling & Logging (~15 min)

### Goal

Implement proper error handling and logging with Symfony Components.

### Actions

1. **Install Monolog**:

```bash
composer require monolog/monolog
```

2. **Error Handling Middleware**:

```php
# filename: src/Middleware/ErrorHandler.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class ErrorHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            $this->logger->error('Unhandled exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => [
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri(),
                ],
            ]);

            if ($request->headers->get('Accept') === 'application/json') {
                return new JsonResponse([
                    'error' => 'Internal Server Error',
                    'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'An error occurred',
                ], 500);
            }

            return new Response('Internal Server Error', 500);
        }
    }
}
```

3. **Structured Logging**:

```php
# filename: examples/logging-setup.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;

// Create logger
$logger = new Logger('app');

// Console handler (development)
if ($_ENV['APP_ENV'] === 'dev') {
    $consoleHandler = new StreamHandler('php://stdout', Logger::DEBUG);
    $logger->pushHandler($consoleHandler);
}

// File handler (production)
$fileHandler = new RotatingFileHandler(
    __DIR__ . '/../var/log/app.log',
    7, // Keep 7 days of logs
    Logger::INFO
);
$fileHandler->setFormatter(new JsonFormatter());
$logger->pushHandler($fileHandler);

// Usage
$logger->info('User registered', [
    'user_id' => 123,
    'email' => 'alice@example.com',
]);

$logger->error('Database connection failed', [
    'host' => 'localhost',
    'database' => 'mydb',
]);
```

4. **Event-Based Logging**:

```php
# filename: src/EventListener/ExceptionListener.php
<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $this->logger->error('Exception occurred', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_uri' => $request->getUri(),
            'request_method' => $request->getMethod(),
        ]);
    }
}
```

### Expected Result

Your application has comprehensive error handling and structured logging.

### Why It Works

Error handling and logging provide:

- **Debugging**: Detailed error logs help identify issues
- **Monitoring**: Track application behavior in production
- **Security**: Don't expose sensitive information in error messages
- **Compliance**: Audit trails for important operations

### Troubleshooting

- **"Logs not writing"** — Check file permissions: `chmod -R 777 var/log`
- **"Too many log files"** — Configure log rotation in Monolog handlers
- **"Errors not caught"** — Ensure error handler middleware is first in pipeline

---

## Exercises

### Exercise 1: Build a Simple API

**Goal**: Create a REST API using HttpFoundation

Create a file called `public/api.php` that handles:

- `GET /api/products` — Returns list of products (JSON)
- `GET /api/products/{id}` — Returns single product (extract ID from path)
- `POST /api/products` — Creates a new product (validate JSON input)
- `DELETE /api/products/{id}` — Deletes a product

**Requirements:**

- Use `Request::createFromGlobals()` to get the request
- Parse path info to extract product ID
- Validate JSON input for POST requests
- Return appropriate HTTP status codes (200, 201, 404)
- Use `JsonResponse` for all responses

**Validation**: Test with curl:

```bash
# Start server
php -S localhost:8000 -t public

# Test endpoints
curl http://localhost:8000/api/products
curl http://localhost:8000/api/products/1
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Widget","price":19.99}'
curl -X DELETE http://localhost:8000/api/products/1
```

### Exercise 2: Create a Console Command

**Goal**: Build a CLI tool using Console component

Create a command `app:user:create` that:

- Takes `name` and `email` as required arguments
- Has optional `--admin` flag (boolean)
- Validates email format using `filter_var($email, FILTER_VALIDATE_EMAIL)`
- Outputs success message with user details using `SymfonyStyle`
- Returns `Command::FAILURE` if email is invalid

**Requirements:**

- Extend `Command` class
- Use `addArgument()` for name and email
- Use `addOption()` for admin flag
- Display formatted output with `SymfonyStyle`
- Show error message if validation fails

**Validation**: Run:

```bash
php bin/console app:user:create Alice alice@example.com
php bin/console app:user:create Bob bob@example.com --admin
php bin/console app:user:create Invalid invalid-email  # Should fail
```

### Exercise 3: Event-Driven User Management

**Goal**: Implement event system for user operations

Create events and listeners for:

- `UserCreatedEvent` — Contains userId, email, name
- `UserUpdatedEvent` — Contains userId, oldData, newData
- **Listeners:**
  - `SendEmailListener` — Sends welcome/update email
  - `LogActivityListener` — Logs to console/file
  - `UpdateCacheListener` — Simulates cache update

**Requirements:**

- Create event classes extending `Event`
- Create listener classes with appropriate methods
- Register listeners with EventDispatcher
- Dispatch events from a UserService
- Use priorities to control execution order (email first, then log, then cache)

**Validation**: Create/update users and verify all listeners execute in correct order:

```php
// Expected output when creating user:
// [Email] Sending welcome email to alice@example.com
// [Log] User 123 created at 2024-01-15 10:30:00
// [Cache] Updating cache for user 123
```

---

## Wrap-up

Congratulations! You've learned how to use Symfony Components as standalone libraries. Here's what you've accomplished:

✓ **Understood component architecture** — Modular, reusable libraries  
✓ **Used HttpFoundation** — Handled HTTP requests and responses  
✓ **Built CLI applications** — Created commands with Console component  
✓ **Implemented events** — Used EventDispatcher for decoupled architecture  
✓ **Managed dependencies** — Set up DependencyInjection container  
✓ **Used routing** — Matched URLs and generated route URLs  
✓ **Combined components** — Built a complete application  
✓ **Used Symfony Flex** — Bootstrapped projects efficiently  
✓ **Applied advanced features** — File uploads, sessions, progress bars, priorities  
✓ **Implemented patterns** — Kernel, middleware pipeline, command bus  
✓ **Optimized performance** — Caching and production configurations  
✓ **Wrote tests** — Comprehensive test coverage  
✓ **Deployed to production** — Docker and DevOps setup  
✓ **Handled errors** — Proper error handling and logging

**Key Takeaways:**

- Symfony Components are framework-agnostic and can be used independently
- Each component solves a specific problem following Single Responsibility Principle
- Components are composable — combine them to build custom solutions
- Similar to Java's Apache Commons and Spring components in philosophy

**When to Use Components vs Full Frameworks:**

| Use Case                  | Recommendation            | Reason                          |
| ------------------------- | ------------------------- | ------------------------------- |
| Microservices/APIs        | Components                | Lightweight, minimal overhead   |
| Full-stack web apps       | Symfony/Laravel Framework | Built-in features, conventions  |
| Legacy system integration | Components                | Can integrate without framework |
| Custom architecture       | Components                | Full control over structure     |
| Rapid prototyping         | Framework                 | Faster development              |
| Learning/experimentation  | Components                | Understand how things work      |
| Performance-critical      | Components                | Less abstraction overhead       |

**Next Steps:**

- Explore more components: Validator, Serializer, Security
- Consider using Symfony Framework for full-stack applications
- Learn about PSR standards that components follow
- Check out Laravel's use of Symfony Components

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="21"
  label="Mastered Symfony Components architecture!"
/>

## Further Reading

- [Symfony Components Documentation](https://symfony.com/components) — Official component documentation
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html) — Best practices for using Symfony
- [Dependency Injection in PHP](https://www.php.net/manual/en/language.oop5.decon.php) — PHP manual on constructors and dependency injection
- [Event-Driven Architecture](https://martinfowler.com/articles/201701-event-driven.html) — Martin Fowler on event-driven architecture
