# SmartDash - AI-Powered Analytics Dashboard

Complete Laravel 11 application integrating four AI services: chatbot, recommendations, forecasting, and image tagging.

## Features

- **AI Chatbot**: GPT-4 powered customer support with conversation history
- **Smart Recommendations**: Collaborative filtering product suggestions
- **Sales Forecasting**: Time series predictions with confidence intervals
- **Image Auto-Tagging**: Automatic image classification with Google Cloud Vision

## Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- MySQL/PostgreSQL (or SQLite for development)
- OpenAI API key
- Google Cloud Vision API key (optional)

## Installation

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env and add your API keys:
# OPENAI_API_KEY=sk-...
# GOOGLE_CLOUD_VISION_KEY=your-key
```

### 3. Set Up Database

```bash
# Create database (MySQL example)
mysql -u root -e "CREATE DATABASE smartdash"

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed
```

### 4. Build Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

## Running the Application

### Development

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start queue worker
php artisan queue:work

# Terminal 3 (optional): Watch assets
npm run dev
```

Visit http://localhost:8000/dashboard

### Production

```bash
# Compile assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run with supervisor (queue workers)
# See deployment documentation
```

## Testing

### Standalone Test Scripts

Run individual service tests:

```bash
# Test Laravel setup
php 01-laravel-setup.php

# Test each AI service
php 02-test-chatbot.php
php 03-test-recommendations.php
php 04-test-forecast.php
php 05-test-vision.php

# Run all tests
php 06-run-all-tests.php
```

### Laravel Tests

```bash
# Run PHPUnit tests
php artisan test

# With coverage
php artisan test --coverage
```

## Project Structure

```
chapter-25/
├── app/
│   ├── Services/          # AI service classes
│   │   ├── ChatbotService.php
│   │   ├── RecommenderService.php
│   │   ├── ForecastService.php
│   │   └── VisionService.php
│   ├── Http/Controllers/  # Web and API controllers
│   │   ├── DashboardController.php
│   │   └── Api/
│   ├── Jobs/              # Background job classes
│   │   ├── GenerateForecastJob.php
│   │   ├── ProcessImageJob.php
│   │   └── GenerateRecommendationsJob.php
│   └── Models/            # Eloquent models
├── database/
│   ├── migrations/        # Database schema
│   └── seeders/           # Sample data
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API endpoints
├── resources/
│   ├── views/            # Blade templates
│   └── js/               # Frontend assets
├── data/                 # Sample data files
│   ├── sample-products.csv
│   ├── sample-interactions.csv
│   ├── sample-sales.csv
│   └── sample-images/
├── 01-laravel-setup.php  # Setup verification
├── 02-test-chatbot.php   # Chatbot test
├── 03-test-recommendations.php
├── 04-test-forecast.php
├── 05-test-vision.php
└── 06-run-all-tests.php  # Comprehensive test suite
```

## API Endpoints

### Chat

- `POST /api/chat/message` - Send message to chatbot
- `GET /api/chat/{sessionId}/history` - Get conversation history
- `GET /api/chat/{sessionId}/cost` - Estimate API cost

### Recommendations

- `POST /api/recommendations/generate` - Generate recommendations
- `POST /api/recommendations/interaction` - Record user interaction
- `GET /api/recommendations/{userId}` - Get stored recommendations

### Forecast

- `POST /api/forecast/generate` - Generate sales forecast
- `GET /api/forecast/{metricName}` - Get forecasts
- `POST /api/forecast/{metricName}/actual` - Record actual value

### Images

- `POST /api/images/upload` - Upload and process image
- `GET /api/images/{imageId}` - Get image with tags
- `GET /api/images/search/{tag}` - Search images by tag

## Configuration

### Environment Variables

Key variables in `.env`:

```env
# AI Services
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4-turbo-preview
GOOGLE_CLOUD_VISION_KEY=...
VISION_PROVIDER=cloud  # or 'local'

# Caching
AI_CACHE_ENABLED=true
AI_CACHE_TTL=3600

# Queue
QUEUE_CONNECTION=database  # or 'redis'
```

### Service Providers

AI services are registered in `app/Providers/AppServiceProvider.php`:

```php
$this->app->singleton(OpenAI\Client::class, function ($app) {
    return OpenAI::client(config('services.openai.api_key'));
});
```

## Cost Estimation

### OpenAI API

- GPT-4 Turbo: ~$0.01 input + $0.03 output per 1K tokens
- GPT-3.5 Turbo: ~$0.001 input + $0.002 output per 1K tokens (10x cheaper)
- Average conversation: ~$0.03 (GPT-4) or $0.003 (GPT-3.5)

### Google Cloud Vision

- $1.50 per 1,000 images after free tier (1,000/month)
- Use caching to reduce costs
- Consider local ONNX models for high volume

### Optimization Tips

1. Cache aggressively (chatbot responses, recommendations, forecasts)
2. Use GPT-3.5 Turbo for non-critical features
3. Batch image processing
4. Implement rate limiting
5. Monitor usage with cost tracking utilities

## Troubleshooting

### Common Issues

**"OpenAI API key not configured"**

- Add `OPENAI_API_KEY` to `.env`
- Ensure key has credits: https://platform.openai.com/account/billing

**"Queue jobs not processing"**

- Start queue worker: `php artisan queue:work`
- Check `QUEUE_CONNECTION` in `.env`
- Verify queue migrations ran: `php artisan migrate:status`

**"Database connection failed"**

- Check database credentials in `.env`
- Ensure database exists: `mysql -u root -e "SHOW DATABASES;"`
- Test connection: `php artisan db:show`

**"Assets not loading"**

- Run `npm install && npm run build`
- Create storage link: `php artisan storage:link`
- Clear cache: `php artisan cache:clear`

**"Vision API errors"**

- Verify Google Cloud Vision API is enabled
- Check API key has necessary permissions
- Ensure billing is set up for your project
- Try `VISION_PROVIDER=local` for testing

## Production Deployment

### Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Compile assets: `npm run build`
- [ ] Set up queue workers with supervisor
- [ ] Configure cron for scheduled tasks
- [ ] Enable HTTPS and force SSL
- [ ] Set up error monitoring (Sentry, Bugsnag)
- [ ] Configure database backups
- [ ] Use Redis for cache and sessions
- [ ] Add rate limiting middleware

### Recommended Hosting

- **Laravel Forge**: Managed Laravel hosting
- **DigitalOcean App Platform**: Container-based deployment
- **AWS Elastic Beanstalk**: Scalable cloud hosting
- **Heroku**: Quick deployment with add-ons

## Further Development

### Suggested Enhancements

1. **Authentication**: Add Laravel Breeze or Sanctum
2. **Multi-tenancy**: Support multiple organizations
3. **Analytics**: Track AI feature usage and costs
4. **A/B Testing**: Compare recommendation algorithms
5. **Real-time Updates**: WebSockets for live results
6. **Mobile App**: Use REST API for iOS/Android apps
7. **Admin Dashboard**: Manage users, view analytics
8. **Export Features**: PDF reports, CSV exports

## Resources

- [Laravel Documentation](https://laravel.com/docs/11.x)
- [OpenAI API Reference](https://platform.openai.com/docs)
- [Google Cloud Vision Docs](https://cloud.google.com/vision/docs)
- [Rubix ML Documentation](https://docs.rubixml.com/)

## License

This project is part of the "AI/ML for PHP Developers" tutorial series.

## Support

For issues or questions:

- Review the chapter content in the tutorial
- Check troubleshooting section above
- Review Laravel documentation
- Check API provider status pages
