<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ClaudePhp\ClaudePhp;

echo "Testing Claude-PHP-SDK integration...\n\n";

// Test 1: Basic client instantiation without API key (should fail gracefully)
echo "Test 1: ClaudePhp instantiation without API key...\n";
try {
    $client = new ClaudePhp(apiKey: '');
    echo "✗ ERROR: Should have failed with empty API key\n";
    exit(1);
} catch (\InvalidArgumentException $e) {
    echo "✓ Correctly rejected empty API key: {$e->getMessage()}\n";
} catch (\Exception $e) {
    echo "✗ Unexpected error: {$e->getMessage()}\n";
    exit(1);
}

// Test 2: Check if required classes exist and can be instantiated
echo "\nTest 2: Class loading and basic structure...\n";
try {
    // Check if ClaudePhp class exists
    if (!class_exists('ClaudePhp\ClaudePhp')) {
        throw new \Exception('ClaudePhp class not found');
    }
    echo "✓ ClaudePhp class found\n";

    // Check if required methods exist
    $reflection = new \ReflectionClass('ClaudePhp\ClaudePhp');
    if (!$reflection->hasMethod('messages')) {
        throw new \Exception('messages() method not found');
    }
    echo "✓ messages() method found\n";

    // Check constructor
    $constructor = $reflection->getConstructor();
    if (!$constructor) {
        throw new \Exception('No constructor found');
    }

    $params = $constructor->getParameters();
    $apiKeyParam = null;
    foreach ($params as $param) {
        if ($param->getName() === 'apiKey') {
            $apiKeyParam = $param;
            break;
        }
    }

    if (!$apiKeyParam) {
        throw new \Exception('apiKey parameter not found in constructor');
    }
    echo "✓ Constructor has apiKey parameter\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}

// Test 3: Test with fake API key (should instantiate but fail on API call)
echo "\nTest 3: ClaudePhp instantiation with fake API key...\n";
try {
    $client = new ClaudePhp(apiKey: 'fake-api-key-for-testing');
    echo "✓ ClaudePhp instantiated with fake API key\n";

    // Test that messages() method returns the right type
    $messagesResource = $client->messages();
    echo "✓ messages() method returns: " . get_class($messagesResource) . "\n";

    // Check if create method exists
    $reflection = new \ReflectionClass($messagesResource);
    if (!$reflection->hasMethod('create')) {
        throw new \Exception('create() method not found on messages resource');
    }
    echo "✓ create() method found on messages resource\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}

// Test 4: Test response structure (mock)
echo "\nTest 4: Response structure validation...\n";
try {
    // We can't make real API calls, but we can test that our response
    // structure expectations match what the SDK expects

    // Check if response classes exist
    if (!class_exists('ClaudePhp\Responses\Message')) {
        throw new \Exception('Message response class not found');
    }
    echo "✓ Message response class found\n";

    if (!class_exists('ClaudePhp\Responses\Usage')) {
        throw new \Exception('Usage response class not found');
    }
    echo "✓ Usage response class found\n";

    if (!class_exists('ClaudePhp\Responses\TextContent')) {
        throw new \Exception('TextContent response class not found');
    }
    echo "✓ TextContent response class found\n";

    // Check properties exist
    $usageReflection = new \ReflectionClass('ClaudePhp\Responses\Usage');
    if (!$usageReflection->hasProperty('input_tokens')) {
        throw new \Exception('input_tokens property not found on Usage class');
    }
    echo "✓ Usage.input_tokens property found\n";

    if (!$usageReflection->hasProperty('output_tokens')) {
        throw new \Exception('output_tokens property not found on Usage class');
    }
    echo "✓ Usage.output_tokens property found\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ ALL TESTS PASSED! Claude-PHP-SDK integration is working correctly.\n";
echo "\nNote: Actual API calls require a valid ANTHROPIC_API_KEY environment variable.\n";
echo "The SDK structure and classes are all properly configured.\n";
