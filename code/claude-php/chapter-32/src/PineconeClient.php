<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\ClaudePhp;

class PineconeClient
{
    private Client $http;
    
    public function __construct(
        private string $apiKey,
        private string $environment,
        private string $indexName
    ) {
        $this->http = new ClaudePhp([
            'base_uri' => "https://{$indexName}-{$environment}.svc.pinecone.io",
            'headers' => ['Api-Key' => $apiKey]
        ]);
    }
    
    public function upsert(array $vectors): array
    {
        $response = $this->http->post('/vectors/upsert', [
            'json' => ['vectors' => $vectors]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function query(array $vector, int $topK = 10, ?array $filter = null): array
    {
        $payload = [
            'vector' => $vector,
            'topK' => $topK,
            'includeMetadata' => true
        ];
        
        if ($filter) {
            $payload['filter'] = $filter;
        }
        
        $response = $this->http->post('/query', ['json' => $payload]);
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function delete(array $ids): array
    {
        $response = $this->http->post('/vectors/delete', [
            'json' => ['ids' => $ids]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }
    
    public function describeIndexStats(): array
    {
        $response = $this->http->get('/describe_index_stats');
        return json_decode($response->getBody()->getContents(), true);
    }
}
