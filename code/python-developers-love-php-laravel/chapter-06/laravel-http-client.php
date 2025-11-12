<?php

declare(strict_types=1);

/**
 * Laravel HTTP Client Example
 * 
 * This example demonstrates how to make HTTP requests to external APIs using Laravel's HTTP Client.
 * Compare this to Python's requests library.
 * 
 * Location: app/Services/ExternalApiService.php
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ExternalApiService
{
    /**
     * Fetch user data from external API.
     * 
     * Compare to Python's requests.get()
     */
    public function fetchUserData(int $userId): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer token123',
                ])
                ->get("https://api.example.com/users/{$userId}");

            // Throw exception for bad status codes (similar to Python's response.raise_for_status())
            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            // Log error or handle gracefully
            // Similar to Python's RequestException handling
            logger()->error('External API error', [
                'url' => $e->request->url(),
                'status' => $e->response->status(),
            ]);
            return null;
        }
    }

    /**
     * Create user in external API.
     * 
     * Compare to Python's requests.post()
     */
    public function createUser(array $userData): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer token123',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.example.com/users', $userData);

        return [
            'data' => $response->json(),
            'status' => $response->status(),
        ];
    }

    /**
     * Make request with retry logic.
     * 
     * Compare to Python's manual retry implementation.
     */
    public function fetchWithRetry(string $url, int $maxRetries = 3): ?array
    {
        return Http::retry($maxRetries, 1000)
            ->get($url)
            ->json();
    }

    /**
     * Example: Fetch with custom retry conditions.
     */
    public function fetchWithCustomRetry(string $url): ?array
    {
        return Http::retry(3, 1000, function ($exception, $request) {
            // Only retry on 5xx errors
            return $exception instanceof RequestException 
                && $exception->response->status() >= 500;
        })
        ->get($url)
        ->json();
    }
}

/**
 * Usage Examples:
 * 
 * $service = new ExternalApiService();
 * 
 * // Fetch user data
 * $user = $service->fetchUserData(1);
 * 
 * // Create user
 * $result = $service->createUser([
 *     'name' => 'John Doe',
 *     'email' => 'john@example.com'
 * ]);
 * 
 * // Fetch with retry
 * $data = $service->fetchWithRetry('https://api.example.com/data');
 */

