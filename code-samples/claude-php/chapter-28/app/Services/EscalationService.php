<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Escalation Service for complex support cases
 */
class EscalationService
{
    public function __construct(
        private readonly TicketClassifier $classifier,
        private readonly float $escalationThreshold = 0.8
    ) {
    }

    /**
     * Determine if ticket should be escalated
     */
    public function shouldEscalate(SupportTicket $ticket): bool
    {
        $classification = $this->classifier->classify($ticket->message);

        // Check various escalation criteria
        $criteria = [
            'requires_human' => $classification['requires_human'],
            'high_priority' => in_array($classification['priority'], ['high', 'urgent']),
            'negative_sentiment' => in_array($classification['sentiment'], ['negative', 'angry']),
            'complex' => $classification['complexity'] === 'complex',
            'repeated_contact' => $this->isRepeatedContact($ticket),
        ];

        $escalationScore = count(array_filter($criteria)) / count($criteria);

        return $escalationScore >= $this->escalationThreshold;
    }

    /**
     * Escalate ticket to human agent
     */
    public function escalate(SupportTicket $ticket, string $reason): void
    {
        $ticket->update([
            'status' => 'escalated',
            'escalated_at' => now(),
            'escalation_reason' => $reason,
        ]);

        // Notify support team
        $this->notifyTeam($ticket, $reason);

        // Log escalation
        Log::info('Ticket escalated', [
            'ticket_id' => $ticket->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Auto-assign to available agent
     */
    public function autoAssign(SupportTicket $ticket): void
    {
        // Find available agent based on:
        // - Current workload
        // - Expertise (category match)
        // - Availability

        $agent = $this->findBestAgent($ticket);

        if ($agent) {
            $ticket->update([
                'assigned_to' => $agent->id,
                'assigned_at' => now(),
            ]);

            // Notify agent
            // Mail::to($agent)->send(new TicketAssigned($ticket));
        }
    }

    /**
     * Check if customer has contacted multiple times
     */
    private function isRepeatedContact(SupportTicket $ticket): bool
    {
        $recentTickets = SupportTicket::where('user_id', $ticket->user_id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $recentTickets > 2;
    }

    /**
     * Notify support team
     */
    private function notifyTeam(SupportTicket $ticket, string $reason): void
    {
        // Send email/slack notification to team
        // Mail::to(config('support.email'))->send(new TicketEscalated($ticket, $reason));
    }

    /**
     * Find best available agent
     */
    private function findBestAgent(SupportTicket $ticket): ?object
    {
        // Simplified - would implement proper agent selection
        return (object)['id' => 1, 'email' => 'agent@example.com'];
    }

    /**
     * Create escalation summary
     */
    public function createEscalationSummary(SupportTicket $ticket): string
    {
        $classifier = new TicketClassifier(config('services.anthropic.api_key'));

        $classification = $classifier->classify($ticket->message);
        $extractedInfo = $classifier->extractInfo($ticket->message);

        return <<<SUMMARY
Ticket #{$ticket->id} Escalation Summary

Customer: {$ticket->user->name} ({$ticket->user->email})
Category: {$classification['category']}
Priority: {$classification['priority']}
Sentiment: {$classification['sentiment']}

Original Message:
{$ticket->message}

Extracted Information:
{$this->formatExtractedInfo($extractedInfo)}

Classification: {$classification['complexity']}
Escalation Reason: {$ticket->escalation_reason}
SUMMARY;
    }

    private function formatExtractedInfo(array $info): string
    {
        return json_encode($info, JSON_PRETTY_PRINT);
    }
}
