<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\ChatbotService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

/**
 * Chatbot Livewire Component
 *
 * Real-time chat interface using Livewire
 */
class Chatbot extends Component
{
    public ?Conversation $conversation = null;
    public string $message = '';
    public array $messages = [];
    public bool $isTyping = false;
    public string $streamingMessage = '';

    public function mount(?int $conversationId = null)
    {
        if ($conversationId) {
            $this->conversation = Conversation::findOrFail($conversationId);
            $this->authorize('view', $this->conversation);
            $this->loadMessages();
        } else {
            $this->createConversation();
        }
    }

    /**
     * Create a new conversation
     */
    public function createConversation(): void
    {
        $this->conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'system_prompt' => config('chatbot.system_prompt'),
        ]);

        $this->messages = [];
    }

    /**
     * Load conversation messages
     */
    public function loadMessages(): void
    {
        $this->messages = $this->conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Send a message
     */
    public function sendMessage(ChatbotService $chatbot): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = trim($this->message);
        $this->message = '';

        // Add user message to UI
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'created_at' => 'Just now',
        ];

        // Save user message
        $this->conversation->messages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        $this->isTyping = true;
        $this->streamingMessage = '';

        // Dispatch event to start streaming (handled by Alpine.js)
        $this->dispatch('start-streaming', [
            'conversationId' => $this->conversation->id,
            'message' => $userMessage,
        ]);
    }

    /**
     * Handle streaming chunk
     */
    #[On('chunk-received')]
    public function handleChunk(string $chunk): void
    {
        $this->streamingMessage .= $chunk;
    }

    /**
     * Handle streaming complete
     */
    #[On('streaming-complete')]
    public function handleStreamingComplete(string $content): void
    {
        $this->isTyping = false;

        // Save assistant message
        $assistantMessage = $this->conversation->messages()->create([
            'role' => 'assistant',
            'content' => $content,
        ]);

        // Add to messages array
        $this->messages[] = [
            'id' => $assistantMessage->id,
            'role' => 'assistant',
            'content' => $content,
            'created_at' => 'Just now',
        ];

        $this->streamingMessage = '';

        // Update title if first message
        if ($this->conversation->messages()->count() === 2) {
            $this->updateTitle();
        }
    }

    /**
     * Clear conversation
     */
    public function clearConversation(): void
    {
        $this->conversation->messages()->delete();
        $this->messages = [];
    }

    /**
     * Delete conversation and create new one
     */
    public function newConversation(): void
    {
        $this->createConversation();
        $this->messages = [];
    }

    /**
     * Update conversation title
     */
    private function updateTitle(): void
    {
        $firstMessage = $this->conversation->messages()->first();
        if ($firstMessage) {
            $title = substr($firstMessage->content, 0, 50);
            if (strlen($firstMessage->content) > 50) {
                $title .= '...';
            }
            $this->conversation->update(['title' => $title]);
        }
    }

    public function render()
    {
        return view('livewire.chatbot');
    }
}
