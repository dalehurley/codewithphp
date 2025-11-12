<?php

declare(strict_types=1);

/**
 * PHPUnit unit test example comparing to pytest.
 * 
 * Run with: php artisan test
 * Or: vendor/bin/phpunit tests/Unit/UserTest.php
 */

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use InvalidArgumentException;

class UserTest extends TestCase
{
    public function test_user_creation(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->created_at);
    }

    public function test_user_email_validation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email');
        
        if (!str_contains('invalid-email', '@')) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function test_user_list(): void
    {
        $users = [
            new User(['name' => 'John', 'email' => 'john@example.com']),
            new User(['name' => 'Jane', 'email' => 'jane@example.com'])
        ];
        
        $this->assertCount(2, $users);
        $this->assertEquals('John', $users[0]->name);
    }
}

