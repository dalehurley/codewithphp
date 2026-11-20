<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AnthropicException;

$config = require __DIR__ . '/../bootstrap.php';
$client = new ClaudePhp(apiKey: $config['apiKey']);

try {
    $response = $client->messages()->create([
        'model' => $config['model'],
        'max_tokens' => 256,
        'messages' => [
            ['role' => 'user', 'content' => 'Return one JSON object with the string keys name and purpose for Composer. Use no surrounding prose.'],
        ],
    ]);

    $text = '';
    foreach ($response->content as $block) {
        if ($block['type'] === 'text') {
            $text .= $block['text'];
        }
    }

    $text = trim($text);
    if (preg_match('/\A```(?:json)?\s*\R(.*?)\R```\z/is', $text, $matches) === 1) {
        $text = $matches[1];
    }

    $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
    if (!is_string($data['name'] ?? null) || !is_string($data['purpose'] ?? null)) {
        throw new UnexpectedValueException('Expected string values for name and purpose.');
    }

    printf("%s: %s\n", $data['name'], $data['purpose']);
} catch (JsonException | UnexpectedValueException $error) {
    fwrite(STDERR, "Claude did not return the expected JSON object.\n");
    exit(1);
} catch (AnthropicException $error) {
    fwrite(STDERR, "The Claude request failed. Check your configuration and try again.\n");
    exit(1);
}
