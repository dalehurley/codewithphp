# Environment Configuration

Create a `.env` file in this directory with the following content:

```bash
# OpenAI API Configuration
# Get your API key from https://platform.openai.com/api-keys
OPENAI_API_KEY=sk-your-actual-api-key-here

# Model Configuration
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7

# Cost Management
# Set a personal spending limit to avoid surprises
OPENAI_MONTHLY_BUDGET=10.00
```

**Important**:

- Never commit the `.env` file to version control
- The `.env` file should already be in `.gitignore`
- Keep your API key secure and never share it publicly

## Getting an API Key

1. Sign up at [platform.openai.com](https://platform.openai.com)
2. Go to API keys section
3. Click "Create new secret key"
4. Copy the key (you won't be able to see it again)
5. Add your key to the `.env` file above

## Setting Usage Limits

To avoid unexpected charges:

1. Go to [platform.openai.com/account/billing](https://platform.openai.com/account/billing)
2. Add a payment method
3. Set a monthly usage limit (recommended: $5-$10 for development)
4. Enable email alerts for usage milestones

## Testing Your Setup

Run this to verify your environment is configured correctly:

```bash
php -r "
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
\$dotenv->required('OPENAI_API_KEY')->notEmpty();
echo 'Environment configured correctly!' . PHP_EOL;
"
```
