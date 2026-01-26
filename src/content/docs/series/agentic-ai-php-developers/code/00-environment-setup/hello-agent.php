<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ClaudePhp\Agent\Agent;

$agent = Agent::make(
    apiKey: getenv('ANTHROPIC_API_KEY')
);

$response = $agent->run('Say hello in one sentence and mention PHP.');

echo $response->text() . PHP_EOL;
