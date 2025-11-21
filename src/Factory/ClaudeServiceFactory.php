<?php
declare(strict_types=1);

namespace App\Factory;

use App\Configuration\ClaudeConfig;
use App\Contracts\ClaudeServiceInterface;
use App\Services\ClaudeService;
use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;

class ClaudeServiceFactory
{
    public static function create(
        ClaudeConfig $config,
        ?LoggerInterface $logger = null
    ): ClaudeServiceInterface {
        // Create the SDK Client
        $client = new ClaudePhp(
            apiKey: $config->apiKey,
            timeout: $config->timeout,
        );

        return new ClaudeService(
            client: $client,
            logger: $logger,
            config: [
                'model' => $config->defaultModel,
                'max_tokens' => $config->maxTokens,
                'temperature' => $config->temperature,
            ]
        );
    }
}
