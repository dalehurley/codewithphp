# Chapter 23: Integrating AI Models into Web Applications

Complete code examples for integrating ML models into Laravel applications.

## Quick Start

### 1. Create Laravel Project

```bash
composer create-project laravel/laravel ml-shop "12.*"
cd ml-shop
```

### 2. Install Dependencies

```bash
composer require php-ai/php-ml
composer require openai-php/client
composer require predis/predis
```

### 3. Copy Code Files

Copy the files from this directory into your Laravel project:

```bash
# Core Services
cp app/Services/ML/* your-project/app/Services/ML/
cp app/Http/Controllers/MLController.php your-project/app/Http/Controllers/
cp app/Providers/MLServiceProvider.php your-project/app/Providers/

# Models & Jobs
cp app/Models/Prediction.php your-project/app/Models/
cp app/Jobs/ProcessPredictionJob.php your-project/app/Jobs/

# Configuration
cp config/ml.php your-project/config/
cp env.example your-project/.env.example
cp routes/api.php your-project/routes/api.php
```

### 4. Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Update these values in .env:
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=database

ML_CACHE_TTL=3600
ML_TIMEOUT=30
```

### 5. Run Migrations

```bash
# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Create queue tables
php artisan queue:table
php artisan migrate
```

### 6. Register Service Provider

Add to `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MLServiceProvider::class,
];
```

### 7. Test the API

```bash
# Start Laravel
php artisan serve

# In another terminal, start queue worker
php artisan queue:work --queue=ml-predictions

# Test sentiment analysis
curl -X POST http://localhost:8000/api/ml/sentiment \
  -H "Content-Type: application/json" \
  -d '{"text":"This product is absolutely amazing! I love it!"}'
```

## File Structure

```
chapter-23/
├── README.md                              # This file
├── composer.json                          # Dependencies
├── env.example                            # Environment template
│
├── app/
│   ├── Services/ML/
│   │   ├── ModelService.php              # Base ML service class
│   │   ├── SentimentAnalysisService.php  # Sentiment analysis implementation
│   │   └── ProductRecommendationService.php  # (Exercise solution)
│   │
│   ├── Http/Controllers/
│   │   └── MLController.php              # ML API endpoints
│   │
│   ├── Jobs/
│   │   └── ProcessPredictionJob.php      # Background prediction processing
│   │
│   ├── Models/
│   │   └── Prediction.php                # Prediction results model
│   │
│   └── Providers/
│       └── MLServiceProvider.php         # Service registration
│
├── bootstrap/
│   └── providers.php                     # Provider registration
│
├── config/
│   └── ml.php                            # ML configuration
│
├── database/
│   └── migrations/
│       └── 2025_10_29_000000_create_predictions_table.php  # Predictions schema
│
├── routes/
│   └── api.php                           # API routes
│
└── tests/
    ├── Feature/
    │   └── MLIntegrationTest.php         # API integration tests
    └── Unit/
        └── SentimentServiceTest.php      # Service unit tests
```

## Available Endpoints

### Sentiment Analysis (Synchronous)

```bash
POST /api/ml/sentiment
Content-Type: application/json

{
  "text": "This product is great!"
}

Response:
{
  "success": true,
  "data": {
    "text": "This product is great!",
    "sentiment": "positive",
    "confidence": 0.85,
    "emoji": "😊",
    "timestamp": "2025-10-29T12:34:56Z"
  }
}
```

### Sentiment Analysis (Asynchronous)

```bash
POST /api/ml/sentiment/async
Content-Type: application/json

{
  "text": "This product is great!",
  "callback_url": "https://example.com/webhook"
}

Response:
{
  "success": true,
  "message": "Prediction queued for processing",
  "job_id": "job_672b4f8e9d1a3",
  "status_url": "http://localhost:8000/api/ml/status/job_672b4f8e9d1a3"
}
```

### Health Check

```bash
GET /api/ml/health

Response:
{
  "services": {
    "sentiment": {
      "model": "sentiment",
      "status": "healthy",
      "loaded": true
    }
  },
  "overall_status": "healthy"
}
```

### Metrics

```bash
GET /api/ml/metrics?service=sentiment&hours=24

Response:
{
  "service": "sentiment",
  "period_hours": 24,
  "metrics": {
    "total_predictions": 150,
    "cache_hit_rate_percent": 65.5,
    "avg_latency_ms": 125.3,
    "error_count": 2,
    "error_rate_percent": 1.33
  }
}
```

## Key Patterns Demonstrated

### 1. Service Layer Architecture

The `ModelService` base class provides common ML functionality:

- Lazy model loading (singleton pattern)
- Automatic caching with Redis
- Error handling with fallback responses
- Logging and monitoring
- Health checks

### 2. Caching Strategy

Predictions are cached using Redis:

- Cache key: `ml:{service}:{input_hash}`
- Configurable TTL (default: 1 hour)
- Automatic cache invalidation
- Cache hit/miss tracking

### 3. Background Processing

Long-running predictions use Laravel queues:

- Jobs implement `ShouldQueue`
- Automatic retry with exponential backoff
- Webhook callbacks when complete
- Separate queue for ML workloads

### 4. Monitoring & Observability

Comprehensive logging and metrics:

- Prediction latency tracking
- Cache hit rate monitoring
- Error rate calculations
- Database logging for audit trails

### 5. API Design

RESTful endpoints with:

- Input validation
- Rate limiting (60 req/min for sync, 30 for async)
- Proper HTTP status codes
- Error responses with details
- Health check endpoints

## Configuration

### ML Service Configuration (`config/ml.php`)

```php
return [
    'cache' => [
        'enabled' => env('ML_CACHE_ENABLED', true),
        'ttl' => env('ML_CACHE_TTL', 3600),
        'prefix' => 'ml:',
    ],

    'timeout' => env('ML_TIMEOUT', 30),

    'models' => [
        'sentiment' => [
            'path' => storage_path('ml/sentiment_model.json'),
            'type' => 'naive_bayes',
        ],
    ],

    'fallback' => [
        'enabled' => env('ML_FALLBACK_ENABLED', true),
        'response' => 'ML service temporarily unavailable',
    ],
];
```

### Environment Variables

```bash
# Cache Configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue Configuration
QUEUE_CONNECTION=database

# ML Configuration
ML_CACHE_ENABLED=true
ML_CACHE_TTL=3600
ML_TIMEOUT=30
ML_FALLBACK_ENABLED=true

# OpenAI (optional - for chatbot features)
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-3.5-turbo
```

## Testing

### Manual Testing

```bash
# Positive sentiment
curl -X POST http://localhost:8000/api/ml/sentiment \
  -H "Content-Type: application/json" \
  -d '{"text":"Excellent product, highly recommend!"}'

# Negative sentiment
curl -X POST http://localhost:8000/api/ml/sentiment \
  -H "Content-Type: application/json" \
  -d '{"text":"Terrible quality, waste of money."}'

# Test caching (same text twice)
curl -X POST http://localhost:8000/api/ml/sentiment \
  -H "Content-Type: application/json" \
  -d '{"text":"Great product!"}'
# Second request should be much faster

# Check metrics
curl http://localhost:8000/api/ml/metrics?service=sentiment&hours=1

# Health check
curl http://localhost:8000/api/ml/health
```

### Automated Testing

The code includes comprehensive test suites:

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=MLIntegrationTest

# Run unit tests only
php artisan test --filter=SentimentServiceTest

# Run with coverage (requires xdebug)
php artisan test --coverage

# Run in parallel (faster)
php artisan test --parallel
```

**Test Coverage:**

- Feature tests: API endpoints, validation, caching, async processing
- Unit tests: Service logic, sentiment detection, confidence calculation
- Integration tests: Database logging, metrics, health checks

## Production Deployment

### 1. Optimize Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Set Up Supervisor for Queue Workers

Create `/etc/supervisor/conf.d/ml-workers.conf`:

```ini
[program:ml-workers]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=ml-predictions --tries=3 --timeout=300
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/ml-workers.log
```

### 3. Configure Redis for Production

```bash
# Increase max memory
redis-cli CONFIG SET maxmemory 2gb
redis-cli CONFIG SET maxmemory-policy allkeys-lru

# Persistence
redis-cli CONFIG SET save "900 1 300 10 60 10000"
```

### 4. Monitor with Laravel Pulse

```bash
composer require laravel/pulse
php artisan pulse:install
php artisan migrate
```

## Troubleshooting

### Redis Connection Issues

```bash
# Check Redis is running
redis-cli ping

# If not, start it
brew services start redis  # macOS
sudo systemctl start redis # Linux

# Test connection from PHP
php artisan tinker
>>> Cache::store('redis')->put('test', 'value')
>>> Cache::store('redis')->get('test')
```

### Queue Not Processing

```bash
# Check if worker is running
ps aux | grep "queue:work"

# Start worker
php artisan queue:work --queue=ml-predictions --tries=3

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Model Not Loading

```bash
# Check PHP-ML is installed
composer show php-ai/php-ml

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Test model loading
php artisan tinker
>>> app(\App\Services\ML\SentimentAnalysisService::class)->healthCheck()
```

## Exercises

See the chapter text for detailed exercise descriptions:

1. **Product Recommendation Endpoint** - Integrate collaborative filtering
2. **Batch Processing** - Process multiple texts in parallel
3. **API Key Rate Limiting** - Implement tier-based rate limits
4. **Frontend Demo Page** - Build interactive sentiment analyzer UI

Solution code for exercises is available in the `solutions/` directory.

## Further Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Redis Best Practices](https://redis.io/docs/management/optimization/)
- [Laravel Queue Documentation](https://laravel.com/docs/12.x/queues)

## Support

For questions or issues:

- Review Chapter 23 text for detailed explanations
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Review the troubleshooting section above
