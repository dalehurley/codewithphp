<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Chatbot Controller
 *
 * Handles chatbot interactions via REST API
 */
class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbot
    ) {
    }

    /**
     * Create a new conversation
     */
    public function createConversation(Request $request): JsonResponse
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'system_prompt' => config('chatbot.system_prompt'),
        ]);

        return response()->json([
            'conversation' => $conversation,
        ], 201);
    }

    /**
     * Get conversation history
     */
    public function getConversation(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Save user message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get Claude response
        $response = $this->chatbot->sendMessage(
            conversation: $conversation,
            message: $validated['message']
        );

        // Save assistant message
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response['content'],
            'metadata' => [
                'model' => $response['model'],
                'tokens' => $response['usage'],
            ],
        ]);

        // Update conversation title if it's the first message
        if ($conversation->messages()->count() === 2) {
            $title = $this->chatbot->generateTitle($validated['message']);
            $conversation->update(['title' => $title]);
        }

        return response()->json([
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'conversation' => $conversation->fresh(),
        ]);
    }

    /**
     * Stream a message response
     */
    public function streamMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Save user message
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        return response()->stream(function () use ($conversation, $validated) {
            $fullResponse = '';

            $this->chatbot->streamMessage(
                conversation: $conversation,
                message: $validated['message'],
                onChunk: function(string $chunk) use (&$fullResponse) {
                    $fullResponse .= $chunk;
                    echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                }
            );

            // Save complete assistant message
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $fullResponse,
            ]);

            echo "data: " . json_encode(['done' => true]) . "\n\n";

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * List all conversations
     */
    public function listConversations(): JsonResponse
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->withCount('messages')
            ->latest()
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation(Conversation $conversation): JsonResponse
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return response()->json([
            'message' => 'Conversation deleted successfully',
        ]);
    }

    /**
     * Clear conversation history
     */
    public function clearConversation(Conversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $conversation->messages()->delete();

        return response()->json([
            'message' => 'Conversation cleared successfully',
        ]);
    }

    /**
     * Export conversation
     */
    public function exportConversation(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        $content = "Conversation: {$conversation->title}\n";
        $content .= "Date: " . $conversation->created_at->toDateTimeString() . "\n";
        $content .= str_repeat('=', 80) . "\n\n";

        foreach ($messages as $message) {
            $role = strtoupper($message->role);
            $time = $message->created_at->format('H:i:s');
            $content .= "[{$time}] {$role}:\n{$message->content}\n\n";
        }

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="conversation-' . $conversation->id . '.txt"');
    }
}
