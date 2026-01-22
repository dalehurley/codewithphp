---
title: "13: Integration Testing"
description: "Database testing, API testing, test fixtures, CI/CD"
sidebar:
  label: "13: Integration Testing"
  order: 13
  badge:
    text: Intermediate
    variant: caution
---

![Integration Testing](/images/php-for-java-developers/chapter-13-integration-testing-hero-full.webp)

# Chapter 13: Integration Testing

<Badge type="warning">Intermediate</Badge>

## Overview

While unit tests verify individual components in isolation, integration tests ensure that different parts of your application work together correctly. Integration tests verify database interactions, API endpoints, external services, and the integration between multiple components. This chapter covers strategies and best practices for writing effective integration tests in PHP.

**What You'll Learn:**
- Differences between unit and integration tests
- Database testing strategies and patterns
- API endpoint testing with PHPUnit
- Test database setup and teardown
- Database seeders and factories
- Transaction rollback for test isolation
- In-memory databases for fast tests
- Testing with Docker containers
- HTTP client testing
- CI/CD pipeline integration
- Performance and load testing
- Database migration testing
- Cache integration testing with Redis
- Queue and background job testing
- File system and upload testing
- Email integration testing
- WebSocket and real-time testing
- Browser-based end-to-end testing
- Configuration testing across environments
- Rate limiting and throttling tests
- Best practices for maintainable integration tests

## Prerequisites

Before starting this chapter, you should be comfortable with:
- Unit testing with PHPUnit — [/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit](/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit)
- Database operations with PDO — [/series/php-for-java-developers/chapters/09-working-with-databases](/series/php-for-java-developers/chapters/09-working-with-databases)
- REST API development — [/series/php-for-java-developers/chapters/10-building-rest-apis](/series/php-for-java-developers/chapters/10-building-rest-apis)
- Dependency injection — [/series/php-for-java-developers/chapters/11-dependency-injection](/series/php-for-java-developers/chapters/11-dependency-injection)

**Estimated Time**: ~90-120 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A complete test database setup with proper isolation and cleanup
- Integration tests for repository methods (CRUD operations)
- API endpoint tests with authentication and validation
- Database seeders and factories for efficient test data creation
- Transactional test cases with automatic rollback
- In-memory SQLite test configuration for fast execution
- Mock HTTP clients for testing external API integrations
- Docker-based test environment configuration
- CI/CD pipeline integration for automated testing
- Performance and load testing suites
- Database migration testing framework
- Cache integration tests with Redis
- Queue and background job testing
- File system and file upload testing
- Email integration tests with mocking
- WebSocket and real-time feature testing
- Browser-based end-to-end tests
- Configuration testing for different environments
- Rate limiting and throttling tests
- A comprehensive understanding of integration testing best practices

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Distinguish** between unit and integration tests
2. **Set up test databases** with proper isolation
3. **Write database tests** that verify data persistence
4. **Test API endpoints** with HTTP requests
5. **Use database seeders** to create test data
6. **Implement transaction rollback** for test cleanup
7. **Configure in-memory databases** for fast tests
8. **Test external API integrations** with mocks
9. **Run tests in Docker** containers
10. **Integrate tests** into CI/CD pipelines
11. **Measure performance** and test application scalability
12. **Test database migrations** safely without data loss
13. **Test cache behavior** and invalidation strategies
14. **Test background jobs** and queue processing
15. **Test file uploads** and file system operations
16. **Test email sending** without sending real emails
17. **Test WebSocket connections** and real-time features
18. **Write end-to-end tests** for complete user workflows
19. **Test application configuration** across environments
20. **Test rate limiting** and API throttling

---

## Section 1: Unit vs Integration Tests

Understanding the difference helps you write the right tests.

### Test Pyramid

```
        /\
       /  \         E2E Tests (Few)
      /____\
     /      \
    / Integration\  Integration Tests (Some)
   /____________\
  /              \
 /   Unit Tests   \ Unit Tests (Many)
/__________________\
```

### Comparison

::: code-group

```php [Integration Test]
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Integration Test - Tests multiple components together
 * Uses real database, real dependencies
 */
class UserRegistrationTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        // Real database connection
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=test_db',
            'test_user',
            'test_password'
        );

        // Clean database
        $this->pdo->exec('DELETE FROM users');
    }

    public function testUserRegistrationCreatesUserInDatabase(): void
    {
        // Real dependencies
        $repository = new UserRepository($this->pdo);
        $emailService = new EmailService(); // Real email service
        $service = new UserService($repository, $emailService);

        // Execute real workflow
        $user = $service->registerUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        // Verify in real database
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(['john@example.com']);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotNull($dbUser);
        $this->assertEquals('John Doe', $dbUser['name']);
        $this->assertTrue(password_verify('secret123', $dbUser['password']));
    }
}
```

```php [Unit Test]
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit Test - Tests single component in isolation
 * Uses mocks, no real dependencies
 */
class UserServiceTest extends TestCase
{
    public function testRegisterUserCallsRepositoryCreate(): void
    {
        // Mock dependencies
        $repository = $this->createMock(UserRepository::class);
        $emailService = $this->createMock(EmailService::class);

        // Set expectations
        $repository
            ->expects($this->once())
            ->method('create')
            ->willReturn(new User(['id' => 1, 'name' => 'John']));

        $service = new UserService($repository, $emailService);

        // Test in isolation
        $user = $service->registerUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertEquals('John', $user->name);
    }
}
```

:::

| Aspect | Unit Tests | Integration Tests |
|--------|-----------|-------------------|
| Scope | Single class/method | Multiple components |
| Dependencies | Mocked | Real |
| Database | No | Yes |
| External APIs | Mocked | Real or sandboxed |
| Speed | Very fast (ms) | Slower (seconds) |
| Quantity | Many (100s-1000s) | Fewer (10s-100s) |
| Stability | Very stable | Can be flaky |
| When to run | Every change | Before commit/merge |

---

## Section 2: Database Test Setup

Setting up a test database is crucial for integration testing.

### Test Database Configuration

```php
# filename: tests/DatabaseTestCase.php
<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class DatabaseTestCase extends BaseTestCase
{
    protected static ?PDO $pdo = null;

    /**
     * Set up test database connection once for all tests
     */
    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO(
            $_ENV['TEST_DB_DSN'] ?? 'mysql:host=localhost;dbname=test_db',
            $_ENV['TEST_DB_USER'] ?? 'test_user',
            $_ENV['TEST_DB_PASS'] ?? 'test_password',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        self::migrateDatabase();
    }

    /**
     * Run database migrations
     */
    private static function migrateDatabase(): void
    {
        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        self::$pdo->exec($schema);
    }

    /**
     * Clean up database after each test
     */
    protected function tearDown(): void
    {
        $this->cleanDatabase();
    }

    /**
     * Remove all data from tables
     */
    protected function cleanDatabase(): void
    {
        self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        $tables = ['users', 'posts', 'comments'];
        foreach ($tables as $table) {
            self::$pdo->exec("TRUNCATE TABLE {$table}");
        }

        self::$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Get PDO instance
     */
    protected function getPdo(): PDO
    {
        return self::$pdo;
    }
}
```

### Environment-Specific Configuration

```php
# filename: tests/bootstrap.php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load test environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Override with test-specific values
$_ENV['DB_DATABASE'] = 'test_db';
$_ENV['DB_CONNECTION'] = 'mysql';
$_ENV['CACHE_DRIVER'] = 'array';
$_ENV['MAIL_DRIVER'] = 'log';

// Ensure we're in test environment
if ($_ENV['APP_ENV'] !== 'testing') {
    die('Tests must run in testing environment. Set APP_ENV=testing');
}
```

```ini
# .env.testing
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=test_db
DB_USERNAME=test_user
DB_PASSWORD=test_password

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

---

## Section 3: Database Testing Patterns

Common patterns for testing database interactions.

### Testing Repository Methods

```php
# filename: tests/Integration/Repositories/UserRepositoryTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use PDO;
use Tests\DatabaseTestCase;
use App\Repositories\UserRepository;
use App\Models\User;

class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository($this->getPdo());
    }

    public function testCreateInsertsUserIntoDatabase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => password_hash('secret', PASSWORD_ARGON2ID),
        ];

        $user = $this->repository->create($userData);

        // Verify user was created
        $this->assertNotNull($user->id);
        $this->assertEquals('John Doe', $user->name);

        // Verify in database
        $stmt = $this->getPdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user->id]);
        $dbUser = $stmt->fetch();

        $this->assertNotNull($dbUser);
        $this->assertEquals('john@example.com', $dbUser['email']);
    }

    public function testFindByIdReturnsCorrectUser(): void
    {
        // Insert test data
        $stmt = $this->getPdo()->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute(['Jane Smith', 'jane@example.com', 'hashed_password']);
        $userId = (int) $this->getPdo()->lastInsertId();

        // Test repository method
        $user = $this->repository->findById($userId);

        $this->assertNotNull($user);
        $this->assertEquals('Jane Smith', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
    }

    public function testFindByEmailReturnsNullForNonExistentUser(): void
    {
        $user = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($user);
    }

    public function testUpdateModifiesUserInDatabase(): void
    {
        // Create user
        $user = $this->repository->create([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'password' => 'hashed',
        ]);

        // Update user
        $this->repository->update($user->id, [
            'name' => 'Updated Name',
        ]);

        // Verify update in database
        $stmt = $this->getPdo()->prepare('SELECT name FROM users WHERE id = ?');
        $stmt->execute([$user->id]);
        $result = $stmt->fetch();

        $this->assertEquals('Updated Name', $result['name']);
    }

    public function testDeleteRemovesUserFromDatabase(): void
    {
        // Create user
        $user = $this->repository->create([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'password' => 'hashed',
        ]);

        // Delete user
        $this->repository->delete($user->id);

        // Verify deletion
        $stmt = $this->getPdo()->prepare('SELECT COUNT(*) FROM users WHERE id = ?');
        $stmt->execute([$user->id]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }

    public function testFindAllReturnsAllUsers(): void
    {
        // Insert multiple users
        $this->repository->create(['name' => 'User 1', 'email' => 'user1@example.com', 'password' => 'hash']);
        $this->repository->create(['name' => 'User 2', 'email' => 'user2@example.com', 'password' => 'hash']);
        $this->repository->create(['name' => 'User 3', 'email' => 'user3@example.com', 'password' => 'hash']);

        $users = $this->repository->findAll();

        $this->assertCount(3, $users);
        $this->assertEquals('User 1', $users[0]->name);
        $this->assertEquals('User 2', $users[1]->name);
        $this->assertEquals('User 3', $users[2]->name);
    }
}
```

### Testing Relationships

```php
# filename: tests/Integration/Repositories/PostRepositoryTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use PDO;
use Tests\DatabaseTestCase;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;

class PostRepositoryTest extends DatabaseTestCase
{
    private UserRepository $userRepo;
    private PostRepository $postRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository($this->getPdo());
        $this->postRepo = new PostRepository($this->getPdo());
    }

    public function testFindWithAuthorLoadsUserRelationship(): void
    {
        // Create user
        $user = $this->userRepo->create([
            'name' => 'Author',
            'email' => 'author@example.com',
            'password' => 'hash',
        ]);

        // Create post
        $post = $this->postRepo->create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Post content',
        ]);

        // Load post with author
        $loadedPost = $this->postRepo->findWithAuthor($post->id);

        $this->assertNotNull($loadedPost);
        $this->assertEquals('Test Post', $loadedPost->title);
        $this->assertEquals('Author', $loadedPost->author_name);
        $this->assertEquals('author@example.com', $loadedPost->author_email);
    }

    public function testCascadeDeleteRemovesRelatedPosts(): void
    {
        // Create user with posts
        $user = $this->userRepo->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'hash',
        ]);

        $this->postRepo->create(['user_id' => $user->id, 'title' => 'Post 1', 'content' => 'Content']);
        $this->postRepo->create(['user_id' => $user->id, 'title' => 'Post 2', 'content' => 'Content']);

        // Delete user
        $this->userRepo->delete($user->id);

        // Verify posts were deleted (cascade)
        $stmt = $this->getPdo()->prepare('SELECT COUNT(*) FROM posts WHERE user_id = ?');
        $stmt->execute([$user->id]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }
}
```

---

## Section 4: Database Seeders and Factories

Create test data efficiently.

### Database Seeder

```php
# filename: tests/Seeders/DatabaseSeeder.php
<?php

declare(strict_types=1);

namespace Tests\Seeders;

use PDO;

class DatabaseSeeder
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function seed(): void
    {
        $this->seedUsers();
        $this->seedPosts();
        $this->seedComments();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Regular User', 'email' => 'user@example.com', 'role' => 'user'],
            ['name' => 'Guest User', 'email' => 'guest@example.com', 'role' => 'guest'],
        ];

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
        );

        foreach ($users as $user) {
            $stmt->execute([
                $user['name'],
                $user['email'],
                password_hash('password', PASSWORD_ARGON2ID),
                $user['role'],
            ]);
        }
    }

    private function seedPosts(): void
    {
        $posts = [
            ['user_id' => 1, 'title' => 'First Post', 'content' => 'Content of first post', 'status' => 'published'],
            ['user_id' => 1, 'title' => 'Second Post', 'content' => 'Content of second post', 'status' => 'published'],
            ['user_id' => 2, 'title' => 'Draft Post', 'content' => 'Draft content', 'status' => 'draft'],
        ];

        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (user_id, title, slug, content, status) VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($posts as $post) {
            $slug = strtolower(str_replace(' ', '-', $post['title']));
            $stmt->execute([
                $post['user_id'],
                $post['title'],
                $slug,
                $post['content'],
                $post['status'],
            ]);
        }
    }

    private function seedComments(): void
    {
        $comments = [
            ['post_id' => 1, 'user_id' => 2, 'content' => 'Great post!'],
            ['post_id' => 1, 'user_id' => 3, 'content' => 'Thanks for sharing'],
            ['post_id' => 2, 'user_id' => 2, 'content' => 'Interesting'],
        ];

        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)'
        );

        foreach ($comments as $comment) {
            $stmt->execute([
                $comment['post_id'],
                $comment['user_id'],
                $comment['content'],
            ]);
        }
    }
}
```

### Factory Pattern

```php
# filename: tests/Factories/UserFactory.php
<?php

declare(strict_types=1);

namespace Tests\Factories;

use PDO;

class UserFactory
{
    private static int $sequence = 0;

    public static function create(PDO $pdo, array $attributes = []): array
    {
        self::$sequence++;

        $defaults = [
            'name' => 'User ' . self::$sequence,
            'email' => 'user' . self::$sequence . '@example.com',
            'password' => password_hash('password', PASSWORD_ARGON2ID),
            'role' => 'user',
        ];

        $data = array_merge($defaults, $attributes);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role'],
        ]);

        $data['id'] = (int) $pdo->lastInsertId();

        return $data;
    }

    public static function createMany(PDO $pdo, int $count, array $attributes = []): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::create($pdo, $attributes);
        }
        return $users;
    }
}

```

```php
# filename: tests/Factories/PostFactory.php
<?php

declare(strict_types=1);

namespace Tests\Factories;

use PDO;

class PostFactory
{
    private static int $sequence = 0;

    public static function create(PDO $pdo, array $attributes = []): array
    {
        self::$sequence++;

        // Create user if not provided
        if (!isset($attributes['user_id'])) {
            $user = UserFactory::create($pdo);
            $attributes['user_id'] = $user['id'];
        }

        $defaults = [
            'title' => 'Post Title ' . self::$sequence,
            'slug' => 'post-title-' . self::$sequence,
            'content' => 'Post content ' . self::$sequence,
            'status' => 'published',
        ];

        $data = array_merge($defaults, $attributes);

        $stmt = $pdo->prepare(
            'INSERT INTO posts (user_id, title, slug, content, status) VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['status'],
        ]);

        $data['id'] = (int) $pdo->lastInsertId();

        return $data;
    }
}
```

### Using Factories in Tests

```php
# filename: tests/Integration/PostServiceTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use Tests\DatabaseTestCase;
use Tests\Factories\{UserFactory, PostFactory};

class PostServiceTest extends DatabaseTestCase
{
    public function testPublishPostUpdatesStatus(): void
    {
        // Create test data using factories
        $user = UserFactory::create($this->getPdo(), ['role' => 'admin']);
        $post = PostFactory::create($this->getPdo(), [
            'user_id' => $user['id'],
            'status' => 'draft',
        ]);

        $service = new PostService(new PostRepository($this->getPdo()));

        // Test publish
        $service->publish($post['id']);

        // Verify status changed
        $stmt = $this->getPdo()->prepare('SELECT status FROM posts WHERE id = ?');
        $stmt->execute([$post['id']]);
        $status = $stmt->fetchColumn();

        $this->assertEquals('published', $status);
    }

    public function testGetPublishedPostsReturnsOnlyPublished(): void
    {
        // Create mixed posts
        $user = UserFactory::create($this->getPdo());
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'published']);
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'published']);
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'draft']);

        $service = new PostService(new PostRepository($this->getPdo()));
        $posts = $service->getPublishedPosts();

        $this->assertCount(2, $posts);
    }
}
```

---

## Section 5: Transaction Rollback Strategy

Use transactions to isolate tests without manual cleanup.

### Transaction Test Case

```php
# filename: tests/TransactionalTestCase.php
<?php

declare(strict_types=1);

namespace Tests;

use PDO;

abstract class TransactionalTestCase extends DatabaseTestCase
{
    /**
     * Begin transaction before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getPdo()->beginTransaction();
    }

    /**
     * Rollback transaction after each test
     */
    protected function tearDown(): void
    {
        $this->getPdo()->rollBack();
        parent::tearDown();
    }
}
```

### Using Transactional Tests

```php
# filename: tests/Integration/UserServiceTransactionalTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use Tests\TransactionalTestCase;
use Tests\Factories\UserFactory;

class UserServiceTransactionalTest extends TransactionalTestCase
{
    public function testCreateUserInsertsData(): void
    {
        // Data created in transaction
        $user = UserFactory::create($this->getPdo(), [
            'email' => 'test@example.com',
        ]);

        // Verify within transaction
        $stmt = $this->getPdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $found = $stmt->fetch();

        $this->assertNotNull($found);
        $this->assertEquals('test@example.com', $found['email']);

        // After test completes, transaction will be rolled back
    }

    public function testAnotherTestHasCleanDatabase(): void
    {
        // Previous test's data was rolled back
        $stmt = $this->getPdo()->query('SELECT COUNT(*) FROM users');
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }
}
```

---

## Section 6: API Testing

Test HTTP endpoints and API responses.

### API Test Base Class

```php
# filename: tests/ApiTestCase.php
<?php

declare(strict_types=1);

namespace Tests;

use PDO;

abstract class ApiTestCase extends TransactionalTestCase
{
    protected function get(string $uri, array $headers = []): ApiResponse
    {
        return $this->request('GET', $uri, [], $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): ApiResponse
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    protected function put(string $uri, array $data = [], array $headers = []): ApiResponse
    {
        return $this->request('PUT', $uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): ApiResponse
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    protected function request(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ): ApiResponse {
        // Simulate HTTP request
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        foreach ($headers as $name => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }

        // Capture output
        ob_start();

        try {
            // Your application's request handling
            $app = require __DIR__ . '/../bootstrap/app.php';
            $request = Request::capture();

            if (!empty($data)) {
                $request->setJsonBody($data);
            }

            $response = $app->handle($request);
            $response->send();

            $output = ob_get_clean();

            return new ApiResponse(
                $response->getStatusCode(),
                $response->getHeaders(),
                $output
            );
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    protected function assertResponseOk(ApiResponse $response): void
    {
        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function assertResponseCreated(ApiResponse $response): void
    {
        $this->assertEquals(201, $response->getStatusCode());
    }

    protected function assertResponseUnauthorized(ApiResponse $response): void
    {
        $this->assertEquals(401, $response->getStatusCode());
    }

    protected function assertResponseNotFound(ApiResponse $response): void
    {
        $this->assertEquals(404, $response->getStatusCode());
    }

    protected function assertResponseValidationError(ApiResponse $response): void
    {
        $this->assertEquals(422, $response->getStatusCode());
    }

    protected function assertJsonResponse(ApiResponse $response, array $expected): void
    {
        $data = $response->json();

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertEquals($value, $data[$key]);
        }
    }
}

```

```php
# filename: tests/ApiResponse.php
<?php

declare(strict_types=1);

namespace Tests;

class ApiResponse
{
    public function __construct(
        private int $statusCode,
        private array $headers,
        private string $body
    ) {}

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function json(): array
    {
        return json_decode($this->body, true);
    }
}
```

### API Endpoint Tests

```php
# filename: tests/Integration/Api/PostApiTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Api;

use PDO;
use Tests\ApiTestCase;
use Tests\Factories\{UserFactory, PostFactory};

class PostApiTest extends ApiTestCase
{
    public function testGetPostsReturnsPublishedPosts(): void
    {
        // Create test data
        $user = UserFactory::create($this->getPdo());
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'published']);
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'published']);
        PostFactory::create($this->getPdo(), ['user_id' => $user['id'], 'status' => 'draft']);

        // Make API request
        $response = $this->get('/api/posts');

        // Assert response
        $this->assertResponseOk($response);

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);
    }

    public function testCreatePostRequiresAuthentication(): void
    {
        $response = $this->post('/api/posts', [
            'title' => 'New Post',
            'content' => 'Post content',
        ]);

        $this->assertResponseUnauthorized($response);
    }

    public function testCreatePostWithValidDataSucceeds(): void
    {
        // Create authenticated user
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        $response = $this->post('/api/posts', [
            'title' => 'New Post',
            'content' => 'This is the post content',
            'status' => 'published',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertResponseCreated($response);

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('New Post', $data['data']['title']);

        // Verify in database
        $stmt = $this->getPdo()->prepare('SELECT * FROM posts WHERE title = ?');
        $stmt->execute(['New Post']);
        $post = $stmt->fetch();

        $this->assertNotNull($post);
        $this->assertEquals('This is the post content', $post['content']);
    }

    public function testCreatePostWithInvalidDataReturnsValidationError(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        $response = $this->post('/api/posts', [
            'title' => '', // Empty title - should fail validation
            'content' => 'Content',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertResponseValidationError($response);

        $data = $response->json();
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('details', $data['error']);
    }

    public function testUpdatePostRequiresOwnership(): void
    {
        // Create post by user 1
        $user1 = UserFactory::create($this->getPdo());
        $post = PostFactory::create($this->getPdo(), ['user_id' => $user1['id']]);

        // Try to update as user 2
        $user2 = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user2);

        $response = $this->put("/api/posts/{$post['id']}", [
            'title' => 'Updated Title',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDeletePostRemovesFromDatabase(): void
    {
        $user = UserFactory::create($this->getPdo());
        $post = PostFactory::create($this->getPdo(), ['user_id' => $user['id']]);
        $token = $this->generateToken($user);

        $response = $this->delete("/api/posts/{$post['id']}", [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(204, $response->getStatusCode());

        // Verify deletion
        $stmt = $this->getPdo()->prepare('SELECT COUNT(*) FROM posts WHERE id = ?');
        $stmt->execute([$post['id']]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count);
    }

    private function generateToken(array $user): string
    {
        // Simplified token generation for testing
        // In production, use a proper JWT library
        return base64_encode(json_encode([
            'userId' => $user['id'],
            'email' => $user['email'],
        ]));
    }
}
```

---

## Section 7: In-Memory Databases

Use SQLite in-memory for fast tests.

### SQLite Configuration

```php
# filename: tests/SqliteTestCase.php
<?php

declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class SqliteTestCase extends BaseTestCase
{
    protected static ?PDO $pdo = null;

    public static function setUpBeforeClass(): void
    {
        // Create in-memory SQLite database
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::createSchema();
    }

    private static function createSchema(): void
    {
        self::$pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT "user",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');

        self::$pdo->exec('
            CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                content TEXT NOT NULL,
                status VARCHAR(50) DEFAULT "draft",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ');
    }

    protected function tearDown(): void
    {
        // Clean tables between tests
        self::$pdo->exec('DELETE FROM posts');
        self::$pdo->exec('DELETE FROM users');
    }

    protected function getPdo(): PDO
    {
        return self::$pdo;
    }
}
```

### Advantages of SQLite for Testing

```php
# filename: tests/Integration/FastDatabaseTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use Tests\SqliteTestCase;

/**
 * SQLite in-memory tests are:
 * - Extremely fast (no disk I/O)
 * - Isolated (each test suite gets fresh database)
 * - No setup required (created on-the-fly)
 * - Perfect for CI/CD pipelines
 */
class FastDatabaseTest extends SqliteTestCase
{
    public function testManyInserts(): void
    {
        $start = microtime(true);

        // Insert 1000 records
        $stmt = $this->getPdo()->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );

        for ($i = 0; $i < 1000; $i++) {
            $stmt->execute(["User {$i}", "user{$i}@example.com", 'hash']);
        }

        $elapsed = microtime(true) - $start;

        // Very fast even with 1000 inserts
        $this->assertLessThan(1.0, $elapsed); // Less than 1 second

        // Verify count
        $count = $this->getPdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $this->assertEquals(1000, $count);
    }
}
```

---

## Section 8: Testing External APIs

Mock external HTTP calls for integration tests.

### HTTP Client Mock

```php
# filename: tests/Mocks/MockHttpClient.php
<?php

declare(strict_types=1);

namespace Tests\Mocks;

class MockHttpClient implements HttpClientInterface
{
    private array $responses = [];
    private array $requests = [];

    public function addResponse(string $url, int $statusCode, array $body): void
    {
        $this->responses[$url] = [
            'status' => $statusCode,
            'body' => $body,
        ];
    }

    public function get(string $url, array $headers = []): HttpResponse
    {
        $this->requests[] = ['method' => 'GET', 'url' => $url, 'headers' => $headers];

        if (!isset($this->responses[$url])) {
            throw new \Exception("No mock response for {$url}");
        }

        $response = $this->responses[$url];

        return new HttpResponse(
            $response['status'],
            [],
            json_encode($response['body'])
        );
    }

    public function post(string $url, array $data, array $headers = []): HttpResponse
    {
        $this->requests[] = ['method' => 'POST', 'url' => $url, 'data' => $data, 'headers' => $headers];

        if (!isset($this->responses[$url])) {
            throw new \Exception("No mock response for {$url}");
        }

        $response = $this->responses[$url];

        return new HttpResponse(
            $response['status'],
            [],
            json_encode($response['body'])
        );
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function assertRequestMade(string $method, string $url): void
    {
        foreach ($this->requests as $request) {
            if ($request['method'] === $method && $request['url'] === $url) {
                return;
            }
        }

        throw new \Exception("Request {$method} {$url} was not made");
    }
}
```

### Testing Services with External APIs

```php
# filename: tests/Integration/Services/PaymentServiceTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

use PDO;
use Tests\DatabaseTestCase;
use Tests\Mocks\MockHttpClient;
use App\Services\PaymentService;

class PaymentServiceTest extends DatabaseTestCase
{
    public function testProcessPaymentCallsExternalGateway(): void
    {
        // Mock HTTP client
        $httpClient = new MockHttpClient();
        $httpClient->addResponse('https://api.payment-gateway.com/charge', 200, [
            'transaction_id' => 'txn_123456',
            'status' => 'success',
            'amount' => 99.99,
        ]);

        $service = new PaymentService($httpClient);

        // Process payment
        $result = $service->charge(99.99, 'card_token_123');

        // Verify result
        $this->assertTrue($result->success);
        $this->assertEquals('txn_123456', $result->transactionId);

        // Verify HTTP request was made
        $httpClient->assertRequestMade('POST', 'https://api.payment-gateway.com/charge');
    }

    public function testProcessPaymentHandlesGatewayFailure(): void
    {
        $httpClient = new MockHttpClient();
        $httpClient->addResponse('https://api.payment-gateway.com/charge', 400, [
            'error' => 'Invalid card',
        ]);

        $service = new PaymentService($httpClient);

        $result = $service->charge(99.99, 'invalid_card');

        $this->assertFalse($result->success);
        $this->assertEquals('Invalid card', $result->error);
    }
}
```

---

## Section 9: Docker for Testing

Use Docker containers for isolated test environments.

### Docker Compose for Tests

```yaml
# docker-compose.test.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.test
    volumes:
      - .:/app
    depends_on:
      - mysql
      - redis
    environment:
      DB_HOST: mysql
      DB_DATABASE: test_db
      DB_USERNAME: test_user
      DB_PASSWORD: test_password
      REDIS_HOST: redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: test_db
      MYSQL_USER: test_user
      MYSQL_PASSWORD: test_password
    tmpfs:
      - /var/lib/mysql  # In-memory for faster tests

  redis:
    image: redis:7-alpine
```

### Test Dockerfile

```dockerfile
# Dockerfile.test
FROM php:8.3-cli

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy application
COPY . .
RUN composer dump-autoload

# Run tests
CMD ["vendor/bin/phpunit"]
```

### Running Tests in Docker

```bash
# Build and run tests
docker-compose -f docker-compose.test.yml up --build --abort-on-container-exit

# Run specific test suite
docker-compose -f docker-compose.test.yml run app vendor/bin/phpunit tests/Integration

# Run with coverage
docker-compose -f docker-compose.test.yml run app vendor/bin/phpunit --coverage-html coverage
```

---

## Section 10: CI/CD Integration

Automate integration tests in pipelines.

### GitHub Actions with Database

```yaml
# .github/workflows/tests.yml
name: Integration Tests

on: [push, pull_request]

jobs:
  integration-tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
          MYSQL_USER: test_user
          MYSQL_PASSWORD: test_password
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

      redis:
        image: redis:7-alpine
        ports:
          - 6379:6379

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo, pdo_mysql, redis
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Wait for MySQL
        run: |
          while ! mysqladmin ping -h127.0.0.1 -P3306 --silent; do
            sleep 1
          done

      - name: Run migrations
        run: php artisan migrate --env=testing
        env:
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: test_db
          DB_USERNAME: test_user
          DB_PASSWORD: test_password

      - name: Run integration tests
        run: vendor/bin/phpunit tests/Integration --coverage-clover=coverage.xml
        env:
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: test_db
          DB_USERNAME: test_user
          DB_PASSWORD: test_password
          REDIS_HOST: 127.0.0.1

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

---

## Section 11: Performance Testing & Load Testing

Measure application performance under load to ensure scalability.

### Performance Test Base Class

```php
# filename: tests/Integration/Performance/PerformanceTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Performance;

use Tests\DatabaseTestCase;

abstract class PerformanceTestCase extends DatabaseTestCase
{
    protected function assertResponseTimeLessThan(
        callable $operation,
        float $maxSeconds
    ): void {
        $start = microtime(true);
        $operation();
        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            $maxSeconds,
            $elapsed,
            "Operation took {$elapsed}s, expected less than {$maxSeconds}s"
        );
    }

    protected function measureThroughput(
        callable $operation,
        int $iterations
    ): float {
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $operation();
        }

        $elapsed = microtime(true) - $start;
        return $iterations / $elapsed; // operations per second
    }
}
```

### Database Query Performance Tests

```php
# filename: tests/Integration/Performance/DatabasePerformanceTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Performance;

use Tests\Integration\Performance\PerformanceTestCase;
use Tests\Factories\UserFactory;
use Tests\Factories\PostFactory;

class DatabasePerformanceTest extends PerformanceTestCase
{
    public function testBulkInsertPerformance(): void
    {
        $this->assertResponseTimeLessThan(function () {
            for ($i = 0; $i < 1000; $i++) {
                UserFactory::create($this->getPdo(), [
                    'email' => "user{$i}@example.com",
                ]);
            }
        }, 5.0); // Should complete in under 5 seconds
    }

    public function testQueryWithIndexesIsFast(): void
    {
        // Create test data
        for ($i = 0; $i < 100; $i++) {
            UserFactory::create($this->getPdo());
        }

        $this->assertResponseTimeLessThan(function () {
            $stmt = $this->getPdo()->prepare(
                'SELECT * FROM users WHERE email = ?'
            );
            $stmt->execute(['user50@example.com']);
            $stmt->fetch();
        }, 0.1); // Indexed query should be very fast
    }

    public function testNPlusOneQueryProblem(): void
    {
        $user = UserFactory::create($this->getPdo());
        PostFactory::create($this->getPdo(), ['user_id' => $user['id']]);
        PostFactory::create($this->getPdo(), ['user_id' => $user['id']]);

        // Bad: N+1 queries
        $start = microtime(true);
        $users = $this->getPdo()->query('SELECT * FROM users')->fetchAll();
        foreach ($users as $user) {
            $stmt = $this->getPdo()->prepare(
                'SELECT * FROM posts WHERE user_id = ?'
            );
            $stmt->execute([$user['id']]);
            $stmt->fetchAll();
        }
        $badTime = microtime(true) - $start;

        // Good: Single query with JOIN
        $start = microtime(true);
        $this->getPdo()->query(
            'SELECT u.*, p.* FROM users u LEFT JOIN posts p ON u.id = p.user_id'
        )->fetchAll();
        $goodTime = microtime(true) - $start;

        $this->assertLessThan($badTime, $goodTime);
    }
}
```

### API Load Testing

```php
# filename: tests/Integration/Performance/ApiLoadTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Performance;

use Tests\ApiTestCase;
use Tests\Factories\UserFactory;

class ApiLoadTest extends ApiTestCase
{
    public function testApiHandlesConcurrentRequests(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        $throughput = $this->measureThroughput(function () use ($token) {
            $this->get('/api/posts', [
                'Authorization' => "Bearer {$token}",
            ]);
        }, 100);

        // Should handle at least 50 requests per second
        $this->assertGreaterThan(50, $throughput);
    }

    public function testApiResponseTimeUnderLoad(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        $responseTimes = [];
        for ($i = 0; $i < 50; $i++) {
            $start = microtime(true);
            $this->get('/api/posts', [
                'Authorization' => "Bearer {$token}",
            ]);
            $responseTimes[] = microtime(true) - $start;
        }

        $avgTime = array_sum($responseTimes) / count($responseTimes);
        $maxTime = max($responseTimes);

        // Average should be under 200ms
        $this->assertLessThan(0.2, $avgTime);
        // Max should be under 500ms
        $this->assertLessThan(0.5, $maxTime);
    }
}
```

---

## Section 12: Database Migration Testing

Test database schema changes and migrations safely.

### Migration Test Helper

```php
# filename: tests/Integration/Migrations/MigrationTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Migrations;

use PDO;
use Tests\DatabaseTestCase;

abstract class MigrationTestCase extends DatabaseTestCase
{
    protected function runMigration(string $migrationFile): void
    {
        $sql = file_get_contents($migrationFile);
        $this->getPdo()->exec($sql);
    }

    protected function rollbackMigration(string $rollbackFile): void
    {
        $sql = file_get_contents($rollbackFile);
        $this->getPdo()->exec($sql);
    }

    protected function assertTableExists(string $tableName): void
    {
        $stmt = $this->getPdo()->query(
            "SHOW TABLES LIKE '{$tableName}'"
        );
        $this->assertNotFalse($stmt->fetch());
    }

    protected function assertColumnExists(
        string $tableName,
        string $columnName
    ): void {
        $stmt = $this->getPdo()->query(
            "SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'"
        );
        $this->assertNotFalse($stmt->fetch());
    }
}
```

### Migration Tests

```php
# filename: tests/Integration/Migrations/AddUserPreferencesTableTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Migrations;

use Tests\Integration\Migrations\MigrationTestCase;
use Tests\Factories\UserFactory;

class AddUserPreferencesTableTest extends MigrationTestCase
{
    public function testMigrationCreatesTable(): void
    {
        $this->runMigration(__DIR__ . '/../../database/migrations/add_user_preferences_table.sql');

        $this->assertTableExists('user_preferences');
        $this->assertColumnExists('user_preferences', 'user_id');
        $this->assertColumnExists('user_preferences', 'theme');
        $this->assertColumnExists('user_preferences', 'language');
    }

    public function testMigrationPreservesExistingData(): void
    {
        // Create user before migration
        $user = UserFactory::create($this->getPdo());

        // Run migration
        $this->runMigration(__DIR__ . '/../../database/migrations/add_user_preferences_table.sql');

        // Verify user still exists
        $stmt = $this->getPdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $found = $stmt->fetch();

        $this->assertNotFalse($found);
        $this->assertEquals($user['email'], $found['email']);
    }

    public function testRollbackRemovesTable(): void
    {
        // Run migration
        $this->runMigration(__DIR__ . '/../../database/migrations/add_user_preferences_table.sql');
        $this->assertTableExists('user_preferences');

        // Rollback
        $this->rollbackMigration(__DIR__ . '/../../database/migrations/rollback_user_preferences_table.sql');

        // Verify table is gone
        $stmt = $this->getPdo()->query(
            "SHOW TABLES LIKE 'user_preferences'"
        );
        $this->assertFalse($stmt->fetch());
    }

    public function testMigrationWithForeignKeyConstraint(): void
    {
        // Create user first
        $user = UserFactory::create($this->getPdo());

        // Run migration that adds foreign key
        $this->runMigration(__DIR__ . '/../../database/migrations/add_user_preferences_table.sql');

        // Insert preference with valid user_id
        $stmt = $this->getPdo()->prepare(
            'INSERT INTO user_preferences (user_id, theme, language) VALUES (?, ?, ?)'
        );
        $stmt->execute([$user['id'], 'dark', 'en']);

        // Verify foreign key constraint works
        $this->expectException(\PDOException::class);
        $stmt->execute([99999, 'light', 'fr']); // Invalid user_id
    }
}
```

### Migration Example Files

```sql
# filename: database/migrations/add_user_preferences_table.sql
CREATE TABLE user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    theme VARCHAR(50) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);
```

```sql
# filename: database/migrations/rollback_user_preferences_table.sql
DROP TABLE IF EXISTS user_preferences;
```

---

## Section 13: Caching Integration Tests

Test cache invalidation and cache behavior.

### Cache Test Base Class

```php
# filename: tests/Integration/Cache/CacheTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Cache;

use PDO;
use Tests\DatabaseTestCase;
use Redis;

abstract class CacheTestCase extends DatabaseTestCase
{
    protected Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redis = new Redis();
        $this->redis->connect(
            $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            $_ENV['REDIS_PORT'] ?? 6379
        );
        $this->redis->flushAll(); // Clean cache before each test
    }

    protected function tearDown(): void
    {
        $this->redis->flushAll();
        $this->redis->close();
        parent::tearDown();
    }
}
```

### Cache Integration Tests

```php
# filename: tests/Integration/Cache/UserCacheTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Cache;

use Tests\Integration\Cache\CacheTestCase;
use Tests\Factories\UserFactory;
use App\Repositories\UserRepository;

class UserCacheTest extends CacheTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository($this->getPdo(), $this->redis);
    }

    public function testCacheHitReturnsCachedData(): void
    {
        $user = UserFactory::create($this->getPdo());

        // First call - cache miss, stores in cache
        $start = microtime(true);
        $user1 = $this->repository->findById($user['id']);
        $firstCallTime = microtime(true) - $start;

        // Second call - cache hit, faster
        $start = microtime(true);
        $user2 = $this->repository->findById($user['id']);
        $secondCallTime = microtime(true) - $start;

        $this->assertEquals($user1['id'], $user2['id']);
        $this->assertLessThan($firstCallTime, $secondCallTime);
    }

    public function testCacheInvalidationOnUpdate(): void
    {
        $user = UserFactory::create($this->getPdo());

        // Load and cache user
        $this->repository->findById($user['id']);
        $this->assertTrue($this->redis->exists("user:{$user['id']}"));

        // Update user
        $this->repository->update($user['id'], ['name' => 'Updated Name']);

        // Cache should be invalidated
        $this->assertFalse($this->redis->exists("user:{$user['id']}"));

        // Next fetch should reload from database
        $updated = $this->repository->findById($user['id']);
        $this->assertEquals('Updated Name', $updated['name']);
    }

    public function testCacheInvalidationOnDelete(): void
    {
        $user = UserFactory::create($this->getPdo());

        // Load and cache user
        $this->repository->findById($user['id']);
        $this->assertTrue($this->redis->exists("user:{$user['id']}"));

        // Delete user
        $this->repository->delete($user['id']);

        // Cache should be cleared
        $this->assertFalse($this->redis->exists("user:{$user['id']}"));

        // User should not be found
        $this->assertNull($this->repository->findById($user['id']));
    }

    public function testCacheExpiration(): void
    {
        $user = UserFactory::create($this->getPdo());

        // Set cache with 1 second TTL
        $this->redis->setex("user:{$user['id']}", 1, json_encode($user));

        $this->assertTrue($this->redis->exists("user:{$user['id']}"));

        // Wait for expiration
        sleep(2);

        $this->assertFalse($this->redis->exists("user:{$user['id']}"));
    }
}
```

---

## Section 14: Queue/Job Testing

Test background job processing and queue integration.

### Queue Test Base Class

```php
# filename: tests/Integration/Queue/QueueTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Queue;

use PDO;
use Tests\DatabaseTestCase;

abstract class QueueTestCase extends DatabaseTestCase
{
    protected array $dispatchedJobs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatchedJobs = [];
    }

    protected function dispatchJob(string $jobClass, array $data): void
    {
        $this->dispatchedJobs[] = [
            'class' => $jobClass,
            'data' => $data,
            'timestamp' => time(),
        ];
    }

    protected function assertJobDispatched(string $jobClass): void
    {
        $found = false;
        foreach ($this->dispatchedJobs as $job) {
            if ($job['class'] === $jobClass) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Job {$jobClass} was not dispatched");
    }

    protected function processQueue(): void
    {
        foreach ($this->dispatchedJobs as $job) {
            $jobInstance = new $job['class']($job['data']);
            $jobInstance->handle();
        }
        $this->dispatchedJobs = [];
    }
}
```

### Queue Integration Tests

```php
# filename: tests/Integration/Queue/EmailQueueTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Queue;

use Tests\Integration\Queue\QueueTestCase;
use Tests\Factories\UserFactory;
use App\Jobs\SendWelcomeEmailJob;
use App\Services\UserService;

class EmailQueueTest extends QueueTestCase
{
    public function testUserRegistrationDispatchesWelcomeEmail(): void
    {
        $service = new UserService(
            new \App\Repositories\UserRepository($this->getPdo()),
            $this
        );

        $user = $service->register([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertJobDispatched(SendWelcomeEmailJob::class);
    }

    public function testWelcomeEmailJobProcessesCorrectly(): void
    {
        $user = UserFactory::create($this->getPdo());

        $job = new SendWelcomeEmailJob([
            'user_id' => $user['id'],
            'email' => $user['email'],
        ]);

        // Process job
        $job->handle();

        // Verify email was sent (check email log or mock)
        $stmt = $this->getPdo()->prepare(
            'SELECT * FROM email_log WHERE user_id = ? AND type = ?'
        );
        $stmt->execute([$user['id'], 'welcome']);
        $email = $stmt->fetch();

        $this->assertNotFalse($email);
        $this->assertEquals($user['email'], $email['to_email']);
    }

    public function testFailedJobHandling(): void
    {
        $user = UserFactory::create($this->getPdo());

        $job = new SendWelcomeEmailJob([
            'user_id' => $user['id'],
            'email' => 'invalid-email',
        ]);

        // Job should handle failure gracefully
        try {
            $job->handle();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verify failure is logged
            $stmt = $this->getPdo()->prepare(
                'SELECT * FROM failed_jobs WHERE user_id = ?'
            );
            $stmt->execute([$user['id']]);
            $failure = $stmt->fetch();

            $this->assertNotFalse($failure);
        }
    }

    public function testJobRetryLogic(): void
    {
        $user = UserFactory::create($this->getPdo());
        $attempts = 0;

        $job = new SendWelcomeEmailJob([
            'user_id' => $user['id'],
            'email' => $user['email'],
        ]);

        // Simulate retry logic
        while ($attempts < 3) {
            try {
                $job->handle();
                break;
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= 3) {
                    throw $e;
                }
                sleep(1); // Wait before retry
            }
        }

        $this->assertLessThan(3, $attempts);
    }
}
```

---

## Section 15: File System Integration Tests

Test file uploads, storage, and file operations.

### File System Test Base Class

```php
# filename: tests/Integration/Filesystem/FilesystemTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Filesystem;

use Tests\DatabaseTestCase;

abstract class FilesystemTestCase extends DatabaseTestCase
{
    protected string $testStoragePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testStoragePath = sys_get_temp_dir() . '/test_storage_' . uniqid();
        mkdir($this->testStoragePath, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testStoragePath);
        parent::tearDown();
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    protected function createTestFile(string $filename, string $content): string
    {
        $path = $this->testStoragePath . '/' . $filename;
        file_put_contents($path, $content);
        return $path;
    }
}
```

### File System Integration Tests

```php
# filename: tests/Integration/Filesystem/FileUploadTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Filesystem;

use Tests\Integration\Filesystem\FilesystemTestCase;
use Tests\Factories\UserFactory;
use App\Services\FileUploadService;

class FileUploadTest extends FilesystemTestCase
{
    public function testFileUploadStoresFile(): void
    {
        $user = UserFactory::create($this->getPdo());
        $service = new FileUploadService($this->testStoragePath);

        $testContent = 'Test file content';
        $testFile = $this->createTestFile('test.txt', $testContent);

        $uploaded = $service->upload($testFile, $user['id'], 'document');

        $this->assertFileExists($uploaded['path']);
        $this->assertEquals($testContent, file_get_contents($uploaded['path']));
        $this->assertEquals('test.txt', $uploaded['original_name']);
    }

    public function testFileUploadValidatesFileType(): void
    {
        $user = UserFactory::create($this->getPdo());
        $service = new FileUploadService($this->testStoragePath);

        $testFile = $this->createTestFile('test.exe', 'executable content');

        $this->expectException(\InvalidArgumentException::class);
        $service->upload($testFile, $user['id'], 'document');
    }

    public function testFileUploadValidatesFileSize(): void
    {
        $user = UserFactory::create($this->getPdo());
        $service = new FileUploadService($this->testStoragePath);

        // Create large file (10MB)
        $largeContent = str_repeat('x', 10 * 1024 * 1024);
        $testFile = $this->createTestFile('large.txt', $largeContent);

        $this->expectException(\InvalidArgumentException::class);
        $service->upload($testFile, $user['id'], 'document', 5 * 1024 * 1024); // 5MB limit
    }

    public function testFileDeletionRemovesFile(): void
    {
        $user = UserFactory::create($this->getPdo());
        $service = new FileUploadService($this->testStoragePath);

        $testFile = $this->createTestFile('test.txt', 'content');
        $uploaded = $service->upload($testFile, $user['id'], 'document');

        $this->assertFileExists($uploaded['path']);

        $service->delete($uploaded['id']);

        $this->assertFileDoesNotExist($uploaded['path']);
    }

    public function testImageUploadGeneratesThumbnail(): void
    {
        $user = UserFactory::create($this->getPdo());
        $service = new FileUploadService($this->testStoragePath);

        // Create test image (simplified - in real app would use GD or Imagick)
        $testFile = $this->createTestFile('test.jpg', 'fake image content');

        $uploaded = $service->uploadImage($testFile, $user['id']);

        $this->assertFileExists($uploaded['path']);
        $this->assertFileExists($uploaded['thumbnail_path']);
        $this->assertLessThan(
            filesize($uploaded['path']),
            filesize($uploaded['thumbnail_path'])
        );
    }
}
```

---

## Section 16: Email Integration Tests

Test email sending without actually sending emails.

### Email Test Base Class

```php
# filename: tests/Integration/Email/EmailTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Email;

use Tests\DatabaseTestCase;

abstract class EmailTestCase extends DatabaseTestCase
{
    protected array $sentEmails = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->sentEmails = [];
    }

    protected function assertEmailSent(string $to, string $subject): void
    {
        $found = false;
        foreach ($this->sentEmails as $email) {
            if ($email['to'] === $to && $email['subject'] === $subject) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Email to {$to} with subject '{$subject}' was not sent");
    }

    protected function getEmailContent(string $to, string $subject): ?string
    {
        foreach ($this->sentEmails as $email) {
            if ($email['to'] === $to && $email['subject'] === $subject) {
                return $email['body'];
            }
        }
        return null;
    }
}
```

### Email Integration Tests

```php
# filename: tests/Integration/Email/EmailServiceTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Email;

use Tests\Integration\Email\EmailTestCase;
use Tests\Factories\UserFactory;
use App\Services\EmailService;

class EmailServiceTest extends EmailTestCase
{
    public function testWelcomeEmailSentOnRegistration(): void
    {
        $user = UserFactory::create($this->getPdo());
        $emailService = new EmailService($this);

        $emailService->sendWelcomeEmail($user['id'], $user['email']);

        $this->assertEmailSent($user['email'], 'Welcome to Our Platform');
    }

    public function testWelcomeEmailContainsCorrectContent(): void
    {
        $user = UserFactory::create($this->getPdo(), [
            'name' => 'John Doe',
        ]);
        $emailService = new EmailService($this);

        $emailService->sendWelcomeEmail($user['id'], $user['email']);

        $content = $this->getEmailContent($user['email'], 'Welcome to Our Platform');
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('Welcome', $content);
    }

    public function testPasswordResetEmailSent(): void
    {
        $user = UserFactory::create($this->getPdo());
        $emailService = new EmailService($this);

        $token = 'reset-token-123';
        $emailService->sendPasswordResetEmail($user['email'], $token);

        $this->assertEmailSent($user['email'], 'Reset Your Password');
        $content = $this->getEmailContent($user['email'], 'Reset Your Password');
        $this->assertStringContainsString($token, $content);
    }

    public function testEmailQueueIntegration(): void
    {
        $user = UserFactory::create($this->getPdo());
        $emailService = new EmailService($this);

        // Email should be queued, not sent immediately
        $emailService->queueWelcomeEmail($user['id'], $user['email']);

        $this->assertCount(0, $this->sentEmails);

        // Process queue
        $emailService->processQueue();

        $this->assertEmailSent($user['email'], 'Welcome to Our Platform');
    }
}
```

### Mailtrap Integration (Alternative)

```php
# filename: tests/Integration/Email/MailtrapEmailTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Email;

use Tests\DatabaseTestCase;
use Tests\Factories\UserFactory;

class MailtrapEmailTest extends DatabaseTestCase
{
    public function testEmailSentToMailtrap(): void
    {
        $user = UserFactory::create($this->getPdo());

        // Configure to use Mailtrap SMTP
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = 'smtp.mailtrap.io';
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAILTRAP_USERNAME'];
        $mailer->Password = $_ENV['MAILTRAP_PASSWORD'];
        $mailer->Port = 2525;

        $mailer->setFrom('noreply@example.com');
        $mailer->addAddress($user['email']);
        $mailer->Subject = 'Test Email';
        $mailer->Body = 'Test content';

        $mailer->send();

        // Verify via Mailtrap API
        $response = file_get_contents(
            "https://mailtrap.io/api/v1/inboxes/{$_ENV['MAILTRAP_INBOX_ID']}/messages",
            false,
            stream_context_create([
                'http' => [
                    'header' => "Authorization: Bearer {$_ENV['MAILTRAP_API_TOKEN']}",
                ],
            ])
        );

        $messages = json_decode($response, true);
        $this->assertGreaterThan(0, count($messages));
    }
}
```

---

## Section 17: WebSocket/Real-time Testing

Test WebSocket connections and real-time features.

### WebSocket Test Helper

```php
# filename: tests/Integration/WebSocket/WebSocketTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\WebSocket;

use Tests\DatabaseTestCase;

abstract class WebSocketTestCase extends DatabaseTestCase
{
    protected function connectWebSocket(string $url): resource
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $parsed = parse_url($url);
        socket_connect($socket, $parsed['host'], $parsed['port'] ?? 8080);
        return $socket;
    }

    protected function sendWebSocketMessage(resource $socket, string $message): void
    {
        socket_write($socket, $message);
    }

    protected function receiveWebSocketMessage(resource $socket): string
    {
        return socket_read($socket, 1024);
    }

    protected function closeWebSocket(resource $socket): void
    {
        socket_close($socket);
    }
}
```

### WebSocket Integration Tests

```php
# filename: tests/Integration/WebSocket/ChatWebSocketTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\WebSocket;

use Tests\Integration\WebSocket\WebSocketTestCase;
use Tests\Factories\UserFactory;

class ChatWebSocketTest extends WebSocketTestCase
{
    public function testUserCanConnectToChat(): void
    {
        $user = UserFactory::create($this->getPdo());
        $socket = $this->connectWebSocket('ws://localhost:8080/chat');

        $this->assertIsResource($socket);

        // Send authentication
        $this->sendWebSocketMessage($socket, json_encode([
            'type' => 'auth',
            'token' => $this->generateToken($user),
        ]));

        $response = $this->receiveWebSocketMessage($socket);
        $data = json_decode($response, true);

        $this->assertEquals('connected', $data['status']);
        $this->closeWebSocket($socket);
    }

    public function testUserCanSendMessage(): void
    {
        $user = UserFactory::create($this->getPdo());
        $socket = $this->connectWebSocket('ws://localhost:8080/chat');

        // Authenticate
        $this->sendWebSocketMessage($socket, json_encode([
            'type' => 'auth',
            'token' => $this->generateToken($user),
        ]));
        $this->receiveWebSocketMessage($socket); // Consume auth response

        // Send message
        $this->sendWebSocketMessage($socket, json_encode([
            'type' => 'message',
            'content' => 'Hello, world!',
        ]));

        $response = $this->receiveWebSocketMessage($socket);
        $data = json_decode($response, true);

        $this->assertEquals('message_sent', $data['status']);

        // Verify message stored in database
        $stmt = $this->getPdo()->prepare(
            'SELECT * FROM chat_messages WHERE user_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$user['id']]);
        $message = $stmt->fetch();

        $this->assertNotFalse($message);
        $this->assertEquals('Hello, world!', $message['content']);

        $this->closeWebSocket($socket);
    }

    public function testMultipleUsersReceiveBroadcast(): void
    {
        $user1 = UserFactory::create($this->getPdo());
        $user2 = UserFactory::create($this->getPdo());

        $socket1 = $this->connectWebSocket('ws://localhost:8080/chat');
        $socket2 = $this->connectWebSocket('ws://localhost:8080/chat');

        // Authenticate both
        $this->sendWebSocketMessage($socket1, json_encode([
            'type' => 'auth',
            'token' => $this->generateToken($user1),
        ]));
        $this->receiveWebSocketMessage($socket1);

        $this->sendWebSocketMessage($socket2, json_encode([
            'type' => 'auth',
            'token' => $this->generateToken($user2),
        ]));
        $this->receiveWebSocketMessage($socket2);

        // User1 sends message
        $this->sendWebSocketMessage($socket1, json_encode([
            'type' => 'message',
            'content' => 'Broadcast test',
        ]));

        // User2 should receive broadcast
        $response = $this->receiveWebSocketMessage($socket2);
        $data = json_decode($response, true);

        $this->assertEquals('message', $data['type']);
        $this->assertEquals('Broadcast test', $data['content']);

        $this->closeWebSocket($socket1);
        $this->closeWebSocket($socket2);
    }
}
```

---

## Section 18: Browser/End-to-End Testing

Test complete user workflows with browser automation.

### Browser Test Base Class

```php
# filename: tests/Integration/Browser/BrowserTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Browser;

use Tests\DatabaseTestCase;

abstract class BrowserTestCase extends DatabaseTestCase
{
    protected function visit(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'html' => $html,
            'status' => $httpCode,
        ];
    }

    protected function submitForm(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'html' => $html,
            'status' => $httpCode,
        ];
    }

    protected function assertPageContains(string $html, string $text): void
    {
        $this->assertStringContainsString($text, $html);
    }
}
```

### End-to-End Tests

```php
# filename: tests/Integration/Browser/UserRegistrationE2ETest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Browser;

use Tests\Integration\Browser\BrowserTestCase;

class UserRegistrationE2ETest extends BrowserTestCase
{
    public function testCompleteRegistrationFlow(): void
    {
        // Visit registration page
        $response = $this->visit('http://localhost:8000/register');
        $this->assertEquals(200, $response['status']);
        $this->assertPageContains($response['html'], 'Register');

        // Submit registration form
        $response = $this->submitForm('http://localhost:8000/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        // Should redirect to dashboard
        $this->assertEquals(302, $response['status']);

        // Verify user created in database
        $stmt = $this->getPdo()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(['john@example.com']);
        $user = $stmt->fetch();

        $this->assertNotFalse($user);
        $this->assertEquals('John Doe', $user['name']);
    }

    public function testLoginFlow(): void
    {
        // Create user first
        $stmt = $this->getPdo()->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            'Jane Doe',
            'jane@example.com',
            password_hash('password123', PASSWORD_ARGON2ID),
        ]);

        // Visit login page
        $response = $this->visit('http://localhost:8000/login');
        $this->assertEquals(200, $response['status']);

        // Submit login form
        $response = $this->submitForm('http://localhost:8000/login', [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to dashboard
        $this->assertEquals(302, $response['status']);
    }

    public function testInvalidLoginShowsError(): void
    {
        $response = $this->visit('http://localhost:8000/login');

        $response = $this->submitForm('http://localhost:8000/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Should stay on login page with error
        $this->assertEquals(200, $response['status']);
        $this->assertPageContains($response['html'], 'Invalid credentials');
    }
}
```

---

## Section 19: Configuration Testing

Test application behavior with different configurations.

### Configuration Test Base Class

```php
# filename: tests/Integration/Configuration/ConfigurationTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Configuration;

use Tests\DatabaseTestCase;

abstract class ConfigurationTestCase extends DatabaseTestCase
{
    protected array $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalEnv = $_ENV;
    }

    protected function tearDown(): void
    {
        $_ENV = $this->originalEnv;
        parent::tearDown();
    }

    protected function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}
```

### Configuration Integration Tests

```php
# filename: tests/Integration/Configuration/AppConfigurationTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\Configuration;

use Tests\Integration\Configuration\ConfigurationTestCase;
use App\Config\AppConfig;

class AppConfigurationTest extends ConfigurationTestCase
{
    public function testAppUsesProductionDatabaseInProduction(): void
    {
        $this->setEnv('APP_ENV', 'production');
        $this->setEnv('DB_DATABASE', 'production_db');

        $config = new AppConfig();
        $dbConfig = $config->getDatabaseConfig();

        $this->assertEquals('production_db', $dbConfig['database']);
        $this->assertFalse($dbConfig['debug']);
    }

    public function testAppUsesTestDatabaseInTesting(): void
    {
        $this->setEnv('APP_ENV', 'testing');
        $this->setEnv('DB_DATABASE', 'test_db');

        $config = new AppConfig();
        $dbConfig = $config->getDatabaseConfig();

        $this->assertEquals('test_db', $dbConfig['database']);
        $this->assertTrue($dbConfig['debug']);
    }

    public function testCacheDriverConfiguration(): void
    {
        // Test Redis cache
        $this->setEnv('CACHE_DRIVER', 'redis');
        $config = new AppConfig();
        $this->assertEquals('redis', $config->getCacheDriver());

        // Test file cache
        $this->setEnv('CACHE_DRIVER', 'file');
        $config = new AppConfig();
        $this->assertEquals('file', $config->getCacheDriver());

        // Test array cache (for testing)
        $this->setEnv('CACHE_DRIVER', 'array');
        $config = new AppConfig();
        $this->assertEquals('array', $config->getCacheDriver());
    }

    public function testMailConfiguration(): void
    {
        $this->setEnv('MAIL_MAILER', 'smtp');
        $this->setEnv('MAIL_HOST', 'smtp.example.com');
        $this->setEnv('MAIL_PORT', '587');

        $config = new AppConfig();
        $mailConfig = $config->getMailConfig();

        $this->assertEquals('smtp', $mailConfig['mailer']);
        $this->assertEquals('smtp.example.com', $mailConfig['host']);
        $this->assertEquals(587, $mailConfig['port']);
    }
}
```

---

## Section 20: Rate Limiting & Throttling Tests

Test API rate limiting and throttling behavior.

### Rate Limiting Test Base Class

```php
# filename: tests/Integration/RateLimit/RateLimitTestCase.php
<?php

declare(strict_types=1);

namespace Tests\Integration\RateLimit;

use Tests\ApiTestCase;

abstract class RateLimitTestCase extends ApiTestCase
{
    protected function makeRequest(string $method, string $uri, array $headers = []): array
    {
        $response = match ($method) {
            'GET' => $this->get($uri, $headers),
            'POST' => $this->post($uri, [], $headers),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
        };

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $response->getBody(),
        ];
    }

    protected function assertRateLimitHeader(array $headers, int $limit, int $remaining): void
    {
        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertEquals($limit, (int) $headers['X-RateLimit-Limit']);
        $this->assertEquals($remaining, (int) $headers['X-RateLimit-Remaining']);
    }
}
```

### Rate Limiting Integration Tests

```php
# filename: tests/Integration/RateLimit/ApiRateLimitTest.php
<?php

declare(strict_types=1);

namespace Tests\Integration\RateLimit;

use Tests\Integration\RateLimit\RateLimitTestCase;
use Tests\Factories\UserFactory;

class ApiRateLimitTest extends RateLimitTestCase
{
    public function testRateLimitHeadersPresent(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        $response = $this->makeRequest('GET', '/api/posts', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertArrayHasKey('X-RateLimit-Limit', $response['headers']);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $response['headers']);
        $this->assertArrayHasKey('X-RateLimit-Reset', $response['headers']);
    }

    public function testRateLimitEnforced(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);
        $limit = 60; // 60 requests per minute

        // Make requests up to limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->makeRequest('GET', '/api/posts', [
                'Authorization' => "Bearer {$token}",
            ]);
            $this->assertEquals(200, $response['status']);
        }

        // Next request should be rate limited
        $response = $this->makeRequest('GET', '/api/posts', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(429, $response['status']);
        $this->assertArrayHasKey('Retry-After', $response['headers']);
    }

    public function testRateLimitResetsAfterWindow(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        // Exhaust rate limit
        for ($i = 0; $i < 60; $i++) {
            $this->makeRequest('GET', '/api/posts', [
                'Authorization' => "Bearer {$token}",
            ]);
        }

        // Wait for reset (in real test, might need to mock time)
        sleep(61);

        // Should be able to make request again
        $response = $this->makeRequest('GET', '/api/posts', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(200, $response['status']);
    }

    public function testDifferentEndpointsHaveSeparateLimits(): void
    {
        $user = UserFactory::create($this->getPdo());
        $token = $this->generateToken($user);

        // Exhaust limit on one endpoint
        for ($i = 0; $i < 60; $i++) {
            $this->makeRequest('GET', '/api/posts', [
                'Authorization' => "Bearer {$token}",
            ]);
        }

        // Different endpoint should still work
        $response = $this->makeRequest('GET', '/api/users', [
            'Authorization' => "Bearer {$token}",
        ]);

        $this->assertEquals(200, $response['status']);
    }

    public function testRateLimitByIPAddress(): void
    {
        // Make requests from same IP
        for ($i = 0; $i < 100; $i++) {
            $response = $this->makeRequest('GET', '/api/public-endpoint');
        }

        // Should be rate limited
        $response = $this->makeRequest('GET', '/api/public-endpoint');
        $this->assertEquals(429, $response['status']);
    }
}
```

---

## Exercises

Practice integration testing concepts:

### Exercise 1: Repository Tests

**Goal**: Write comprehensive integration tests for a UserRepository class.

Create a file called `tests/Integration/Repositories/UserRepositoryExerciseTest.php` and implement:

- Test `create()` method inserts user into database and returns user object with ID
- Test `findById()` returns correct user or null if not found
- Test `findByEmail()` returns user by email address
- Test `update()` modifies existing user data in database
- Test `delete()` removes user from database
- Test `findAll()` returns all users in database
- Test unique email constraint prevents duplicate emails
- Test cascade delete removes related posts when user is deleted

**Validation**: Run your tests:

```bash
vendor/bin/phpunit tests/Integration/Repositories/UserRepositoryExerciseTest.php
```

Expected output:

```
OK (8 tests, 15 assertions)
```

### Exercise 2: API Endpoint Tests

**Goal**: Create comprehensive API tests for a Posts REST API.

Create a file called `tests/Integration/Api/PostsApiExerciseTest.php` and implement:

- Test `GET /api/posts` returns list of published posts (200 OK)
- Test `GET /api/posts/{id}` returns single post or 404 if not found
- Test `POST /api/posts` requires authentication (401 Unauthorized)
- Test `POST /api/posts` with valid data creates post (201 Created)
- Test `POST /api/posts` with invalid data returns validation errors (422)
- Test `PUT /api/posts/{id}` requires ownership (403 Forbidden)
- Test `DELETE /api/posts/{id}` removes post from database (204 No Content)
- Test pagination with `?page=1&limit=10` parameters
- Verify database state after each operation

**Validation**: Run your tests:

```bash
vendor/bin/phpunit tests/Integration/Api/PostsApiExerciseTest.php
```

Expected output:

```
OK (9 tests, 25+ assertions)
```

### Exercise 3: Transaction Tests

**Goal**: Implement transactional test isolation to ensure clean database state.

Create a file called `tests/Integration/TransactionExerciseTest.php` that extends `TransactionalTestCase` and implement:

- Test that data created in one test is not visible in the next test
- Test that rollback occurs automatically after each test
- Test multiple operations within a single transaction
- Verify that `tearDown()` properly rolls back all changes
- Test that database is clean before each test runs

**Validation**: Run your tests multiple times to verify consistency:

```bash
vendor/bin/phpunit tests/Integration/TransactionExerciseTest.php --repeat 5
```

Expected output:

```
OK (5 tests, 10 assertions)
```

All tests should pass consistently, proving transaction isolation works correctly.

---

## Common Pitfalls

**❌ Sharing State Between Tests**

```php
<?php
// Bad - Tests depend on execution order
class BadTest extends TestCase
{
    private static $userId;

    public function testCreateUser(): void
    {
        self::$userId = $this->repository->create(['name' => 'Test'])->id;
    }

    public function testUpdateUser(): void
    {
        // Depends on previous test!
        $this->repository->update(self::$userId, ['name' => 'Updated']);
    }
}

// Good - Each test is independent
class GoodTest extends TestCase
{
    public function testUpdateUser(): void
    {
        // Create own test data
        $user = UserFactory::create($this->getPdo());
        $this->repository->update($user['id'], ['name' => 'Updated']);
    }
}
```

**❌ Not Cleaning Up Test Data**

```php
<?php
// Bad - Leaves data in database
public function testCreateUser(): void
{
    $this->repository->create(['name' => 'Test']);
    // Data remains after test
}

// Good - Use transactions or cleanup
public function testCreateUser(): void
{
    $this->getPdo()->beginTransaction();
    $this->repository->create(['name' => 'Test']);
    $this->getPdo()->rollBack();
}
```

**❌ Testing Too Much in One Test**

```php
<?php
// Bad - Testing entire workflow
public function testCompleteUserWorkflow(): void
{
    $user = $this->service->register(...);
    $this->service->verifyEmail($user);
    $this->service->updateProfile($user, ...);
    $this->service->subscribe($user, ...);
    // Too much - hard to debug failures
}

// Good - Separate tests
public function testRegisterUser(): void { /* ... */ }
public function testVerifyEmail(): void { /* ... */ }
public function testUpdateProfile(): void { /* ... */ }
```

---

## Best Practices Summary

✅ **Use test databases** - Never test against production
✅ **Isolate tests** - Each test should be independent
✅ **Use transactions** - Rollback for automatic cleanup
✅ **Seed test data** - Use factories and seeders
✅ **Test happy and sad paths** - Success and failure cases
✅ **Keep tests fast** - Use in-memory databases when possible
✅ **Clean up** - Remove test data after tests
✅ **Use realistic data** - Test with production-like scenarios
✅ **Test integrations** - Verify components work together
✅ **Automate in CI/CD** - Run tests on every commit

---

## Further Reading

- [PHPUnit Database Testing](https://phpunit.de/manual/current/en/database.html)
- [Docker for Testing](https://docs.docker.com/get-started/)
- [Database Testing Best Practices](https://www.martinfowler.com/articles/nonDeterminism.html)
- [Integration Testing Strategies](https://martinfowler.com/bliki/IntegrationTest.html)

---

## Wrap-up

Congratulations! You've completed a comprehensive chapter on integration testing in PHP. Let's review what you've accomplished:

✅ **Understood the difference** between unit tests and integration tests, and when to use each
✅ **Set up test databases** with proper isolation and cleanup strategies
✅ **Created database tests** that verify data persistence and relationships
✅ **Built API endpoint tests** with authentication, validation, and error handling
✅ **Implemented seeders and factories** for efficient test data creation
✅ **Used transaction rollback** for automatic test cleanup and isolation
✅ **Configured in-memory databases** for fast test execution
✅ **Mocked external APIs** to test service integrations without real dependencies
✅ **Set up Docker environments** for consistent test execution
✅ **Integrated tests into CI/CD pipelines** for automated quality assurance
✅ **Measured application performance** and tested scalability under load
✅ **Tested database migrations** safely to prevent data loss
✅ **Verified cache behavior** including hits, misses, and invalidation
✅ **Tested background jobs** and queue processing workflows
✅ **Validated file uploads** and file system operations
✅ **Tested email functionality** without sending real emails
✅ **Tested WebSocket connections** and real-time broadcasting
✅ **Created end-to-end tests** for complete user workflows
✅ **Tested application configuration** across different environments
✅ **Verified rate limiting** and API throttling behavior

### Key Takeaways

Integration tests are essential for verifying that different parts of your application work together correctly. Unlike unit tests that isolate individual components, integration tests use real databases, real dependencies, and real workflows to catch issues that only appear when components interact.

The most important principles for integration testing are:
- **Isolation**: Each test should be independent and not rely on other tests
- **Cleanup**: Always clean up test data to prevent test pollution
- **Speed**: Use in-memory databases and transactions to keep tests fast
- **Realism**: Test with production-like data and scenarios
- **Automation**: Run tests in CI/CD pipelines to catch issues early

### Next Steps

In the next chapter, you'll learn about code quality tools that help maintain high standards across your PHP codebase, including static analysis, code style checkers, and automated code review tools.

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Distinguish between unit and integration tests
- [ ] Set up test databases with proper isolation
- [ ] Write tests that verify database persistence
- [ ] Test API endpoints with HTTP requests
- [ ] Use seeders and factories for test data
- [ ] Implement transaction rollback for cleanup
- [ ] Configure SQLite for fast in-memory tests
- [ ] Mock external API dependencies
- [ ] Run tests in Docker containers
- [ ] Integrate tests into CI/CD pipelines
- [ ] Measure and test application performance
- [ ] Test database migrations safely
- [ ] Test cache behavior and invalidation
- [ ] Test background jobs and queues
- [ ] Test file uploads and storage
- [ ] Test email sending with mocks
- [ ] Test WebSocket connections
- [ ] Write end-to-end browser tests
- [ ] Test application configuration
- [ ] Test rate limiting and throttling

---


<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit">← Chapter 12</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/14-code-quality-tools">Chapter 14 →</a></div>
</div>
