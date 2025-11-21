<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\ClaudePhp;

class WeaviateClient
{
    private Client $http;
    
    public function __construct(
        private string $host,
        private string $scheme = 'http',
        private ?string $apiKey = null
    ) {
        $headers = ['Content-Type' => 'application/json'];
        if ($apiKey) {
            $headers['Authorization'] = "Bearer {$apiKey}";
        }
        
        $this->http = new ClaudePhp([
            'base_uri' => "{$scheme}://{$host}",
            'headers' => $headers
        ]);
    }
    
    public function createObject(string $className, array $properties, ?array $vector = null): array
    {
        $payload = [
            'class' => $className,
            'properties' => $properties
        ];
        
        if ($vector) {
            $payload['vector'] = $vector;
        }
        
        $response = $this->http->post('/v1/objects', ['json' => $payload]);
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function nearVector(string $className, array $vector, int $limit = 10): array
    {
        $query = [
            'nearVector' => ['vector' => $vector],
            'limit' => $limit
        ];
        
        $graphql = <<<GQL
        {
            Get {
                {$className}(nearVector: {vector: $vector, limit: $limit}) {
                    _additional { id distance }
                }
            }
        }
        GQL;
        
        $response = $this->http->post('/v1/graphql', [
            'json' => ['query' => $graphql]
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function deleteObject(string $id): bool
    {
        $response = $this->http->delete("/v1/objects/{$id}");
        return $response->getStatusCode() === 204;
    }
    
    public function getSchema(): array
    {
        $response = $this->http->get('/v1/schema');
        return json_decode($response->getBody()->getContents(), true);
    }
}
