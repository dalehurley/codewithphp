<?php

declare(strict_types=1);

namespace DataScience\Collectors;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class ApiCollector
{
    private Client $client;
    private int $rateLimitDelay; // Milliseconds between requests
    private int $maxRetries;
    private array $lastRequestTime = [];
    
    public function __construct(
        string $baseUri,
        array $headers = [],
        int $rateLimitDelay = 1000,
        int $maxRetries = 3
    ) {
        $this->rateLimitDelay = $rateLimitDelay;
        $this->maxRetries = $maxRetries;
        
        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout' => 30,
            'headers' => array_merge([
                'Accept' => 'application/json',
                'User-Agent' => 'PHP Data Collector/1.0',
            ], $headers),
        ]);
    }
    
    /**
     * Make GET request with retry logic
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $params]);
    }
    
    /**
     * Make POST request
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }
    
    /**
     * Core request method with retry and rate limiting
     */
    private function request(
        string $method,
        string $endpoint,
        array $options = []
    ): array {
        $this->enforceRateLimit($endpoint);
        
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->client->request($method, $endpoint, $options);
                $body = (string)$response->getBody();
                
                return json_decode($body, true) ?? [];
                
            } catch (RequestException $e) {
                $lastException = $e;
                $attempt++;
                
                // Check if we should retry
                $statusCode = $e->getResponse()?->getStatusCode();
                
                if ($statusCode && !$this->shouldRetry($statusCode)) {
                    throw new RuntimeException(
                        "API request failed with status {$statusCode}: " .
                        $e->getMessage()
                    );
                }
                
                // Exponential backoff
                $waitTime = $this->calculateBackoff($attempt);
                echo "Request failed (attempt {$attempt}), retrying in {$waitTime}ms...\n";
                usleep($waitTime * 1000);
                
            } catch (GuzzleException $e) {
                throw new RuntimeException(
                    "API request failed: " . $e->getMessage()
                );
            }
        }
        
        throw new RuntimeException(
            "API request failed after {$this->maxRetries} attempts: " .
            $lastException?->getMessage()
        );
    }
    
    /**
     * Enforce rate limiting between requests
     */
    private function enforceRateLimit(string $endpoint): void
    {
        $key = md5($endpoint);
        
        if (isset($this->lastRequestTime[$key])) {
            $elapsed = (microtime(true) - $this->lastRequestTime[$key]) * 1000;
            $waitTime = max(0, $this->rateLimitDelay - $elapsed);
            
            if ($waitTime > 0) {
                usleep((int)($waitTime * 1000));
            }
        }
        
        $this->lastRequestTime[$key] = microtime(true);
    }
    
    /**
     * Determine if status code should trigger retry
     */
    private function shouldRetry(int $statusCode): bool
    {
        // Retry on server errors and rate limiting
        return in_array($statusCode, [429, 500, 502, 503, 504]);
    }
    
    /**
     * Calculate exponential backoff delay
     */
    private function calculateBackoff(int $attempt): int
    {
        // Exponential backoff: 1s, 2s, 4s, 8s...
        return min(1000 * (2 ** ($attempt - 1)), 10000);
    }
    
    /**
     * Collect paginated data from API
     */
    public function collectPaginated(
        string $endpoint,
        string $pageParam = 'page',
        string $dataKey = 'data',
        int $maxPages = 100
    ): array {
        $allData = [];
        $page = 1;
        
        while ($page <= $maxPages) {
            echo "Fetching page {$page}...\n";
            
            $response = $this->get($endpoint, [$pageParam => $page]);
            
            // Extract data from response
            $data = $response[$dataKey] ?? $response;
            
            if (empty($data)) {
                break; // No more data
            }
            
            $allData = array_merge($allData, $data);
            $page++;
            
            // Check if there's a next page indicator
            if (isset($response['has_more']) && !$response['has_more']) {
                break;
            }
        }
        
        echo "✓ Collected " . count($allData) . " items from {$page} pages\n";
        
        return $allData;
    }
}


