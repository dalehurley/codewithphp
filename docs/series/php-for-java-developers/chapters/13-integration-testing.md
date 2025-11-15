---
title: "13: Integration Testing"
description: "Database testing, API testing, test fixtures, CI/CD"
series: "php-for-java-developers"
chapter: 13
order: 13
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit"
---

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
- Best practices for maintainable integration tests

## Prerequisites

Before starting this chapter, you should be comfortable with:
- Unit testing with PHPUnit (Chapter 12)
- Database operations with PDO (Chapter 9)
- REST API development (Chapter 10)
- Dependency injection (Chapter 11)

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
<?php

declare(strict_types=1);

namespace Tests;

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
<?php

// tests/bootstrap.php

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
<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

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
<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use Tests\DatabaseTestCase;

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
<?php

declare(strict_types=1);

namespace Tests\Seeders;

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
<?php

declare(strict_types=1);

namespace Tests\Factories;

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
<?php

declare(strict_types=1);

namespace Tests\Integration;

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
<?php

declare(strict_types=1);

namespace Tests;

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
<?php

declare(strict_types=1);

namespace Tests\Integration;

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
<?php

declare(strict_types=1);

namespace Tests;

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
<?php

declare(strict_types=1);

namespace Tests\Integration\Api;

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
        return JWT::encode([
            'userId' => $user['id'],
            'email' => $user['email'],
        ], $_ENV['JWT_SECRET']);
    }
}
```

---

## Section 7: In-Memory Databases

Use SQLite in-memory for fast tests.

### SQLite Configuration

```php
<?php

declare(strict_types=1);

namespace Tests;

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
<?php

declare(strict_types=1);

namespace Tests\Integration;

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
<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

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

## Exercises

Practice integration testing concepts:

### Exercise 1: Repository Tests

Write integration tests for a complete repository:

```php
<?php

// TODO: Test UserRepository
// - Create, read, update, delete operations
// - Test unique email constraint
// - Test cascade deletes for related data
// - Verify all database interactions
```

### Exercise 2: API Endpoint Tests

Create comprehensive API tests:

```php
<?php

// TODO: Test complete REST API
// - Test all CRUD endpoints
// - Test authentication requirements
// - Test validation errors
// - Test pagination
// - Verify database state after operations
```

### Exercise 3: Transaction Tests

Implement transactional test isolation:

```php
<?php

// TODO: Use transaction rollback
// - Ensure each test starts with clean database
// - Verify rollback works correctly
// - Test nested transactions if needed
```

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

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit">← Chapter 12</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/14-code-quality-tools">Chapter 14 →</a></div>
</div>
