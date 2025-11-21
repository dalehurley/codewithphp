# Chapter 24: Content Generation API

Complete RESTful API for AI-powered content generation including blog posts, product descriptions, social media, emails, and more.

## Features

- Blog post generation
- Product descriptions
- Social media posts (Twitter, Facebook, LinkedIn, Instagram)
- Email templates
- Batch generation
- Content improvement
- Meta tag generation
- API authentication with Laravel Sanctum
- Rate limiting

## API Endpoints

### Generate Blog Post
```
POST /api/content/blog
{
  "topic": "Laravel Best Practices",
  "tone": "professional",
  "length": "medium",
  "keywords": ["laravel", "php", "best practices"]
}
```

### Generate Product Description
```
POST /api/content/product
{
  "product_name": "Smart Watch Pro",
  "features": ["Heart rate monitor", "GPS", "Waterproof"],
  "target_audience": "fitness enthusiasts",
  "tone": "energetic"
}
```

### Generate Social Media Posts
```
POST /api/content/social
{
  "content": "Launching our new product!",
  "platforms": ["twitter", "facebook", "linkedin"]
}
```

### Batch Generate
```
POST /api/content/batch
{
  "items": [
    {"type": "blog", "data": {...}},
    {"type": "product", "data": {...}}
  ]
}
```

## Installation

```bash
composer install
php artisan migrate
php artisan key:generate
```

## Authentication

Use Laravel Sanctum tokens:

```bash
curl -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -X POST http://localhost/api/content/blog \
  -d '{"topic":"AI in PHP"}'
```

## Rate Limiting

Default: 60 requests per minute per user.

Configure in `.env`:
```
API_RATE_LIMIT=60
```

## Next Steps

- Admin panel (Chapter 25)
- Content moderation (Chapter 29)
## 📚 Resources

- **[Official Anthropic Documentation](https://docs.anthropic.com/)** — Complete API reference
- **[Official PHP SDK on GitHub](https://github.com/anthropics/anthropic-sdk-php)** — Anthropic's official PHP implementation
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples
- **[PHP SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package
- **[Community Discord](https://discord.gg/anthropic)** — Get help and discuss
