<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY')
);

// Create agent with the client
$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.');

// Run the agent
$result = $agent->run('Say hello in one sentence and mention PHP.');

echo $result->getAnswer() . PHP_EOL;
