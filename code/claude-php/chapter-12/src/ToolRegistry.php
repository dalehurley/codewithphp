<?php

declare(strict_types=1);

namespace ClaudePHP\CustomTools;

/**
 * Dynamic tool registry for managing custom tools
 */
class ToolRegistry
{
    private array $tools = [];
    private array $categories = [];

    public function register(
        string $name,
        string $description,
        array $inputSchema,
        callable $handler,
        string $category = 'general'
    ): void {
        $this->tools[$name] = [
            'name' => $name,
            'description' => $description,
            'input_schema' => $inputSchema,
            'handler' => $handler,
            'category' => $category,
        ];

        $this->categories[$category][] = $name;
    }

    public function getTools(): array
    {
        return array_map(fn($tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'],
            'input_schema' => $tool['input_schema'],
        ], $this->tools);
    }

    public function execute(string $name, array $input): mixed
    {
        if (!isset($this->tools[$name])) {
            throw new \Exception("Tool '{$name}' not found");
        }

        return ($this->tools[$name]['handler'])($input);
    }

    public function getByCategory(string $category): array
    {
        return $this->categories[$category] ?? [];
    }
}
