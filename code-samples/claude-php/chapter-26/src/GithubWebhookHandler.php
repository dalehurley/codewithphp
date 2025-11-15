<?php

declare(strict_types=1);

namespace CodeReview;

use GuzzleHttp\Client;

/**
 * GitHub Webhook Handler for automated code reviews
 */
class GithubWebhookHandler
{
    private PullRequestAnalyzer $analyzer;
    private Client $github;

    public function __construct(
        string $anthropicKey,
        private readonly string $githubToken
    ) {
        $this->analyzer = new PullRequestAnalyzer($anthropicKey);
        $this->github = new Client([
            'base_uri' => 'https://api.github.com',
            'headers' => [
                'Authorization' => "token {$githubToken}",
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);
    }

    /**
     * Handle pull request webhook event
     */
    public function handlePullRequest(array $payload): void
    {
        $action = $payload['action'];
        $pr = $payload['pull_request'];

        if (!in_array($action, ['opened', 'synchronize', 'reopened'])) {
            return;
        }

        // Get PR diff
        $diff = $this->getPullRequestDiff($pr['url']);

        // Analyze with Claude
        $review = $this->analyzer->analyzePullRequest($diff, [
            'description' => $pr['body'] ?? '',
            'title' => $pr['title'],
        ]);

        // Post review comment
        $this->postReviewComment($pr, $review);
    }

    /**
     * Get pull request diff
     */
    private function getPullRequestDiff(string $prUrl): array
    {
        $response = $this->github->get($prUrl . '/files');
        $files = json_decode($response->getBody()->getContents(), true);

        return array_map(fn($file) => [
            'filename' => $file['filename'],
            'status' => $file['status'],
            'patch' => $file['patch'] ?? '',
            'additions' => $file['additions'],
            'deletions' => $file['deletions'],
        ], $files);
    }

    /**
     * Post review comment on GitHub
     */
    private function postReviewComment(array $pr, array $review): void
    {
        $commentsUrl = $pr['comments_url'];

        $comment = "## AI Code Review\n\n";
        $comment .= $review['summary'];
        $comment .= "\n\n---\n*Powered by Claude AI*";

        $this->github->post($commentsUrl, [
            'json' => ['body' => $comment],
        ]);
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($hash, $signature);
    }
}
