<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\CodeGenerator;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$generator = new CodeGenerator($client);

try {
    echo "Generating a PHP function with focused settings:\n\n";

    $function = $generator->generateFunction(
        description: 'Validate and sanitize email addresses',
        parameters: ['email' => 'string'],
        returnType: 'string|null'
    );

    echo $function;

    echo "\n" . str_repeat('=', 50) . "\n\n";

    echo "Refactoring existing code:\n\n";

    $codeToRefactor = '<?php
function validateEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return strtolower(trim($email));
    }
    return null;
}
';

    $refactored = $generator->refactorCode($codeToRefactor, [
        'Add type hints and return types',
        'Use declare(strict_types=1)',
        'Add PHPDoc comments',
        'Improve error handling'
    ]);

    echo $refactored;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your ANTHROPIC_API_KEY is set correctly.\n";
}
