<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class CodeGenerator
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function generateFunction(
        string $description,
        array $parameters = [],
        ?string $returnType = null
    ): string {
        $paramsList = '';
        foreach ($parameters as $name => $type) {
            $paramsList .= "{$type} \${$name}, ";
        }
        $paramsList = rtrim($paramsList, ', ');

        $returnHint = $returnType ? ": {$returnType}" : '';

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'temperature' => 0.3,  // Low for consistent, reliable code
            'top_p' => 0.85,       // Focused on conventional patterns
            'system' => 'Generate clean PHP 8.4+ code following PSR-12 standards. Include type hints, return types, and PHPDoc comments.',
            'messages' => [[
                'role' => 'user',
                'content' => "Generate a PHP function:\n\nDescription: {$description}\nParameters: {$paramsList}\nReturn type: {$returnHint}\n\nUse declare(strict_types=1) and modern PHP features."
            ]]
        ]);

        return $response->content[0]['text'] ?? '';
    }

    public function refactorCode(string $code, array $improvements = []): string
    {
        $improvementsList = implode("\n", array_map(fn($i) => "- {$i}", $improvements));

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'temperature' => 0.2,  // Very focused - we want reliable refactoring
            'top_p' => 0.8,
            'system' => 'Refactor PHP code following best practices. Maintain functionality while improving code quality.',
            'messages' => [[
                'role' => 'user',
                'content' => "Refactor this code:\n\n```php\n{$code}\n```\n\nFocus on:\n{$improvementsList}"
            ]]
        ]);

        return $response->content[0]['text'] ?? '';
    }
}
