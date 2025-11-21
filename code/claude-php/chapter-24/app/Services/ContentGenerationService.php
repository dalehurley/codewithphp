<?php

declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use Anthropic\Resources\Messages;

class ContentGenerationService
{
    private Messages $messages;

    public function __construct()
    {
        $client = new ClaudePhp(
                apiKey: config('services.anthropic.api_key')
            );

        $this->messages = $client->messages();
    }

    public function generateBlogPost(
        string $topic,
        string $tone,
        string $length,
        array $keywords
    ): array {
        $wordCount = match($length) {
            'short' => '500-700',
            'medium' => '1000-1500',
            'long' => '2000-3000',
        };

        $keywordsText = !empty($keywords) ? "\nInclude these keywords: " . implode(', ', $keywords) : '';

        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => "Write a {$tone} blog post about: {$topic}\n\n"
                    . "Length: {$wordCount} words\n"
                    . "{$keywordsText}\n\n"
                    . "Include:\n"
                    . "- Engaging title\n"
                    . "- Introduction\n"
                    . "- Main sections with subheadings\n"
                    . "- Conclusion\n"
                    . "- Call to action",
            ]],
        ]);

        return [
            'text' => $response->content[0]->text,
            'tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
            'metadata' => [
                'topic' => $topic,
                'tone' => $tone,
                'length' => $length,
            ],
        ];
    }

    public function generateProductDescription(
        string $productName,
        array $features,
        string $targetAudience,
        string $tone
    ): string {
        $featuresText = "- " . implode("\n- ", $features);

        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => "Write a {$tone} product description for: {$productName}\n\n"
                    . "Target audience: {$targetAudience}\n\n"
                    . "Features:\n{$featuresText}\n\n"
                    . "Make it compelling and highlight key benefits.",
            ]],
        ]);

        return $response->content[0]->text;
    }

    public function generateSocialMediaPosts(string $content, array $platforms): array
    {
        $posts = [];

        foreach ($platforms as $platform) {
            $constraints = match($platform) {
                'twitter' => 'Maximum 280 characters. Use hashtags.',
                'facebook' => 'Engaging post with emoji. Can be longer.',
                'linkedin' => 'Professional tone. Include relevant insights.',
                'instagram' => 'Visual description with hashtags.',
            };

            $response = $this->messages->create([
                'model' => config('services.anthropic.model'),
                'max_tokens' => 500,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Create a {$platform} post about:\n{$content}\n\n"
                        . "Constraints: {$constraints}",
                ]],
            ]);

            $posts[$platform] = $response->content[0]->text;
        }

        return $posts;
    }

    public function generateEmail(
        string $type,
        string $subject,
        array $keyPoints,
        string $tone
    ): array {
        $pointsText = "- " . implode("\n- ", $keyPoints);

        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => "Create a {$tone} {$type} email.\n\n"
                    . "Subject: {$subject}\n\n"
                    . "Key points to cover:\n{$pointsText}\n\n"
                    . "Include:\n"
                    . "- Compelling subject line (may improve the provided one)\n"
                    . "- Preview text\n"
                    . "- Email body with proper formatting\n"
                    . "- Clear call to action",
            ]],
        ]);

        $content = $response->content[0]->text;

        // Parse response (simplified - would need better parsing)
        return [
            'subject' => $subject,
            'preview_text' => substr($content, 0, 100),
            'body' => $content,
        ];
    }

    public function batchGenerate(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $result = match($item['type']) {
                'blog' => $this->generateBlogPost(
                    $item['data']['topic'],
                    $item['data']['tone'] ?? 'professional',
                    $item['data']['length'] ?? 'medium',
                    $item['data']['keywords'] ?? []
                ),
                'product' => ['text' => $this->generateProductDescription(
                    $item['data']['product_name'],
                    $item['data']['features'],
                    $item['data']['target_audience'] ?? 'general',
                    $item['data']['tone'] ?? 'professional'
                )],
                default => ['text' => '', 'tokens' => 0],
            };

            $results[] = $result;
        }

        return $results;
    }

    public function improveContent(string $content, array $improvements): array
    {
        $improvementsText = implode(', ', $improvements);

        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => "Improve this content focusing on: {$improvementsText}\n\n"
                    . "Original content:\n{$content}\n\n"
                    . "Provide the improved version and list key changes made.",
            ]],
        ]);

        return [
            'text' => $response->content[0]->text,
            'changes' => $improvements,
        ];
    }

    public function generateMetaTags(string $content, array $keywords): array
    {
        $keywordsText = !empty($keywords) ? "\nFocus keywords: " . implode(', ', $keywords) : '';

        $response = $this->messages->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => "Generate SEO meta tags for this content:\n{$content}\n{$keywordsText}\n\n"
                    . "Provide:\n"
                    . "- Meta title (50-60 chars)\n"
                    . "- Meta description (150-160 chars)\n"
                    . "- Keywords\n"
                    . "Format as JSON.",
            ]],
        ]);

        // Would parse JSON response properly
        return [
            'title' => 'Generated Title',
            'description' => 'Generated Description',
            'keywords' => $keywords,
        ];
    }
}
