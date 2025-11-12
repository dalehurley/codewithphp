# Chapter 24: Deploying and Scaling AI-Powered PHP Services

Complete code examples for deploying and scaling AI/ML services in production.

## Overview

This directory contains all the code, configuration files, and scripts needed to deploy a production-ready AI-powered PHP service with Docker, Redis job queues, load balancing, monitoring, and CI/CD.

## Prerequisites

- PHP 8.4+ with extensions: redis, pcntl, sockets
- Docker 20+ and Docker Compose
- Redis 7+
- Git
- Cloud server account (DigitalOcean, AWS, etc.)

## Quick Start

### Local Development

```bash
# 1. Clone and navigate to chapter directory
cd docs/series/ai-ml-php-developers/code/chapter-24

# 2. Copy environment file
cp env.example .env

# 3. Start services
docker compose up -d

# 4. Check service health
curl http://localhost/health

# 5. Queue a test job
php 02-job-queue-system.php

# 6. View worker logs
docker compose logs -f worker
```

## Directory Structure

```
chapter-24/
├── README.md                       # This file
├── composer.json                   # PHP dependencies
├── Dockerfile                      # Multi-stage Docker build
├── docker-compose.yml              # Development stack
├── docker-compose.prod.yml         # Production overrides
├── .dockerignore                   # Files to exclude from Docker build
├── env.example                     # Environment variables template
├── .env.php                        # Environment loader utility
│
├── Application Code
├── 01-simple-docker-test.php       # Verify Docker setup
├── 02-job-queue-system.php         # Job and Queue classes
├── 03-ml-worker.php                # Worker daemon process
├── 04-api-endpoint.php             # REST API for predictions
├── 05-caching-layer.php            # Redis caching implementation
├── 06-health-check.php             # Health monitoring endpoint
├── 07-metrics-collector.php        # Performance metrics
│
├── nginx/                          # Nginx configuration
│   ├── default.conf                # Reverse proxy config
│   └── load-balancer.conf          # Load balancer setup
│
├── supervisor/                     # Process supervision
│   └── worker.conf                 # Supervisor config for workers
│
├── scripts/                        # Deployment and utility scripts
│   ├── deploy.sh                   # Main deployment script
│   └── scale-workers.sh            # Worker scaling script
│
├── monitoring/                     # Monitoring and dashboards
│   ├── dashboard.php               # Simple metrics dashboard
│   └── logger.php                  # Centralized logging
│
├── .github/workflows/              # CI/CD pipelines
│   └── deploy.yml                  # GitHub Actions workflow
│
├── solutions/                      # Exercise solutions
│   ├── exercise1-autoscale.php     # Auto-scaling implementation
│   ├── exercise2-blue-green.sh     # Blue-green deployment
│   ├── exercise3-health-check.php  # Advanced health checks
│   └── exercise4-optimized.Dockerfile  # Optimized Docker image
│
└── data/                           # Sample data
    └── sample-model.onnx           # Small test model (placeholder)
```

## Running the Examples

### Example 1: Docker Setup

```bash
# Test Docker configuration
docker build -t ai-ml-service:latest .
docker run --rm ai-ml-service:latest php 01-simple-docker-test.php
```

### Example 2: Job Queue System

```bash
# Start Redis
docker compose up -d redis

# Queue jobs
php 02-job-queue-system.php

# Check Redis
docker compose exec redis redis-cli LLEN ml:jobs
```

### Example 3: Worker Process

```bash
# Start worker
docker compose up -d worker

# View logs
docker compose logs -f worker

# Queue some jobs to see worker process them
php 02-job-queue-system.php
```

### Example 4: API Endpoint

```bash
# Start all services
docker compose up -d

# Make prediction request
curl -X POST http://localhost/api/predict \
  -H "Content-Type: application/json" \
  -d '{"features": [1.5, 2.3, 4.1], "type": "classification"}'

# Check job was queued
docker compose exec redis redis-cli LLEN ml:jobs
```

### Example 5: Caching Layer

```bash
# Run caching demo
php 05-caching-layer.php

# Check cache stats
docker compose exec redis redis-cli GET metrics:cache:hits
```

### Example 6: Health Check

```bash
# Access health endpoint
curl http://localhost/health | jq

# Or open in browser
open http://localhost/health
```

### Example 7: Metrics Dashboard

```bash
# Access monitoring dashboard
open http://localhost/monitoring/dashboard.php

# View metrics via curl
curl http://localhost/metrics | jq
```

## Production Deployment

### 1. Prepare Server

```bash
# SSH to your server
ssh root@your-server-ip

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
apt update && apt install -y docker-compose-plugin

# Clone repository
git clone https://github.com/yourusername/ai-ml-service.git
cd ai-ml-service/docs/series/ai-ml-php-developers/code/chapter-24
```

### 2. Configure Environment

```bash
# Copy and edit environment file
cp env.example .env.production

# Edit with your values
nano .env.production

# Generate strong Redis password
openssl rand -base64 32
```

### 3. Deploy

```bash
# Make deploy script executable
chmod +x scripts/deploy.sh

# Run deployment
./scripts/deploy.sh

# Verify deployment
curl http://localhost/health
```

### 4. Configure SSL (Optional)

```bash
# Install Certbot
apt install -y certbot

# Get SSL certificate
certbot certonly --standalone -d yourdomain.com

# Update nginx config to enable HTTPS section
# Then redeploy
./scripts/deploy.sh
```

## Scaling

### Horizontal Scaling

```bash
# Scale workers
docker compose up -d --scale worker=4

# Scale application instances (requires load balancer adjustment)
docker compose up -d --scale app=3

# Verify scaling
docker compose ps
```

### Vertical Scaling

```yaml
# Edit docker-compose.yml or docker-compose.prod.yml
services:
  worker:
    deploy:
      resources:
        limits:
          cpus: "2.0"
          memory: 4G
        reservations:
          cpus: "1.0"
          memory: 2G
```

## Monitoring

### View Logs

```bash
# All services
docker compose logs

# Specific service
docker compose logs worker

# Follow logs
docker compose logs -f --tail=100 worker

# Search logs
docker compose logs | grep ERROR
```

### Check Metrics

```bash
# Health status
curl http://localhost/health | jq

# Queue depth
docker compose exec redis redis-cli LLEN ml:jobs

# Worker metrics
docker compose exec redis redis-cli GET metrics:worker:worker-1

# Cache stats
docker compose exec redis redis-cli GET metrics:cache:hits
```

### Resource Usage

```bash
# Docker stats
docker stats

# Container-specific
docker stats ai-ml-service-worker-1
```

## Troubleshooting

### Common Issues

#### 1. Redis Connection Failed

```bash
# Check Redis is running
docker compose ps redis

# Test connection
docker compose exec app php -r "\$r = new Redis(); \$r->connect('redis', 6379); echo 'OK';"

# Check network
docker network inspect chapter-24_default
```

#### 2. Worker Not Processing Jobs

```bash
# Check worker logs
docker compose logs worker

# Manually process a job
docker compose exec app php 03-ml-worker.php

# Check queue has jobs
docker compose exec redis redis-cli LLEN ml:jobs
```

#### 3. Health Check Fails

```bash
# Test endpoint directly
curl -v http://localhost/health

# Check application logs
docker compose logs app

# Verify all services running
docker compose ps
```

#### 4. High Memory Usage

```bash
# Check memory usage
docker stats

# Restart worker (clears memory)
docker compose restart worker

# Adjust memory limits in docker-compose.yml
```

### Reset Everything

```bash
# Stop all containers
docker compose down

# Remove volumes (clears Redis data)
docker compose down -v

# Remove images
docker compose down --rmi all

# Start fresh
docker compose up -d
```

## Testing

### Run Syntax Checks

```bash
# Check all PHP files
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
```

### Load Testing

```bash
# Install Apache Bench (ab)
apt install apache2-utils

# Load test prediction endpoint
ab -n 1000 -c 10 -T application/json \
   -p request.json \
   http://localhost/api/predict

# Monitor while testing
watch -n 1 'curl -s http://localhost/health | jq'
```

### Test Auto-Scaling

```bash
# Queue many jobs
php -r "
\$redis = new Redis();
\$redis->connect('localhost', 6379);
for (\$i = 0; \$i < 100; \$i++) {
    \$redis->lPush('ml:jobs', json_encode(['id' => \$i]));
}
echo 'Queued 100 jobs\n';
"

# Watch auto-scaler (from solutions)
php solutions/exercise1-autoscale.php

# Monitor scaling
watch -n 1 'docker compose ps worker'
```

## CI/CD Setup

### GitHub Actions

1. **Add repository secrets** (Settings → Secrets and variables → Actions):

   - `DOCKER_USERNAME` - Docker Hub username
   - `DOCKER_PASSWORD` - Docker Hub access token
   - `DEPLOY_HOST` - Server IP/hostname
   - `DEPLOY_USER` - SSH username
   - `DEPLOY_SSH_KEY` - Private SSH key

2. **Configure workflow**: `.github/workflows/deploy.yml` is already set up

3. **Test deployment**:

   ```bash
   git add .
   git commit -m "Test CI/CD"
   git push origin main
   ```

4. **Monitor**: Visit https://github.com/yourusername/repo/actions

## Performance Optimization

### Docker Image Size

```bash
# Check current size
docker images ai-ml-service

# Use optimized Dockerfile
docker build -f solutions/exercise4-optimized.Dockerfile -t ai-ml-service:optimized .

# Compare
docker images | grep ai-ml-service
```

### Caching Optimization

- Adjust TTL in `05-caching-layer.php`
- Monitor hit rate: `curl http://localhost/health | jq '.cache.hit_rate'`
- Target 70%+ hit rate for optimal performance

### Worker Tuning

- Monitor queue depth: Should stay < 20 under normal load
- Scale workers if consistently > 50
- Tune `processRetries()` interval based on retry rate

## Security Best Practices

1. **Never commit secrets**: Use `.env` files (git ignored)
2. **Use strong Redis password**: Generate with `openssl rand -base64 32`
3. **Restrict Redis access**: Only bind to localhost in production
4. **Enable SSL/TLS**: Use Let's Encrypt for free certificates
5. **Keep images updated**: Regularly rebuild with latest security patches
6. **Limit container resources**: Prevent resource exhaustion attacks
7. **Use read-only filesystems** where possible
8. **Scan images**: Use `docker scan` or security scanning tools

## Cost Optimization

### Cloud Provider Costs (Estimated Monthly)

**Minimal Setup** ($15-25/month):

- 1x $12 Droplet (2GB RAM, 1 CPU) - DigitalOcean
- 2 workers, 1 app instance
- Handles ~500 predictions/minute

**Production Setup** ($50-100/month):

- 1x $40 Droplet (4GB RAM, 2 CPU)
- 4-6 workers, 2 app instances
- Load balancer
- Handles ~2000 predictions/minute

**Enterprise Setup** ($200+/month):

- Multiple servers, managed Kubernetes
- Auto-scaling, high availability
- Handles 10,000+ predictions/minute

### Reduce Costs

1. **Optimize caching**: Reduce ML inference costs
2. **Use spot instances**: Save 60-90% on cloud compute
3. **Right-size containers**: Don't over-provision resources
4. **Schedule scaling**: Scale down during off-peak hours
5. **Monitor usage**: Track and optimize expensive operations

## Support and Resources

- **Documentation**: See main chapter in docs/series/ai-ml-php-developers/
- **Issues**: Report bugs or ask questions on GitHub
- **Docker Docs**: https://docs.docker.com/
- **Redis Docs**: https://redis.io/documentation
- **Nginx Docs**: https://nginx.org/en/docs/

## License

This code is part of the "Code with PHP" tutorial series and follows the project's license terms.

