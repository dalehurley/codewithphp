# Chapter 11 Code Files

This directory contains the **Smart Product Recommender** project from Chapter 11.

## Project Structure

```
chapter-11-real-world-project/
├── api/
│   └── openapi.yaml          # API documentation (OpenAPI 3.0 spec)
├── config/
│   ├── app.php               # Application configuration
│   └── database.php          # Database configuration
├── database/
│   └── schema.sql            # MySQL database schema
├── examples/
│   └── benchmark-system.php  # Performance benchmarking
├── src/
│   ├── Database.php          # Database singleton
│   ├── Config.php            # Configuration management
│   ├── DataCollection/       # (See chapter for complete code)
│   ├── ML/                   # (See chapter for complete code)
│   ├── API/                  # (See chapter for complete code)
│   └── Analytics/            # (See chapter for complete code)
├── tests/
│   └── Integration/          # (See chapter for complete code)
├── public/                   # (See chapter for complete code)
├── composer.json             # Dependencies
├── phpunit.xml               # Test configuration
├── README.md                 # Project documentation
└── DEPLOYMENT.md             # Deployment guide

## Complete Implementation

For the **complete, working implementation** of all source files, please refer to **Chapter 11** in the tutorial, which includes:

### Step 1: Project Setup
- Database.php
- Config.php
- Configuration files

### Step 2: Data Collection (~500 lines)
- `src/DataCollection/InteractionTracker.php` - User interaction tracking
- `src/DataCollection/TestDataGenerator.php` - Test data generation

### Step 3: ML Model Training (~400 lines)
- `src/ML/CollaborativeFilter.php` - Collaborative filtering implementation
- Cosine similarity calculation
- User-item matrix construction
- Recommendation generation

### Step 4: REST API (~300 lines)
- `src/API/RecommendationController.php` - API controller
- `public/api.php` - API router
- Request validation and error handling

### Step 5: Analytics Dashboard (~400 lines)
- `src/Analytics/RecommendationAnalytics.php` - Analytics engine
- `public/dashboard.php` - Interactive dashboard HTML

### Step 6: Testing & Deployment (~300 lines)
- `tests/Integration/RecommendationSystemTest.php` - Integration tests
- DEPLOYMENT.md - Complete deployment guide

## Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Setup database:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. **See Chapter 11 for complete code:**
   - Copy all code blocks from the chapter
   - Or use the testing directory: `/testing/data-science-php-developers/chapter-11/`

## Features

✅ Real-time product recommendations  
✅ User behavior tracking  
✅ Collaborative filtering ML model  
✅ REST API endpoints  
✅ Analytics dashboard  
✅ Comprehensive tests  
✅ Production deployment guide  
✅ OpenAPI specification  
✅ Performance benchmarking  

## Documentation

- **README.md** - Project overview and quick start
- **DEPLOYMENT.md** - Production deployment guide
- **api/openapi.yaml** - Complete API specification
- **Chapter 11** - Complete tutorial with all code

## Testing

```bash
# Run tests
./vendor/bin/phpunit

# Run benchmarks
php examples/benchmark-system.php
```

## Support

For the complete source code, see Chapter 11 of "Data Science for PHP Developers" or contact the project maintainers.
