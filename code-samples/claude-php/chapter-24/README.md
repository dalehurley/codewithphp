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
