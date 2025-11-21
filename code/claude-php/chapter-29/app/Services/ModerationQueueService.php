<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ModerationItem;
use App\Jobs\ModerateContent;
use Illuminate\Support\Facades\Log;

/**
 * Moderation Queue Service
 *
 * Manages content moderation workflow with AI assistance
 */
class ModerationQueueService
{
    public function __construct(
        private readonly ToxicContentDetector $toxicDetector,
        private readonly PiiRedactionService $piiRedactor
    ) {
    }

    /**
     * Submit content for moderation
     */
    public function submit(
        string $content,
        string $contentType,
        int $userId,
        array $metadata = []
    ): ModerationItem {
        $item = ModerationItem::create([
            'content' => $content,
            'content_type' => $contentType,
            'user_id' => $userId,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);

        // Queue for AI analysis
        ModerateContent::dispatch($item);

        return $item;
    }

    /**
     * Process moderation item
     */
    public function process(ModerationItem $item): void
    {
        try {
            // Run toxic content analysis
            $toxicAnalysis = $this->toxicDetector->analyze($item->content);

            // Check for PII
            $piiDetection = $this->piiRedactor->detectPii($item->content);

            // Calculate overall moderation score
            $moderationScore = $this->calculateScore($toxicAnalysis, $piiDetection);

            // Determine action
            $action = $this->determineAction($moderationScore, $toxicAnalysis);

            // Update item
            $item->update([
                'ai_analysis' => [
                    'toxic' => $toxicAnalysis,
                    'pii' => $piiDetection,
                    'score' => $moderationScore,
                ],
                'ai_recommendation' => $action,
                'processed_at' => now(),
            ]);

            // Auto-action if confidence is high
            if ($this->shouldAutoAction($moderationScore, $action)) {
                $this->applyAction($item, $action);
            } else {
                // Send to human review
                $item->update(['status' => 'review']);
            }

        } catch (\Exception $e) {
            Log::error('Moderation processing failed', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            $item->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Apply moderation action
     */
    public function applyAction(ModerationItem $item, string $action): void
    {
        match($action) {
            'approve' => $this->approve($item),
            'reject' => $this->reject($item),
            'flag' => $this->flag($item),
            'redact' => $this->redact($item),
            default => null,
        };

        $item->update([
            'status' => $action,
            'actioned_at' => now(),
        ]);
    }

    /**
     * Approve content
     */
    private function approve(ModerationItem $item): void
    {
        // Publish/approve the content
        Log::info('Content approved', ['item_id' => $item->id]);
    }

    /**
     * Reject content
     */
    private function reject(ModerationItem $item): void
    {
        // Block/delete the content
        Log::info('Content rejected', ['item_id' => $item->id]);

        // Notify user
        // Mail::to($item->user)->send(new ContentRejected($item));
    }

    /**
     * Flag for review
     */
    private function flag(ModerationItem $item): void
    {
        // Flag for human moderator review
        $item->update(['flagged_at' => now()]);
        Log::info('Content flagged', ['item_id' => $item->id]);
    }

    /**
     * Redact PII and approve
     */
    private function redact(ModerationItem $item): void
    {
        $redacted = $this->piiRedactor->redact($item->content);

        $item->update([
            'content' => $redacted['redacted'],
            'original_content' => $redacted['original'],
        ]);

        $this->approve($item);
    }

    /**
     * Calculate overall moderation score
     */
    private function calculateScore(array $toxicAnalysis, array $piiDetection): float
    {
        $toxicScore = $toxicAnalysis['toxicity_score'] ?? 0.0;
        $piiScore = !empty($piiDetection) ? 0.3 : 0.0;

        return min(1.0, $toxicScore + $piiScore);
    }

    /**
     * Determine moderation action
     */
    private function determineAction(float $score, array $analysis): string
    {
        if ($score >= 0.9) {
            return 'reject';
        } elseif ($score >= 0.7) {
            return 'flag';
        } elseif ($score >= 0.3) {
            return 'review';
        } else {
            return 'approve';
        }
    }

    /**
     * Check if should auto-action
     */
    private function shouldAutoAction(float $score, string $action): bool
    {
        // Auto-approve if very safe
        if ($action === 'approve' && $score < 0.2) {
            return true;
        }

        // Auto-reject if clearly toxic
        if ($action === 'reject' && $score >= 0.95) {
            return true;
        }

        return false;
    }

    /**
     * Get moderation queue statistics
     */
    public function getStats(): array
    {
        return [
            'pending' => ModerationItem::where('status', 'pending')->count(),
            'review' => ModerationItem::where('status', 'review')->count(),
            'approved' => ModerationItem::where('status', 'approve')->count(),
            'rejected' => ModerationItem::where('status', 'reject')->count(),
            'avg_processing_time' => ModerationItem::whereNotNull('processed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as avg')
                ->value('avg'),
        ];
    }
}
