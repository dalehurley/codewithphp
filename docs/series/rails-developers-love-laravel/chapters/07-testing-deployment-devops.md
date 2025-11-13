---
title: "Testing, Deployment, DevOps: Best Practices"
description: Learn how to test, deploy, and maintain Laravel applications using your Rails DevOps knowledge.
series: rails-developers-love-laravel
chapter: 7
difficulty: Intermediate
tags: ["laravel", "testing", "deployment", "devops", "ci-cd", "phpunit"]
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

# Chapter 07: Testing, Deployment, DevOps <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

Rails developers know the importance of testing and smooth deployments. Laravel provides excellent testing tools and deployment options that will feel familiar while offering some unique advantages.

This chapter covers everything from unit tests to production deployments, helping you translate your Rails DevOps knowledge to Laravel.

## What You'll Learn

- PHPUnit vs RSpec/Minitest
- Feature and unit testing in Laravel
- Database testing strategies
- Test factories and seeders
- CI/CD with GitHub Actions
- Deployment strategies (Forge, Envoyer, manual)
- Zero-downtime deployments
- Database migrations in production
- Monitoring and logging
- Performance optimization

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
// Laravel comes with PHPUnit pre-configured
// composer.json (included by default)
{
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "mockery/mockery": "^1.6"
    }
}

// tests/TestCase.php
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

Laravel includes testing tools out of the box—no additional gems needed.

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
// tests/Unit/UserTest.php
namespace Tests\Unit;

use App\Models\User;
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

Laravel also supports **Pest** — a modern testing framework with Ruby-like syntax:

```php
<?php
// tests/Unit/UserTest.php
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
// tests/Feature/PostManagementTest.php
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
namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
// database/factories/UserFactory.php
namespace Database\Factories;

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
// Create user with posts and comments
$user = User::factory()
    ->has(
        Post::factory()
            ->count(3)
            ->has(Comment::factory()->count(2))
    )
    ->create();

// Or more readable
$user = User::factory()
    ->hasPosts(3)
        ->hasComments(2)
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
// tests/Unit/PaymentServiceTest.php
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
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

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

// Other facade fakes
Queue::fake();
Storage::fake();
Event::fake();
Notification::fake();
```

**Laravel's facade faking is easier than Rails mocking!**

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

# Deploy
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
// config/logging.php
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
];

// Usage
use Illuminate\Support\Facades\Log;

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
// app/Jobs/SendEmail.php
namespace App\Jobs;

use App\Models\User;
use App\Mail\Welcome;
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
// app/Console/Kernel.php
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

// Single cron entry
* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
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

## Key Takeaways

1. **Testing is Similar** — PHPUnit feels like RSpec, Pest even more so
2. **Pest is Amazing** — Ruby-like testing syntax for PHP
3. **Built-in Tools** — Queues, scheduling, logging all included
4. **Forge Simplifies Deployment** — Much easier than managing Rails servers
5. **Laravel Telescope** — Incredible debugging and monitoring tool
6. **Zero-Downtime Easy** — Octane and FPM reloads are seamless
7. **Better Mocking** — Facade faking is cleaner than Rails mocking

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

## What's Next?

Now that you know how to test and deploy Laravel applications, the next chapter explores Laravel's ecosystem, popular packages, and community resources.

---

::: tip Continue Learning
Move on to [Chapter 08: Ecosystem, Community, Packages](/series/rails-developers-love-laravel/chapters/08-ecosystem-community-packages) to discover Laravel's rich package ecosystem.
:::

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
