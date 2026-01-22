---
title: "12: Deploying Data Science Systems in Production with PHP"
description: "Deploy, monitor, and maintain data pipelines and ML systems with proper error handling and scaling"
sidebar:
  label: "12: Deploying Data Science Systems in Production with PHP"
  order: 12
  badge:
    text: Advanced
    variant: danger
---

![Deploying Data Science Systems in Production with PHP](/images/data-science-php-developers/chapter-12-production-deployment-hero-full.webp)

# Chapter 12: Deploying Data Science Systems in Production with PHP

## Overview

You've built your data science system—now it's time to deploy it to production where it handles real traffic, serves thousands of users, and operates 24/7. This final chapter teaches you everything needed to take systems from development to production: containerization, deployment automation, monitoring, error handling, scaling, and maintenance.

You'll learn to containerize applications with Docker, set up continuous integration and deployment (CI/CD) pipelines, implement comprehensive monitoring with alerting, handle errors gracefully at scale, optimize performance for high traffic, and establish maintenance workflows. By the end, you'll understand how to operate data science systems reliably in production environments.

This is where you become a complete data science engineer—building systems that not only work, but operate reliably, scale efficiently, and maintain themselves with minimal intervention.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 11: Building a Real-World Project](/series/data-science-php-developers/chapters/11-building-a-real-world-data-science-project-with-php)
- PHP 8.4+ installed
- Docker installed
- Basic Linux/Unix knowledge
- Access to a production server or cloud platform
- **Estimated Time**: ~90 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Check Docker installation
docker --version
docker-compose --version

# Check system resources
free -h
df -h

# Test server connectivity
ping -c 3 your-production-server.com
```

## What You'll Build

By the end of this chapter, you will have created:

- **Docker containerization** for PHP applications
- **Docker Compose** multi-container setup
- **CI/CD pipeline** with GitHub Actions
- **Production deployment script** with zero-downtime
- **Monitoring dashboard** with Prometheus/Grafana
- **Error tracking** with structured logging
- **Health check endpoints** for load balancers
- **Backup and recovery** system
- **Performance optimization** toolkit

## Objectives

- Containerize data science applications with Docker
- Set up CI/CD pipelines for automated deployment
- Implement comprehensive monitoring and alerting
- Handle errors gracefully in production
- Scale systems horizontally and vertically
- Optimize performance for high traffic
- Establish backup and recovery procedures
- Create maintenance runbooks

## Production Deployment Architecture

**CI/CD Pipeline:**

1. Git Repository → CI/CD Pipeline → Build Docker Images → Run Tests
2. Tests Pass?
   - **Yes** → Push to Registry → Deploy to Staging
   - **No** → Alert Developer
3. Staging OK?
   - **Yes** → Deploy to Production
   - **No** → Alert Developer

**Production Infrastructure:**

- **Load Balancer** distributes traffic to:
  - PHP App Container 1, 2, ..., N (horizontally scalable)
- All containers connect to:
  - **Database** (shared data store)
  - **Redis Cache** (shared caching layer)
- **Monitoring** tracks all containers → Alert System
- **Log Aggregation** collects logs from all containers

This architecture ensures high availability (multiple containers), scalability (add more containers), and observability (monitoring + logging).

## Step 1: Containerization with Docker (~20 min)

### Goal

Package application and dependencies into portable Docker containers.

### Actions

**1. Create Dockerfile for PHP application:**

```dockerfile
# filename: Dockerfile
# Multi-stage build for security and smaller image size
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    fcgi

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Production stage
FROM base AS production

# Set working directory
WORKDIR /var/www

# Create non-root user for security
RUN addgroup -g 1000 app && \
    adduser -D -u 1000 -G app app

# Copy composer files first for better layer caching
COPY --chown=app:app composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application files
COPY --chown=app:app . .

# Complete composer installation
RUN composer dump-autoload --optimize --classmap-authoritative

# Set permissions
RUN mkdir -p storage/logs storage/cache && \
    chown -R app:app storage && \
    chmod -R 755 storage

# Copy PHP-FPM health check script
COPY docker/php-fpm-healthcheck.sh /usr/local/bin/php-fpm-healthcheck
RUN chmod +x /usr/local/bin/php-fpm-healthcheck

# Switch to non-root user
USER app

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
```

**2. Create Docker Compose for multi-container setup:**

```yaml
# filename: docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      target: production
    image: smart-recommender:latest
    container_name: recommender-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./storage/logs:/var/www/storage/logs
    networks:
      - recommender-network
    environment:
      - APP_ENV=production
      - DB_HOST=db
      - DB_DATABASE=smart_recommender
      - DB_USERNAME=recommender_user
      - REDIS_HOST=redis
    secrets:
      - db_password
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    deploy:
      resources:
        limits:
          memory: 512M
        reservations:
          memory: 256M

  nginx:
    image: nginx:alpine
    container_name: recommender-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./public:/var/www/public:ro
      - ./docker/nginx:/etc/nginx/conf.d:ro
      - ./docker/nginx/ssl:/etc/nginx/ssl:ro
      - ./storage/logs/nginx:/var/log/nginx
    networks:
      - recommender-network
    depends_on:
      - app
    deploy:
      resources:
        limits:
          memory: 128M

  db:
    image: mysql:8.0
    container_name: recommender-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: smart_recommender
      MYSQL_USER: recommender_user
    secrets:
      - db_password
      - db_root_password
    environment:
      MYSQL_PASSWORD_FILE: /run/secrets/db_password
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
    volumes:
      - db-data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/schema.sql:ro
    networks:
      - recommender-network
    command: --default-authentication-plugin=mysql_native_password
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p$$(cat /run/secrets/db_root_password)"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s
    deploy:
      resources:
        limits:
          memory: 1G

  redis:
    image: redis:alpine
    container_name: recommender-redis
    restart: unless-stopped
    ports:
      - "127.0.0.1:6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - recommender-network
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 5
    deploy:
      resources:
        limits:
          memory: 256M

  prometheus:
    image: prom/prometheus:latest
    container_name: recommender-prometheus
    restart: unless-stopped
    ports:
      - "127.0.0.1:9090:9090"
    volumes:
      - ./docker/prometheus:/etc/prometheus:ro
      - prometheus-data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.path=/prometheus'
      - '--storage.tsdb.retention.time=30d'
    networks:
      - recommender-network
    deploy:
      resources:
        limits:
          memory: 512M

  grafana:
    image: grafana/grafana:latest
    container_name: recommender-grafana
    restart: unless-stopped
    ports:
      - "127.0.0.1:3000:3000"
    environment:
      - GF_SECURITY_ADMIN_USER=${GRAFANA_USER:-admin}
    secrets:
      - grafana_password
    environment:
      - GF_SECURITY_ADMIN_PASSWORD__FILE=/run/secrets/grafana_password
    volumes:
      - grafana-data:/var/lib/grafana
      - ./docker/grafana/dashboards:/etc/grafana/provisioning/dashboards:ro
      - ./docker/grafana/datasources:/etc/grafana/provisioning/datasources:ro
    networks:
      - recommender-network
    depends_on:
      - prometheus
    deploy:
      resources:
        limits:
          memory: 256M

networks:
  recommender-network:
    driver: bridge
    ipam:
      config:
        - subnet: 172.25.0.0/16

volumes:
  db-data:
    driver: local
  redis-data:
    driver: local
  prometheus-data:
    driver: local
  grafana-data:
    driver: local

secrets:
  db_password:
    file: ./secrets/db_password.txt
  db_root_password:
    file: ./secrets/db_root_password.txt
  grafana_password:
    file: ./secrets/grafana_password.txt
```

**Create secrets directory structure:**

```bash
# filename: .gitignore (add these lines)
secrets/
*.secret
```

```bash
# Create secrets directory and files (run once during setup)
mkdir -p secrets
echo "your_db_password" > secrets/db_password.txt
echo "your_db_root_password" > secrets/db_root_password.txt
echo "your_grafana_password" > secrets/grafana_password.txt
chmod 600 secrets/*.txt
```

**3. Create Nginx configuration:**

```nginx
# filename: docker/nginx/default.conf

# Rate limiting zones
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login_limit:10m rate=5r/m;

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    
    # Allow Let's Encrypt validation
    location /.well-known/acme-challenge/ {
        root /var/www/public;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/public;
    index index.php;

    # SSL configuration
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    ssl_stapling on;
    ssl_stapling_verify on;

    # Logging
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log warn;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    # Remove server header
    server_tokens off;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript application/x-javascript text/xml application/xml application/xml+rss text/javascript;
    gzip_min_length 1000;

    # Client body size limit
    client_max_body_size 10M;
    client_body_buffer_size 128k;

    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    keepalive_timeout 15;
    send_timeout 10;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API endpoints with rate limiting
    location /api/ {
        limit_req zone=api_limit burst=20 nodelay;
        limit_req_status 429;
        
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Login endpoint with stricter rate limiting
    location /api/login {
        limit_req zone=login_limit burst=3 nodelay;
        limit_req_status 429;
        
        try_files $uri /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        
        # Timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
        
        # Buffer sizes
        fastcgi_buffer_size 32k;
        fastcgi_buffers 16 32k;
        fastcgi_busy_buffers_size 64k;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to sensitive files
    location ~ /(vendor|storage|database|tests|config)/ {
        deny all;
        return 404;
    }

    # Static assets caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Health check endpoint (no auth required)
    location /health {
        access_log off;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        include fastcgi_params;
    }

    # Metrics endpoint (restrict access)
    location /metrics {
        access_log off;
        
        # Allow only from Prometheus container
        allow 172.25.0.0/16;
        deny all;
        
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        include fastcgi_params;
    }

    # Favicon
    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    # Robots.txt
    location = /robots.txt {
        access_log off;
        log_not_found off;
    }
}
```

**For development (HTTP only):**

```nginx
# filename: docker/nginx/default.dev.conf
server {
    listen 80;
    server_name _;
    root /var/www/public;
    index index.php;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Basic security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location /health {
        access_log off;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        include fastcgi_params;
    }
}
```

**4. Create deployment script:**

```bash
# filename: deploy.sh
#!/bin/bash

set -e

echo "=== Zero-Downtime Deployment for Smart Recommender ==="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Store current image tags for rollback
CURRENT_VERSION=$(docker-compose images -q app 2>/dev/null | head -1)
NEW_VERSION=$(date +%Y%m%d-%H%M%S)

echo "Current version: ${CURRENT_VERSION:-none}"
echo "New version: ${NEW_VERSION}"
echo ""

# Pull latest code
echo "1. Pulling latest code..."
git pull origin main
if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Failed to pull latest code${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Code updated${NC}"

# Build new Docker images
echo ""
echo "2. Building Docker images..."
docker-compose build --no-cache app
if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Failed to build Docker images${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Images built successfully${NC}"

# Tag new version
echo ""
echo "3. Tagging new version..."
docker tag smart-recommender:latest smart-recommender:${NEW_VERSION}
docker tag smart-recommender:latest smart-recommender:backup
echo -e "${GREEN}✓ Version tagged: ${NEW_VERSION}${NC}"

# Run tests on new image
echo ""
echo "4. Running tests on new image..."
docker-compose run --rm -e APP_ENV=testing app vendor/bin/phpunit --stop-on-failure
if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Tests failed - deployment cancelled${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Tests passed${NC}"

# Scale up with new containers
echo ""
echo "5. Starting new containers alongside existing ones..."
docker-compose up -d --scale app=2 --no-recreate
sleep 10

# Wait for new containers to be healthy
echo ""
echo "6. Waiting for health checks..."
MAX_ATTEMPTS=30
ATTEMPT=0
while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if curl -f -s http://localhost/health > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Health check passed${NC}"
        break
    fi
    ATTEMPT=$((ATTEMPT + 1))
    echo "  Attempt $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo -e "${RED}✗ Health check failed after $MAX_ATTEMPTS attempts${NC}"
    echo "Rolling back..."
    docker-compose up -d --scale app=1
    if [ ! -z "$CURRENT_VERSION" ]; then
        docker tag ${CURRENT_VERSION} smart-recommender:latest
    fi
    exit 1
fi

# Verify application functionality
echo ""
echo "7. Verifying application functionality..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)
if [ "$HTTP_CODE" != "200" ]; then
    echo -e "${RED}✗ Application returned HTTP $HTTP_CODE - rolling back${NC}"
    docker-compose up -d --scale app=1
    if [ ! -z "$CURRENT_VERSION" ]; then
        docker tag ${CURRENT_VERSION} smart-recommender:latest
    fi
    exit 1
fi
echo -e "${GREEN}✓ Application responding correctly${NC}"

# Run database migrations
echo ""
echo "8. Running database migrations..."
docker-compose exec -T app php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠  Migration failed, but continuing...${NC}"
fi

# Scale down old containers
echo ""
echo "9. Removing old containers..."
docker-compose up -d --scale app=1 --remove-orphans
sleep 5
echo -e "${GREEN}✓ Old containers removed${NC}"

# Clear caches
echo ""
echo "10. Clearing application caches..."
docker-compose exec -T app php artisan cache:clear 2>/dev/null || true
docker-compose exec -T app php artisan config:cache 2>/dev/null || true
docker-compose exec -T app php artisan route:cache 2>/dev/null || true
echo -e "${GREEN}✓ Caches cleared${NC}"

# Final health check
echo ""
echo "11. Final verification..."
sleep 3
if curl -f -s http://localhost/health > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Final health check passed${NC}"
else
    echo -e "${RED}✗ Final health check failed${NC}"
    exit 1
fi

# Clean up old images
echo ""
echo "12. Cleaning up old images..."
docker image prune -f > /dev/null 2>&1

# Save deployment info
echo ""
echo "13. Recording deployment..."
cat > .last-deployment <<EOF
version: ${NEW_VERSION}
timestamp: $(date -u +"%Y-%m-%dT%H:%M:%SZ")
commit: $(git rev-parse HEAD)
deployed_by: $(whoami)
EOF

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Deployment Info:"
echo "  - Version: ${NEW_VERSION}"
echo "  - Commit: $(git rev-parse --short HEAD)"
echo "  - Time: $(date)"
echo ""
echo "Services:"
echo "  - Application: http://localhost"
echo "  - Grafana: http://localhost:3000"
echo "  - Prometheus: http://localhost:9090"
echo ""
echo "Rollback command (if needed):"
echo "  docker tag smart-recommender:backup smart-recommender:latest"
echo "  docker-compose up -d --force-recreate"
echo ""
```

**Create rollback script:**

```bash
# filename: rollback.sh
#!/bin/bash

set -e

echo "=== Rollback Smart Recommender ==="
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if backup exists
if ! docker images | grep -q "smart-recommender.*backup"; then
    echo -e "${RED}✗ No backup image found${NC}"
    echo "Cannot rollback - no previous version available"
    exit 1
fi

# Get last deployment info
if [ -f .last-deployment ]; then
    echo "Last deployment info:"
    cat .last-deployment
    echo ""
fi

# Confirm rollback
read -p "Are you sure you want to rollback? (yes/no) " -r
if [[ ! $REPLY =~ ^yes$ ]]; then
    echo "Rollback cancelled"
    exit 0
fi

echo ""
echo "1. Stopping current containers..."
docker-compose down

echo ""
echo "2. Restoring backup image..."
docker tag smart-recommender:backup smart-recommender:latest

echo ""
echo "3. Starting containers with backup version..."
docker-compose up -d

echo ""
echo "4. Waiting for services to start..."
sleep 15

echo ""
echo "5. Health check..."
if curl -f -s http://localhost/health > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Rollback successful${NC}"
    echo ""
    echo "System rolled back to previous version"
else
    echo -e "${RED}✗ Health check failed after rollback${NC}"
    exit 1
fi
```

Make scripts executable:

```bash
chmod +x deploy.sh rollback.sh
```

### Expected Result

Running `docker-compose up -d`:

```
Creating network "smart-recommender_recommender-network" with driver "bridge"
Creating volume "smart-recommender_db-data" with default driver
Creating volume "smart-recommender_redis-data" with default driver
Creating recommender-db ... done
Creating recommender-redis ... done
Creating recommender-app ... done
Creating recommender-nginx ... done
Creating recommender-prometheus ... done
Creating recommender-grafana ... done

Services started successfully!
```

Checking status:

```bash
$ docker-compose ps

NAME                  STATUS         PORTS
recommender-app       Up 30 seconds  9000/tcp
recommender-nginx     Up 30 seconds  0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
recommender-db        Up 30 seconds  3306/tcp
recommender-redis     Up 30 seconds  0.0.0.0:6379->6379/tcp
recommender-prometheus Up 30 seconds 0.0.0.0:9090->9090/tcp
recommender-grafana   Up 30 seconds  0.0.0.0:3000->3000/tcp
```

### Why It Works

**Docker containerization** provides:

- **Consistency**: Same environment everywhere (dev/staging/prod)
- **Isolation**: Dependencies don't conflict
- **Portability**: Run anywhere Docker runs
- **Scalability**: Easy to replicate containers
- **Reproducibility**: Dockerfile documents setup

**Docker Compose** orchestrates:
- Multiple related services
- Network communication
- Volume persistence
- Environment configuration

### Troubleshooting

**Problem: Port already in use**

**Cause**: Another service using port 80, 3306, etc.

**Solution**: Change ports in docker-compose.yml:

```yaml
ports:
  - "8080:80"  # Use port 8080 instead
```

**Problem: Permission denied errors**

**Cause**: Wrong file permissions inside container.

**Solution**: Fix permissions in Dockerfile:

```dockerfile
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache
```

**Problem: Database connection refused**

**Cause**: App starts before database is ready.

**Solution**: Add healthcheck or wait script:

```yaml
services:
  app:
    depends_on:
      db:
        condition: service_healthy
  
  db:
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
```

## Step 2: CI/CD Pipeline with GitHub Actions (~20 min)

### Goal

Automate testing, building, and deployment on every code push.

### Actions

**1. Create GitHub Actions workflow:**

```yaml
# filename: .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo_mysql, zip
          coverage: none
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Copy environment file
        run: cp .env.testing .env
      
      - name: Generate application key
        run: php artisan key:generate
      
      - name: Run database migrations
        run: php artisan migrate --force
      
      - name: Run tests
        run: vendor/bin/phpunit --testdox
      
      - name: Run static analysis
        run: vendor/bin/phpstan analyse

  build:
    name: Build Docker Image
    runs-on: ubuntu-latest
    needs: test
    if: github.event_name == 'push' && github.ref == 'refs/heads/main'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v2
      
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      
      - name: Build and push
        uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: |
            ${{ secrets.DOCKER_USERNAME }}/smart-recommender:latest
            ${{ secrets.DOCKER_USERNAME }}/smart-recommender:${{ github.sha }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: build
    if: github.event_name == 'push' && github.ref == 'refs/heads/main'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/smart-recommender
            git pull origin main
            docker-compose pull
            docker-compose up -d --no-build
            docker-compose exec -T app php artisan migrate --force
            docker-compose exec -T app php artisan cache:clear
      
      - name: Notify deployment
        uses: 8398a7/action-slack@v3
        if: always()
        with:
          status: ${{ job.status }}
          text: |
            Deployment ${{ job.status }}
            Commit: ${{ github.sha }}
            Author: ${{ github.actor }}
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

**2. Create test environment config:**

```bash
# filename: .env.testing
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:TEST_KEY_HERE

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testing
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

**3. Add PHPUnit configuration:**

```xml
# filename: phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="testing"/>
    </php>
</phpunit>
```

**4. Create sample tests:**

```php
# filename: tests/Unit/RecommenderTest.php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartRecommender\ML\CollaborativeFilter;

class RecommenderTest extends TestCase
{
    public function test_recommender_returns_products(): void
    {
        $recommender = new CollaborativeFilter();
        
        // Test with mock data
        $recommendations = $recommender->recommend(
            userId: 1,
            count: 5
        );
        
        $this->assertIsArray($recommendations);
        $this->assertLessThanOrEqual(5, count($recommendations));
    }
    
    public function test_recommender_excludes_owned_products(): void
    {
        $recommender = new CollaborativeFilter();
        
        $recommendations = $recommender->recommend(
            userId: 1,
            count: 10,
            excludeOwned: true
        );
        
        foreach ($recommendations as $rec) {
            $this->assertArrayHasKey('product_id', $rec);
            $this->assertArrayHasKey('score', $rec);
        }
    }
}
```

### Expected Result

On git push to main:

```
✓ test/Run Tests (2m 15s)
  - Setup PHP: ✓
  - Install dependencies: ✓
  - Run tests: ✓ (47 tests, 120 assertions)
  - Static analysis: ✓

✓ build/Build Docker Image (3m 42s)
  - Build and push: ✓
  - Tagged: smart-recommender:latest, smart-recommender:a1b2c3d

✓ deploy/Deploy to Production (1m 33s)
  - Deploy to server: ✓
  - Health check: ✓
  - Notify deployment: ✓

Deployment completed successfully!
```

### Why It Works

**CI/CD automation**:

1. **Continuous Integration**: Run tests on every push
2. **Continuous Deployment**: Auto-deploy passing builds
3. **Quality gates**: Block bad code from production
4. **Fast feedback**: Know within minutes if something breaks

**Benefits**:
- Catch bugs early
- Deploy frequently
- Reduce manual errors
- Faster development

### Troubleshooting

**Problem: Tests fail in CI but pass locally**

**Cause**: Different environments or database state.

**Solution**: Use fresh database for each test:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecommenderTest extends TestCase
{
    use RefreshDatabase;
    
    // Tests run with clean database
}
```

**Problem: Docker build fails in CI**

**Cause**: Missing secrets or network issues.

**Solution**: Check GitHub Actions secrets and add retry:

```yaml
- name: Build and push
  uses: docker/build-push-action@v4
  with:
    context: .
    push: true
    tags: myimage:latest
  retry:
    max_attempts: 3
```

## Step 3: Monitoring and Alerting (~15 min)

### Goal

Implement comprehensive monitoring to detect issues before users do.

### Actions

**1. Create health check endpoint:**

```php
# filename: src/API/HealthController.php
<?php

declare(strict_types=1);

namespace SmartRecommender\API;

use SmartRecommender\Database;
use PDO;

class HealthController
{
    /**
     * Comprehensive health check
     */
    public function check(): array
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'version' => $this->getAppVersion(),
            'checks' => [],
        ];
        
        // Database check with query time
        $health['checks']['database'] = $this->checkDatabase();
        if ($health['checks']['database']['status'] !== 'ok') {
            $health['status'] = 'unhealthy';
        }
        
        // Redis check
        $health['checks']['redis'] = $this->checkRedis();
        if ($health['checks']['redis']['status'] !== 'ok') {
            $health['status'] = 'degraded'; // Can work without Redis
        }
        
        // Disk space check
        $health['checks']['disk'] = $this->checkDiskSpace();
        if ($health['checks']['disk']['status'] === 'critical') {
            $health['status'] = 'unhealthy';
        }
        
        // Memory check
        $health['checks']['memory'] = $this->checkMemory();
        if ($health['checks']['memory']['status'] === 'critical') {
            $health['status'] = 'unhealthy';
        }
        
        // Model availability check
        $health['checks']['models'] = $this->checkModels();
        if ($health['checks']['models']['status'] !== 'ok') {
            $health['status'] = 'degraded';
        }
        
        // API dependencies check
        $health['checks']['dependencies'] = $this->checkDependencies();
        
        // Set HTTP status code based on health
        if ($health['status'] === 'unhealthy') {
            http_response_code(503);
        } elseif ($health['status'] === 'degraded') {
            http_response_code(200); // Still accepting traffic
        }
        
        return $health;
    }
    
    /**
     * Readiness check (for load balancer)
     */
    public function ready(): array
    {
        $start = microtime(true);
        
        try {
            $db = Database::getInstance();
            
            // Can we query database?
            $stmt = $db->query('SELECT COUNT(*) FROM users LIMIT 1');
            
            // Can we access models?
            $modelsReady = $this->checkModels()['status'] === 'ok';
            
            $responseTime = (microtime(true) - $start) * 1000;
            
            if ($modelsReady) {
                return [
                    'ready' => true,
                    'response_time_ms' => round($responseTime, 2)
                ];
            } else {
                http_response_code(503);
                return [
                    'ready' => false,
                    'reason' => 'models_not_loaded'
                ];
            }
        } catch (\Exception $e) {
            http_response_code(503);
            return [
                'ready' => false,
                'reason' => 'database_unavailable',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Liveness check (for Kubernetes)
     */
    public function alive(): array
    {
        // Simple check - is PHP running?
        return [
            'alive' => true,
            'timestamp' => time()
        ];
    }
    
    /**
     * Check database connectivity and performance
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);
        
        try {
            $db = Database::getInstance();
            $db->query('SELECT 1');
            
            $responseTime = (microtime(true) - $start) * 1000;
            
            // Check connection count
            $stmt = $db->query('SHOW STATUS LIKE "Threads_connected"');
            $connections = $stmt->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0;
            
            return [
                'status' => 'ok',
                'response_time_ms' => round($responseTime, 2),
                'connections' => (int)$connections
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check Redis connectivity
     */
    private function checkRedis(): array
    {
        $start = microtime(true);
        
        try {
            $redis = new \Redis();
            $redis->connect(
                $_ENV['REDIS_HOST'] ?? 'redis',
                (int)($_ENV['REDIS_PORT'] ?? 6379)
            );
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redis->auth($_ENV['REDIS_PASSWORD']);
            }
            
            $redis->ping();
            
            $responseTime = (microtime(true) - $start) * 1000;
            
            // Get memory usage
            $info = $redis->info('memory');
            $memoryUsed = $info['used_memory_human'] ?? 'unknown';
            
            return [
                'status' => 'ok',
                'response_time_ms' => round($responseTime, 2),
                'memory_used' => $memoryUsed
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskPercent = ($diskFree / $diskTotal) * 100;
        
        $status = 'ok';
        if ($diskPercent < 5) {
            $status = 'critical';
        } elseif ($diskPercent < 10) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'free_percent' => round($diskPercent, 2),
            'free_gb' => round($diskFree / (1024 ** 3), 2),
            'total_gb' => round($diskTotal / (1024 ** 3), 2),
        ];
    }
    
    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memUsage = memory_get_usage(true);
        $memLimit = $this->getMemoryLimit();
        $memPercent = ($memUsage / $memLimit) * 100;
        
        $status = 'ok';
        if ($memPercent > 95) {
            $status = 'critical';
        } elseif ($memPercent > 90) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'usage_percent' => round($memPercent, 2),
            'usage_mb' => round($memUsage / (1024 ** 2), 2),
            'limit_mb' => round($memLimit / (1024 ** 2), 2),
        ];
    }
    
    /**
     * Check ML models availability
     */
    private function checkModels(): array
    {
        $modelsDir = __DIR__ . '/../../models';
        $requiredModels = [
            'collaborative_filter.model',
            'evaluation/metrics.json',
        ];
        
        $missing = [];
        $sizes = [];
        
        foreach ($requiredModels as $model) {
            $path = $modelsDir . '/' . $model;
            if (!file_exists($path)) {
                $missing[] = $model;
            } else {
                $sizes[$model] = filesize($path);
            }
        }
        
        if (empty($missing)) {
            return [
                'status' => 'ok',
                'models_loaded' => count($requiredModels),
                'total_size_mb' => round(array_sum($sizes) / (1024 ** 2), 2)
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Missing models',
            'missing' => $missing,
        ];
    }
    
    /**
     * Check external dependencies
     */
    private function checkDependencies(): array
    {
        $dependencies = [
            'php_version' => PHP_VERSION,
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'redis' => extension_loaded('redis'),
                'mbstring' => extension_loaded('mbstring'),
                'json' => extension_loaded('json'),
            ],
            'composer' => file_exists(__DIR__ . '/../../vendor/autoload.php'),
        ];
        
        return $dependencies;
    }
    
    /**
     * Get application version
     */
    private function getAppVersion(): string
    {
        if (file_exists(__DIR__ . '/../../.last-deployment')) {
            $deployment = parse_ini_file(__DIR__ . '/../../.last-deployment');
            return $deployment['version'] ?? 'unknown';
        }
        
        // Try git commit
        $gitHead = __DIR__ . '/../../.git/HEAD';
        if (file_exists($gitHead)) {
            $head = trim(file_get_contents($gitHead));
            if (preg_match('/^[0-9a-f]{40}$/i', $head)) {
                return substr($head, 0, 7);
            }
        }
        
        return 'dev';
    }
    
    /**
     * Get PHP memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit == -1) {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int)$limit;
        
        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
```

**2. Create Prometheus metrics exporter:**

```php
# filename: src/Monitoring/MetricsExporter.php
<?php

declare(strict_types=1);

namespace SmartRecommender\Monitoring;

use SmartRecommender\Database;

class MetricsExporter
{
    private Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Export metrics in Prometheus format
     */
    public function export(): string
    {
        $metrics = [];
        
        // Recommendation metrics
        $metrics[] = $this->formatMetric(
            'recommender_requests_total',
            'counter',
            'Total recommendation requests',
            $this->getRecommendationCount()
        );
        
        $metrics[] = $this->formatMetric(
            'recommender_cache_hit_ratio',
            'gauge',
            'Cache hit ratio',
            $this->getCacheHitRatio()
        );
        
        $metrics[] = $this->formatMetric(
            'recommender_avg_response_time_seconds',
            'gauge',
            'Average response time',
            $this->getAvgResponseTime()
        );
        
        // Model metrics
        $metrics[] = $this->formatMetric(
            'model_accuracy',
            'gauge',
            'Current model accuracy',
            $this->getModelAccuracy()
        );
        
        $metrics[] = $this->formatMetric(
            'model_predictions_total',
            'counter',
            'Total predictions made',
            $this->getPredictionCount()
        );
        
        // Business metrics
        $metrics[] = $this->formatMetric(
            'recommendations_clicked_total',
            'counter',
            'Total recommendation clicks',
            $this->getClickCount()
        );
        
        $metrics[] = $this->formatMetric(
            'recommendations_purchased_total',
            'counter',
            'Total purchases from recommendations',
            $this->getPurchaseCount()
        );
        
        return implode("\n", $metrics);
    }
    
    /**
     * Format metric in Prometheus format
     */
    private function formatMetric(
        string $name,
        string $type,
        string $help,
        float $value
    ): string {
        return sprintf(
            "# HELP %s %s\n# TYPE %s %s\n%s %f",
            $name,
            $help,
            $name,
            $type,
            $name,
            $value
        );
    }
    
    private function getRecommendationCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM recommendation_logs");
        return (int)$stmt->fetchColumn();
    }
    
    private function getCacheHitRatio(): float
    {
        // Implement cache hit tracking
        return 0.75; // Example value
    }
    
    private function getAvgResponseTime(): float
    {
        // Implement response time tracking
        return 0.045; // Example: 45ms
    }
    
    private function getModelAccuracy(): float
    {
        $stmt = $this->db->query("
            SELECT metric_value
            FROM model_metrics
            WHERE metric_name = 'accuracy'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        return (float)($stmt->fetchColumn() ?: 0.0);
    }
    
    private function getPredictionCount(): int
    {
        return $this->getRecommendationCount();
    }
    
    private function getClickCount(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM recommendation_logs
            WHERE clicked = TRUE
        ");
        
        return (int)$stmt->fetchColumn();
    }
    
    private function getPurchaseCount(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM recommendation_logs
            WHERE purchased = TRUE
        ");
        
        return (int)$stmt->fetchColumn();
    }
}
```

**3. Configure Prometheus:**

```yaml
# filename: docker/prometheus/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'smart-recommender'
    static_configs:
      - targets: ['app:8080']
    metrics_path: '/metrics'

  - job_name: 'node'
    static_configs:
      - targets: ['node-exporter:9100']

alerting:
  alertmanagers:
    - static_configs:
        - targets: ['alertmanager:9093']

rule_files:
  - 'alerts.yml'
```

**4. Define alert rules:**

```yaml
# filename: docker/prometheus/alerts.yml
groups:
  - name: smart_recommender
    interval: 30s
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.05
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          description: "Error rate is {{ $value | humanizePercentage }} over the last 5 minutes"
      
      - alert: LowModelAccuracy
        expr: model_accuracy < 0.70
        for: 10m
        labels:
          severity: warning
        annotations:
          summary: "Model accuracy below threshold"
          description: "Model accuracy is {{ $value }}, below 0.70 threshold"
      
      - alert: HighResponseTime
        expr: recommender_avg_response_time_seconds > 1.0
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High response time"
          description: "Average response time is {{ $value }}s"
      
      - alert: DatabaseDown
        expr: up{job="mysql"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Database is down"
          description: "MySQL database is unreachable"
```

### Expected Result

Accessing `/health`:

```json
{
  "status": "healthy",
  "timestamp": "2026-01-12T15:30:45+00:00",
  "checks": {
    "database": { "status": "ok" },
    "redis": { "status": "ok" },
    "disk": { "status": "ok", "free_percent": 45.32 },
    "memory": { "status": "ok", "usage_percent": 35.17 }
  }
}
```

Accessing `/metrics`:

```
# HELP recommender_requests_total Total recommendation requests
# TYPE recommender_requests_total counter
recommender_requests_total 125847

# HELP model_accuracy Current model accuracy
# TYPE model_accuracy gauge
model_accuracy 0.923

# HELP recommendations_clicked_total Total recommendation clicks
# TYPE recommendations_clicked_total counter
recommendations_clicked_total 8542
```

### Why It Works

**Proactive monitoring** detects issues:

- **Health checks**: Quick status verification
- **Metrics**: Quantifiable system performance
- **Alerts**: Automated problem notification
- **Dashboards**: Visual system overview

**Multi-layered approach**:
1. Application metrics (recommendations, accuracy)
2. System metrics (CPU, memory, disk)
3. Business metrics (clicks, conversions)

### Troubleshooting

**Problem: Prometheus not scraping metrics**

**Cause**: Wrong endpoint or network configuration.

**Solution**: Verify Prometheus can reach app:

```bash
# From Prometheus container
docker-compose exec prometheus wget -O- http://app:8080/metrics

# Check Prometheus targets
curl http://localhost:9090/api/v1/targets
```

**Problem: Alerts not firing**

**Cause**: Alert rules syntax error or wrong thresholds.

**Solution**: Validate alert rules:

```bash
# Check Prometheus UI for rule errors
# Visit: http://localhost:9090/rules

# Test alert expression
curl 'http://localhost:9090/api/v1/query?query=model_accuracy<0.70'
```

## Step 4: Performance Optimization (~20 min)

### Goal

Optimize PHP-FPM, OPcache, and database for production performance.

### Actions

**1. Configure PHP-FPM for production:**

```ini
# filename: docker/php-fpm/www.conf
[www]
; Process management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Performance
pm.process_idle_timeout = 10s
request_terminate_timeout = 300

; Status page
pm.status_path = /fpm-status
ping.path = /fpm-ping
ping.response = pong

; Logging
php_admin_value[error_log] = /var/www/storage/logs/php-fpm.log
php_admin_flag[log_errors] = on
catch_workers_output = yes

; Resource limits
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 60
php_admin_value[max_input_time] = 60
php_admin_value[post_max_size] = 10M
php_admin_value[upload_max_filesize] = 10M

; Security
php_admin_value[open_basedir] = /var/www:/tmp
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen
```

**2. Configure OPcache for production:**

```ini
# filename: docker/php/opcache.ini
[opcache]
; Enable OPcache
opcache.enable=1
opcache.enable_cli=0

; Memory settings
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000

; Performance
opcache.validate_timestamps=0  ; Disable in production for max performance
opcache.revalidate_freq=0
opcache.fast_shutdown=1
opcache.enable_file_override=1

; Optimization
opcache.optimization_level=0x7FFEBFFF
opcache.max_wasted_percentage=5

; JIT (PHP 8.4)
opcache.jit=tracing
opcache.jit_buffer_size=128M

; Debugging (disable in production)
opcache.error_log=/var/www/storage/logs/opcache.log
```

**3. Configure PHP production settings:**

```ini
# filename: docker/php/php.ini
[PHP]
; Performance
realpath_cache_size=4096K
realpath_cache_ttl=600

; Memory
memory_limit=256M

; Error handling
display_errors=Off
display_startup_errors=Off
log_errors=On
error_log=/var/www/storage/logs/php-error.log
error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT

; Security
expose_php=Off
session.cookie_httponly=On
session.cookie_secure=On
session.use_strict_mode=On

; File uploads
file_uploads=On
upload_max_filesize=10M
max_file_uploads=20

; Timeouts
max_execution_time=60
max_input_time=60
default_socket_timeout=60

; Output
output_buffering=4096
implicit_flush=Off

; Date
date.timezone=UTC
```

**4. Create database performance tuning script:**

```sql
# filename: database/performance-tuning.sql

-- MySQL performance settings
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL innodb_log_file_size = 268435456;     -- 256MB
SET GLOBAL innodb_flush_log_at_trx_commit = 2;   -- Better performance, slight risk
SET GLOBAL innodb_flush_method = O_DIRECT;

-- Query cache (if using MySQL < 8.0)
-- SET GLOBAL query_cache_type = 1;
-- SET GLOBAL query_cache_size = 67108864; -- 64MB

-- Connection settings
SET GLOBAL max_connections = 200;
SET GLOBAL max_connect_errors = 100;

-- Table optimization
SET GLOBAL innodb_file_per_table = ON;
SET GLOBAL innodb_stats_on_metadata = OFF;

-- Show current settings
SHOW VARIABLES LIKE 'innodb_buffer_pool_size';
SHOW VARIABLES LIKE 'max_connections';
SHOW STATUS LIKE 'Threads_connected';
```

**5. Add database indexes for recommendations:**

```sql
# filename: database/optimize-indexes.sql

-- User table indexes
ALTER TABLE users
  ADD INDEX idx_created_at (created_at),
  ADD INDEX idx_last_login (last_login_at);

-- Products table indexes
ALTER TABLE products
  ADD INDEX idx_category_price (category_id, price),
  ADD INDEX idx_created_at (created_at),
  ADD FULLTEXT INDEX idx_name_description (name, description);

-- Interactions table (most critical for recommendations)
ALTER TABLE user_product_interactions
  ADD INDEX idx_user_product (user_id, product_id),
  ADD INDEX idx_user_timestamp (user_id, interaction_timestamp),
  ADD INDEX idx_product_timestamp (product_id, interaction_timestamp),
  ADD INDEX idx_interaction_type (interaction_type);

-- Recommendations cache table
CREATE TABLE IF NOT EXISTS recommendation_cache (
    user_id INT UNSIGNED NOT NULL,
    product_ids TEXT NOT NULL,
    scores TEXT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Analyze tables
ANALYZE TABLE users;
ANALYZE TABLE products;
ANALYZE TABLE user_product_interactions;

-- Show index usage
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    SEQ_IN_INDEX,
    COLUMN_NAME,
    CARDINALITY
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

**6. Create performance monitoring script:**

```php
# filename: src/Monitoring/PerformanceMonitor.php
<?php

declare(strict_types=1);

namespace SmartRecommender\Monitoring;

class PerformanceMonitor
{
    private float $startTime;
    private array $metrics = [];
    
    public function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    /**
     * Track a performance metric
     */
    public function track(string $name, callable $callback): mixed
    {
        $start = microtime(true);
        $result = $callback();
        $duration = (microtime(true) - $start) * 1000;
        
        $this->metrics[$name] = [
            'duration_ms' => round($duration, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / (1024 ** 2), 2),
        ];
        
        return $result;
    }
    
    /**
     * Get all metrics
     */
    public function getMetrics(): array
    {
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        
        return [
            'total_time_ms' => round($totalTime, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / (1024 ** 2), 2),
            'operations' => $this->metrics,
            'opcache' => $this->getOPcacheStats(),
        ];
    }
    
    /**
     * Get OPcache statistics
     */
    private function getOPcacheStats(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }
        
        $status = opcache_get_status(false);
        
        if (!$status) {
            return ['enabled' => false];
        }
        
        return [
            'enabled' => true,
            'hit_rate' => round($status['opcache_statistics']['opcache_hit_rate'] ?? 0, 2),
            'memory_used_mb' => round($status['memory_usage']['used_memory'] / (1024 ** 2), 2),
            'cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
        ];
    }
}
```

**7. Update Dockerfile to include performance configs:**

```dockerfile
# Add to Dockerfile after extensions installation
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
```

### Expected Result

**OPcache status (after deployment):**

```
OPcache Status:
  - Hit rate: 99.8%
  - Memory used: 147 MB / 256 MB
  - Cached scripts: 1,847
  - JIT enabled: Yes
```

**Performance improvements:**

```
Before optimization:
  - Average response time: 250ms
  - Requests per second: 45
  - Memory per request: 18 MB
  - Database query time: 120ms

After optimization:
  - Average response time: 45ms  (↓ 82%)
  - Requests per second: 185     (↑ 311%)
  - Memory per request: 12 MB    (↓ 33%)
  - Database query time: 15ms    (↓ 87%)
```

### Why It Works

**PHP-FPM tuning**:
- Dynamic process management adjusts to load
- Process recycling prevents memory leaks
- Status page enables monitoring

**OPcache optimization**:
- Eliminates PHP compilation overhead
- Keeps compiled code in memory
- JIT compilation for hot code paths
- Disabling timestamp validation in production

**Database optimization**:
- Strategic indexes speed up queries
- Buffer pool fits working set in memory
- Connection pooling reduces overhead

### Troubleshooting

**Problem: OPcache not enabled**

**Cause**: Configuration not loaded or extension missing.

**Solution**: Verify OPcache:

```bash
docker-compose exec app php -i | grep opcache
docker-compose exec app php -r "var_dump(opcache_get_status());"
```

**Problem: High memory usage**

**Cause**: Too many PHP-FPM workers or memory leaks.

**Solution**: Adjust pm.max_children:

```ini
; Reduce max workers if memory constrained
pm.max_children = 25
pm.max_requests = 100  ; Recycle workers more often
```

**Problem: Slow database queries**

**Cause**: Missing indexes or unoptimized queries.

**Solution**: Use EXPLAIN to analyze:

```sql
EXPLAIN SELECT * FROM user_product_interactions 
WHERE user_id = 123 
ORDER BY interaction_timestamp DESC 
LIMIT 10;
```

## Step 5: Backup and Recovery (~15 min)

### Goal

Implement automated backup and tested recovery procedures.

### Actions

**1. Create backup script:**

```bash
# filename: scripts/backup.sh
#!/bin/bash

set -e

echo "=== Backup Smart Recommender ==="
echo ""

# Configuration
BACKUP_DIR="${BACKUP_DIR:-/backups}"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_PATH="$BACKUP_DIR/$TIMESTAMP"
RETENTION_DAYS=30

# Load environment
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Create backup directory
mkdir -p "$BACKUP_PATH"

# 1. Backup database
echo "1. Backing up database..."
docker-compose exec -T db mysqldump \
    -u recommender_user \
    -p$(cat secrets/db_password.txt) \
    smart_recommender \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    > "$BACKUP_PATH/database.sql"

if [ $? -eq 0 ]; then
    echo "✓ Database backed up ($(du -h "$BACKUP_PATH/database.sql" | cut -f1))"
else
    echo "✗ Database backup failed"
    exit 1
fi

# 2. Backup ML models
echo "2. Backing up ML models..."
if [ -d "models" ]; then
    tar -czf "$BACKUP_PATH/models.tar.gz" models/
    echo "✓ Models backed up ($(du -h "$BACKUP_PATH/models.tar.gz" | cut -f1))"
else
    echo "⚠  No models directory found"
fi

# 3. Backup uploaded files
echo "3. Backing up user files..."
if [ -d "storage/uploads" ]; then
    tar -czf "$BACKUP_PATH/uploads.tar.gz" storage/uploads/
    echo "✓ Files backed up ($(du -h "$BACKUP_PATH/uploads.tar.gz" | cut -f1))"
else
    echo "⚠  No uploads directory found"
fi

# 4. Backup configuration files
echo "4. Backing up configuration..."
cp .env "$BACKUP_PATH/.env.backup" 2>/dev/null || true
cp docker-compose.yml "$BACKUP_PATH/docker-compose.yml"
cp -r secrets "$BACKUP_PATH/secrets" 2>/dev/null || true

# 5. Create backup manifest
echo "5. Creating backup manifest..."
cat > "$BACKUP_PATH/manifest.json" <<EOF
{
  "timestamp": "$TIMESTAMP",
  "date": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "version": "$(git rev-parse HEAD 2>/dev/null || echo 'unknown')",
  "hostname": "$(hostname)",
  "files": {
    "database": "$(stat -f%z "$BACKUP_PATH/database.sql" 2>/dev/null || stat -c%s "$BACKUP_PATH/database.sql")",
    "models": "$(stat -f%z "$BACKUP_PATH/models.tar.gz" 2>/dev/null || stat -c%s "$BACKUP_PATH/models.tar.gz" 2>/dev/null || echo 0)",
    "uploads": "$(stat -f%z "$BACKUP_PATH/uploads.tar.gz" 2>/dev/null || stat -c%s "$BACKUP_PATH/uploads.tar.gz" 2>/dev/null || echo 0)"
  }
}
EOF

# 6. Verify backup integrity
echo "6. Verifying backup integrity..."
ERRORS=0

if [ ! -s "$BACKUP_PATH/database.sql" ]; then
    echo "✗ Database backup is empty"
    ERRORS=$((ERRORS + 1))
fi

if [ $ERRORS -eq 0 ]; then
    echo "✓ Backup verification passed"
else
    echo "✗ Backup verification failed with $ERRORS error(s)"
    exit 1
fi

# 7. Compress entire backup
echo "7. Compressing backup..."
COMPRESSED="$BACKUP_DIR/backup-$TIMESTAMP.tar.gz"
tar -czf "$COMPRESSED" -C "$BACKUP_DIR" "$(basename "$BACKUP_PATH")"
rm -rf "$BACKUP_PATH"
echo "✓ Backup compressed: $COMPRESSED"

# 8. Calculate checksum
echo "8. Creating checksum..."
sha256sum "$COMPRESSED" > "$COMPRESSED.sha256"

# 9. Cleanup old backups
echo "9. Cleaning up old backups (keeping last $RETENTION_DAYS days)..."
find "$BACKUP_DIR" -name "backup-*.tar.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -name "backup-*.tar.gz.sha256" -mtime +$RETENTION_DAYS -delete

# Summary
echo ""
echo "✓ Backup complete!"
echo "  Location: $COMPRESSED"
echo "  Size: $(du -h "$COMPRESSED" | cut -f1))"
echo ""
echo "Recent backups:"
ls -lh "$BACKUP_DIR"/backup-*.tar.gz | tail -5
```

**2. Create restore script:**

```bash
# filename: scripts/restore.sh
#!/bin/bash

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ -z "$1" ]; then
    echo "Usage: ./restore.sh <backup-file>"
    echo ""
    echo "Available backups:"
    ls -lh /backups/backup-*.tar.gz 2>/dev/null | tail -10 || echo "No backups found"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}✗ Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

# Verify checksum if available
if [ -f "$BACKUP_FILE.sha256" ]; then
    echo "Verifying backup integrity..."
    if sha256sum -c "$BACKUP_FILE.sha256" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Checksum verified${NC}"
    else
        echo -e "${RED}✗ Checksum verification failed${NC}"
        exit 1
    fi
fi

echo "=== Restoring from Backup ==="
echo "Backup: $BACKUP_FILE"
echo ""

# Show manifest if available
RESTORE_DIR="/tmp/restore-$(date +%s)"
mkdir -p "$RESTORE_DIR"
tar -xzf "$BACKUP_FILE" -C "$RESTORE_DIR"
BACKUP_CONTENTS=$(ls "$RESTORE_DIR")

if [ -f "$RESTORE_DIR/"*/manifest.json ]; then
    echo "Backup information:"
    cat "$RESTORE_DIR/"*/manifest.json | grep -E '"(timestamp|date|version)"'
    echo ""
fi

# Confirm restore
echo -e "${YELLOW}WARNING: This will overwrite current data!${NC}"
read -p "Continue with restore? (yes/no) " -r
if [[ ! $REPLY =~ ^yes$ ]]; then
    echo "Restore cancelled"
    rm -rf "$RESTORE_DIR"
    exit 0
fi

# Load environment
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# 1. Stop application
echo ""
echo "1. Stopping application..."
docker-compose stop app
echo -e "${GREEN}✓ Application stopped${NC}"

# 2. Restore database
echo ""
echo "2. Restoring database..."
docker-compose exec -T db mysql \
    -u root \
    -p$(cat secrets/db_root_password.txt) \
    smart_recommender \
    < "$RESTORE_DIR/"*/database.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database restored${NC}"
else
    echo -e "${RED}✗ Database restore failed${NC}"
    docker-compose start app
    rm -rf "$RESTORE_DIR"
    exit 1
fi

# 3. Restore models
echo ""
echo "3. Restoring ML models..."
if [ -f "$RESTORE_DIR/"*/models.tar.gz ]; then
    tar -xzf "$RESTORE_DIR/"*/models.tar.gz -C .
    echo -e "${GREEN}✓ Models restored${NC}"
else
    echo -e "${YELLOW}⚠  No models in backup${NC}"
fi

# 4. Restore files
echo ""
echo "4. Restoring user files..."
if [ -f "$RESTORE_DIR/"*/uploads.tar.gz ]; then
    tar -xzf "$RESTORE_DIR/"*/uploads.tar.gz -C storage/
    echo -e "${GREEN}✓ Files restored${NC}"
else
    echo -e "${YELLOW}⚠  No uploads in backup${NC}"
fi

# 5. Restart application
echo ""
echo "5. Restarting application..."
docker-compose start app
sleep 10

# 6. Health check
echo ""
echo "6. Verifying application health..."
MAX_ATTEMPTS=10
ATTEMPT=0

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if curl -f -s http://localhost/health > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Application is healthy${NC}"
        break
    fi
    ATTEMPT=$((ATTEMPT + 1))
    echo "  Attempt $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 3
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo -e "${RED}✗ Health check failed${NC}"
    rm -rf "$RESTORE_DIR"
    exit 1
fi

# Cleanup
rm -rf "$RESTORE_DIR"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Restore Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
```

**3. Set up automated backups with cron:**

```bash
# filename: scripts/setup-backup-cron.sh
#!/bin/bash

# Add to crontab for automated backups
CRON_JOB="0 2 * * * cd /var/www/smart-recommender && /bin/bash scripts/backup.sh >> /var/log/backups.log 2>&1"

# Install cron job
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

echo "✓ Backup cron job installed"
echo "  Schedule: Daily at 2:00 AM"
echo "  Log: /var/log/backups.log"
```

**4. Create backup testing script:**

```bash
# filename: scripts/test-backup.sh
#!/bin/bash

echo "=== Testing Backup and Restore ==="
echo ""

# Create test backup
echo "1. Creating test backup..."
./scripts/backup.sh
LATEST_BACKUP=$(ls -t /backups/backup-*.tar.gz | head -1)

if [ -z "$LATEST_BACKUP" ]; then
    echo "✗ Backup creation failed"
    exit 1
fi

echo "✓ Backup created: $LATEST_BACKUP"

# Test restore (dry run)
echo ""
echo "2. Testing restore (extraction only)..."
TEST_DIR="/tmp/backup-test-$$"
mkdir -p "$TEST_DIR"
tar -xzf "$LATEST_BACKUP" -C "$TEST_DIR"

# Verify backup contents
echo ""
echo "3. Verifying backup contents..."
ERRORS=0

if [ ! -f "$TEST_DIR/"*/database.sql ]; then
    echo "✗ Database backup missing"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ Database backup present"
fi

if [ ! -f "$TEST_DIR/"*/manifest.json ]; then
    echo "✗ Manifest missing"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ Manifest present"
fi

# Cleanup
rm -rf "$TEST_DIR"

echo ""
if [ $ERRORS -eq 0 ]; then
    echo "✓ Backup test passed"
    exit 0
else
    echo "✗ Backup test failed with $ERRORS error(s)"
    exit 1
fi
```

Make scripts executable:

```bash
chmod +x scripts/backup.sh scripts/restore.sh scripts/test-backup.sh scripts/setup-backup-cron.sh
```

### Expected Result

**Running backup:**

```bash
$ ./scripts/backup.sh

=== Backup Smart Recommender ===

1. Backing up database...
✓ Database backed up (45M)

2. Backing up ML models...
✓ Models backed up (123M)

3. Backing up user files...
✓ Files backed up (2.3G)

4. Backing up configuration...
5. Creating backup manifest...
6. Verifying backup integrity...
✓ Backup verification passed

7. Compressing backup...
✓ Backup compressed: /backups/backup-20260117-142530.tar.gz

8. Creating checksum...
9. Cleaning up old backups (keeping last 30 days)...

✓ Backup complete!
  Location: /backups/backup-20260117-142530.tar.gz
  Size: 2.4G

Recent backups:
-rw-r--r-- 1 root root 2.1G Jan 16 02:00 backup-20260116-020000.tar.gz
-rw-r--r-- 1 root root 2.3G Jan 17 02:00 backup-20260117-020000.tar.gz
-rw-r--r-- 1 root root 2.4G Jan 17 14:25 backup-20260117-142530.tar.gz
```

**Testing restore:**

```bash
$ ./scripts/test-backup.sh

=== Testing Backup and Restore ===

1. Creating test backup...
✓ Backup created: /backups/backup-20260117-142530.tar.gz

2. Testing restore (extraction only)...

3. Verifying backup contents...
✓ Database backup present
✓ Manifest present

✓ Backup test passed
```

### Why It Works

**Comprehensive backup strategy**:
- Database: mysqldump with consistent snapshot
- Models: Tar archives preserve file structure
- Uploads: User-generated content preserved
- Configuration: Environment and secrets backed up

**Safety features**:
- Checksums verify backup integrity
- Manifests document backup contents
- Test mode validates restore process
- Retention policy prevents disk overflow

**Automation**:
- Cron scheduling for hands-off backups
- Logging tracks backup history
- Failure alerts via exit codes

### Troubleshooting

**Problem: Backup fails with "disk full"**

**Cause**: Not enough space in backup directory.

**Solution**: Clean old backups or increase retention:

```bash
# Clean backups older than 7 days
find /backups -name "backup-*.tar.gz" -mtime +7 -delete

# Or mount larger volume
docker volume create --driver local \
  --opt type=none \
  --opt device=/mnt/large-disk/backups \
  --opt o=bind \
  backup-volume
```

**Problem: Database backup is empty**

**Cause**: mysqldump authentication failed.

**Solution**: Verify database credentials:

```bash
# Test database connection
docker-compose exec db mysql -u recommender_user -p$(cat secrets/db_password.txt) -e "SELECT 1"

# Check if database exists
docker-compose exec db mysql -u root -p$(cat secrets/db_root_password.txt) -e "SHOW DATABASES"
```

## Step 6: Security Hardening (~15 min)
## Load Testing and Performance Benchmarking

### Load Test Script

```bash
# filename: scripts/load-test.sh
#!/bin/bash

echo "=== Load Testing Smart Recommender ==="
echo ""

# Check if Apache Bench is installed
if ! command -v ab > /dev/null 2>&1; then
    echo "✗ Apache Bench (ab) not installed"
    echo "  Install: sudo apt-get install apache2-utils (Ubuntu/Debian)"
    echo "  Or: brew install ab (macOS)"
    exit 1
fi

# Test 1: Baseline performance
echo "1. Baseline test (10 requests)..."
ab -n 10 -c 1 http://localhost/api/recommendations/1

# Test 2: Moderate load
echo ""
echo "2. Moderate load test (1000 requests, 10 concurrent)..."
ab -n 1000 -c 10 -g results-moderate.tsv \
   http://localhost/api/recommendations/1

# Test 3: High concurrency
echo ""
echo "3. High concurrency test (5000 requests, 50 concurrent)..."
ab -n 5000 -c 50 \
   http://localhost/api/recommendations/1

# Test 4: Stress test
echo ""
echo "4. Stress test (60 second duration, 100 concurrent)..."
ab -t 60 -c 100 \
   http://localhost/api/recommendations/1

# Test 5: Different endpoints
echo ""
echo "5. Testing multiple endpoints..."
ab -n 500 -c 20 http://localhost/health
ab -n 500 -c 20 http://localhost/api/products

echo ""
echo "✓ Load testing complete"
echo ""
echo "Results saved to results-moderate.tsv"
echo "Import to your favorite graphing tool for visualization"
```

### Enhanced Prometheus Configuration

```yaml
# filename: docker/prometheus/prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s
  external_labels:
    cluster: 'production'
    environment: 'prod'

# Alertmanager configuration
alerting:
  alertmanagers:
    - static_configs:
        - targets: ['alertmanager:9093']

# Alert rules
rule_files:
  - 'alerts/*.yml'

# Scrape configurations
scrape_configs:
  # Smart Recommender application
  - job_name: 'smart-recommender'
    static_configs:
      - targets: ['app:8080']
    metrics_path: '/metrics'
    scrape_interval: 10s

  # Nginx metrics (requires nginx-prometheus-exporter)
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx-exporter:9113']

  # MySQL metrics (requires mysqld-exporter)
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql-exporter:9104']

  # Redis metrics (requires redis-exporter)
  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']

  # Node exporter (system metrics)
  - job_name: 'node'
    static_configs:
      - targets: ['node-exporter:9100']

  # Prometheus itself
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']
```

### Enhanced Alert Rules

```yaml
# filename: docker/prometheus/alerts/critical.yml
groups:
  - name: critical_alerts
    interval: 30s
    rules:
      - alert: ServiceDown
        expr: up == 0
        for: 2m
        labels:
          severity: critical
          team: ops
        annotations:
          summary: "Service {{ $labels.job }} is down"
          description: "{{ $labels.instance }} has been down for more than 2 minutes"

      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.05
        for: 5m
        labels:
          severity: critical
          team: backend
        annotations:
          summary: "High 5xx error rate"
          description: "Error rate is {{ $value | humanizePercentage }} on {{ $labels.instance }}"

      - alert: DatabaseConnectionPoolExhausted
        expr: mysql_global_status_threads_connected / mysql_global_variables_max_connections > 0.9
        for: 2m
        labels:
          severity: critical
        annotations:
          summary: "Database connection pool nearly exhausted"
          description: "{{ $value | humanizePercentage }} of connections in use"

      - alert: DiskSpaceCritical
        expr: (node_filesystem_avail_bytes / node_filesystem_size_bytes) < 0.1
        for: 5m
        labels:
          severity: critical
        annotations:
          summary: "Disk space critical"
          description: "Only {{ $value | humanizePercentage }} disk space remaining"

      - alert: HighMemoryUsage
        expr: (node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes) / node_memory_MemTotal_bytes > 0.9
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High memory usage"
          description: "Memory usage is {{ $value | humanizePercentage }}"

      - alert: ModelAccuracyDegraded
        expr: model_accuracy < 0.70
        for: 15m
        labels:
          severity: warning
          team: data-science
        annotations:
          summary: "Model accuracy degraded"
          description: "Model accuracy is {{ $value }}, below 0.70 threshold"
```

### Grafana Dashboard

```json
# filename: docker/grafana/dashboards/smart-recommender.json
{
  "dashboard": {
    "title": "Smart Recommender - Production Metrics",
    "tags": ["production", "recommender", "ml"],
    "timezone": "UTC",
    "panels": [
      {
        "id": 1,
        "title": "Recommendation Request Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(recommender_requests_total[5m])",
            "legendFormat": "Requests/sec",
            "refId": "A"
          }
        ],
        "gridPos": {"h": 8, "w": 12, "x": 0, "y": 0}
      },
      {
        "id": 2,
        "title": "Model Accuracy",
        "type": "gauge",
        "targets": [
          {
            "expr": "model_accuracy",
            "legendFormat": "Accuracy",
            "refId": "A"
          }
        ],
        "fieldConfig": {
          "defaults": {
            "thresholds": {
              "steps": [
                {"value": 0.0, "color": "red"},
                {"value": 0.7, "color": "yellow"},
                {"value": 0.85, "color": "green"}
              ]
            },
            "min": 0,
            "max": 1
          }
        },
        "gridPos": {"h": 8, "w": 6, "x": 12, "y": 0}
      },
      {
        "id": 3,
        "title": "Response Time (p95)",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "p95 latency",
            "refId": "A"
          },
          {
            "expr": "histogram_quantile(0.50, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "p50 latency",
            "refId": "B"
          }
        ],
        "gridPos": {"h": 8, "w": 12, "x": 0, "y": 8}
      },
      {
        "id": 4,
        "title": "Error Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total{status=~\"5..\"}[5m])",
            "legendFormat": "5xx errors/sec",
            "refId": "A"
          },
          {
            "expr": "rate(http_requests_total{status=~\"4..\"}[5m])",
            "legendFormat": "4xx errors/sec",
            "refId": "B"
          }
        ],
        "gridPos": {"h": 8, "w": 12, "x": 12, "y": 8}
      },
      {
        "id": 5,
        "title": "Cache Hit Ratio",
        "type": "stat",
        "targets": [
          {
            "expr": "recommender_cache_hit_ratio",
            "legendFormat": "Hit ratio",
            "refId": "A"
          }
        ],
        "fieldConfig": {
          "defaults": {
            "thresholds": {
              "steps": [
                {"value": 0.0, "color": "red"},
                {"value": 0.5, "color": "yellow"},
                {"value": 0.8, "color": "green"}
              ]
            },
            "unit": "percentunit"
          }
        },
        "gridPos": {"h": 4, "w": 6, "x": 0, "y": 16}
      },
      {
        "id": 6,
        "title": "Top Recommended Products",
        "type": "table",
        "targets": [
          {
            "expr": "topk(10, recommender_product_recommendations_total)",
            "legendFormat": "{{product_id}}",
            "refId": "A",
            "format": "table"
          }
        ],
        "gridPos": {"h": 8, "w": 12, "x": 6, "y": 16}
      }
    ],
    "refresh": "30s",
    "schemaVersion": 27,
    "version": 1
  }
}
```

## Exercises

### Exercise 1: Deploy Your Application

**Objective**: Complete a full production deployment.

**Steps**:
1. Clone the smart recommender repository
2. Run `./scripts/init-secrets.sh` to create secrets
3. Build Docker images: `docker-compose build`
4. Start all services: `docker-compose up -d`
5. Verify health: `curl http://localhost/health`
6. Access Grafana at http://localhost:3000

**Expected outcome**: All services running and healthy.

---

### Exercise 2: Test Zero-Downtime Deployment

**Objective**: Practice rolling deployments without downtime.

**Steps**:
1. Start load test in background: `./scripts/load-test.sh &`
2. Make a code change (update version number)
3. Run deployment: `./deploy.sh`
4. Monitor for any dropped requests
5. Verify new version deployed

**Expected outcome**: No 5xx errors during deployment.

---

### Exercise 3: Trigger and Respond to Alert

**Objective**: Practice incident response.

**Steps**:
1. Configure alert to fire on high error rate
2. Introduce a bug that causes 50% error rate
3. Wait for alert to fire
4. Investigate using logs and metrics
5. Fix the bug and verify alert clears

**Expected outcome**: Alert fires within 5 minutes, clears after fix.

---

### Exercise 4: Backup and Restore

**Objective**: Validate backup/recovery procedures.

**Steps**:
1. Take a full backup: `./scripts/backup.sh`
2. Make some database changes (add test data)
3. Restore from backup: `./scripts/restore.sh <backup-file>`
4. Verify original data restored

**Expected outcome**: System restored to exact state at backup time.

---

### Exercise 5: Performance Optimization

**Objective**: Improve system performance.

**Steps**:
1. Run baseline load test and record metrics
2. Enable OPcache and tune PHP-FPM
3. Add database indexes
4. Run load test again
5. Compare before/after metrics

**Expected outcome**: >50% improvement in response time.

---

### Exercise 6: Security Audit

**Objective**: Identify and fix security issues.

**Steps**:
1. Run `./scripts/security-audit.sh`
2. Fix any failures (SSL, secrets, permissions)
3. Run audit again until it passes
4. Document security posture

**Expected outcome**: Security audit passes with no critical issues.

## Wrap-up

### What You've Built

In this chapter, you created a complete production deployment system:

**Infrastructure**:
- ✅ Docker containerization for portability
- ✅ Docker Compose multi-service orchestration
- ✅ Nginx reverse proxy with SSL/TLS
- ✅ Database with proper configuration
- ✅ Redis caching layer

**Deployment**:
- ✅ Zero-downtime deployment strategy
- ✅ Automated CI/CD pipeline
- ✅ Rollback procedures
- ✅ Health checks and readiness probes

**Monitoring**:
- ✅ Prometheus metrics collection
- ✅ Grafana dashboards
- ✅ Alert rules for critical issues
- ✅ Health check endpoints

**Operations**:
- ✅ Automated backup system
- ✅ Tested restore procedures
- ✅ Security hardening
- ✅ Performance optimization

### Key Takeaways

**1. Production is different from development**: Security, performance, and reliability matter more than convenience.

**2. Automation prevents errors**: Automated deployments and backups reduce human mistakes.

**3. Observability is essential**: You can't fix what you can't see. Monitoring and logging are critical.

**4. Plan for failure**: Backups, rollbacks, and disaster recovery aren't optional.

**5. Security is ongoing**: Regular audits and updates keep systems secure.

**6. Performance requires tuning**: Default configurations rarely optimal for production.

### Production Checklist

Before going live, verify:

- [ ] All services containerized and tested
- [ ] CI/CD pipeline running successfully
- [ ] SSL/TLS certificates installed and valid
- [ ] Secrets properly managed (not in code)
- [ ] Monitoring and alerting configured
- [ ] Backup automation tested
- [ ] Restore procedure validated
- [ ] Security audit passed
- [ ] Load testing completed
- [ ] Documentation updated
- [ ] Runbook created for common issues
- [ ] On-call rotation established

### Next Steps

**Immediate (Week 1)**:
- Deploy to staging environment
- Run load tests
- Fix any performance bottlenecks
- Validate backup/restore

**Short-term (Month 1)**:
- Deploy to production
- Monitor closely for issues
- Tune performance based on real traffic
- Establish SLAs

**Medium-term (Quarter 1)**:
- Set up log aggregation (ELK stack)
- Implement distributed tracing
- Add automated scaling
- Create disaster recovery plan

**Long-term**:
- Migrate to Kubernetes for orchestration
- Implement blue-green deployments
- Add chaos engineering practices
- Build self-healing systems

### Further Reading

**Books**:
- *Site Reliability Engineering* (Google) - SRE best practices
- *The DevOps Handbook* - DevOps culture and practices
- *Release It!* (Nygard) - Design patterns for production systems

**Documentation**:
- Docker: https://docs.docker.com/
- Prometheus: https://prometheus.io/docs/
- Grafana: https://grafana.com/docs/
- Nginx: https://nginx.org/en/docs/

**Tools to explore**:
- Kubernetes - Container orchestration
- Terraform - Infrastructure as code
- Ansible - Configuration management
- Datadog/New Relic - APM platforms

### Series Completion

**Congratulations!** You've completed the Data Science for PHP Developers series. You now know how to:

- ✅ Set up PHP environments for data science
- ✅ Work with data using PHP
- ✅ Apply statistical analysis
- ✅ Build machine learning models
- ✅ Create recommendation systems
- ✅ Integrate with AI APIs
- ✅ Build real-world projects
- ✅ Deploy to production

You're now equipped to build, deploy, and maintain data science systems in PHP. The skills you've learned apply to any web application, not just data science projects.

**Keep learning, keep building, and share what you create!**

---

## Additional Resources

### Project Repository

Find all code examples, scripts, and configurations at:
- GitHub: [dalehurley/smart-recommender](https://github.com/example/smart-recommender)

### Community

Join other PHP data science developers:
- Discord: [PHP Data Science Community]
- Forum: [discuss.codewithphp.com]

### Support

Questions or issues?
- Documentation: [docs.codewithphp.com]
- Email: support@codewithphp.com

---

**You made it!** Time to deploy your own data science systems to production. 🚀
