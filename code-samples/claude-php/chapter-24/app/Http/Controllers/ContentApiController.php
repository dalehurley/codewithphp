<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ContentGenerationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Content Generation API Controller
 */
class ContentApiController extends Controller
{
    public function __construct(
        private readonly ContentGenerationService $generator
    ) {
    }

    /**
     * Generate blog post
     */
    public function generateBlogPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'tone' => 'nullable|in:professional,casual,technical,creative',
            'length' => 'nullable|in:short,medium,long',
            'keywords' => 'nullable|array',
        ]);

        $content = $this->generator->generateBlogPost(
            topic: $validated['topic'],
            tone: $validated['tone'] ?? 'professional',
            length: $validated['length'] ?? 'medium',
            keywords: $validated['keywords'] ?? []
        );

        return response()->json([
            'content' => $content['text'],
            'metadata' => $content['metadata'],
            'tokens_used' => $content['tokens'],
        ]);
    }

    /**
     * Generate product description
     */
    public function generateProductDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'features' => 'required|array',
            'target_audience' => 'nullable|string',
            'tone' => 'nullable|string',
        ]);

        $description = $this->generator->generateProductDescription(
            productName: $validated['product_name'],
            features: $validated['features'],
            targetAudience: $validated['target_audience'] ?? 'general',
            tone: $validated['tone'] ?? 'professional'
        );

        return response()->json(['description' => $description]);
    }

    /**
     * Generate social media posts
     */
    public function generateSocialMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'platforms' => 'required|array',
            'platforms.*' => 'in:twitter,facebook,linkedin,instagram',
        ]);

        $posts = $this->generator->generateSocialMediaPosts(
            content: $validated['content'],
            platforms: $validated['platforms']
        );

        return response()->json(['posts' => $posts]);
    }

    /**
     * Generate email template
     */
    public function generateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:welcome,newsletter,promotional,transactional',
            'subject' => 'required|string|max:255',
            'key_points' => 'required|array',
            'tone' => 'nullable|string',
        ]);

        $email = $this->generator->generateEmail(
            type: $validated['type'],
            subject: $validated['subject'],
            keyPoints: $validated['key_points'],
            tone: $validated['tone'] ?? 'professional'
        );

        return response()->json([
            'subject' => $email['subject'],
            'body' => $email['body'],
            'preview_text' => $email['preview_text'],
        ]);
    }

    /**
     * Batch generate content
     */
    public function batchGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|max:10',
            'items.*.type' => 'required|in:blog,product,social,email',
            'items.*.data' => 'required|array',
        ]);

        $results = $this->generator->batchGenerate($validated['items']);

        return response()->json([
            'results' => $results,
            'total_tokens' => array_sum(array_column($results, 'tokens')),
        ]);
    }

    /**
     * Improve existing content
     */
    public function improveContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'improvements' => 'required|array',
            'improvements.*' => 'in:grammar,clarity,tone,seo,engagement',
        ]);

        $improved = $this->generator->improveContent(
            content: $validated['content'],
            improvements: $validated['improvements']
        );

        return response()->json([
            'original' => $validated['content'],
            'improved' => $improved['text'],
            'changes' => $improved['changes'],
        ]);
    }

    /**
     * Generate meta tags
     */
    public function generateMetaTags(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'keywords' => 'nullable|array',
        ]);

        $meta = $this->generator->generateMetaTags(
            content: $validated['content'],
            keywords: $validated['keywords'] ?? []
        );

        return response()->json($meta);
    }
}
