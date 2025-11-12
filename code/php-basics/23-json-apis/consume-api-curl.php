<?php

declare(strict_types=1);

/**
 * Consuming REST APIs with cURL
 * 
 * Demonstrates making HTTP requests to external APIs
 */

echo "=== Consuming REST APIs with cURL ===" . PHP_EOL . PHP_EOL;

// 1. Basic GET request
echo "1. Basic GET Request:" . PHP_EOL;

function makeGetRequest(string $url): array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }

    return [
        'success' => true,
        'data' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

// Example: JSONPlaceholder API (free test API)
$result = makeGetRequest('https://jsonplaceholder.typicode.com/posts/1');

if ($result['success']) {
    echo "✓ Request successful" . PHP_EOL;
    echo "HTTP Code: " . $result['http_code'] . PHP_EOL;
    echo "Title: " . $result['data']['title'] . PHP_EOL;
} else {
    echo "✗ Request failed: " . $result['error'] . PHP_EOL;
}
echo PHP_EOL;

// 2. POST request
echo "2. POST Request:" . PHP_EOL;

function makePostRequest(string $url, array $data, array $headers = []): array
{
    $ch = curl_init($url);

    $jsonData = json_encode($data);

    $defaultHeaders = [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    return [
        'success' => empty($error),
        'data' => json_decode($response, true),
        'http_code' => $httpCode,
        'error' => $error ?: null
    ];
}

$newPost = [
    'title' => 'Test Post',
    'body' => 'This is a test post',
    'userId' => 1
];

$result = makePostRequest('https://jsonplaceholder.typicode.com/posts', $newPost);

if ($result['success']) {
    echo "✓ POST successful" . PHP_EOL;
    echo "Created post ID: " . $result['data']['id'] . PHP_EOL;
} else {
    echo "✗ POST failed: " . $result['error'] . PHP_EOL;
}
echo PHP_EOL;

// 3. Complete API Client Class
echo "3. API Client Class:" . PHP_EOL;

class ApiClient
{
    private string $baseUrl;
    private array $defaultHeaders = [];
    private int $timeout = 30;

    public function __construct(string $baseUrl, array $headers = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->defaultHeaders = $headers;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->request('GET', $url);
    }

    public function post(string $endpoint, array $data): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, $data);
    }

    public function put(string $endpoint, array $data): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('PUT', $url, $data);
    }

    public function delete(string $endpoint): array
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('DELETE', $url);
    }

    private function request(string $method, string $url, ?array $data = null): array
    {
        $ch = curl_init($url);

        $headers = array_merge(
            ['Content-Type: application/json'],
            $this->defaultHeaders
        );

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("API request failed: $error");
        }

        $decodedResponse = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status_code' => $httpCode,
            'data' => $decodedResponse,
        ];
    }

    private function buildUrl(string $endpoint, array $params = []): string
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function addHeader(string $name, string $value): void
    {
        $this->defaultHeaders[] = "$name: $value";
    }
}

// Using the API client
$api = new ApiClient('https://jsonplaceholder.typicode.com');

// GET with query parameters
$response = $api->get('/posts', ['userId' => 1]);
echo "Found " . count($response['data']) . " posts for user 1" . PHP_EOL;

// POST
$newPost = [
    'title' => 'API Client Test',
    'body' => 'Testing the API client class',
    'userId' => 1
];

$response = $api->post('/posts', $newPost);
if ($response['success']) {
    echo "✓ Created post via API client" . PHP_EOL;
}
echo PHP_EOL;

// 4. Error Handling Pattern
echo "4. Error Handling:" . PHP_EOL;

class ApiException extends \Exception
{
    private int $statusCode;
    private ?array $responseData;

    public function __construct(string $message, int $statusCode, ?array $responseData = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->responseData = $responseData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}

function safeApiCall(callable $callback): array
{
    try {
        return [
            'success' => true,
            'data' => $callback()
        ];
    } catch (ApiException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'status_code' => $e->getStatusCode()
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => 'Unexpected error: ' . $e->getMessage()
        ];
    }
}

$result = safeApiCall(function () use ($api) {
    return $api->get('/posts/9999'); // Non-existent post
});

if (!$result['success']) {
    echo "✗ API call failed (expected for demo)" . PHP_EOL;
}
echo PHP_EOL;

// 5. Rate Limiting Helper
echo "5. Rate Limiting:" . PHP_EOL;

class RateLimiter
{
    private int $maxRequests;
    private int $timeWindow;
    private array $requests = [];

    public function __construct(int $maxRequests, int $timeWindow)
    {
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    public function canMakeRequest(): bool
    {
        $now = time();

        // Remove old requests
        $this->requests = array_filter(
            $this->requests,
            fn($timestamp) => $timestamp > $now - $this->timeWindow
        );

        return count($this->requests) < $this->maxRequests;
    }

    public function recordRequest(): void
    {
        $this->requests[] = time();
    }

    public function getRemainingRequests(): int
    {
        $this->canMakeRequest(); // Clean old requests
        return max(0, $this->maxRequests - count($this->requests));
    }
}

// Allow 10 requests per 60 seconds
$limiter = new RateLimiter(10, 60);

if ($limiter->canMakeRequest()) {
    $limiter->recordRequest();
    echo "✓ Request allowed. Remaining: " . $limiter->getRemainingRequests() . PHP_EOL;
}

echo PHP_EOL;
echo "✓ API consumption examples complete!" . PHP_EOL;
