<?php

/**
 * Basic Laravel Integration Examples
 */

use ClaudePhp\LaravelIntegration\Facades\Claude;

// Example 1: Simple message
$response = Claude::message('Tell me a joke about PHP');
echo $response->content();
echo "\nTokens used: " . $response->totalTokens() . "\n";

// Example 2: With system prompt
$response = Claude::message(
    messages: 'Explain Laravel middleware',
    system: 'You are a Laravel expert. Provide concise, accurate answers.'
);
echo $response->content();

// Example 3: Conversation
$conversation = Claude::conversation('You are a code review assistant');

$conversation
    ->user('Review this code: function add($a, $b) { return $a + $b; }')
    ->assistant('This is a simple addition function.')
    ->user('How can I improve it?');

$response = $conversation->send();
echo $response->content();

// Example 4: Continue conversation
$nextResponse = $conversation->continue('What about type safety?');
echo $nextResponse->content();

// Example 5: Streaming response
Claude::stream(
    messages: 'Write a short poem about coding',
    onChunk: function(string $chunk) {
        echo $chunk;
        flush();
    }
);
