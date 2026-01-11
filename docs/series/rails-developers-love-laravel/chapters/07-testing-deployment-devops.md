---
title: "07: Testing, Deployment, DevOps: Best Practices"
description: "Learn how to test, deploy, and maintain Laravel applications using your Rails DevOps knowledge"
series: rails-developers-love-laravel
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/rails-developers-love-laravel/chapters/06-building-rest-apis"
tags: ["laravel", "testing", "deployment", "devops", "ci-cd", "phpunit"]
teaches:
  - 'Understand the differences between PHPUnit and RSpec testing approaches'
  - 'Write unit and feature tests for Laravel applications'
  - 'Set up test factories and database testing strategies'
  - 'Configure CI/CD pipelines with GitHub Actions'
  - 'Deploy Laravel applications using multiple strategies'
---
<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 07</span>
</div>

![Testing, Deployment, DevOps](/images/rails-developers-love-laravel/chapter-07-testing-deployment-devops-hero-full.webp)

# 07: Testing, Deployment, DevOps <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Rails developers know the importance of testing and smooth deployments. Laravel provides excellent testing tools and deployment options that will feel familiar while offering some unique advantages.

This chapter covers everything from unit tests to production deployments, helping you translate your Rails DevOps knowledge to Laravel. You'll discover how Laravel's built-in testing tools compare to RSpec, how deployment workflows differ, and how to leverage Laravel's powerful DevOps ecosystem.

By the end of this chapter, you'll be able to write comprehensive tests, set up CI/CD pipelines, and deploy Laravel applications with confidence.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 06: Building REST APIs](/series/rails-developers-love-laravel/chapters/06-building-rest-apis) or equivalent understanding
- A Laravel application set up and running locally
- Basic familiarity with testing concepts (unit tests, integration tests)
- Git and GitHub account for CI/CD setup
- **Estimated Time**: ~90-120 minutes

**Verify your setup:**

```bash
# Check Laravel installation
php artisan --version

# Verify PHPUnit is available
./vendor/bin/phpunit --version
```

## What You'll Build

By the end of this chapter, you will have:

- A comprehensive test suite using PHPUnit and Pest
- Feature tests for API endpoints and authentication
- Test factories for generating test data
- A GitHub Actions CI/CD pipeline
- A deployment script for zero-downtime deployments
- Understanding of Laravel's testing and deployment ecosystem

## 📦 Code Samples

Complete testing examples and deployment patterns:

- **[TaskMaster Tests](https://github.com/dalehurley/codewithphp/tree/main/code/rails-developers-love-laravel/chapter-10/Tests)** — 16 comprehensive test cases using PHPUnit and Pest with factories and seeders
- **[TaskMaster Migrations](https://github.com/dalehurley/codewithphp/tree/main/code/rails-developers-love-laravel/chapter-10/Migrations)** — Database migration examples
- **[TaskMaster Factories](https://github.com/dalehurley/codewithphp/tree/main/code/rails-developers-love-laravel/chapter-10/Factories)** — Factory patterns for test data generation

Access code samples:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel/chapter-10
```

## Objectives

- Understand the differences between PHPUnit and RSpec testing approaches
- Write unit and feature tests for Laravel applications
- Set up test factories and database testing strategies
- Configure CI/CD pipelines with GitHub Actions
- Deploy Laravel applications using multiple strategies
- Implement zero-downtime deployment workflows
- Monitor and troubleshoot production applications

## Testing: PHPUnit vs RSpec

### Rails Testing Setup

```ruby
# Gemfile
group :development, :test do
  gem 'rspec-rails'
  gem 'factory_bot_rails'
  gem 'faker'
end

# spec/rails_helper.rb
RSpec.configure do |config|
  config.use_transactional_fixtures = true
  config.include FactoryBot::Syntax::Methods
end
```

### Laravel Testing Setup

```php
<?php
# filename: composer.json (excerpt)
// Laravel comes with PHPUnit pre-configured
{
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "mockery/mockery": "^1.6"
    }
}
```

```php
<?php
# filename: tests/TestCase.php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup code
    }
}
```

Laravel includes testing tools out of the box - no additional gems needed.

## Unit Tests

### Rails Unit Test (RSpec)

```ruby
# spec/models/user_spec.rb
require 'rails_helper'

RSpec.describe User, type: :model do
  describe 'validations' do
    it { should validate_presence_of(:name) }
    it { should validate_presence_of(:email) }
    it { should validate_uniqueness_of(:email) }
  end

  describe 'associations' do
    it { should have_many(:posts) }
  end

  describe '#full_name' do
    it 'returns first and last name' do
      user = create(:user, first_name: 'John', last_name: 'Doe')
      expect(user.full_name).to eq('John Doe')
    end
  end

  describe '#active?' do
    it 'returns true for active users' do
      user = create(:user, status: 'active')
      expect(user.active?).to be true
    end
  end
end
```

### Laravel Unit Test (PHPUnit)

```php
<?php
# filename: tests/Unit/UserTest.php
namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_is_active(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->assertTrue($user->isActive());
    }

    public function test_user_has_many_posts(): void
    {
        $user = User::factory()
            ->hasPosts(3)
            ->create();

        $this->assertCount(3, $user->posts);
        $this->assertInstanceOf(Collection::class, $user->posts);
    }
}
```

**Key Differences:**
- PHPUnit uses methods instead of blocks (no `describe`, `it`)
- Test method names should start with `test_` or use `@test` annotation
- Assertions are methods: `$this->assertEquals()`, `$this->assertTrue()`
- RefreshDatabase trait handles database cleanup

### Pest: Modern PHP Testing

Laravel also supports **Pest** - a modern testing framework with Ruby-like syntax:

```php
<?php
# filename: tests/Unit/UserTest.php
use App\Models\User;

it('has full name attribute', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($user->full_name)->toBe('John Doe');
});

it('is active when status is active', function () {
    $user = User::factory()->create(['status' => 'active']);

    expect($user->isActive())->toBeTrue();
});

it('has many posts', function () {
    $user = User::factory()
        ->hasPosts(3)
        ->create();

    expect($user->posts)->toHaveCount(3);
});
```

**Pest feels like RSpec!** It's becoming very popular in the Laravel community.

## Feature Tests (Integration Tests)

### Rails Feature Test

```ruby
# spec/features/post_management_spec.rb
require 'rails_helper'

RSpec.describe 'Post Management', type: :feature do
  let(:user) { create(:user) }

  before { sign_in user }

  describe 'creating a post' do
    it 'allows user to create a new post' do
      visit new_post_path

      fill_in 'Title', with: 'My New Post'
      fill_in 'Body', with: 'This is the content'
      click_button 'Create Post'

      expect(page).to have_content('Post was successfully created')
      expect(page).to have_content('My New Post')
    end
  end

  describe 'editing a post' do
    let(:post) { create(:post, user: user) }

    it 'allows user to edit their post' do
      visit edit_post_path(post)

      fill_in 'Title', with: 'Updated Title'
      click_button 'Update Post'

      expect(page).to have_content('Post was successfully updated')
      expect(page).to have_content('Updated Title')
    end
  end
end
```

### Laravel Feature Test

```php
<?php
# filename: tests/Feature/PostManagementTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/posts', [
                'title' => 'My New Post',
                'body' => 'This is the content',
            ]);

        $response->assertRedirect(route('posts.show', Post::first()));

        $this->assertDatabaseHas('posts', [
            'title' => 'My New Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_edit_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put(route('posts.update', $post), [
                'title' => 'Updated Title',
                'body' => $post->body,
            ]);

        $response->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_edit_others_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
            ->put(route('posts.update', $post), [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('posts', [
            'title' => 'Hacked Title',
        ]);
    }
}
```

**Key Differences:**
- Laravel tests HTTP endpoints directly (no Capybara browser simulation)
- `actingAs()` authenticates a user
- Fluent assertions: `assertRedirect()`, `assertForbidden()`
- Database assertions: `assertDatabaseHas()`, `assertDatabaseMissing()`

### With Pest

```php
<?php
# filename: tests/Feature/PostManagementTest.php
use App\Models\User;
use App\Models\Post;

it('allows user to create post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', [
            'title' => 'My New Post',
            'body' => 'Content',
        ])
        ->assertRedirect();

    expect(Post::where('title', 'My New Post'))->toExist();
});

it('prevents editing other users posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)
        ->put(route('posts.update', $post), ['title' => 'Hacked'])
        ->assertForbidden();
});
```

Much cleaner!

## HTTP Testing Assertions

Laravel provides powerful HTTP testing assertions that make API testing straightforward. Here are the most commonly used assertions:

### JSON Assertions

```php
<?php
# filename: tests/Feature/ApiPostTest.php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_post_as_json(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/posts/{$post->id}");

        // Assert JSON structure
        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'user_id' => $user->id,
        ]);

        // Assert JSON fragment (partial match)
        $response->assertJsonFragment([
            'title' => $post->title,
        ]);

        // Assert JSON structure (keys exist, values can vary)
        $response->assertJsonStructure([
            'id',
            'title',
            'body',
            'user' => [
                'id',
                'name',
            ],
            'created_at',
        ]);

        // Assert exact JSON match
        $response->assertExactJson([
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
        ]);
    }

    public function test_api_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', []);

        // Assert validation errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'body']);
    }
}
```

### Status Code and Header Assertions

```php
<?php
# filename: tests/Feature/ApiResponseTest.php
public function test_response_assertions(): void
{
    $response = $this->get('/posts');

    // Status codes
    $response->assertStatus(200);
    $response->assertOk();              // 200
    $response->assertCreated();         // 201
    $response->assertAccepted();        // 202
    $response->assertNoContent();       // 204
    $response->assertNotFound();        // 404
    $response->assertForbidden();       // 403
    $response->assertUnauthorized();    // 401
    $response->assertUnprocessable();   // 422
    $response->assertServerError();     // 500

    // Headers
    $response->assertHeader('Content-Type', 'application/json');
    $response->assertHeaderMissing('X-Custom-Header');

    // Cookies
    $response->assertCookie('session_id');
    $response->assertCookie('session_id', 'value');
    $response->assertCookieMissing('deleted_cookie');

    // Session
    $response->assertSessionHas('key', 'value');
    $response->assertSessionHas('key');
    $response->assertSessionHasAll(['key1' => 'value1', 'key2' => 'value2']);
    $response->assertSessionMissing('key');
    $response->assertSessionHasErrors(['field']);
    $response->assertSessionHasNoErrors();
}
```

### View and Redirect Assertions

```php
<?php
public function test_view_and_redirect_assertions(): void
{
    $response = $this->get('/posts/create');

    // View assertions
    $response->assertViewIs('posts.create');
    $response->assertViewHas('post');
    $response->assertViewHas('post', function ($post) {
        return $post->title === 'Expected Title';
    });
    $response->assertViewHasAll(['post', 'categories']);
    $response->assertViewMissing('deleted_variable');

    // Redirect assertions
    $response->assertRedirect('/posts');
    $response->assertRedirect(route('posts.index'));
    $response->assertRedirectToRoute('posts.show', ['post' => 1]);
}
```

**Key Differences from Rails:**
- Laravel's HTTP assertions are more fluent and chainable
- JSON testing is built-in (no need for `json_response` helpers)
- Session and cookie assertions are more comprehensive

## Database Testing

### Rails Database Strategy

```ruby
# spec/rails_helper.rb
RSpec.configure do |config|
  # Transactional fixtures - roll back after each test
  config.use_transactional_fixtures = true

  # Database cleaner for JavaScript tests
  config.before(:suite) do
    DatabaseCleaner.strategy = :transaction
    DatabaseCleaner.clean_with(:truncation)
  end
end
```

### Laravel Database Strategy

```php
<?php
# filename: tests/Feature/PostTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostTest extends TestCase
{
    // Option 1: Refresh entire database before each test
    use RefreshDatabase;

    // Option 2: Run migrations before each test
    use DatabaseMigrations;

    // Option 3: Transactions (rolls back after each test)
    use DatabaseTransactions;

    public function test_example(): void
    {
        // Database is clean for each test
    }
}
```

**RefreshDatabase** (most common):
- Runs migrations once per test suite
- Wraps each test in transaction
- Fast and clean

**DatabaseMigrations**:
- Runs migrations before each test
- Slower but more thorough

**DatabaseTransactions**:
- Wraps test in transaction
- Rolls back at end
- Fastest option

## Factories and Test Data

### Rails Factories (FactoryBot)

```ruby
# spec/factories/users.rb
FactoryBot.define do
  factory :user do
    name { Faker::Name.name }
    email { Faker::Internet.email }
    password { 'password123' }

    trait :admin do
      role { 'admin' }
    end

    factory :user_with_posts do
      transient do
        posts_count { 5 }
      end

      after(:create) do |user, evaluator|
        create_list(:post, evaluator.posts_count, user: user)
      end
    end
  end
end

# Usage
user = create(:user)
admin = create(:user, :admin)
user_with_posts = create(:user_with_posts, posts_count: 3)
```

### Laravel Factories

```php
<?php
# filename: database/factories/UserFactory.php
namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password123'),
        ];
    }

    // State modifier (like Rails traits)
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    // With relationships
    public function withPosts(int $count = 5): static
    {
        return $this->has(Post::factory()->count($count));
    }
}

// Usage
$user = User::factory()->create();
$admin = User::factory()->admin()->create();
$userWithPosts = User::factory()->withPosts(3)->create();

// Or using hasPosts relationship
$user = User::factory()
    ->hasPosts(3)
    ->create();
```

**Laravel Factory Advantages:**
- Type-safe with modern PHP
- Relationship methods auto-generated
- Fluent chaining
- Better IDE support

### Factory Relationships

**Rails:**
```ruby
# Create user with posts and comments
user = create(:user) do |u|
  create_list(:post, 3, user: u) do |post|
    create_list(:comment, 2, post: post)
  end
end
```

**Laravel:**
```php
<?php
# filename: tests/Feature/UserFactoryTest.php (example)
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

// Create user with posts and comments
$user = User::factory()
    ->has(
        Post::factory()
            ->count(3)
            ->has(Comment::factory()->count(2))
    )
    ->create();

// Or more readable (if relationships are defined)
$user = User::factory()
    ->hasPosts(3)
    ->create();
```

Laravel's factory API is incredibly intuitive!

## Mocking and Stubbing

### Rails Mocking (RSpec)

```ruby
# spec/services/payment_service_spec.rb
require 'rails_helper'

RSpec.describe PaymentService do
  describe '#charge' do
    let(:user) { create(:user) }
    let(:stripe_client) { instance_double(Stripe::Charge) }

    before do
      allow(Stripe::Charge).to receive(:create).and_return(stripe_client)
      allow(stripe_client).to receive(:id).and_return('ch_123')
    end

    it 'charges the user' do
      service = PaymentService.new(user)
      result = service.charge(100)

      expect(Stripe::Charge).to have_received(:create).with(
        amount: 100,
        currency: 'usd',
        customer: user.stripe_id
      )
      expect(result.transaction_id).to eq('ch_123')
    end
  end
end
```

### Laravel Mocking (Mockery)

```php
<?php
# filename: tests/Unit/PaymentServiceTest.php
namespace Tests\Unit;

use App\Services\PaymentService;
use App\Models\User;
use Stripe\Charge;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_charges_user(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_123']);

        // Mock Stripe Charge
        $chargeMock = \Mockery::mock('overload:' . Charge::class);
        $chargeMock->shouldReceive('create')
            ->once()
            ->with([
                'amount' => 100,
                'currency' => 'usd',
                'customer' => 'cus_123',
            ])
            ->andReturn((object) ['id' => 'ch_123']);

        $service = new PaymentService();
        $result = $service->charge($user, 100);

        $this->assertEquals('ch_123', $result->transaction_id);
    }
}
```

### Laravel Facades (Easier Mocking)

```php
<?php
# filename: tests/Feature/UserRegistrationTest.php
namespace Tests\Feature;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    public function test_sends_welcome_email(): void
    {
        // Fake the Mail facade
        Mail::fake();

        $user = User::factory()->create();
        $user->sendWelcomeEmail();

        // Assert email was sent
        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}

// Other facade fakes you can use:
// Queue::fake();
// Storage::fake();
// Event::fake();
// Notification::fake();
```

**Laravel's facade faking is easier than Rails mocking!**

## Testing Middleware, Events, and Commands

### Testing Middleware

```php
<?php
# filename: tests/Unit/Middleware/EnsureUserIsActiveTest.php
namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_active_users(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $middleware = new EnsureUserIsActive();
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_blocks_inactive_users(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);
        $middleware = new EnsureUserIsActive();
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }
}
```

### Testing Events

```php
<?php
# filename: tests/Feature/UserRegistrationTest.php
namespace Tests\Feature;

use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_fires_event(): void
    {
        Event::fake();

        $user = User::factory()->create();

        event(new UserRegistered($user));

        // Assert event was dispatched
        Event::assertDispatched(UserRegistered::class);

        // Assert event was dispatched with specific data
        Event::assertDispatched(UserRegistered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

        // Assert event was dispatched multiple times
        Event::assertDispatched(UserRegistered::class, 2);

        // Assert event was not dispatched
        Event::assertNotDispatched(AnotherEvent::class);
    }

    public function test_listener_handles_event(): void
    {
        Event::fake([UserRegistered::class]);

        $user = User::factory()->create();
        event(new UserRegistered($user));

        // Listener should have been called
        // (test the side effects of the listener)
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'registered',
        ]);
    }
}
```

### Testing Artisan Commands

```php
<?php
# filename: tests/Feature/Commands/SendDailyDigestTest.php
namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDailyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_digest_to_all_users(): void
    {
        Mail::fake();

        User::factory()->count(5)->create();

        $this->artisan('users:send-digest')
            ->expectsOutput('Sending daily digest to 5 users...')
            ->assertExitCode(0);

        Mail::assertSent(DailyDigestMail::class, 5);
    }

    public function test_command_with_arguments(): void
    {
        $this->artisan('user:create', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
            ->expectsQuestion('Enter password:', 'secret123')
            ->expectsOutput('User created successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_command_with_options(): void
    {
        $this->artisan('posts:cleanup', ['--days' => 30])
            ->expectsConfirmation('Delete posts older than 30 days?', 'yes')
            ->assertExitCode(0);
    }
}
```

### Testing File Uploads

```php
<?php
# filename: tests/Feature/FileUploadTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('avatars');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertRedirect('/profile');

        // Assert file was stored
        Storage::disk('avatars')->assertExists($file->hashName());

        // Assert file was stored with specific name
        Storage::disk('avatars')->assertExists("avatars/{$user->id}/{$file->hashName()}");
    }

    public function test_validates_file_type(): void
    {
        Storage::fake('avatars');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertSessionHasErrors(['avatar']);
    }
}
```

## Test Organization

### Rails Test Structure

```
spec/
├── models/
├── controllers/
├── requests/
├── features/
├── support/
└── factories/
```

### Laravel Test Structure

```
tests/
├── Unit/          # Unit tests
├── Feature/       # Integration tests
├── Browser/       # Dusk browser tests (optional)
└── TestCase.php   # Base test class
```

Simpler structure, clear separation.

## Laravel Dusk (Browser Testing)

Laravel Dusk is Laravel's browser testing tool, similar to Capybara in Rails. It uses ChromeDriver and provides a clean API for browser automation.

### Installation

```bash
# Install Dusk
composer require --dev laravel/dusk

# Install Dusk
php artisan dusk:install
```

### Basic Dusk Test

```php
<?php
# filename: tests/Browser/PostManagementTest.php
namespace Tests\Browser;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PostManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/posts/create')
                ->type('title', 'My New Post')
                ->type('body', 'This is the content')
                ->press('Create Post')
                ->assertPathIs('/posts')
                ->assertSee('My New Post')
                ->assertSee('Post was successfully created');
        });
    }

    public function test_user_can_edit_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                ->visit("/posts/{$post->id}/edit")
                ->type('title', 'Updated Title')
                ->press('Update Post')
                ->assertPathIs("/posts/{$post->id}")
                ->assertSee('Updated Title');
        });
    }

    public function test_user_can_delete_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                ->visit("/posts/{$post->id}")
                ->press('Delete')
                ->acceptDialog()  // Accept JavaScript confirm dialog
                ->assertPathIs('/posts')
                ->assertDontSee($post->title);
        });
    }
}
```

### Dusk Page Objects (Like Capybara Page Objects)

```php
<?php
# filename: tests/Browser/Pages/PostCreatePage.php
namespace Tests\Browser\Pages;

use Laravel\Dusk\Page;

class PostCreatePage extends Page
{
    public function url(): string
    {
        return '/posts/create';
    }

    public function elements(): array
    {
        return [
            '@title' => 'input[name="title"]',
            '@body' => 'textarea[name="body"]',
            '@submit' => 'button[type="submit"]',
        ];
    }

    public function createPost(Browser $browser, string $title, string $body): void
    {
        $browser->type('@title', $title)
            ->type('@body', $body)
            ->press('@submit');
    }
}
```

```php
<?php
# Usage in test
public function test_using_page_object(): void
{
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit(new PostCreatePage)
            ->createPost('My Post', 'Content')
            ->assertPathIs('/posts');
    });
}
```

### Dusk Assertions

```php
<?php
$browser->assertTitle('Page Title')
    ->assertUrlIs('https://example.com/posts')
    ->assertPathIs('/posts')
    ->assertSee('Text on page')
    ->assertDontSee('Text not on page')
    ->assertSeeIn('@element', 'Text')
    ->assertVisible('@element')
    ->assertPresent('@element')
    ->assertMissing('@element')
    ->assertInputValue('@input', 'value')
    ->assertChecked('@checkbox')
    ->assertSelected('@select', 'option')
    ->assertHasClass('@element', 'class-name')
    ->assertAttribute('@element', 'data-id', '123');
```

**Key Differences from Capybara:**
- Dusk uses ChromeDriver (headless Chrome)
- More fluent API with method chaining
- Built-in page objects support
- Better integration with Laravel's authentication

## Running Tests

### Rails

```bash
# All tests
bundle exec rspec

# Specific file
bundle exec rspec spec/models/user_spec.rb

# Specific test
bundle exec rspec spec/models/user_spec.rb:12

# With coverage
COVERAGE=true bundle exec rspec

# Parallel tests
bundle exec parallel_test spec/
```

### Laravel

```bash
# All tests
php artisan test

# Or directly with PHPUnit
./vendor/bin/phpunit

# Specific file
php artisan test tests/Feature/PostTest.php

# Specific test
php artisan test --filter test_user_can_create_post

# With coverage
php artisan test --coverage

# Parallel tests
php artisan test --parallel
```

**Laravel's `php artisan test` provides prettier output than PHPUnit!**

## Continuous Integration

### Rails CI (GitHub Actions)

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v3

      - name: Setup Ruby
        uses: ruby/setup-ruby@v1
        with:
          ruby-version: 3.2
          bundler-cache: true

      - name: Setup Database
        env:
          RAILS_ENV: test
        run: |
          bin/rails db:create
          bin/rails db:schema:load

      - name: Run tests
        run: bundle exec rspec
```

### Laravel CI (GitHub Actions)

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: laravel_test
        options: >-
          --health-cmd "mysqladmin ping"
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
          extensions: mbstring, pdo_mysql
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Copy Environment File
        run: cp .env.example .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Run Migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_test
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan migrate

      - name: Run Tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_DATABASE: laravel_test
          DB_USERNAME: root
          DB_PASSWORD: password
        run: php artisan test --coverage --min=80
```

Nearly identical workflow structure!

## Deployment: Rails vs Laravel

### Rails Deployment (Capistrano)

```ruby
# Capfile
require 'capistrano/rails'
require 'capistrano/bundler'
require 'capistrano/rbenv'
require 'capistrano/puma'

# config/deploy.rb
set :application, 'my_app'
set :repo_url, 'git@github.com:user/repo.git'
set :deploy_to, '/var/www/my_app'
set :branch, 'main'

namespace :deploy do
  desc 'Restart application'
  task :restart do
    on roles(:app) do
      execute :touch, release_path.join('tmp/restart.txt')
    end
  end

  after :publishing, :restart
end

# Deploy
cap production deploy
```

### Laravel Deployment (Envoy)

```php
# filename: Envoy.blade.php
@servers(['web' => 'user@server.com'])

@setup
    $repository = 'git@github.com:user/repo.git';
    $releases_dir = '/var/www/releases';
    $app_dir = '/var/www/app';
    $release = date('YmdHis');
    $new_release_dir = $releases_dir .'/'. $release;
@endsetup

@story('deploy')
    clone_repository
    run_composer
    update_symlinks
    optimize
    migrate
    reload_php
@endstory

@task('clone_repository')
    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
    cd {{ $new_release_dir }}
    git reset --hard {{ $commit }}
@endtask

@task('run_composer')
    echo "Installing Composer dependencies"
    cd {{ $new_release_dir }}
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
@endtask

@task('update_symlinks')
    echo "Updating symlinks"
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}
    ln -nfs {{ $app_dir }}/storage/app/public {{ $app_dir }}/public/storage
@endtask

@task('optimize')
    echo "Optimizing application"
    cd {{ $app_dir }}
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
@endtask

@task('migrate')
    echo "Running migrations"
    cd {{ $app_dir }}
    php artisan migrate --force
@endtask

@task('reload_php')
    echo "Reloading PHP-FPM"
    sudo systemctl reload php8.4-fpm
@endtask
```

```bash
# Deploy using Envoy
php vendor/bin/envoy run deploy
```

Both use similar task-based deployment strategies.

## Laravel Forge (Managed Hosting)

**Laravel Forge** is like Heroku for Laravel (but better):

### Features
- ✅ One-click server provisioning (AWS, DigitalOcean, etc.)
- ✅ Automatic deployments from Git
- ✅ SSL certificates (Let's Encrypt)
- ✅ Database backups
- ✅ Queue workers management
- ✅ Scheduled jobs (cron)
- ✅ Server monitoring
- ✅ Easy rollbacks

### Forge Deployment

```bash
# 1. Connect your repository in Forge UI
# 2. Configure deployment script (Forge provides default)
# 3. Enable quick deploy on push

# Default Forge deploy script:
cd /home/forge/example.com
git pull origin main
composer install --no-interaction --prefer-dist --optimize-autoloader

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

# 4. Push to trigger deployment
git push origin main
```

**Forge is significantly easier than managing Rails servers!**

## Docker and Containerization

Docker provides a consistent environment across development, staging, and production, similar to how Rails developers use Docker.

### Basic Docker Setup

```dockerfile
# Dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
```

### Docker Compose for Development

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - laravel

  nginx:
    image: nginx:alpine
    container_name: laravel_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - laravel

  db:
    image: mysql:8.0
    container_name: laravel_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: root
      MYSQL_PASSWORD: secret
      MYSQL_USER: laravel
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - laravel

  redis:
    image: redis:alpine
    container_name: laravel_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - laravel

volumes:
  dbdata:
    driver: local

networks:
  laravel:
    driver: bridge
```

### Running Laravel in Docker

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Run tests
docker-compose exec app php artisan test

# View logs
docker-compose logs -f app

# Stop containers
docker-compose down

# Rebuild after changes
docker-compose up -d --build
```

### Production Docker Deployment

```bash
# Build production image
docker build -t myapp:latest .

# Run container
docker run -d \
  --name myapp \
  -p 80:9000 \
  -v $(pwd)/.env:/var/www/.env \
  myapp:latest

# Or use docker-compose for production
docker-compose -f docker-compose.prod.yml up -d
```

### Docker with Laravel Sail

Laravel Sail is Laravel's official Docker development environment:

```bash
# Install Sail
composer require laravel/sail --dev

# Publish Sail config
php artisan sail:install

# Start Sail
./vendor/bin/sail up -d

# Run commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail test
```

**Key Differences from Rails:**
- Laravel Sail provides Docker setup out of the box
- Similar Docker patterns to Rails
- PHP-FPM instead of Puma/Unicorn
- Nginx instead of Passenger (typically)

## Zero-Downtime Deployments

### Rails (Puma with Phased Restart)

```ruby
# config/puma.rb
workers ENV.fetch("WEB_CONCURRENCY") { 2 }
preload_app!

on_worker_boot do
  ActiveRecord::Base.establish_connection
end

# Phased restart
pumactl phased-restart
```

### Laravel (Octane or FPM Reload)

```bash
# Option 1: Laravel Octane (recommended)
php artisan octane:reload

# Option 2: PHP-FPM reload
sudo systemctl reload php8.4-fpm

# Option 3: Swoole hot reload
php artisan octane:reload --swoole
```

**Laravel Octane** provides instant reloads without downtime.

## Blue-Green Deployments

Blue-green deployment is a strategy that reduces downtime and risk by running two identical production environments. Only one environment is live at a time.

### Concept

- **Blue Environment**: Current production (live)
- **Green Environment**: New version (staging)
- Switch traffic from Blue to Green when ready
- Keep Blue as fallback

### Implementation with Laravel

```bash
# 1. Set up two identical servers/environments
# Blue: production-blue.example.com
# Green: production-green.example.com

# 2. Deploy to Green (non-live)
cd /var/www/green
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Test Green environment
curl https://production-green.example.com/health

# 4. Switch load balancer to Green
# (Update DNS or load balancer config)

# 5. Monitor Green
tail -f /var/www/green/storage/logs/laravel.log

# 6. If issues, switch back to Blue
# If successful, Blue becomes new staging for next deployment
```

### Using Laravel Forge

Forge supports blue-green deployments:

```bash
# In Forge deployment script
cd /home/forge/green.example.com
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Switch DNS/load balancer to green
# Monitor and verify
# Then update blue for next deployment
```

### Database Considerations

```bash
# Run migrations on Green BEFORE switching
# Both environments can share same database
# OR use database replication

# Option 1: Shared database (simpler)
# Both Blue and Green connect to same database
# Migrations run on Green before switch

# Option 2: Database replication (safer)
# Green has its own database
# Replicate data before switch
# More complex but allows rollback
```

### Automated Blue-Green Script

```bash
#!/bin/bash
# blue-green-deploy.sh

set -e

GREEN_DIR="/var/www/green"
BLUE_DIR="/var/www/blue"
CURRENT_ENV=$(cat /var/www/current-env.txt)

if [ "$CURRENT_ENV" = "blue" ]; then
    DEPLOY_TO="green"
    DEPLOY_DIR=$GREEN_DIR
else
    DEPLOY_TO="blue"
    DEPLOY_DIR=$BLUE_DIR
fi

echo "🚀 Deploying to $DEPLOY_TO environment..."

cd $DEPLOY_DIR
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Health check
if curl -f http://$DEPLOY_TO.example.com/health; then
    echo "✅ Health check passed"
    
    # Switch traffic (update load balancer/DNS)
    echo "$DEPLOY_TO" > /var/www/current-env.txt
    
    echo "✅ Switched to $DEPLOY_TO environment"
else
    echo "❌ Health check failed - keeping current environment"
    exit 1
fi
```

**Benefits:**
- Zero downtime deployments
- Easy rollback (switch back to previous environment)
- Test new version before going live
- Reduced risk

## Database Migrations in Production

### Rails

```bash
# Run migrations
RAILS_ENV=production bundle exec rails db:migrate

# Rollback if needed
RAILS_ENV=production bundle exec rails db:rollback

# Check migration status
RAILS_ENV=production bundle exec rails db:migrate:status
```

### Laravel

```bash
# Run migrations
php artisan migrate --force

# Rollback last batch
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=3

# Check migration status
php artisan migrate:status

# Refresh (drop all + migrate - DANGEROUS!)
php artisan migrate:fresh --force
```

**Best Practice (Both Frameworks):**
1. Test migrations locally first
2. Backup database before migration
3. Make migrations reversible
4. Deploy migrations separately from code
5. Monitor application during migration

## Environment Configuration

### Rails (.env)

```bash
# .env.production
DATABASE_URL=postgresql://user:pass@host/db
REDIS_URL=redis://localhost:6379/0
SECRET_KEY_BASE=long_secret_key
RAILS_ENV=production
RACK_ENV=production
```

### Laravel (.env)

```bash
# .env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:generated_key

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

Laravel's `.env` is more verbose but clearer.

## Asset Compilation and Bundling

Laravel uses **Vite** (or Laravel Mix) for asset compilation, similar to Webpacker in Rails.

### Laravel Vite (Recommended)

```bash
# Install Vite (included in Laravel 9+)
npm install

# Development
npm run dev

# Production build
npm run build
```

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Content --}}
</body>
</html>
```

### Laravel Mix (Legacy)

```bash
# Install Mix
npm install

# Development
npm run dev

# Production with versioning
npm run production
```

```javascript
// webpack.mix.js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .version();  // Adds hash for cache busting
```

```blade
{{-- In Blade templates --}}
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<script src="{{ mix('js/app.js') }}"></script>
```

### Asset Versioning for Cache Busting

```php
<?php
# filename: config/app.php (excerpt)
// Laravel automatically handles asset versioning with Vite
// For Mix, use mix() helper which adds hash to filename

// In Blade:
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
// Outputs: /css/app.css?id=abc123 (hash changes on rebuild)
```

### Production Asset Optimization

```bash
# Build for production
npm run build

# This will:
# - Minify CSS and JavaScript
# - Remove comments
# - Tree-shake unused code
# - Generate source maps (optional)
# - Add version hashes
```

**Key Differences from Rails:**
- Laravel uses Vite (faster) or Mix (Webpack wrapper)
- No need for separate asset pipeline configuration
- Built-in versioning with `mix()` helper
- Hot module replacement in development

## Logging and Monitoring

### Rails Logging

```ruby
# config/environments/production.rb
config.log_level = :info
config.log_formatter = ::Logger::Formatter.new

# Log rotation
config.logger = ActiveSupport::Logger.new(
  Rails.root.join('log', 'production.log'),
  1, 50.megabytes
)

# Usage
Rails.logger.info "User #{user.id} logged in"
Rails.logger.error "Payment failed: #{error.message}"
```

### Laravel Logging

```php
<?php
# filename: config/logging.php
return [
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'level' => 'error',
        ],
    ],
];
```

```php
<?php
# filename: app/Http/Controllers/AuthController.php (example usage)
use Illuminate\Support\Facades\Log;

// Usage examples
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['error' => $exception->getMessage()]);
Log::channel('slack')->critical('Database connection lost');
```

**Laravel's logging is more flexible:**
- Multiple channels
- Stack multiple drivers
- Slack/email notifications built-in
- Context-aware logging

## Performance Monitoring

### Rails (New Relic / Skylight)

```ruby
# Gemfile
gem 'newrelic_rpm'
gem 'skylight'

# config/newrelic.yml
production:
  app_name: My App
  license_key: <%= ENV['NEW_RELIC_LICENSE_KEY'] %>
  monitor_mode: true
```

### Laravel (Laravel Telescope / Horizon)

```bash
# Install Telescope (development monitoring)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Install Horizon (queue monitoring)
composer require laravel/horizon
php artisan horizon:install
```

**Laravel Telescope** provides incredible insights:
- Request timings
- Database queries
- Cache operations
- Queue jobs
- Exceptions
- Logs
- All built-in!

## Queue Workers

### Rails (Sidekiq)

```ruby
# Gemfile
gem 'sidekiq'

# app/workers/send_email_worker.rb
class SendEmailWorker
  include Sidekiq::Worker

  def perform(user_id)
    user = User.find(user_id)
    UserMailer.welcome(user).deliver_now
  end
end

# Enqueue
SendEmailWorker.perform_async(user.id)

# Start worker
bundle exec sidekiq
```

### Laravel (Built-in Queues)

```php
<?php
# filename: app/Jobs/SendEmail.php
namespace App\Jobs;

use App\Mail\Welcome;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Mail::to($this->user)->send(new Welcome($this->user));
    }
}

// Enqueue
SendEmail::dispatch($user);

// Or delay
SendEmail::dispatch($user)->delay(now()->addMinutes(10));

// Start worker
php artisan queue:work
```

**Laravel queues are built-in!** No Sidekiq or Redis required (though supported).

## Scheduled Tasks

### Rails (whenever gem)

```ruby
# Gemfile
gem 'whenever', require: false

# config/schedule.rb
every 1.day, at: '4:30 am' do
  runner "User.send_daily_digest"
end

every :hour do
  rake "cleanup:old_sessions"
end

# Generate crontab
whenever --update-crontab
```

### Laravel (Built-in Task Scheduling)

```php
<?php
# filename: app/Console/Kernel.php
namespace App\Console;

use App\Jobs\ProcessPodcast;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('users:send-digest')
            ->dailyAt('04:30');

        $schedule->command('cleanup:old-sessions')
            ->hourly();

        $schedule->job(new ProcessPodcast)
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('backup:run')
            ->daily()
            ->onFailure(fn() => Log::error('Backup failed'));
    }
}
```

```bash
# Single cron entry (add to crontab)
# crontab -e
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Laravel's scheduler is built-in and more powerful!**

## Complete Deployment Checklist

### Pre-Deployment

- [ ] All tests passing
- [ ] Database migrations tested
- [ ] Environment variables configured
- [ ] Assets compiled
- [ ] Dependencies updated
- [ ] Security checks passed

### Deployment

```bash
# Laravel deployment steps

# 1. Enable maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Migrate database
php artisan migrate --force

# 5. Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers
php artisan queue:restart

# 7. Disable maintenance mode
php artisan up

# 8. Monitor logs
tail -f storage/logs/laravel.log
```

### Post-Deployment

- [ ] Verify application is running
- [ ] Check error logs
- [ ] Monitor performance metrics
- [ ] Test critical features
- [ ] Verify scheduled tasks
- [ ] Check queue workers

::: tip Pro Tip: Zero-Downtime Deployment Script
Create a deployment script that ensures zero downtime:

```bash
#!/bin/bash
# deploy.sh - Zero-downtime Laravel deployment

set -e # Exit on any error

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations (non-blocking)
php artisan migrate --force

# Clear old caches
php artisan cache:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Reload PHP-FPM (zero downtime!)
sudo systemctl reload php8.4-fpm

echo "✅ Deployment complete!"

# Health check
curl -f http://localhost/health || echo "⚠️  Health check failed!"
```

**Make it executable:**
```bash
chmod +x deploy.sh
./deploy.sh
```
:::

::: warning Common Deployment Gotchas
**Issue #1: Cache Config Breaks Environment Variables**

```bash
# ❌ PROBLEM: After running config:cache, .env changes ignored
php artisan config:cache

# Why: Config is cached, .env not read anymore

# ✅ SOLUTION: Clear cache when changing .env
php artisan config:clear
# Then update .env
# Then cache again
php artisan config:cache
```

**Issue #2: Migrations Fail in Production**

```bash
# ❌ PROBLEM: Migration works locally, fails in production
php artisan migrate
# Error: Syntax error near...

# Why: Different MySQL/PostgreSQL versions

# ✅ SOLUTION: Test migrations on production-like database first
# Use Laravel's databaseRefresher in tests
```

**Issue #3: File Permissions**

```bash
# ❌ PROBLEM: 500 errors after deployment
# storage/logs/laravel.log: Permission denied

# ✅ SOLUTION: Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Issue #4: Queue Workers Not Processing**

```bash
# ❌ PROBLEM: Jobs stuck in queue after deployment

# Why: Old workers still running with old code

# ✅ SOLUTION: Always restart workers after deploy
php artisan queue:restart

# Or use Supervisor to auto-restart
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
```

**Issue #5: CSS/JS Not Updating**

```bash
# ❌ PROBLEM: Users see old CSS after deployment

# Why: Browser caching + no asset versioning

# ✅ SOLUTION: Version assets with Laravel Mix
// webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .version(); // Adds hash to filename

// In Blade:
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
```

**Issue #6: Application Key Missing**

```bash
# ❌ PROBLEM: "No application encryption key has been specified"

# ✅ SOLUTION: Generate key in production
php artisan key:generate

# Or copy from .env.example and generate
cp .env.example .env
php artisan key:generate
```
:::

## Troubleshooting Production Issues

### Debug Mode in Production

**Never enable debug in production!**

```bash
# ❌ NEVER DO THIS IN PRODUCTION
# .env
APP_DEBUG=true  # Exposes sensitive info!
```

```php
<?php
# filename: config/logging.php (excerpt)
// ✅ Instead, log errors properly
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'], // Alert on errors
    ],
],
```

### Finding the Root Cause

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
tail -f /var/log/php8.4-fpm.log

# Check system logs
tail -f /var/log/syslog
```

### Performance Debugging

```bash
# Enable query logging temporarily
DB::enableQueryLog();
// Your code
dd(DB::getQueryLog());

# Use Laravel Telescope in staging
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Use Laravel Debugbar in development
composer require barryvdh/laravel-debugbar --dev
```

## Wrap-up

Congratulations! You've completed a comprehensive overview of testing, deployment, and DevOps in Laravel. Here's what you've accomplished:

- ✅ **Understood testing frameworks** - Compared PHPUnit and Pest to RSpec
- ✅ **Written comprehensive tests** - Unit tests, feature tests, and database tests
- ✅ **Set up test factories** - Created reusable test data generators
- ✅ **Configured CI/CD** - Built GitHub Actions workflows
- ✅ **Explored deployment options** - Forge, Envoy, and manual deployments
- ✅ **Implemented zero-downtime strategies** - Learned deployment best practices
- ✅ **Monitored applications** - Used Laravel Telescope and logging

### Key Takeaways

1. **Testing is Similar** - PHPUnit feels like RSpec, Pest even more so
2. **Pest is Amazing** - Ruby-like testing syntax for PHP
3. **Built-in Tools** - Queues, scheduling, logging all included
4. **Forge Simplifies Deployment** - Much easier than managing Rails servers
5. **Laravel Telescope** - Incredible debugging and monitoring tool
6. **Zero-Downtime Easy** - Octane and FPM reloads are seamless
7. **Better Mocking** - Facade faking is cleaner than Rails mocking

You now have the knowledge to test and deploy Laravel applications professionally. The next chapter will explore Laravel's rich ecosystem of packages and community resources.

## Practice Exercises

### Exercise 1: Write Comprehensive Tests
Create tests for a blog application:
- Unit tests for models
- Feature tests for CRUD operations
- Authentication tests
- API endpoint tests

### Exercise 2: Set Up CI/CD
Configure GitHub Actions:
- Run tests on push
- Deploy to staging on merge to main
- Deploy to production on tag

### Exercise 3: Deploy to Production
Deploy a Laravel app:
- Set up a server (DigitalOcean/AWS)
- Configure environment
- Set up database
- Deploy with Envoy or Forge
- Configure SSL
- Set up monitoring

## Further Reading

- [Laravel Testing Documentation](https://laravel.com/docs/testing) - Official Laravel testing guide
- [PHPUnit Documentation](https://phpunit.de/documentation.html) - Complete PHPUnit reference
- [Pest PHP Documentation](https://pestphp.com/docs) - Modern PHP testing framework
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment) - Official deployment guide
- [Laravel Forge Documentation](https://forge.laravel.com/docs) - Managed hosting platform
- [GitHub Actions Documentation](https://docs.github.com/en/actions) - CI/CD workflows
- [Laravel Telescope Documentation](https://laravel.com/docs/telescope) - Application debugging tool
- [Laravel Horizon Documentation](https://laravel.com/docs/horizon) - Queue monitoring dashboard

## What's Next?

Now that you know how to test and deploy Laravel applications, the next chapter explores Laravel's ecosystem, popular packages, and community resources.

---

::: tip Continue Learning
Move on to [Chapter 08: Ecosystem, Community, Packages](/series/rails-developers-love-laravel/chapters/08-ecosystem-community-packages) to discover Laravel's rich package ecosystem.
:::

<ChapterCheckbox 
  seriesId="rails-developers-love-laravel"
  chapterId="07"
  label="Completed Testing, Deployment, and DevOps best practices"
/>

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />

<style>
.deployment-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.testing-box {
  background: #f0fdf4;
  border-left: 4px solid #10b981;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
