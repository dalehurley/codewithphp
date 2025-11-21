<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\ClaudePhp;

echo "Custom Tools - API Integration\n\n";

$client = new ClaudePhp(['timeout' => 10]);

function createAPITool(string $name, string $url): array
{
    return [
        'name' => $name,
        'description' => "Fetch data from {$url}",
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'endpoint' => ['type' => 'string'],
                'params' => ['type' => 'object'],
            ],
        ],
        'handler' => function($input) use ($client, $url) {
            try {
                $response = $client->get($url . $input['endpoint'], [
                    'query' => $input['params'] ?? [],
                ]);
                return json_decode($response->getBody()->getContents(), true);
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        },
    ];
}

// Example: GitHub API tool
$githubTool = createAPITool('github_api', 'https://api.github.com');
echo "Created GitHub API tool\n";
echo "Tool name: {$githubTool['name']}\n";
echo "Description: {$githubTool['description']}\n";

// Simulate tool execution
echo "\nSimulating API call to /users/octocat\n";
$result = ($githubTool['handler'])(['endpoint' => '/users/octocat', 'params' => []]);
if (isset($result['login'])) {
    echo "✓ User found: {$result['login']}\n";
    echo "  Name: {$result['name']}\n";
    echo "  Public repos: {$result['public_repos']}\n";
}
