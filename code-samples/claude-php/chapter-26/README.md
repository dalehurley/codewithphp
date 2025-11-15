# Chapter 26: Code Review Assistant

AI-powered code review system with GitHub integration for automated pull request analysis, security checks, and improvement suggestions.

## Features

- Pull request analysis
- Security vulnerability detection
- Code quality assessment
- Performance review
- Improvement suggestions
- GitHub webhook integration
- Automated review comments

## Installation

```bash
composer install
cp .env.example .env
```

## Usage

### Manual Code Review

```php
$analyzer = new PullRequestAnalyzer($apiKey);

// Review file
$review = $analyzer->reviewFile('src/User.php', $content, $diff);

// Security check
$security = $analyzer->securityCheck($code);

// Suggest improvements
$improvements = $analyzer->suggestImprovements($code);
```

### GitHub Webhook Integration

Setup webhook in GitHub repository:
- URL: `https://your-domain.com/webhook/github`
- Events: Pull requests
- Secret: Set in `.env`

```php
$handler = new GithubWebhookHandler($anthropicKey, $githubToken);

// Verify and handle webhook
if ($handler->verifySignature($payload, $signature, $secret)) {
    $handler->handlePullRequest($webhookData);
}
```

## GitHub Actions Integration

`.github/workflows/ai-review.yml`:

```yaml
name: AI Code Review
on:
  pull_request:
    types: [opened, synchronize]

jobs:
  review:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run AI Review
        run: php review-pr.php ${{ github.event.pull_request.number }}
        env:
          ANTHROPIC_API_KEY: ${{ secrets.ANTHROPIC_API_KEY }}
```

## Next Steps

- Documentation generator (Chapter 27)
- Support bot (Chapter 28)
