<?php

declare(strict_types=1);

namespace DocsGenerator;

use ClaudePhp\ClaudePhp;

/**
 * API Documentation Generator using Claude
 */
class ApiDocsGenerator
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = new ClaudePhp(apiKey: $apiKey);
    }

    /**
     * Generate documentation for a class
     */
    public function generateClassDocs(array $classInfo): string
    {
        $classStructure = json_encode($classInfo, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Generate comprehensive documentation for this PHP class:

{$classStructure}

Include:
- Class overview and purpose
- Usage examples
- Method descriptions with parameters and return values
- Property descriptions
- Code examples
- Best practices

Format in Markdown.
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    /**
     * Generate API endpoint documentation
     */
    public function generateEndpointDocs(string $method, string $route, array $controller): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Generate API documentation for this endpoint:

Method: {$method}
Route: {$route}
Controller: {$controller['class']}::{$controller['method']}

Include:
- Endpoint description
- Request parameters
- Request body (if applicable)
- Response format
- Status codes
- Example requests/responses
- Error handling

Format in Markdown.
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    /**
     * Generate README from codebase
     */
    public function generateReadme(array $projectStructure): string
    {
        $structure = json_encode($projectStructure, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Generate a comprehensive README.md for this PHP project:

Project Structure:
{$structure}

Include:
- Project title and description
- Features list
- Installation instructions
- Usage examples
- API documentation links
- Configuration
- Contributing guidelines
- License information

Format in Markdown.
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    /**
     * Generate changelog entry
     */
    public function generateChangelog(array $commits): string
    {
        $commitsText = implode("\n", array_map(
            fn($c) => "- {$c['message']} ({$c['hash']})",
            $commits
        ));

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Generate a changelog entry for these commits:

{$commitsText}

Format in standard changelog format with sections:
- Added
- Changed
- Fixed
- Deprecated
- Removed
- Security
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }

    /**
     * Improve existing documentation
     */
    public function improveDocs(string $existingDocs): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Improve this documentation:

{$existingDocs}

Make it:
- More comprehensive
- Better structured
- Include more examples
- Add missing details
- Fix any errors or unclear sections

Return the improved documentation in Markdown.
PROMPT,
            ]],
        ]);

        return $response->content[0]['text'];
    }
}
