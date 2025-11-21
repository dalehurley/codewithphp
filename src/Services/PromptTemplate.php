<?php
declare(strict_types=1);

namespace App\Services;

class PromptTemplate
{
    public function __construct(
        private string $template,
        private array $defaults = []
    ) {}

    public function render(array $variables = []): string
    {
        $merged = array_merge($this->defaults, $variables);

        $output = $this->template;

        foreach ($merged as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $output = str_replace($placeholder, (string) $value, $output);
        }

        if (preg_match('/\{\{([^}]+)\}\}/', $output, $matches)) {
            throw new \RuntimeException("Unresolved template variable: {$matches[1]}");
        }

        return $output;
    }
}
