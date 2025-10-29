# Chapter 15: Language Models and Text Generation with OpenAI APIs

Complete code examples demonstrating how to integrate OpenAI's GPT models into PHP applications.

## Prerequisites

- PHP 8.4+ with cURL extension enabled
- Composer installed
- OpenAI API account with API key
- **Budget awareness**: These examples will cost approximately $0.50-$1.00 total to run

## Quick Setup

### 1. Install Dependencies

```bash
cd docs/series/ai-ml-php-developers/code/chapter-15
composer install
```

### 2. Configure API Key

Create a `.env` file (never commit this!):

```bash
cp .env.example .env
```

Edit `.env` and add your OpenAI API key:

```
OPENAI_API_KEY=sk-your-actual-key-here
```

Get your API key from [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys)

### 3. Set Usage Limits (Recommended)

Go to [https://platform.openai.com/account/billing/limits](https://platform.openai.com/account/billing/limits) and set a monthly budget limit (e.g., $10) to avoid unexpected charges.

## Example Files

### Basic HTTP and Library Usage

| File                      | Description                     | Cost    |
| ------------------------- | ------------------------------- | ------- |
| `01-raw-http-request.php` | Raw cURL request to OpenAI API  | ~$0.001 |
| `02-library-setup.php`    | Using openai-php/client library | ~$0.001 |

### Text Generation

| File                            | Description                       | Cost    |
| ------------------------------- | --------------------------------- | ------- |
| `03-simple-text-generation.php` | Creative text generation examples | ~$0.003 |
| `TextGenerator.php`             | Reusable text generator class     | N/A     |

### Article Summarization

| File                        | Description                           | Cost    |
| --------------------------- | ------------------------------------- | ------- |
| `04-article-summarizer.php` | Summarize articles in multiple styles | ~$0.002 |
| `Summarizer.php`            | Summarization class                   | N/A     |

### Chatbots

| File                          | Description                                  | Cost     |
| ----------------------------- | -------------------------------------------- | -------- |
| `05-simple-chatbot.php`       | Interactive CLI chatbot                      | Variable |
| `06-chatbot-with-history.php` | Enhanced chatbot with history management     | Variable |
| `07-streaming-chatbot.php`    | Real-time streaming responses                | Variable |
| `08-production-chatbot.php`   | Production-ready chatbot with error handling | Variable |
| `Chatbot.php`                 | Full-featured chatbot class                  | N/A      |

### Advanced Features

| File                         | Description                                     | Cost    |
| ---------------------------- | ----------------------------------------------- | ------- |
| `07-streaming-responses.php` | Real-time word-by-word response streaming       | ~$0.001 |
| `09-function-calling.php`    | Let GPT call PHP functions (AI agents)          | ~$0.003 |
| `10-structured-output.php`   | Get reliable JSON responses for data extraction | ~$0.002 |

### Utilities

| File               | Description                | Cost |
| ------------------ | -------------------------- | ---- |
| `OpenAIClient.php` | Custom HTTP client wrapper | N/A  |

### Sample Data

- `data/sample-articles/` — Sample articles for summarization practice
- `data/conversations/` — Example conversation histories
- `data/prompts/` — Reusable system prompt templates

### Exercise Solutions

- `solutions/translator-exercise.php` — Language translation
- `solutions/code-explainer-exercise.php` — Code explanation generator
- `solutions/content-moderator-exercise.php` — Content moderation

## Running the Examples

### Basic Examples (No Interactive Input)

```bash
# Set your API key
export OPENAI_API_KEY="sk-your-key-here"

# Run any non-interactive example
php 01-raw-http-request.php
php 02-library-setup.php
php 03-simple-text-generation.php
php 04-article-summarizer.php
```

### Interactive Chatbots

```bash
# Simple chatbot
php 05-simple-chatbot.php

# Enhanced chatbot with commands
php 06-chatbot-with-history.php

# Type 'quit' or 'exit' to end the conversation
```

### Using the Classes

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/TextGenerator.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$generator = new TextGenerator($client);

$result = $generator->generate(
    prompt: "Explain PHP in one paragraph",
    temperature: 0.7,
    maxTokens: 150
);

echo $result['text'] . "\n";
echo "Cost: $" . number_format($result['cost'], 6) . "\n";
```

## Cost Management

### Estimating Costs

- **gpt-3.5-turbo**: $0.002 per 1,000 tokens (~$0.001 per typical request)
- **gpt-4**: $0.03 per 1,000 tokens (~$0.015 per typical request)
- **gpt-4-turbo**: $0.01 per 1,000 tokens (~$0.005 per typical request)

**Token Estimation**:

- 1 token ≈ 4 characters
- 1 token ≈ 0.75 words (English)
- "Hello, world!" = 4 tokens
- 100 words ≈ 133 tokens

### Reducing Costs

1. **Use gpt-3.5-turbo** for most tasks (15x cheaper than gpt-4)
2. **Set max_tokens** limits to prevent unexpectedly long responses
3. **Cache responses** when the same prompt is used repeatedly
4. **Truncate conversation history** in chatbots after 10-15 exchanges
5. **Use lower temperature** (0.3-0.5) for factual tasks to reduce retries

### Monitoring Usage

Check your usage at: [https://platform.openai.com/account/usage](https://platform.openai.com/account/usage)

## Common Issues

### Authentication Errors

**Error**: `Incorrect API key provided`

**Solution**:

- Verify your API key starts with `sk-`
- Check for extra spaces or quotes in `.env`
- Regenerate key if needed at platform.openai.com

### Rate Limiting

**Error**: `Rate limit exceeded`

**Solution**:

- Wait 60 seconds and retry
- Implement exponential backoff (see `08-production-chatbot.php`)
- Upgrade to paid tier for higher limits

### Context Length Errors

**Error**: `This model's maximum context length is 4096 tokens`

**Solution**:

- Reduce prompt length
- Truncate conversation history
- Use a model with larger context window (gpt-4-turbo: 128K tokens)
- Split long documents into chunks

### Quota Errors

**Error**: `You exceeded your current quota`

**Solution**:

- Add payment method at platform.openai.com/account/billing
- Check usage limits and increase if needed
- Verify credit card is valid

## Security Best Practices

1. **Never commit `.env` files** - Always use `.gitignore`
2. **Never hardcode API keys** - Always use environment variables
3. **Validate user input** - Sanitize before sending to API
4. **Set rate limits** - Prevent abuse in production
5. **Use HTTPS only** - API calls should always be encrypted
6. **Rotate keys regularly** - Generate new keys periodically
7. **Monitor usage** - Watch for unexpected spikes

## Production Deployment

When deploying to production:

1. **Use environment variables** via your hosting provider's control panel
2. **Implement caching** using Redis or Memcached
3. **Add request queuing** for high-volume applications
4. **Set up monitoring** and alerts for usage/costs
5. **Implement retry logic** with exponential backoff
6. **Log all API calls** for debugging and auditing
7. **Consider fallbacks** for when the API is unavailable

## Further Resources

- [OpenAI API Documentation](https://platform.openai.com/docs)
- [Pricing Information](https://openai.com/pricing)
- [Best Practices Guide](https://platform.openai.com/docs/guides/production-best-practices)
- [Rate Limits](https://platform.openai.com/docs/guides/rate-limits)
- [Safety Best Practices](https://platform.openai.com/docs/guides/safety-best-practices)

## Support

For issues with:

- **OpenAI API**: Contact [OpenAI Support](https://help.openai.com)
- **This tutorial**: Open an issue on the Code with PHP GitHub repository
- **PHP environment**: Check [PHP Manual](https://www.php.net/docs.php)

## License

All code examples are provided under the MIT License. See the main repository LICENSE file for details.
