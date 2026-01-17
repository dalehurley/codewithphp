# Smart Recommender Deployment Guide

## Prerequisites

- PHP 8.4+
- MySQL 8.0+
- Composer
- 2GB+ RAM recommended
- HTTPS support (production)

## Installation Steps

### 1. Clone Repository

```bash
git clone <repository-url>
cd smart-recommender
```

### 2. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment

```bash
cp .env.example .env
nano .env
```

Set production values:
```env
APP_ENV=production
APP_DEBUG=false

DB_HOST=your-db-host
DB_NAME=smart_recommender
DB_USER=your-db-user
DB_PASSWORD=your-db-password
```

### 4. Setup Database

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE smart_recommender CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p smart_recommender < database/schema.sql
```

### 5. Seed Initial Data (Optional)

For testing or demo purposes:

```bash
php examples/collect-data.php
```

### 6. Train Initial Model

```bash
php examples/train-model.php
```

Model will be trained and ready for predictions.

### 7. Start Application

**Development:**
```bash
php -S localhost:8000 -t public
```

**Production (with Apache):**

Create Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName recommender.example.com
    DocumentRoot /path/to/smart-recommender/public
    
    <Directory /path/to/smart-recommender/public>
        AllowOverride All
        Require all granted
        
        # Rewrite rules
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ index.php [QSA,L]
    </Directory>
    
    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

**Production (with Nginx):**

```nginx
server {
    listen 80;
    server_name recommender.example.com;
    root /path/to/smart-recommender/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

## Performance Tuning

### 1. Enable OPcache

Add to `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0  # For production
```

### 2. Setup Model Caching

Cache trained models to avoid retraining on every request:

```php
// In RecommendationController::__construct()
$cacheFile = __DIR__ . '/../../models/trained_model.cache';

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
    $this->recommender = unserialize(file_get_contents($cacheFile));
} else {
    $this->recommender->train();
    file_put_contents($cacheFile, serialize($this->recommender));
}
```

### 3. Database Optimization

```sql
-- Analyze tables for query optimization
ANALYZE TABLE users, products, user_interactions, recommendation_logs;

-- Enable query cache (if supported)
SET GLOBAL query_cache_size = 268435456;
SET GLOBAL query_cache_type = ON;

-- Add additional indexes if needed
SHOW INDEX FROM user_interactions;
```

### 4. Setup APCu for Result Caching

```bash
# Install APCu
pecl install apcu
echo "extension=apcu.so" > /etc/php/8.4/mods-available/apcu.ini
phpenmod apcu
```

```php
// Cache recommendations
$cacheKey = "recommendations_user_{$userId}";
$cached = apcu_fetch($cacheKey);

if ($cached !== false) {
    return $cached;
}

$recommendations = $this->recommender->recommend($userId);
apcu_store($cacheKey, $recommendations, 3600); // 1 hour TTL
```

## Monitoring

### Setup Logging

Create log directory:

```bash
mkdir -p logs
chmod 755 logs
```

Add logging to Config:

```php
'logging' => [
    'path' => __DIR__ . '/../logs/app.log',
    'level' => 'info',
]
```

### Monitor Key Metrics

Track these metrics in production:

- **Recommendation latency**: Target <100ms P95
- **Click-through rate (CTR)**: Target >10%
- **Conversion rate**: Target >2%
- **Model accuracy**: Target >70%
- **Memory usage**: Monitor for leaks
- **Error rates**: Set alerts for spikes

### Health Check Endpoint

Add to `public/health.php`:

```php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => time(),
    'database' => 'ok',
    'model' => 'ok',
];

try {
    $db = SmartRecommender\Database::getInstance();
    $db->query('SELECT 1');
} catch (\Exception $e) {
    $health['status'] = 'error';
    $health['database'] = 'error';
}

echo json_encode($health);
```

## Maintenance

### Daily Tasks

```bash
# Backup database
mysqldump smart_recommender > backups/backup_$(date +%Y%m%d).sql

# Review logs for errors
tail -n 100 logs/app.log | grep ERROR

# Check disk space
df -h
```

### Weekly Tasks

```bash
# Retrain model with new data
php examples/train-model.php

# Analyze performance
php examples/benchmark-system.php

# Check database size
mysql -e "SELECT 
  table_name,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'smart_recommender'
ORDER BY (data_length + index_length) DESC;"
```

### Monthly Tasks

- Review and optimize slow queries
- Clean old recommendation logs (>90 days):
  ```sql
  DELETE FROM recommendation_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
  ```
- Update dependencies: `composer update`
- Review security updates
- Analyze model performance trends

## Troubleshooting

### High Memory Usage

**Symptom**: PHP process using >500MB RAM

**Solutions**:

1. Reduce user matrix size:
   ```php
   // In CollaborativeFilter::calculateUserSimilarities()
   $maxUsers = 1000;
   $sampledUserIds = array_slice($userIds, 0, $maxUsers);
   ```

2. Implement pagination for large datasets
3. Use database aggregation instead of in-memory operations

### Slow Recommendations

**Symptom**: Recommendations taking >200ms

**Solutions**:

1. Enable caching (see Performance Tuning above)
2. Reduce similarity calculation complexity
3. Pre-compute recommendations for active users
4. Add database indexes

### Database Connection Errors

**Symptom**: "Too many connections" or "Connection refused"

**Solutions**:

```sql
-- Increase max connections
SET GLOBAL max_connections = 200;

-- Check current connections
SHOW PROCESSLIST;

-- Kill idle connections
-- (Implement connection pooling in production)
```

### Model Training Failures

**Symptom**: Training crashes or takes too long

**Solutions**:

1. Check available memory
2. Reduce dataset size for training
3. Implement incremental learning
4. Use sampling for large datasets

## Security Checklist

- [ ] Environment variables protected (.env not in git)
- [ ] Database credentials secure and rotated
- [ ] API rate limiting enabled
- [ ] Input validation on all endpoints
- [ ] HTTPS enforced in production
- [ ] SQL injection prevention (using prepared statements)
- [ ] XSS protection (using htmlspecialchars)
- [ ] CSRF protection for forms
- [ ] Error messages don't leak sensitive info
- [ ] File permissions properly set (755 for dirs, 644 for files)
- [ ] Regular security updates applied
- [ ] Logging excludes sensitive data

## Scaling Considerations

### Horizontal Scaling

1. **Load Balancer**: Use Nginx/HAProxy for distributing traffic
2. **Session Storage**: Use Redis for shared sessions
3. **Model Caching**: Use Redis/Memcached for model cache
4. **Database**: Setup read replicas for analytics queries

### Vertical Scaling

1. Increase PHP memory_limit
2. Add more CPU cores for parallel processing
3. Use SSD for database storage
4. Increase database connection pool

## Backup and Recovery

### Database Backups

```bash
# Daily automated backup
0 2 * * * mysqldump smart_recommender | gzip > /backups/$(date +\%Y\%m\%d).sql.gz

# Retain backups for 30 days
find /backups -name "*.sql.gz" -mtime +30 -delete
```

### Model Backups

```bash
# Backup trained models
0 3 * * * tar -czf /backups/models_$(date +\%Y\%m\%d).tar.gz models/
```

### Recovery Process

```bash
# Restore database
gunzip < backup_20260117.sql.gz | mysql smart_recommender

# Restore models
tar -xzf models_20260117.tar.gz -C /

# Retrain if needed
php examples/train-model.php
```

## Testing

### Run Test Suite

```bash
./vendor/bin/phpunit

# Expected output:
# OK (4 tests, 12 assertions)
```

### Load Testing

Use Apache Bench for load testing:

```bash
# Test recommendation endpoint
ab -n 1000 -c 10 http://localhost:8000/api.php/recommendations/1

# Analyze results:
# - Requests per second
# - Time per request (mean)
# - Transfer rate
```

### Integration Testing

```bash
# Test complete workflow
php tests/integration-test.php

# Should test:
# 1. Data collection
# 2. Model training
# 3. Recommendation generation
# 4. API endpoints
# 5. Dashboard access
```

## Support

For issues and questions:

- **Documentation**: See README.md and code comments
- **API Docs**: api/openapi.yaml
- **Issues**: GitHub Issues
- **Email**: support@example.com

## License

MIT License - See LICENSE file for details
