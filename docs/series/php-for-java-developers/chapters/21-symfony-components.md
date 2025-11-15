---
title: "21: Symfony Components"
description: "HttpFoundation, Console, EventDispatcher, DependencyInjection"
series: "php-for-java-developers"
chapter: 21
order: 21
difficulty: "Advanced"
prerequisites:
  - "/series/php-for-java-developers/chapters/20-laravel-fundamentals"
---

# Chapter 21: Symfony Components

<Badge type="danger">Advanced</Badge>

## Overview

Symfony provides reusable PHP components used by many frameworks, similar to Apache Commons for Java.

**Topics:** HttpFoundation, Console, EventDispatcher, DependencyInjection, Routing

## Section 1: HttpFoundation

```php
<?php
use Symfony\Component\HttpFoundation\{Request, Response};

$request = Request::createFromGlobals();
$response = new Response('Hello World', 200);
$response->send();
```

## Section 2: Console Component

```php
<?php
use Symfony\Component\Console\Command\Command;

class GreetCommand extends Command {
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Hello!');
        return Command::SUCCESS;
    }
}
```

## Section 3: EventDispatcher

```php
<?php
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();
$dispatcher->addListener('user.created', function($event) {
    // Handle event
});
$dispatcher->dispatch($event, 'user.created');
```

## Section 4: DependencyInjection

```php
<?php
use Symfony\Component\DependencyInjection\ContainerBuilder;

$container = new ContainerBuilder();
$container->register('mailer', Mailer::class);
$mailer = $container->get('mailer');
```

## Section 5: Full Symfony Application

```php
<?php
// config/routes.yaml
users_index:
    path: /users
    controller: App\Controller\UserController::index

// src/Controller/UserController.php
class UserController extends AbstractController {
    #[Route('/users', methods: ['GET'])]
    public function index(UserRepository $users): Response {
        return $this->json($users->findAll());
    }
}
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/20-laravel-fundamentals">← Chapter 20</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/22-micro-frameworks-slim">Chapter 22 →</a></div>
</div>
