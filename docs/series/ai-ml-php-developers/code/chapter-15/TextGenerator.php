<?php

declare(strict_types=1);

/**
 * Simple text generation using OpenAI GPT models
 * 
 * Demonstrates prompt engineering and parameter tuning for creative text generation.
 * 
 * Usage:
 *   $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
 *   $generator = new TextGenerator($client);
 *   $result = $generator->generate("Write a haiku about PHP");
 *   echo $result['text'];
 */
final class TextGenerator
{
    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {}

    /**
     * Generate text based on a prompt
     *
     * @param string $prompt The prompt to generate from
     * @param float $temperature Creativity (0.0-2.0): 0.3=focused, 1.0=creative
     * @param int $maxTokens Maximum response length
     * @param string|null $systemPrompt Optional system instruction
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generate(
        string $prompt,
        float $temperature = 0.7,
        int $maxTokens = 500,
        ?string $systemPrompt = null,
    ): array {
        $messages = [];

        // Add system prompt if provided
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Make API request
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        // Calculate cost (approximate for gpt-3.5-turbo)
        $costPerThousandTokens = $this->getCostPerThousandTokens();
        $cost = ($response->usage->totalTokens / 1000) * $costPerThousandTokens;

        return [
            'text' => trim($response->choices[0]->message->content),
            'tokens' => $response->usage->totalTokens,
            'cost' => $cost,
        ];
    }

    /**
     * Generate creative story
     *
     * @param string $premise Story premise or starting point
     * @param int $maxWords Target word count (approximate)
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generateStory(string $premise, int $maxWords = 200): array
    {
        $systemPrompt = "You are a creative fiction writer who crafts engaging, " .
            "imaginative stories with vivid descriptions.";

        $prompt = "Write a short story (approximately {$maxWords} words) based on " .
            "this premise: {$premise}";

        return $this->generate(
            prompt: $prompt,
            temperature: 1.0,  // Higher for creativity
            maxTokens: (int)($maxWords * 1.5),  // Tokens ≈ 1.5x words
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate product description
     *
     * @param string $productName Name of the product
     * @param array<string> $features List of product features
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generateProductDescription(
        string $productName,
        array $features,
    ): array {
        $systemPrompt = "You are a marketing copywriter who creates compelling, " .
            "benefit-focused product descriptions.";

        $featureList = implode(', ', $features);
        $prompt = "Write a product description for '{$productName}' with these " .
            "features: {$featureList}. Focus on benefits and keep it under 100 words.";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.8,
            maxTokens: 200,
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate blog post outline
     *
     * @param string $topic Blog post topic
     * @param int $sections Number of main sections
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generateBlogOutline(string $topic, int $sections = 5): array
    {
        $systemPrompt = "You are a content strategist who creates well-structured " .
            "blog post outlines with SEO in mind.";

        $prompt = "Create a blog post outline for: {$topic}. Include {$sections} " .
            "main sections with brief descriptions of what each covers.";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.6,  // Lower for more structured output
            maxTokens: 400,
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate code documentation
     *
     * @param string $code Code snippet to document
     * @param string $language Programming language
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generateCodeDocumentation(string $code, string $language = 'PHP'): array
    {
        $systemPrompt = "You are a technical writer who creates clear, comprehensive code documentation.";

        $prompt = "Generate documentation for this {$language} code. Include a description, " .
            "parameters, return values, and usage examples:\n\n{$code}";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.4,  // Low for accuracy
            maxTokens: 500,
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Generate email draft
     *
     * @param string $context Email context and purpose
     * @param string $tone Desired tone (formal, friendly, apologetic, etc.)
     * @return array{text: string, tokens: int, cost: float}
     */
    public function generateEmail(string $context, string $tone = 'professional'): array
    {
        $systemPrompt = "You are an expert at writing {$tone} emails that are clear, " .
            "concise, and appropriate for business communication.";

        $prompt = "Write an email based on this context: {$context}. " .
            "Use a {$tone} tone. Include appropriate greeting and closing.";

        return $this->generate(
            prompt: $prompt,
            temperature: 0.7,
            maxTokens: 300,
            systemPrompt: $systemPrompt,
        );
    }

    /**
     * Get cost per thousand tokens for the current model
     */
    private function getCostPerThousandTokens(): float
    {
        return match ($this->model) {
            'gpt-4' => 0.03,
            'gpt-4-turbo', 'gpt-4-turbo-preview' => 0.01,
            'gpt-3.5-turbo', 'gpt-3.5-turbo-16k' => 0.002,
            default => 0.002,  // Default to gpt-3.5-turbo pricing
        };
    }
}
