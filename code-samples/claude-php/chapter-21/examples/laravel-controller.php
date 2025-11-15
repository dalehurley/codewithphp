<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use ClaudePhp\LaravelIntegration\Facades\Claude;

/**
 * Example Laravel Controller using Claude
 */
class ClaudeController extends Controller
{
    /**
     * Send a simple message
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'system' => 'nullable|string|max:1000',
        ]);

        try {
            $response = Claude::message(
                messages: $validated['message'],
                system: $validated['system'] ?? null
            );

            return response()->json([
                'success' => true,
                'response' => $response->content(),
                'usage' => $response->usage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream a response
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        return response()->stream(function () use ($validated) {
            Claude::stream(
                messages: $validated['message'],
                onChunk: function(string $chunk) {
                    echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            );

            echo "data: " . json_encode(['done' => true]) . "\n\n";
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Code analysis endpoint
     */
    public function analyzeCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'language' => 'required|string',
        ]);

        $systemPrompt = "You are an expert code reviewer. Analyze code for:\n"
            . "- Bugs and errors\n"
            . "- Security issues\n"
            . "- Performance problems\n"
            . "- Best practices\n"
            . "Provide specific, actionable feedback.";

        $response = Claude::message(
            messages: "Analyze this {$validated['language']} code:\n\n{$validated['code']}",
            system: $systemPrompt
        );

        return response()->json([
            'analysis' => $response->content(),
            'tokens' => $response->totalTokens(),
        ]);
    }

    /**
     * Multi-turn conversation
     */
    public function conversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string',
            'system' => 'nullable|string',
        ]);

        $conversation = Claude::conversation($validated['system'] ?? null);

        // Build conversation history
        foreach ($validated['messages'] as $message) {
            if ($message['role'] === 'user') {
                $conversation->user($message['content']);
            } else {
                $conversation->assistant($message['content']);
            }
        }

        $response = $conversation->send();

        return response()->json([
            'response' => $response->content(),
            'usage' => $response->usage(),
        ]);
    }
}
