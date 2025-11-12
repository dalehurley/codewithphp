<?php

declare(strict_types=1);

/**
 * PHPUnit mocks and Laravel fakes example comparing to pytest fixtures and mocks.
 * 
 * Run with: php artisan test tests/Unit/UserServiceTest.php
 */

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_create_user_sends_email(): void
    {
        // Create mock
        $emailService = Mockery::mock(EmailService::class);
        $emailService->shouldReceive('sendWelcomeEmail')
            ->once()
            ->with('john@example.com');
        
        // Inject mock
        $userService = new UserService($emailService);
        
        // Test
        $user = $userService->createUser('John', 'john@example.com');
        
        $this->assertEquals('John', $user->name);
        Mockery::close();
    }

    public function test_external_api_call_with_fake(): void
    {
        // Fake HTTP client
        Http::fake([
            'api.example.com/*' => Http::response(['status' => 'ok'], 200)
        ]);
        
        $response = Http::get('https://api.example.com/data');
        
        $this->assertEquals('ok', $response->json()['status']);
        
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/data';
        });
    }

    public function test_mail_fake(): void
    {
        Mail::fake();
        
        // Code that sends email
        Mail::to('user@example.com')->send(new \App\Mail\WelcomeEmail());
        
        Mail::assertSent(\App\Mail\WelcomeEmail::class, function ($mail) {
            return $mail->hasTo('user@example.com');
        });
    }
}

