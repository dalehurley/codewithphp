# Smart Product Recommender

Production-ready recommendation system using collaborative filtering.

## Features

- ✅ Real-time product recommendations
- ✅ User behavior tracking  
- ✅ Collaborative filtering ML model
- ✅ REST API
- ✅ Analytics dashboard
- ✅ Comprehensive test coverage

## Quick Start

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
nano .env

# Setup database
mysql -u root -p < database/schema.sql

# Generate test data
php examples/collect-data.php

# Train model
php examples/train-model.php

# Start server
php -S localhost:8000 -t public
```

## Endpoints

Visit:
- **API**: http://localhost:8000/api.php/recommendations/1
- **Dashboard**: http://localhost:8000/dashboard.php

## API Usage

**Get Recommendations:**
```bash
curl http://localhost:8000/api.php/recommendations/1?count=5
```

**Record Feedback:**
```bash
curl -X POST http://localhost:8000/api.php/feedback \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"product_id":1,"action":"click"}'
```

## API Documentation

See [api/openapi.yaml](api/openapi.yaml) for full OpenAPI specification.

## Project Structure

```
smart-recommender/
├── config/              # Configuration files
│   ├── app.php
│   └── database.php
├── src/                 # Application code
│   ├── DataCollection/  # Event tracking
│   ├── ML/              # Machine learning models
│   ├── API/             # REST API controllers
│   └── Analytics/       # Analytics and reporting
├── tests/               # Test suite
│   ├── Unit/
│   └── Integration/
├── database/            # Database schemas
│   └── schema.sql
├── examples/            # Usage examples
│   ├── collect-data.php
│   ├── train-model.php
│   ├── test-api.php
│   └── benchmark-system.php
├── public/              # Web root
│   ├── api.php
│   └── dashboard.php
└── api/                 # API documentation
    └── openapi.yaml
```

## Running Tests

```bash
./vendor/bin/phpunit

# Expected output:
# OK (4 tests, 12 assertions)
```

## Performance Benchmarking

```bash
php examples/benchmark-system.php

# Benchmarks:
# - Model training time
# - Recommendation latency (avg, P95, P99)
# - Throughput (req/sec)
# - Memory usage
# - Cold start performance
```

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment guide.

## Documentation

- **Deployment Guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- **API Documentation**: [api/openapi.yaml](api/openapi.yaml)
- **Tutorial**: See Chapter 11 of "Data Science for PHP Developers"

## Architecture

The system uses collaborative filtering to generate personalized recommendations:

1. **Data Collection**: Tracks user interactions (views, clicks, purchases)
2. **Model Training**: Builds user-item matrix and calculates similarities
3. **Recommendations**: Generates personalized suggestions based on similar users
4. **Analytics**: Monitors performance metrics (CTR, conversion rate)

## Success Metrics

- **Click-through rate (CTR)**: % of recommendations clicked
- **Conversion rate**: % of recommendations purchased  
- **Recommendation latency**: P95 <100ms
- **Model accuracy**: Precision@K, NDCG scores

## License

MIT

## Support

For issues and questions, please open a GitHub issue or contact support@example.com.
