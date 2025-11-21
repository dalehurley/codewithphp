<?php

declare(strict_types=1);

namespace ClaudePhp\PromptEngineering;

/**
 * PromptTemplate - Build reusable prompt templates
 */
class PromptTemplate
{
    private string $template;
    private array $variables = [];

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    /**
     * Set template variables
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Render the template with variables
     */
    public function render(): string
    {
        $output = $this->template;
        foreach ($this->variables as $key => $value) {
            $output = str_replace("{{" . $key . "}}", (string)$value, $output);
        }
        return $output;
    }

    /**
     * Create a few-shot prompt
     */
    public static function fewShot(string $task, array $examples, string $input): string
    {
        $prompt = "Task: {$task}\n\nExamples:\n";

        foreach ($examples as $example) {
            $prompt .= "\nInput: {$example['input']}\n";
            $prompt .= "Output: {$example['output']}\n";
        }

        $prompt .= "\nNow process this input:\n{$input}";

        return $prompt;
    }

    /**
     * Create a chain-of-thought prompt
     */
    public static function chainOfThought(string $question): string
    {
        return "{$question}\n\nThink through this step-by-step:\n1. ";
    }
}
